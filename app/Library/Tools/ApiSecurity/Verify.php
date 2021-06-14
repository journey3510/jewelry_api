<?php

namespace App\Library\Tools\ApiSecurity;

use App\Library\Tools\Redis\RedisTool;

class Verify
{
    protected $redis;

    public function __construct(RedisTool $redisTool)
    {
        $this->redis = $redisTool;
    }
    /**
     * 通用接口
     * @param $request
     * @return bool|string
     */
    public function common($request)
    {
        if ($request->all()) {
            $data = $request->all();
            $ckTime = $this->checkTime($data['time']);
            if (!$ckTime) return 'SN002';
            if (!isset($data['guid'])) return 'SN004';
            // 根据版本设计不同的验证
            switch ($data['version']) {
                case 1:
                    $temp = $this->checkCommon_v1($request);
                    break;
                default:
                    $temp = $this->checkCommon_v1($request);
                    break;
            }
            if ($temp) {
                return "SN200";
            }
            return "SN005";
        }
        return false;
    }


    /**
     * 非通用接口
     * @param $request
     * @return bool|string
     */
    public function proprietary($request)
    {
        if ($request->all()) {
            $data = $request->all();
            $ckTime = $this->checkTime($data['time']);
            if (!$ckTime) return 'SN002';
            if (!isset($data['guid'])) return "SN004";
            // 根据版本设计不同的验证
            switch ($data['version']) {
                case 1:
                    $temp = $this->checkProprietary_v1($request);
                    break;
                default:
                    $temp = $this->checkProprietary_v1($request);
                    break;
            }
            if ($temp) {
                switch ($temp) {
                    case 'SN007':
                        return 'SN007';
                        break;
                    case 'SN008':
                        return 'SN008';
                        break;
                    case 'SN009':
                        return 'SN009';
                        break;
                    default:
                        return 'SN200';
                        break;
                }
            }
            return "SN005";
        }
        // No access! 没有添加签名验证
        return false;
    }

    /**
     * 时间验证
     * @param $time
     * @return bool|string
     */
    public function checkTime($time)
    {

        $Time_difference = abs(time() - $time);
        if ($Time_difference > 30) {
            return false;
        }
        return true;
    }


    /**
     * 通用接口验证
     * @param $request
     * @return bool
     */
    private function checkCommon_v1($request)
    {
        $data = $request->all();
        $path = '/' . $request->path();
        $time = $data['time'];
        $guid = 'asdfghjkl456';
        $param = $data['param'];
        $cryptToken = "journey";
        $signature = md5($path . $time . $guid . $param . $cryptToken);
        if ($signature != $data['signatures']) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 非通用接口验证
     * @param $request
     * @return bool
     */
    private function checkProprietary_v1($request)
    {

        $data = $request->all();

        $path = '/' . $request->path();
        // 获取参数
        $param = $data['param'];
        // 获取用户ID
        $guid = $data['guid'];
        // 获取签名
        $signature = $data['signatures'];
        // 获取提交时间
        $time = $data['time'];
        // 获取用户信息
        if (strpos($path, '/api/wxMapp/') !== false) {
            $user = $this->wxMappUser($guid);
        } else {
            $user = $this->user($guid);
        }
        if (!$user) return 'SN007';  // 用户不存在
        // TOKEN 过期
        if (time() > $user['token_time']) {
            return 'SN009';
        }
        $token = $user['token'];
        $hashs = [
            [0, 4, 1, 17, 22, 29],
            [2, 8, 19, 23, 30, 31],
            [4, 12, 31, 1, 5, 10],
            [6, 16, 31, 10, 12, 18],
            [8, 20, 12, 18, 25, 20],
            [10, 24, 17, 27, 1, 22],
            [12, 28, 13, 19, 20, 21],
            [14, 0, 20, 29, 18, 20]
        ];
        $strs = substr($token, 1, 1);
        $strs .= substr($token, 4, 1);
        $strs .= substr($token, 7, 1);
        $code = hexdec($strs);
        $str1 = $code % 8;
        $arr = $hashs["$str1"];
        $m = null;
        foreach ($arr as $v) {
            $m .= substr($token, $v, 1);
        }
        $str = md5($path . $time . $guid . $param . $m);
        if ($signature == $str) {
            return 'SN200';
        } else {
            return false;
        }
    }

    /**
     * 根据guid 获取token
     *
     * @param $guid
     * @return bool|int
     * @author zhangyuchao
     */
    public function user($guid)
    {
        // 拼接获取token的key
        $redisKey = config('datarediskey')['tokenInfo'] . $guid;
        // 获取缓存里的token
        $data = $this->redis->hashGetAll($redisKey);
        // 判断是否获取
        if ($data) {
            $this->redis->expire($redisKey, 3600 * 24);
            return $data;
        }
        // 没有获取到重新获取
        $res = app('db')->table('data_admin_token')->where(['guid' => $guid])->first();

        if ($res) {
            $arr = [];
            $arr['token'] = $res->token;
            $arr['token_time'] = $res->token_time;
            $this->redis->hashMSet($redisKey, $arr);
            $this->redis->expire($redisKey, 3600 * 24);
            // 返回
            return $arr;
        }
        // 返回错误
        return false;
    }

    /**
     * 根据guid 获取微信小程序用户token
     *
     * @param $guid
     * @return bool|int
     * @author zhangyuchao
     */
    public function wxMappUser($guid)
    {
        // 拼接获取token的key
        $redisKey = config('datarediskey')['wxTokenInfo'] . $guid;
        // 获取缓存里的token
        $data = $this->redis->hashGetAll($redisKey);
        // 判断是否获取
        if ($data) {
            $this->redis->expire($redisKey, 3600 * 24);
            return $data;
        }
        // 没有获取到重新获取
        $res = app('db')->table('data_user_token')->where(['guid' => $guid])->first();
        if ($res) {
            $arr = [];
            $arr['token'] = $res->token;
            $arr['token_time'] = $res->token_time;
            $this->redis->hashMSet($redisKey, $arr);
            $this->redis->expire($redisKey, 3600 * 24);
            // 返回
            return $arr;
        }
        // 返回错误
        return false;
    }
}
