<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use App\Library\Tools\Common\Common;
use App\Library\Tools\Redis\RedisTool;
// use App\Repositories\CosRepository as Cos;
use App\Repositories\Admin\DataAdminLoginRepository as AdminLogin;
use App\Repositories\Admin\DataAdminTokenRepository as AdminToken;
use App\Repositories\Admin\DataAdminInfoRepository as AdminInfo;
use App\Repositories\Admin\DataRoleRepository as RoleInfo;
use App\Repositories\Admin\RelAdminRoleRepository as RelRoleUser;

use App\Repositories\Admin\RelRoleMenuRepository as RelRoleMenu;
use App\Repositories\Admin\RelRoleActionRepository as RelRoleAction;



class AdminController extends Controller
{
    protected $redis;
    protected $login;
    protected $loginToken;
    protected $info;
    protected $roleInfo;
    protected $relRoleUser;
    // 
    protected $relRoleMenu;
    protected $relRoleAction;
    protected $cos;


    public function __construct(RedisTool $redisTool, AdminLogin $adminLogin, AdminToken $adminToken, AdminInfo $adminInfo, RoleInfo $roleInfo, RelRoleUser $relRoleUser, RelRoleMenu $relRoleMenu, RelRoleAction $relRoleAction)
    {
        $this->redis = $redisTool;
        $this->login = $adminLogin;
        $this->loginToken = $adminToken;
        $this->info = $adminInfo;
        // 角色信息
        $this->roleInfo = $roleInfo;
        $this->relRoleUser = $relRoleUser;
        // 
        $this->relRoleMenu = $relRoleMenu;
        $this->relRoleAction = $relRoleAction;
        // $this->cos = $cos;
    }

    /**
     * 用户登录
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function login(Request $request)
    {
        $params = json_decode($request['param'], true);

        $lastTime = $_SERVER['REQUEST_TIME'];
        $lastIp = $request->getClientIp();
        //todo 验证码逻辑
        $codeParam = [
            'verifyCode' => $params['captcha'],
            'number' => $params['number']
        ];

        $checkCaptcha = $this->verifyCode($codeParam);
        if (!$checkCaptcha['status']) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => $checkCaptcha['msg']]);
        }

        // 加密密码
        $password = Common::cryptString($params['username'], $params['password']);

        // 账号密码一起验证
        $res = $this->login->getOneData(['admin_name' => $params['username'], 'password' => $password, 'status' => 1]);
        if (!empty($res)) {

            $token = Common::getUuid()->toString();
            $param = [
                'guid' => $res->guid,
                'token' => $token, //token
                'token_time' => $lastTime + 2592000, //token过期时间
            ];
            // 每次登录 更新token
            $data = $this->loginToken->updateData(['guid' => $res->guid], $param);
            // 更改登录时间和ip
            $rel = $this->login->updateData(['guid' => $res->guid], ['last_time' => $lastTime, 'last_ip' => $lastIp]);
            if (!empty($data) && !empty($rel)) {
                $this->pushToken($param);
                $res->token = $token;
                $res->last_time = $lastTime;
                $res->last_ip = $lastIp;
                return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $res]);
            }
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '数据异常']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '账户或密码错误']);
        }
    }

    /**
     * 用户信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function getInfo(Request $request)
    {

        $data = $request->all();
        $res = $this->login->getOneData(['guid' => $data['guid']]);
        $token = $this->loginToken->getOneData(['guid' => $data['guid']]);
        $index = $this->info->getOneData(['guid' => $data['guid']]);

        // 获取角色
        $role = $this->relRoleUser->getAllData(['admin_guid' => $data['guid']], '');
        $rolename = [];
        $roleTable = $this->roleInfo->getOneData(['guid' => $role[0]->role_guid]);

        if ($roleTable->status === 2) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '角色已禁用']);
        }

        // 插入角色
        array_push($rolename, $roleTable->name);

        // 获取角色拥有的菜单权限
        $menu = $this->relRoleMenu->getAllData(['role_guid' => $role[0]->role_guid], '');
        $menuAuthority = [];
        foreach ($menu as $key => $value) {
            $menu_guid = $menu[$key]->menu_guid;
            $menuinfo = app('db')->table('data_menu')->where(['guid' => $menu_guid])->first();
            $menuAuthority[$key] = $menuinfo;
        }

        // 获取角色拥有的活动权限
        $action = $this->relRoleAction->getAllData(['role_guid' => $role[0]->role_guid], '');
        $actionAuthority = [];
        foreach ($action as $key => $value) {
            $action_guid = $action[$key]->action_guid;
            $actioninfo = app('db')->table('data_action')->where(['guid' => $action_guid])->first();
            $actionAuthority[$key] = $actioninfo;
        }


        if ($res && $token && $index) {
            $res->token = $token->token;
            $res->nick_name = $index->nick_name;
            $res->avatar_img = $index->avatar_img;
            $res->roles = $rolename;
            $res->authority = ['menu' => $menuAuthority, 'action' => $actionAuthority];
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $res]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => 'guid有误']);
        }
    }

    /**
     * 退出登录
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function logout(Request $request)
    {
        // 获取所有参数
        $param = $request->all();
        $key = config('datarediskey')['tokenInfo'];
        // 获取员工存储token的RedisKey
        $redisKey = $key . $param['guid'];
        $this->redis->del($redisKey);
        return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '退出成功']);
    }


    /**
     * 验证码
     *
     * @param Request $request
     */
    public function getCheckVerifyCode(Request $request)
    {
        $phrase = new PhraseBuilder;
        // 设置验证码位数
        $code = $phrase->build(4);
        // 生成验证码图片的Builder对象，配置相应属性
        $builder = new CaptchaBuilder($code, $phrase);
        $builder->setBackgroundColor(255, 255, 255);
        $builder->setMaxBehindLines(0);
        $builder->setMaxFrontLines(0);
        $builder->build($width = 140, $height = 52, $font = null);
        $phrase = strtolower($builder->getPhrase());
        $key = config('datarediskey')['verify_code'];
        $this->redis->sEteX($key . $request['number'], 180, $phrase);
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type:image/jpeg");
        $builder->output();
    }

