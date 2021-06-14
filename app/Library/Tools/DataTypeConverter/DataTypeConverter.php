<?php
/**
 * 数据类型转换类
 * CustomerModel: Redscarf
 * Date: 17/4/14
 * Time: 上午9:48
 */

namespace App\Library\Tools\DataTypeConverter;


/**
 * json和数组转换的类
 *
 * @author  Redscarf
 * @date    20170414
 */
class DataTypeConverter
{
    /**
     * 二维数组中第二维是json的，将其转为正规的二维数组
     *
     * @param   array
     * @return  array
     * @author  cxs
     * @date    20170414
     */
    public function jsonAndArray($twoArray)
    {
        $newArray = array();

        // 遍历非正规二维数组
        foreach ($twoArray as $key => $value) {
            $newArray[$key] = json_decode($value, true);
        }

        return $newArray;
    }


    /**
     * 数组转换成对象
     *
     * @param $array
     * @return Object
     */
    public static function arrayToObject($e)
    {

        if (gettype($e) != 'array') return;
        foreach ($e as $k => $v) {
            if (gettype($v) == 'array' || getType($v) == 'object')
                $e[$k] = (object)self::arrayToObject($v);
        }
        return (object)$e;
    }

    /**
     * 对象转换成数组
     *
     * @param $array
     * @return Object
     */
    public static function objectToArray($e)
    {
        $e = (array)$e;
        foreach ($e as $k => $v) {
            if (gettype($v) == 'resource') return;
            if (gettype($v) == 'object' || gettype($v) == 'array')
                $e[$k] = (array)self::objectToArray($v);
        }
        return $e;
    }

    /**
     * 数字对应位的二进制表示
     *
     * @param array $data
     * @return string
     * @author 郭鹏超
     */
    public static function encodeBin(Array $data)
    {
        sort($data);
        // 获取最大值
        $max = $data[count($data)-1];
        // 键值互换
        $data = array_flip($data);
        $resStr = '';
        for ($i = 1; $i <= $max; $i++) {
            $resStr .= (isset($data[$i]) ? 1 : 0);
        }

        return $resStr;
    }
    /**
     * 二进制对应位的数字表示
     *
     * @param $binString
     * @return string
     * @author 郭鹏超
     */
    public static function decodeBin($binString)
    {
        // 获取最大值
        $max = strlen($binString);
        $data = [];
        for ($i = 1; $i <= $max; $i++) {
            if ($binString[$i-1] == 1) {
                $data[] = $i;
            }
        }
        return $data;
    }
}
