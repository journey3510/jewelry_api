<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Library\Tools\Common\Common;
use App\Library\Tools\Redis\RedisTool;
use App\Repositories\Admin\DataActionRepository as ActionInfo;
use App\Repositories\Admin\RelRoleActionRepository as RelRoleAction;

class ActionController extends Controller
{
    protected $redis;
    protected $role;
    protected $actionInfo;
    protected $relRoleAction;

    public function __construct(RedisTool $redisTool, ActionInfo $actionInfo, RelRoleAction $relRoleAction)
    {
        $this->redis = $redisTool;
        $this->actionInfo = $actionInfo;
        $this->relRoleAction = $relRoleAction;
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

        $list = $this->actionInfo->getAllOrderData($where, [], '', '', 'desc');

        // 总条数
        $count = $this->actionInfo->getCount($where);
        if (count($list) > 0) {
            $ResultData = [];
            $ResultData['list'] = $list;
            $ResultData['count'] = $count;
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $ResultData]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => 'guid有误']);
        }
    }

    /**
     * 修改动作信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function modify(Request $request)
    {
        $params = json_decode($request['param'], true);
        $where = ['id' => $params['id']];
        $name = $params['name'];
        $pid = $params['pid'];

        if ($pid === '') {
            $title = $params['name'];
        } else {
            $info = $this->actionInfo->getOneData(['id' => $pid]);
            $title = $info->name . '-' . $params['name'];
        }
        $title = $title;
        $data = ['name' => $name, 'title' => $title];
        $flag = $this->actionInfo->updateData($where, $data);
        if ($flag) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '修改成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '修改失败']);
        }
    }

    /**
     * 添加 动作信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function add(Request $request)
    {
        $params = json_decode($request['param'], true);
        $guid = Common::getUuid()->toString();
        $time = time();
        $pid = $params['pid'];
        $name = $params['name'];
        if ($pid === 0) {
            $title = $params['name'];
        } else {
            $info = $this->actionInfo->getOneData(['id' => $pid]);
            $title = $info->name . '-' . $params['name'];
        }
        $data = ['guid' => $guid, 'name' => $name, 'title' => $title, 'pid' => $pid, 'add_time' => $time];
        $flag = $this->actionInfo->insertData($data);

        if ($flag) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '添加成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '添加失败']);
        }
    }

    /**
     * 删除 动作信息
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
                $oneData = $this->actionInfo->getOneData(['id' => $value]);
                $action_guid = $oneData->guid;
                $this->relRoleAction->delete(['action_guid' => $action_guid]);
            }
            // 后删除 动作数据
            $this->actionInfo->deleteWhereIn($where, 'id');
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
