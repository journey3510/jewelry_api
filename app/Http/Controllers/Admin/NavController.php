<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Library\Tools\Common\Common;
use App\Library\Tools\Redis\RedisTool;
use App\Repositories\Admin\DataNavRepository as NavInfo;
use App\Repositories\Admin\RelRoleMenuRepository as RelRoleMenu;

class NavController extends Controller
{
    protected $redis;
    protected $navInfo;
    protected $role;
    protected $menu;
    protected $relRoleMenu;


    public function __construct(RedisTool $redisTool, NavInfo $navInfo, RelRoleMenu $relRoleMenu)
    {
        $this->redis = $redisTool;
        $this->navInfo = $navInfo;
        $this->relRoleMenu = $relRoleMenu;
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
        $where = $params['where'];
        if ($where['id'] === '') {
            unset($where['id']);
            unset($where['pid']);
        }

        $list = $this->navInfo->getAllOrderData($where, [], '', '', 'desc');

        if (count($list) > 0) {
            $ResultData = [];
            $ResultData['list'] = $list;
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $ResultData]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => 'guid有误']);
        }
    }


    /**
     * 修改路由信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function modify(Request $request)
    {
        $params = json_decode($request['param'], true);
        $where = ['id' => $params['id']];
        $name = $params['name'];
        $data = ['name' => $name];
        $flag = $this->navInfo->updateData($where, $data);
        if ($flag) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '修改成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '修改失败']);
        }
    }

    /**
     * 添加 路由信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function add(Request $request)
    {
        $params = json_decode($request['param'], true);
        $guid = Common::getUuid()->toString();
        $time = time();
        $name = $params['name'];
        $pid = $params['pid'];
        $data = ['guid' => $guid, 'name' => $name, 'pid' => $pid, 'add_time' => $time];
        $flag = $this->navInfo->insertData($data);

        if ($flag) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '添加成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '添加失败']);
        }
    }

    /**
     * 删除 路由信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function delete(Request $request)
    {
        $params = json_decode($request['param'], true);
        $where = $params['id'];
        $flag = true;
        app('db')->beginTransaction();
        try {
            // 先删除 已赋给角色 的权限
            foreach ($where as $key => $value) {
                $oneData = $this->navInfo->getOneData(['id' => $value]);
                $menu_guid = $oneData->guid;
                $this->relRoleMenu->delete(['menu_guid' => $menu_guid]);
            }
            // 后删除 菜单数据
            $this->navInfo->deleteWhereIn($where, 'id');
            app('db')->commit();
        } catch (\Exception $e) {
            $flag = false;
            app('log')->error($e->getMessage());
            app('db')->rollBack();
        }

        if ($flag) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '删除成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '删除失败']);
        }
    }
}