    /**
     * 检验验证码
     *
     * @param $verifyCode
     * @param $number
     * @return array
     */
    protected function checkverifyCode($verifyCode, $number)
    {
        //验证码验证
        $key = config('datarediskey')['verify_code']['string'];
        //验证码是否存在
        if (!$this->redis->exists($key . $number)) {
            return ['status' => false, 'msg' => '验证码已过期'];
        }
        //验证码是否正确
        $code = $this->redis->get($key . $number);
        if ($code != $verifyCode) {
            return ['status' => false, 'msg' => '验证码不正确'];
        }
        return ['status' => true, 'msg' => ''];
    }

    /**
     * 验证码验证逻辑
     *
     * @param  $params
     * @return array|false|string
     */
    private function verifyCode($params)
    {
        //验证码验证
        $key = config('datarediskey')['verify_code'];

        //验证码是否存在
        if (!$this->redis->exists($key . $params['number'])) {
            return ['status' => false, 'msg' => '验证码已过期'];
        }
        //验证码是否正确
        $code = $this->redis->get($key . $params['number']);
        $params['verifyCode'] = strtolower($params['verifyCode']);
        if ($code != $params['verifyCode']) {
            return ['status' => false, 'msg' => '验证码错误'];
        }
        unset($params['number'], $params['verifyCode']);

        return ['status' => true, 'msg' => $params];
    }

    /**
     * 登录验证完成缓存Token相关数据
     * @param $tokenMessage
     * @return bool
     */
    private function pushToken($param)
    {
        $key = config('datarediskey')['tokenInfo'];
        // 获取员工存储token的RedisKey
        $redisKey = $key . $param['guid'];
        // 重新存入的redis中
        $this->redis->hashMSet($redisKey, $param);
        return true;
    }

