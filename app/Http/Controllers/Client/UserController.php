<?php

namespace App\Http\Controllers\Client;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Library\Tools\Redis\RedisTool;
use App\Library\Tools\Common\Common;
use App\Repositories\Admin\DataUserRepository as UserInfo;
use App\Repositories\Client\DataUserTokenRepository as UserToken;
use App\Repositories\Client\DataCommentRepository as Comment;
use App\Repositories\Client\DataOrderRepository as OrderInfo;


class UserController extends Controller
{
    protected $redis;
    protected $login;
    protected $loginToken;
    protected $info;
    protected $commentinfo;
    protected $orderinfo;

    public function __construct(RedisTool $redisTool, UserInfo $userInfo, UserToken $loginToken, Comment $commentinfo, OrderInfo $orderinfo)
    {
        $this->redis = $redisTool;
        $this->info = $userInfo;
        $this->loginToken = $loginToken;
        $this->commentinfo = $commentinfo;
        $this->orderinfo = $orderinfo;
    }


    //  用户登录 
    public function login(Request $request)
    {
        $data = $request->all();
        $password = $data['password'];
        $res = $this->info->getOneData(['phone' => $data['phone'], 'status' => 1]);
        if ($res && $password === $res->password) {
            $token = Common::getUuid()->toString();
            $param = [
                'guid' => $res->guid,
                'token' => $token, //token
            ];
            // 每次登录 更新token
            $data = $this->loginToken->updateData(['guid' => $res->guid], $param);
            $res->token = $token;
            return response()->json(['ServerTime' => time(), 'code' => 200, 'ResultData' => $res]);
        } else {
            return response()->json(['ServerTime' => time(), 'code' => 400, 'ResultData' => '登录失败']);
        }
    }



    //  用户注册 
    public function regiter(Request $request)
    {
        $data = $request->all();
        $res = $this->info->getOneData(['nick_name' => $data['name']]);
        $password = $data['password'];
        if ($password === $res->password) {
            // $res->token = $token->token;
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $res]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '登录失败']);
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
     * 用户注册
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function addUser(Request $request)
    {

        $params = $request;
        $data = [];
        $data['nick_name'] = $params['nick_name'];
        $data['password'] = $params['password'];
        $data['phone'] = $params['phone'];
        $data['guid'] =  Common::getUuid()->toString();
        $data['add_time'] = time();
        $data['avatar_img'] = 'http://api.journey3510.ltd/admin/132.jpg';
        $data['address'] = '[]';
        app('db')->beginTransaction();
        try {
            $flag = $this->info->insertData($data);
            app('db')->commit();
        } catch (\Exception $e) {
            app('log')->error($e->getMessage());
            app('db')->rollBack();
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '注册失败']);
        }
        if ($flag) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '注册成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '注册失败']);
        }
    }


    /**
     * 用户地址
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function getAddress(Request $request)
    {
        $params = $request;
        // json_decode($request['param'], true);
        $params['guid'] =  Common::getUuid()->toString();
        $userInfo =  $this->info->getOneData(['guid' => $params['user_guid']]);
        $address = json_decode($userInfo->address);
        if ($address === '' ||  $address === null) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'data' => []]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'data' => $address]);
        }
    }


    /**
     * 添加 地址
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function addAddress(Request $request)
    {

        $params = $request->all();
        $params['address'] = $params['city'] . $params['province'] . $params['county'] . ' ' . $params['addressDetail'];
        $user_guid = $params['user_guid'];
        $userInfo =  $this->info->getOneData(['guid' => $user_guid]);
        $oldAddress = $userInfo->address;
        if ($oldAddress === '' ||  $oldAddress === null) {
            $theAddress = [];
            array_push($theAddress, $params);
            $flag = $this->info->updateData(['guid' => $user_guid], ['address' => json_encode($theAddress)]);
        } else {
            $theAddress = json_decode($oldAddress);
            array_push($theAddress, $params);
            $flag = $this->info->updateData(['guid' => $user_guid], ['address' => json_encode($theAddress)]);
        }
        if ($flag) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '失败']);
        }
    }



    /**
     * 添加 地址
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function updataAddress(Request $request)
    {

        $params = $request->all();
        $params['address'] = $params['city'] . $params['province'] . $params['county'] . ' ' . $params['addressDetail'];
        $user_guid = $params['user_guid'];

        $theAddress = [];
        array_push($theAddress, $params);
        $flag = $this->info->updateData(['guid' => $user_guid], ['address' => json_encode($theAddress)]);

        if ($flag) {
            $info = $this->info->getOneData(['guid' => $user_guid]);

            return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => $info->address]);
        } else {
            return response()->json(['ServerTime' => time(), 'code' => 400, 'data' => '失败']);
        }
    }


    /**
     * 添加 地址
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function addComment(Request $request)
    {

        $params = $request->all();
        $params['add_time'] = time();
        $params['status'] = 1;

        app('db')->beginTransaction();
        try {
            $this->commentinfo->insertData($params);
            $order = $this->orderinfo->getOneData(['order_num' => $params['order_num']]);
            $orderinfo = json_decode($order->orderinfo);
            foreach ($orderinfo as $key => $value) {
                if ($value->item_guid === $params['item_guid']) {
                    $value->commentStatus = 1;
                }
            }
            $order = $this->orderinfo->updateData(
                ['order_num' => $params['order_num']],
                ['orderinfo' => json_encode($orderinfo)]
            );


            app('db')->commit();
            return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => 'dd']);
        } catch (\Exception $e) {
            app('log')->error($e->getMessage());
            app('db')->rollBack();
            return response()->json(['ServerTime' => time(), 'code' => 400, 'data' => '失败']);
        }
    }


    public function test1(Request $request)
    {
        return response()->json(['ServerTime' => time(), 'code' => 400, 'data' =>
        ['a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7', 'a8', 'a9', 'a10']]);
    }

    public function test2(Request $request)
    {
        return response()->json(['ServerTime' => time(), 'code' => 400, 'data' =>
        ['b1', 'b2', 'b3', 'b4', 'b5', 'b6', 'b7', 'b8', 'b9', 'b10']]);
    }
}
