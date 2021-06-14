<?php

namespace App\Http\Controllers\Client;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Library\Tools\Common\Common;
use App\Library\Tools\Redis\RedisTool;
use App\Repositories\Client\DataCarRepository as CarInfo;
use App\Repositories\Client\DataUserRepository as UserInfo;
use App\Repositories\Client\DataItemRepository as ItemInfo;

class CarController extends Controller
{
    protected $redis;
    protected $info;
    protected $order;
    protected $userinfo;
    protected $logCarInfo;
    protected $iteminfo;

    public function __construct(RedisTool $redisTool, CarInfo $carinfo, UserInfo $userinfo, ItemInfo $iteminfo)
    {
        $this->redis = $redisTool;
        $this->info = $carinfo;
        // $this->serie = $serieinfo;
        $this->userinfo = $userinfo;
        $this->iteminfo = $iteminfo;
    }

    /**
     * 添加购物车
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function addCart(Request $request)
    {
        // $params = json_decode($request['param'], true);
        $params = $request;
        $findData = $this->info->getOneData(['user_guid' => $params['user_guid'], 'item_guid' => $params['item_guid']]);
        $itemData = $this->iteminfo->getOneData(['guid' => $params['item_guid']]);
        if ($findData) {
            if (($findData->quantity + $params['quantity']) <= $itemData->remain) {
                $flag = $this->info->updateData(
                    ['user_guid' => $params['user_guid'], 'item_guid' => $params['item_guid']],
                    ['quantity' => ($findData->quantity + $params['quantity']), 'add_time' => time()]
                );
                return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => '添加成功']);
            } else {
                return response()->json(['ServerTime' => time(), 'code' => 400, 'data' => '购物车总量超过库存']);
            }
        } else {
            if ($params['quantity'] <= $itemData->remain) {
                $data = [
                    'user_guid' => $params['user_guid'],
                    'item_guid' => $params['item_guid'],
                    'quantity' => $params['quantity'],
                    'add_time' => time()
                ];
                $flag = $this->info->insertData($data);
                return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => '添加成功']);
            } else {
                return response()->json(['ServerTime' => time(), 'code' => 400, 'data' => '余量不足']);
            }
        }
    }

    /**
     * 获取购物车
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function findCart(Request $request)
    {
        $params = $request;
        $list = $this->info->getAllData(['user_guid' => $params['user_guid']]);
        foreach ($list as $key => $value) {
            $item_guid = $list[$key]->item_guid;
            $listinfo = app('db')->table('data_item')->where(['guid' => $item_guid])->first();
            $list[$key]->info = $listinfo;
        }
        if ($list) {
            return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => $list]);
        } else {
            return response()->json(['ServerTime' => time(), 'code' => 400, 'data' => 'guid有误']);
        }
    }

    /**
     * 购物车 增加 数量 
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function cartNum(Request $request)
    {
        $params = $request;
        $itemData = $this->iteminfo->getOneData(['guid' => $params['item_guid']]);
        if ($params['quantity'] <= $itemData->remain) {
            $flag = $this->info->updateData(
                ['user_guid' => $params['user_guid'], 'item_guid' => $params['item_guid']],
                ['quantity' => $params['quantity']]
            );
            return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => '成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'code' => 400, 'data' => '库存不足']);
        }
    }



    /**
     * 购物车 删除商品
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function deleteCart(Request $request)
    {
        $params = $request;
        $flag = $this->info->delete(['user_guid' => $params['user_guid'], 'item_guid' => $params['item_guid']]);
        if ($flag) {
            return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => '删除成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'code' => 400, 'data' => '删除失败']);
        }
    }
}
