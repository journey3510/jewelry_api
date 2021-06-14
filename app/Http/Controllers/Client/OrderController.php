<?php

namespace App\Http\Controllers\Client;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Library\Tools\Common\Common;
use App\Library\Tools\Redis\RedisTool;
use App\Repositories\Client\DataOrderRepository as OrderInfo;
use App\Repositories\Client\DataItemRepository as ItemInfo;
use App\Repositories\Client\DataCarRepository as CarInfo;
use App\Repositories\Admin\DataSerieRepository as Serieinfo;
use App\Repositories\Admin\DataUserRepository as Userinfo;



class OrderController extends Controller
{
    protected $redis;
    protected $info;
    protected $iteminfo;
    protected $serieinfo;
    protected $userinfo;

    public function __construct(RedisTool $redisTool, OrderInfo $orderinfo, ItemInfo $iteminfo, CarInfo $carInfo, Serieinfo $serieinfo, Userinfo $userinfo)
    {
        $this->redis = $redisTool;
        $this->info = $orderinfo;
        $this->iteminfo = $iteminfo;
        $this->carinfo = $carInfo;
        $this->serieinfo = $serieinfo;
        $this->userinfo = $userinfo;
    }

    /**
     * 信息列表
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function findOrderList(Request $request)
    {
        // $params = json_decode($request['param'], true);
        $params = $request;
        $nowPage = $params['pageNumber'];
        $offset = $params['pageSize'];
        if ($params['status'] > 4) {
            $where = ['user_guid' => $params['user_guid']];
        } else {
            $where = ['status' => $params['status'], 'user_guid' => $params['user_guid']];
        }
        $list = $this->info->getPageData($nowPage, $offset, $where, 'id', 'desc');

        if (count($list) > 0) {
            return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => $list]);
        } else {
            return response()->json(['ServerTime' => time(), 'code' => 200, 'ResultData' => []]);
        }
    }

    public function createOrder(Request $request)
    {
        $params = $request;
        $item_list = json_decode($params['list']);
        foreach ($item_list as $key => $value) {
            $iteminfo =  $this->iteminfo->getOneData(['guid' => $value->item_guid]);
            $item_list[$key]->item_info = $iteminfo;
            $serieinfo = $this->serieinfo->getOneData(['guid' => $iteminfo->serie_guid]);
            $item_list[$key]->item_info->serie_guid = $iteminfo->serie_guid;
            $item_list[$key]->item_info->serie_name = $serieinfo->name;
        }
        $order_num = date('YmdHis', time());
        $hint = '';
        $money = 0;
        app('db')->beginTransaction();
        try {
            foreach ($item_list as $key => $value) {
                // 删除购物车 商品
                $this->carinfo->delete([
                    'user_guid' => $params['user_guid'],
                    'item_guid' => $value->item_guid
                ]);

                $iteminfo = $this->iteminfo->getOneData(['guid' => $value->item_guid]);

                if ($iteminfo->remain > $value->quantity) {
                    // 减少余量
                    $this->iteminfo->decrementWhere(
                        ['guid' => $value->item_guid],
                        $value->quantity,
                        'remain'
                    );
                    $money += $iteminfo->price;
                } else {
                    array_splice($item_list, 1, 1);
                    $hint = '存在余量不足';
                }
            }
            if (count($item_list) > 0) {
                // 生成订单信息
                $userinfo = $this->userinfo->getOneData(['guid' => $params['user_guid']]);
                $receiptinfo = json_decode($userinfo->address);

                $flag = $this->info->addData([
                    'order_num' => $order_num,
                    'user_guid' => $params['user_guid'],
                    'money' => $params['money'],
                    'total_num' => $params['total_num'],
                    'order_time' => time(),
                    'status' => 1,
                    'orderinfo' => json_encode($item_list),
                    'receiptname' => $receiptinfo[0]->name,
                    'address' => $receiptinfo[0]->address,
                    'phone' => $receiptinfo[0]->tel

                ]);
            }

            app('db')->commit();
            return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => $order_num, 'hint' => $hint]);
        } catch (\Exception $e) {
            app('log')->error($e->getMessage());
            app('db')->rollBack();
            return response()->json(['ServerTime' => time(), 'code' => 400, 'data' => '创建失败', 'hint' => $hint]);
        }
    }


    /**
     * 订单支付
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function orderPay(Request $request)
    {
        $params = $request;
        $where = ['order_num' => $params['order_num']];
        app('db')->beginTransaction();
        try {
            $this->info->updateData($where, ['status' => 2, 'pay_time' => time()]);
            app('db')->commit();
            return response()->json(['ServerTime' => time(), 'code' => 200, 'ResultData' => '成功']);
        } catch (\Exception $e) {
            app('log')->error($e->getMessage());
            app('db')->rollBack();
            return response()->json(['ServerTime' => time(), 'code' => 400, 'ResultData' => 'guid有误']);
        }
    }



    /**
     * 订单确定
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function orderSure(Request $request)
    {
        $params = $request;
        $item_list = json_decode($params['list']);
        foreach ($item_list as $key => $value) {
            $iteminfo =  $this->iteminfo->getOneData(['guid' => $value->item_guid]);
            $item_list[$key]->item_info = $iteminfo;
        }

        if ($item_list) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'data' => $item_list]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'data' => '有误']);
        }
    }

    /**
     * 订单确定收货
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function receipt(Request $request)
    {
        // $params = json_decode($request['param'], true);
        $params = $request;
        // 修改订单 状态
        $flag = $this->info->updateData(['order_num' => $params['order_num']], ['status' => 4]);
        if ($flag) {
            return response()->json(['ServerTime' => time(), 'code' => 200, 'ResultData' => '收货成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'code' => 400, 'ResultData' => '收货失败']);
        }
    }
}
