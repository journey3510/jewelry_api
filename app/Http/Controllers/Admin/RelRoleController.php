<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Library\Tools\Common\Common;
use App\Library\Tools\Redis\RedisTool;
use App\Repositories\Admin\DataRoleRepository as RoleInfo;
use App\Repositories\Admin\DataNavRepository as NavInfo;
use App\Repositories\Admin\RelRoleMenuRepository as RelRoleMenu;
// 
use App\Repositories\Admin\DataActionRepository as ActionInfo;
use App\Repositories\Admin\RelRoleActionRepository as RelRoleAction;


// 角色权限
class RelRoleController extends Controller
{
    protected $redis;
    protected $roleinfo;
    protected $relRoleMenu;
    protected $navInfo;
    protected $actionInfo;
    protected $relRoleAction;


    public function __construct(RedisTool $redisTool, RoleInfo $roleInfo, RelRoleMenu $relRoleMenu, NavInfo $navInfo, ActionInfo $actionInfo, RelRoleAction $relRoleAction)
    {
        $this->redis = $redisTool;
        $this->roleinfo = $roleInfo;
        // 菜单
        $this->relRoleMenu = $relRoleMenu;
        $this->navInfo = $navInfo;
        // 动作
        $this->actionInfo = $actionInfo;
        $this->relRoleAction = $relRoleAction;
    }

    /**
     * 管理员 权限列表 获取
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function getInfoList(Request $request)
    {
        $params = json_decode($request['param'], true);
        $menulist = $this->navInfo->getAllOrderData([], [], '', '', 'desc');
        $actionlist = $this->actionInfo->getAllOrderData([], [], '', '', 'desc');

        // 获取角色拥有的菜单权限
        $menu = $this->relRoleMenu->getAllData(['role_guid' => $params['role_guid']], '');
        $menuAuthority = [];
        foreach ($menu as $key => $value) {
            $menu_guid = $menu[$key]->menu_guid;
            $menuinfo = app('db')->table('data_menu')->where(['guid' => $menu_guid])->first();
            if ($menuinfo) {
                $menuAuthority[$key] = $menuinfo->id;
            }
        }

        // 获取角色拥有的活动权限
        $action = $this->relRoleAction->getAllData(['role_guid' => $params['role_guid']], '');
        $actionAuthority = [];
        foreach ($action as $key => $value) {
            $action_guid = $action[$key]->action_guid;
            $actioninfo = app('db')->table('data_action')->where(['guid' => $action_guid])->first();
            if ($actioninfo) {
                $actionAuthority[$key] = $actioninfo->id;
            }
        }

        if (count($menulist) > 0) {
            $ResultData = [];
            $ResultData['menulist'] = $menulist;
            $ResultData['actionlist'] = $actionlist;
            $ResultData['menu'] = $menuAuthority;
            $ResultData['action'] = $actionAuthority;
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $ResultData]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => []]);
        }
    }


    /**
     * 角色 - 菜单 关系修改
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function updata(Request $request)
    {
        $params = json_decode($request['param'], true);
        $flag = true;
        if ($params['type'] == 'add') {
            $data = ['role_guid' => $params['role_guid'], 'menu_guid' => $params['menu_guid']];
            $flag = $this->relRoleMenu->addData($data);
        }

        if ($params['type'] == 'cut') {
            $data = ['role_guid' => $params['role_guid'], 'menu_guid' => $params['menu_guid']];
            $flag = $this->relRoleMenu->delete($data);
        }
        return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '修改成功']);
    }

    /**
     * 角色 - 动作 关系修改
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function actionUpdata(Request $request)
    {
        $params = json_decode($request['param'], true);
        $flag = true;
        if ($params['type'] == 'add') {
            $data = ['role_guid' => $params['role_guid'], 'action_guid' => $params['action_guid']];
            $flag = $this->relRoleAction->addData($data);
        }

        if ($params['type'] == 'cut') {
            $data = ['role_guid' => $params['role_guid'], 'action_guid' => $params['action_guid']];
            $flag = $this->relRoleAction->delete($data);
        }
        return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '修改成功']);
    }


    /**
     * 管理员 权限列表 获取
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function roleAuthority(Request $request)
    {
        $params = json_decode($request['param'], true);
        $roleinfo = $this->roleinfo->getOneData(['name' => $params['name']]);
        $role_guid = $roleinfo->guid;

        // 获取角色拥有的菜单权限
        $menu = $this->relRoleMenu->getAllData(['role_guid' => $role_guid], '');
        $menuAuthority = [];
        foreach ($menu as $key => $value) {
            $menu_guid = $menu[$key]->menu_guid;
            $menuOne = $this->navInfo->getOneData(['guid' => $menu_guid]);
            $menuAuthority[$key] = $menuOne;
        }

        // // 获取角色拥有的活动权限
        $action = $this->relRoleAction->getAllData(['role_guid' => $role_guid], '');
        $actionAuthority = [];
        foreach ($action as $key => $value) {
            $action_guid = $action[$key]->action_guid;
            $actionOne = $this->actionInfo->getOneData(['guid' => $action_guid]);
            $actionAuthority[$key] = $actionOne;
        }

        $ResultData = [];
        $ResultData['menulist'] = $menuAuthority;
        $ResultData['actionlist'] = $actionAuthority;
        return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $ResultData]);
    }
}
