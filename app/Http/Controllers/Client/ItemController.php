<?php

namespace App\Http\Controllers\Client;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Library\Tools\Redis\RedisTool;
use App\Repositories\Client\DataItemRepository as ItemInfo;
use App\Repositories\Client\DataSerieRepository as Serieinfo;
use App\Repositories\Client\DataCommentRepository as Commentinfo;
use App\Repositories\Client\DataUserRepository as Userinfo;


class ItemController extends Controller
{
    protected $redis;
    protected $info;
    protected $serieinfo;
    protected $commentinfo;
    protected $userinfo;

    public function __construct(RedisTool $redisTool, ItemInfo $iteminfo, Serieinfo $serieinfo, Commentinfo $commentinfo, Userinfo $userinfo)
    {
        $this->redis = $redisTool;
        $this->info = $iteminfo;
        $this->serieinfo = $serieinfo;
        $this->commentinfo = $commentinfo;
        $this->userinfo = $userinfo;
    }


    /**
     * 获取商品 系列列表
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function getSerieList(Request $request)
    {
        // $params = json_decode($request['param'], true);
        $params = $request;
        $list = $this->serieinfo->getAllData([], []);
        if (count($list) > 0) {
            return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => $list]);
        } else {
            return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => []]);
        }
    }


    /**
     * 搜索商品
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function search(Request $request)
    {
        // $params = json_decode($request['param'], true);
        $params = $request;
        $searchWord = $params['name'];
        $list = $this->info->getLikeData($searchWord, []);
        if (count($list) > 0) {
            return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => $list]);
        } else {
            return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => []]);
        }
    }



    /**
     * 商品列表
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function productList(Request $request)
    {
        // $params = json_decode($request['param'], true);
        $params = $request;
        $nowPage = $params['pageNum'];
        $offset = $params['pageSize'];
        if (isset($params['optionId'])) {

            // 材质 texture
            if ($params['category'] === 1) {
                $where = ['texture' => $params['optionId']];
                $list = $this->info->getPageData($nowPage, $offset, $where, 'id', 'desc');
                return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => $list]);
            }

            // 类型 type
            if ($params['category'] === 2) {
                $where = ['type' => $params['optionId']];
                $list = $this->info->getPageData($nowPage, $offset, $where, 'id', 'desc');
                return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => $list]);
            }

            //  系列
            if ($params['category'] === 3) {
                $where = ['serie_guid' => $params['optionId']];
                $list = $this->info->getPageData($nowPage, $offset, $where, 'id', 'desc');
                return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => $list]);
            }
            $where = [];
            $list = $this->info->getPageData($nowPage, $offset, $where, 'id', 'desc');
            return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => $list]);
        } else {
            $list = $this->info->getPageData($nowPage, $offset, [], 'id', 'desc');
            return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => $list]);
        }

        return response()->json(['ServerTime' => time(), 'code' => 200, 'data' => []]);
    }





    /**
     * 商品详情
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function detail(Request $request)
    {
        // $params = json_decode($request['param'], true);
        $params = $request;
        $list = $this->info->getOneData(['guid' => $params['guid']]);
        return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'data' => $list]);
    }


    /** 
     *  获取 评论
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function getComment(Request $request)
    {
        // $params = json_decode($request['param'], true);
        $params = $request;
        $list = $this->commentinfo->getAllData(['item_guid' => $params['item_guid']]);
        foreach ($list as $key => $value) {
            $user_info = $this->userinfo->getOneData(['guid' => $value->user_guid]);
            $list[$key]->username = $user_info->nick_name;
            $list[$key]->avatar_img = $user_info->avatar_img;
        }
        if ($list) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'data' => $list]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'data' => 'guid有误']);
        }
    }
}
