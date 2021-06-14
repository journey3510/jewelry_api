<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Library\Tools\Common\Common;
use App\Library\Tools\Redis\RedisTool;
use App\Repositories\Admin\DataCarRepository as CarInfo;
use App\Repositories\Admin\DataSerieRepository as SerieInfo;
use App\Repositories\Admin\DataUserRepository as UserInfo;
use App\Repositories\Admin\LogCarRepository as LogCarInfo;

class CarController extends Controller
{
    protected $redis;
    protected $info;
    protected $order;
    protected $userinfo;
    protected $logCarInfo;

    public function __construct(RedisTool $redisTool, CarInfo $carinfo, SerieInfo $serieinfo, UserInfo $userinfo,  LogCarInfo $logCarInfo)
    {
        $this->redis = $redisTool;
        $this->info = $carinfo;
        $this->serie = $serieinfo;
        $this->userinfo = $userinfo;
        $this->logCarInfo = $logCarInfo;
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
        $nowPage = $params['page'];
        $offset = $params['pagesize'];

        //  筛选字段参数
        $where = Common::trimParam($params);
        // 存在 筛选参数 
        if (count($where) > 0) {
            if (isset($where['user_name'])) {
                $userinfo = $this->userinfo->getOneData(['nick_name' => $where['user_name']]);
                unset($where['user_name']);
                $where['user_guid'] = $userinfo->guid;
            }

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


        foreach ($list as $key => $value) {
            $item_guid = $list[$key]->item_guid;
            $item = app('db')->table('data_item')->where(['guid' => $item_guid])->first();
            $user_guid = $list[$key]->user_guid;
            $user = app('db')->table('data_user')->where(['guid' => $user_guid])->first();
            $serie_guid = $item->serie_guid;
            $serie = app('db')->table('data_serie')->where(['guid' => $serie_guid])->first();


            $list[$key]->item_guid = $item->guid;
            $list[$key]->item_name = $item->name;
            $list[$key]->item_img = $item->item_img;
            $list[$key]->serie_name = $serie->name;
            $list[$key]->nick_name = $user->nick_name;
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
     * 获取单条信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function details(Request $request)
    {
        $params = json_decode($request['param'], true);
        $list = $this->info->getOneData(['guid' => $params['guid']]);
        $user_guid = $list->user_guid;
        $userinfo = $this->userinfo->getOneData(['guid' => $user_guid]);
        // 添加用户登录的电话号码
        $list->user_phone = $userinfo->phone;
        if ($list) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $list]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => 'guid有误']);
        }
    }
}
