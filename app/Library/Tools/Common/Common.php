<?php

namespace App\Library\Tools\Common;

use Ramsey\Uuid\Uuid;

class Common
{
    /**
     * 加密算法
     * @param string $user
     * @param string $pwd
     * @param integer $position
     * @return string
     */
    public static function cryptString($user, $pwd, $position = 5)
    {
        $subUser = substr(md5($user), 0, $position);
        $cryptPwd = md5($pwd);
        return md5(md5($cryptPwd . $subUser));
    }

    /**
     * 返回uuid
     * @return string
     */
    public static function getUuid()
    {
        $uuid = Uuid::uuid1();
        return $uuid->getHex();
    }

    /**
     * 返回uuid
     * @return string
     */
    public static function getUuid4()
    {
        $uuid = Uuid::uuid4();
        return $uuid->getHex();
    }

    /**
     *  获取本月第一天和最后一天
     * @param $date
     * @return array
     */
    public static function getMonth($date)
    {
        $firstday = date("Y-m-01", strtotime($date));
        $lastday = date("Y-m-d", strtotime("$firstday +1 month -1 day"));
        return array($firstday, $lastday);
    }


    /**
     *  获取上个月第一天和最后一天
     * @param $date
     * @return array
     */
    public static function getlastMonthDays($date)
    {
        $timestamp = strtotime($date);
        $firstday = date('Y-m-01', strtotime(date('Y', $timestamp) . '-' . (date('m', $timestamp) - 1) . '-01'));
        $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
        return array($firstday, $lastday);
    }


    /**
     *  获取下个月第一天和最后一天
     * @param $date
     * @return array
     */
    public static function getNextMonthDays($date)
    {
        $timestamp = strtotime($date);
        $arr = getdate($timestamp);
        if ($arr['mon'] == 12) {
            $year = $arr['year'] + 1;
            $month = $arr['mon'] - 11;
            $firstday = $year . '-0' . $month . '-01';
            $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
        } else {
            $firstday = date('Y-m-01', strtotime(date('Y', $timestamp) . '-' . (date('m', $timestamp) + 1) . '-01'));
            $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
        }
        return array($firstday, $lastday);
    }


    /**
     * 产生cookie
     * @return string
     * @author
     */
    public static function generateCookie($key)
    {
        if (empty($key)) return false;
        $value = md5(REGISTER_SIGNATURE . $key);
        return cookie($key, $value, COOKIE_LIFETIME);
    }

    /**
     * 用户注册生成随机串
     * @param  int 生成长度
     * @return string 生成的字条串
     */
    public static function random($length)
    {
        $hash = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($chars) - 1;
        PHP_VERSION < '4.2.0' && mt_srand((float) microtime() * 1000000);
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }


    /**
     * 无限分类
     *
     * @param   array $arr 待分类的数据
     * @param   int /string   $departmen_id        要找的子节点id
     * @param   int $level 节点等级
     * @return array  数组
     */
    public static function getTree($arr, $id = 0, $lev = 0)
    {
        // 获取子孙树
        if (empty($arr)) {
            return false;
        }
        $tree = [];
        foreach ($arr as $v) {
            if ($v['pid'] == $id) {
                $v['level'] = $lev;
                $tree[] = $v;
                $tree = array_merge($tree, self::getTree($arr, $v['id'], $lev + 1));
            }
        }
        return $tree;
    }



    /**
     * 时间戳
     *
     * @return array
     * @author sunchanghao
     */
    public static function getTimeStamp($param = '')
    {
        if (empty($param)) {
            return time();
        }
    }


    /**
     * CURLf方法
     * @param $url
     * @param bool data
     * @param int $ispost
     * @param int $https
     * @return bool|mixed
     */
    public static function curl($url, $data = false, $ispost = 1, $https = 0)
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }

        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($data) {
                if (is_array($data)) {
                    $data = http_build_query($data);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $data);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $response = curl_exec($ch);
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }


    /**
     * CURLf方法
     * @param $url
     * @param bool $data
     * @param int $ispost
     * @param int $https
     * @return bool|mixed
     */
    public static function passCurl($url, $data = false, $ispost = 1, $https = 0)
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }
        if ($ispost) {
            //            $headers[] = 'Content-Type: application/json;charset=utf-8';
            //            $headers[] = 'Content-Length:' . strlen($data);
            //            // 设置header头
            //            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($data) {
                if (is_array($data)) {
                    $data = http_build_query($data);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $data);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $response = curl_exec($ch);
        if ($response === FALSE) {
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }

    /**
     * 循环对参数每个值进行去空格处理
     *
     * @param $param
     * @return array
     */
    public static function trimParam($param)
    {
        // 返回值
        $where = [];
        // 检查参数
        if (empty($param['where']) || !is_array($param['where'])) {
            return $where;
        }
        // 循环对参数每个值进行去空格处理
        foreach ($param['where'] as $key => $value) {
            if (!empty($value) && !is_array($value)) {
                if (!empty(trim($value))) {
                    $where[$key] = $value;
                }
            }
            if (is_array($value) && count($value) > 0) {
                $where[$key] = $value;
            }
        }

        return $where;
    }
}