    /**
     * 用户信息列表
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function getInfoList(Request $request)
    {
        $params = json_decode($request['param'], true);
        $nowPage = $params['page'];
        $offset = $params['pagesize'];
        // 
        $res = $this->login->getPageData($nowPage, $offset, '', '', 'desc');
        $token = $this->loginToken->getPageData($nowPage, $offset, '', '', 'desc');
        $index = $this->info->getPageData($nowPage, $offset, '', '', 'desc');
        // 总条数
        $count = $this->login->getCount();
        if ($res && $token && $index) {
            for ($i = 0; $i < count($res); $i++) {
                $admin_guid = $res[$i]->guid;
                $relroleuser = $this->relRoleUser->getOneData(['admin_guid' => $admin_guid]);
                $res[$i]->token = $token[$i]->token;
                $res[$i]->nick_name = $index[$i]->nick_name;
                $res[$i]->avatar_img = $index[$i]->avatar_img;
                if (isset($relroleuser->role_guid)) {
                    $role_guid =  $relroleuser->role_guid;
                    $role = $this->roleInfo->getOneData(['guid' => $role_guid]);
                    $res[$i]->role_guid = $role_guid;
                    $res[$i]->rolename = $role->name;
                }
            }
            // 结果
            $ResultData = [];
            $ResultData['list'] = $res;
            $ResultData['count'] = $count;
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $ResultData]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => 'guid有误']);
        }
    }

    /**
     * 禁用用户
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function disable(Request $request)
    {

        $params = json_decode($request['param'], true);
        $res = $this->login->getOneData(['guid' => $params['guid']]);
        if ($res) {
            $this->login->updateData(['guid' => $params['guid']], ['status' => 0]);
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '禁用成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => 'guid有误']);
        }
    }

    /**
     * 启用用户
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function enable(Request $request)
    {

        $params = json_decode($request['param'], true);
        $res = $this->login->getOneData(['guid' => $params['guid']]);
        if ($res) {
            $this->login->updateData(['guid' => $params['guid']], ['status' => 1]);
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '启用成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => 'guid有误']);
        }
    }

    /**
     * 添加用户
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function addUser(Request $request)
    {

        $params = json_decode($request['param'], true);
        if ($params['avatar_img']) {
            $avatar_img = $params['avatar_img'];
        } else {
            $avatar_img = 'https://hn-lvshi.oss-cn-beijing.aliyuncs.com/admin/1607395687419';
        }
        $guid = Common::getUuid()->toString();
        $token = Common::getUuid()->toString();
        $tokenTime = $_SERVER['REQUEST_TIME'];
        // 加密密码
        $password = Common::cryptString($params['admin_name'], $params['password']);
        $res = $this->login->addData(['guid' => $guid, 'admin_name' => $params['admin_name'], 'password' => $password, 'add_time' => $tokenTime]);
        $token = $this->info->addData(['guid' => $guid, 'nick_name' => $params['nick_name'], 'avatar_img' => $avatar_img]);
        $index = $this->loginToken->addData(['guid' => $guid, 'token' => $token, 'token_time' => $tokenTime]);
        if ($res && $token && $index) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '添加成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '添加失败']);
        }
    }

    /**
     * 修改用户信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function updateUser(Request $request)
    {
        $params = json_decode($request['param'], true);
        $token = $this->info->updateData(['guid' => $params['guid']], ['nick_name' => $params['nick_name'], 'avatar_img' => $params['avatar_img']]);
        if ($token) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '修改成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '修改失败']);
        }
    }

    /**
     * 用户 选择 角色
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function selectRole(Request $request)
    {
        $params = json_decode($request['param'], true);
        $isExist = $this->relRoleUser->getOneData(['admin_guid' => $params['admin_guid']]);
        if ($isExist) {
            $this->relRoleUser->updateData(['admin_guid' => $params['admin_guid']], ['role_guid' => $params['role_guid']]);
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '选择成功']);
        }
        $flag = $this->relRoleUser->addData(['admin_guid' => $params['admin_guid'], 'role_guid' => $params['role_guid']]);
        if ($flag) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '选择成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '选择失败']);
        }
    }
}
