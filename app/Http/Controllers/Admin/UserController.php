<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Library\Tools\Common\Common;
use App\Library\Tools\Redis\RedisTool;
use App\Repositories\Admin\DataUserRepository as UserInfo;

class UserController extends Controller
{
    protected $redis;
    protected $login;
    protected $loginToken;
    protected $info;

    public function __construct(RedisTool $redisTool, UserInfo $userInfo)
    {
        $this->redis = $redisTool;
        $this->info = $userInfo;
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
        if ($res && $token && $index) {
            $res->token = $token->token;
            $res->nick_name = $index->nick_name;
            $res->avatar_img = $index->avatar_img;
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $res]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => 'guid有误']);
        }
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

        //  筛选字段参数
        $where = Common::trimParam($params);
        // 存在 筛选参数 
        if (count($where) > 0) {
            if (isset($where['add_time'])) {
                $start = ['add_time', '>', ($where['add_time'][0])];
                $end = ['add_time', '<', ($where['add_time'][1])];
                // 删除传过来的 时间（不符合要求）
                unset($where['add_time']);
                // 插入新的时间点
                array_push($where, $start, $end);
            }
            // 综合筛选 符合 $where的数据
            $list = $this->info->getPageData($nowPage, $offset, $where, 'id', 'desc');
            $count = $this->info->getCount($where);
        } else {
            // 不存在筛选参数 
            $list = $this->info->getPageData($nowPage, $offset, '', 'id', 'desc');
            $count = $this->info->getCount();
        }

        if (count($list) > 0) {
            $ResultData = [];
            $ResultData['list'] = $list;
            $ResultData['count'] = $count;
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $ResultData]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => []]);
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
        $res = $this->info->getOneData(['guid' => $params['guid']]);
        if ($res) {
            $this->info->updateData(['guid' => $params['guid']], ['status' => 2]);
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
        $res = $this->info->getOneData(['guid' => $params['guid']]);
        if ($res) {
            $this->info->updateData(['guid' => $params['guid']], ['status' => 1]);
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '启用成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => 'guid有误']);
        }
    }


    /**
     * 修改用户信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function updataUser(Request $request)
    {

        $params = json_decode($request['param'], true);
        $flag = $this->info->updateData(['guid' => $params['guid']], $params);
        if ($flag) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '修改成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '修改失败']);
        }
    }


    /**
     * 详细用户信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function details(Request $request)
    {

        $params = json_decode($request['param'], true);
        $data = $this->info->getOneData(['guid' => $params['user_guid']]);
        if ($data) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $data]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '修改失败']);
        }
    }
}
