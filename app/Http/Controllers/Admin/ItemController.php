<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Library\Tools\Common\Common;
use App\Library\Tools\Redis\RedisTool;
use App\Repositories\Admin\DataItemRepository as ItemInfo;
use App\Repositories\Admin\DataSerieRepository as Serieinfo;
use App\Repositories\Admin\DataCommentRepository as Commentinfo;
use App\Repositories\Admin\DataUserRepository as Userinfo;


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
        $this->commentinfo = $commentinfo;
        $this->serieinfo = $serieinfo;
        $this->userinfo = $userinfo;
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
            $serie_guid = $list[$key]->serie_guid;
            $serie = app('db')->table('data_serie')->where(['guid' => $serie_guid])->first();
            $list[$key]->serie_name = $serie->name;
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
     * 修改商品 信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function updateGoods(Request $request)
    {
        $params = json_decode($request['param'], true);
        $iteminfo = $this->info->getOneData(['guid' => $params['guid']]);

        if (isset($params['addnum'])) {
            if (isset($params['remain'])) {
                $params['remain'] = $params['remain'] + $params['addnum'];
            } else {
                $params['remain'] = $iteminfo->remain + $params['addnum'];
            }
            unset($params['addnum']);
        }
        // 编辑修改其他信息 
        $flag = $this->info->updateData(['guid' => $params['guid']], $params);
        if ($flag) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '修改成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '修改失败']);
        }
    }

    /**
     * 获取单条商品信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function details(Request $request)
    {
        $params = json_decode($request['param'], true);
        $info = $this->info->getOneData(['guid' => $params['guid']]);
        if ($info->serie_guid !== '' || $info->serie_guid !== null) {
            $serieinfo = $this->serieinfo->getOneData(['guid' => $info->serie_guid]);
            $info->serie_name = $serieinfo->name;
        }
        if ($info) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $info]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => 'guid有误']);
        }
    }

    /**
     * 新增 商品信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function goodsAdd(Request $request)
    {
        $params = json_decode($request['param'], true);
        $params['add_time'] = time();
        $params['rollImg'] = '["' . $params['item_img'] . '"]';
        $params['guid'] = Common::getUuid()->toString();
        if (isset($params['addnum'])) {
            $params['remain'] = $params['remain'] + $params['addnum'];
            unset($params['addnum']);
        }
        $flag = $this->info->insertData($params);
        if ($flag) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '添加失败']);
        }
    }

    /**
     * 商品 图片 删除信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function rollimgDelete(Request $request)
    {
        $params = json_decode($request['param'], true);
        $info = $this->info->getOneData(['guid' => $params['guid']]);
        $rollImg = json_decode($info->rollImg);
        array_splice($rollImg, $params['index'], 1);
        $newRollImg = json_encode($rollImg);
        $flag = $this->info->updateData(['guid' => $params['guid']], ['rollImg' => $newRollImg]);
        if ($flag) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $newRollImg]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => 'guid有误']);
        }
    }

    /**
     * 商品 图片 添加
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function rollimgAdd(Request $request)
    {
        $params = json_decode($request['param'], true);
        $info = $this->info->getOneData(['guid' => $params['guid']]);
        $rollImg = json_decode($info->rollImg);
        array_push($rollImg, $params['imgurl']);
        $newRollImg = json_encode($rollImg);
        $flag = $this->info->updateData(['guid' => $params['guid']], ['rollImg' => $newRollImg]);
        if ($flag) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $newRollImg]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => 'guid有误']);
        }
    }


    /** 
     *  获取 评论
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function getComment(Request $request)
    {
        // $params = json_decode($request['param'], true);
        $params = json_decode($request['param'], true);
        $nowPage = $params['pageNum'];
        $offset = $params['pageSize'];
        $list = $this->commentinfo->getPageData($nowPage, $offset, ['item_guid' => $params['item_guid']], 'id', 'desc');

        foreach ($list as $key => $value) {
            $user_info = $this->userinfo->getOneData(['guid' => $value->user_guid]);
            $list[$key]->username = $user_info->nick_name;
            $list[$key]->avatar_img = $user_info->avatar_img;
        }
        if ($list) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $list]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => 'guid有误']);
        }
    }
}
