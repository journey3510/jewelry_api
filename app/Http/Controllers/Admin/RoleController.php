<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Library\Tools\Common\Common;
use App\Library\Tools\Redis\RedisTool;
use App\Repositories\Admin\DataRoleRepository as RoleInfo;
use App\Repositories\Admin\RelAdminRoleRepository as RelAdminMenu;


class RoleController extends Controller
{
    protected $redis;
    protected $info;
    protected $relAdminMenu;


    public function __construct(RedisTool $redisTool, RoleInfo $roleInfo, RelAdminMenu $relAdminMenu)
    {
        $this->redis = $redisTool;
        $this->info = $roleInfo;
        $this->relAdminMenu = $relAdminMenu;
    }

    /**
     * 信息列表
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function getInfoList(Request $request)
    {
        $params = json_decode($request['param'], true);
        if (isset($params['page']) && isset($params['pagesize'])) {
            $nowPage = $params['page'];
            $offset = $params['pagesize'];
            $list = $this->info->getPageData($nowPage, $offset, '', '', 'desc');
        } else {
            //  管理员分配角色 需要看到现有 所有角色
            $list = $this->info->getAllData([], []);
        }

        // 总条数
        $count = $this->info->getCount();
        if (count($list) > 0) {
            $ResultData = [];
            $ResultData['list'] = $list;
            $ResultData['count'] = $count;
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $ResultData]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => []]);
        }
    }

    /**
     * 禁用 角色
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
     * 启用 角色
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
     * 修改角色信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function updata(Request $request)
    {
        $params = json_decode($request['param'], true);
        $isExist = $this->info->getOneData(['name' => $params['name']]);
        if ($isExist) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '已存在该角色']);
        }
        $token = $this->info->updateData(['guid' => $params['guid']], $params);
        if ($token) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '修改成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '修改失败']);
        }
    }

    /**
     * 新增角色
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function addRole(Request $request)
    {
        $params = json_decode($request['param'], true);
        $isExist = $this->info->getOneData(['name' => $params['name']]);
        if ($isExist) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '已存在该角色']);
        }
        $params['add_time'] = time();
        $guid = Common::getUuid()->toString();
        $params['guid'] = $guid;
        $flag = $this->info->insertData($params);
        if ($flag) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '新增成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '新增失败']);
        }
    }
}
