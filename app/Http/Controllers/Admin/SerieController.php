<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Library\Tools\Common\Common;
use App\Library\Tools\Redis\RedisTool;
use App\Repositories\Admin\DataSerieRepository as SerieInfo;


class SerieController extends Controller
{
    protected $redis;
    protected $info;
    protected $order;
    protected $userinfo;
    protected $logCarInfo;

    public function __construct(RedisTool $redisTool, SerieInfo $serieinfo)
    {
        $this->redis = $redisTool;
        $this->info = $serieinfo;
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
     * 获取所有系列名称 列表
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function getAllserieNameList(Request $request)
    {
        $params = json_decode($request['param'], true);
        $where = Common::trimParam($params);
        $list = $this->info->getAllData([], ['name', 'guid']);
        if (count($list) > 0) {
            $ResultData = [];
            $ResultData['list'] = $list;
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => $ResultData]);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => []]);
        }
    }

    /**
     * 修改 系列信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function updateSerie(Request $request)
    {
        $params = json_decode($request['param'], true);
        $params['update_time'] = time();
        $flag = $this->info->updateData(['guid' => $params['guid']], $params);
        if ($flag) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '修改成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => '修改失败']);
        }
    }


    /**
     * 新增 系列信息
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse|mixed
     */
    public function serieAdd(Request $request)
    {
        $params = json_decode($request['param'], true);
        $params['add_time'] = time();
        $params['guid'] = Common::getUuid()->toString();
        $flag = $this->info->insertData($params);
        if ($flag) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '成功']);
        } else {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 400, 'ResultData' => 'guid有误']);
        }
    }
}
