<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Library\Tools\Common\Common;
use App\Library\Tools\Redis\RedisTool;
use App\Repositories\Admin\DataOrderRepository as OrderInfo;
use App\Repositories\Admin\DataCarRepository as CarInfo;
use App\Repositories\Admin\DataSerieRepository as SerieInfo;


class OrderController extends Controller
{
    protected $redis;
    protected $info;

    public function __construct(RedisTool $redisTool, OrderInfo $orderinfo, CarInfo $carinfo)
    {
        $this->redis = $redisTool;
        $this->info = $orderinfo;
        $this->car = $carinfo;
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
            if (isset($where['order_time'])) {
                $start = ['order_time', '>', ($where['order_time'][0])];
                $end = ['order_time', '<', ($where['order_time'][1])];
                // 删除传过来的 时间（不符合要求）
                unset($where['order_time']);
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
            $user_guid = $list[$key]->user_guid;
            $user = app('db')->table('data_user')->where(['guid' => $user_guid])->first();
            $list[$key]->nick_name = $user->nick_name;
            $list[$key]->phone = $user->phone;
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
     * 订单发货
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function shipment(Request $request)
    {
        $params = json_decode($request['param'], true);
        // 修改订单 状态
        $flag = $this->info->updateData(['order_num' => $params['order_num']], ['status' => 3]);
        if ($flag) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '发货成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '发货失败']);
        }
    }

    /**
     * 获取单条  订单信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function details(Request $request)
    {
        $params = json_decode($request['param'], true);
        $list = $this->info->getOneData(['order_num' => $params['order_num']]);

        if ($list) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $list]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => 'guid有误']);
        }
    }
}
