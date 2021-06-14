<?php
namespace App\Library\Tools\Redis;


/**
 * 操作Redis的工具类
 *
 * @author  jiaxiaofei
 * @date    20170411
 */
class RedisTool
{
    /**
     * list添加操作
     *
     * @param   $keyName
     * @param   $value (数组或单个值)
     * @return  int     列表的长度
     * @author  jiaxiaofei
     * @date    20170411  Redis Lpush 命令将一个或多个值插入到列表头部(放入到表头)
     */
    public function listAdd($keyName, $value)
    {
        return app('redis')->LPUSH($keyName, $value);
    }

    /**
     * hash获取域所有值的操作
     *
     * @param   $keyName 域名
     * @return  int     列表的长度
     * @author  renqingbin
     * @date    20170411  Redis Hgetall 命令用于返回哈希表中，所有的字段和值。(取出所有缓存)
     */
    public function hashGetAll($keyName)
    {
        return app('redis')->HGETALL($keyName);
    }

    /**
     * hash获取单个值的操作
     *
     * @param   $keyName 域名
     * @param   $value  单个值
     * @return  int     列表的长度
     * @author  renqingbin
     * @date    20170411  Redis HGET命令用于获取与字段中存储的键哈希相关联的值 (获取键哈希值)
     */
    public function hashGet($keyName, $value)
    {
        return app('redis')->HGET($keyName, $value);
    }

    /**
     * hash获取设置一个哈希表里多个域与值
     *
     * @param   $keyName 域名
     * @param   $value (数组或单个值)
     * @return  int     1成功0失败
     * @author  renqingbin
     * @date    20170411  Redis Hmset 命令用于同时将多个 field-value (字段-值)对设置到哈希表中。(设置在哈希表中)
     */
    public function hashMSet($keyName, $value)
    {
        return app('redis')->HMSET($keyName, $value);
    }

    /**
     * 为哈希表 key 中的域 field 的值加上增量 increment
     *
     * @param   $key 哈希表key
     * @param   $member 域名
     * @param   $score  域值
     * @return  int     1成功0失败
     * @author  renqingbin
     * @date    20170411 Redis HINCRBY命令用于增加存储在字段中存储由增量键哈希的数量。
     */
     public function hashHincrby($key, $member, $score)
     {
         return app('redis')->HINCRBY($key, $member, $score);
     }

    /**
     * 为字符串 key 中的 field 的值加上增量 increment
     *
     * @param   $key 字符串key
     * @param   $score  域值
     * @return  int     1成功0失败
     * @author  renqingbin
     * @date    20170411 。
     */
    public function incrby($key, $score)
    {
        return app('redis')->INCRBY($key, $score);
    }

     /**
      * 删除某条缓存
      *
      * @param   $key 哈希表key
      * @return  int     1成功0失败
      * @author  renqingbin
      * @date    20170411   删除对应的hash值
      */
      public function del($key)
      {
          return app('redis')->DEL($key);
      }


    /**
     * 删除hash中某个值
     *
     * @param $key
     * @param $value
     * @return mixed
     * @author jiaxiaofei
     */
    public function hdel($key, $value)
    {
        return app('redis')->HDEL($key, $value);
    }

    /**
     * 删除有序集合
     *
     * @param $key
     * @param $value
     * @return mixed
     * @author jiaxiaofei
     */
    public function sdel($key, $value)
    {
        return app('redis')->ZREM($key, $value);
    }
    /**
     * list查询操作
     *
     * @param   $keyName
     * @param   $start
     * @param   $end
     * @return  list     包含指定区间内的元素
     * @author  jiaxiaofei
     * @date    20170411  Redis LRANGE命令将返回存储在key列表的特定元素。偏移量开始和停止是从0开始的索引
     */
    public function listSelect($keyName, $start = 0, $end = -1)
    {
        return app('redis')->LRANGE($keyName, $start, $end);
    }

    /**
     * list，根据键的索引更新其值
     *
     * @param   $keyName
     * @param   $keyIndex
     * @param   $data
     * @return  string     成功返回ok
     * @author  jiaxiaofei
     * @date    20170412   Redis Lset 通过索引来设置元素的值。
     */
    public function listSetKeyIndexValue($keyName, $keyIndex, $data)
    {
        return app('redis')->LSET($keyName, $keyIndex, $data);
    }

    /**
     * list，判断是否存在某个键
     *
     * @param   $keyName
     * @return  int     不存在返回0，存在返回列表的长度
     * @author  jiaxiaofei
     * @date    20170412   Redis LLEN命令将返回存储在key列表的长度
     */
    public function listExistKey($keyName)
    {
        return app('redis')->Llen($keyName);
    }

    /**
     * list， 根据键名、元素值匹配删除
     *
     * @param   $keyName
     * @param   $value
     * @param   $count
     * @author  jiaxiaofei
     * @date    20170413  Redis Lrem 根据参数 COUNT 的值，移除列表中与参数 VALUE 相等的元素。
     */
    public function listDelKeyValue($keyName, $count = 0, $value)
    {
        return app('redis')->LREM($keyName, $count, $value);
    }

    /**
     * list,获取列表长度的方法
     *
     * @param   $keyName
     * @return  int
     * @author  jiaxiaofei
     * @date    20170413  Redis LLEN命令将返回存储在key列表的长度。
     */
    public function listGetLength($keyName)
    {
        return  app('redis')->Llen($keyName);
    }

     /**
     * redis set
     *
     * @param   $keyName
     * @param   $value ()
     * @return  int
     * @author  dmy
     * @date    20170411
     */
    public function set($keyName, $value)
    {
        return  app('redis')->set($keyName, $value);
    }

     /**
     * redis get
     *
     * @param   $keyName
     * @return  value
     * @author  dmy
     * @date    20170411
     */
    public function get($keyName)
    {
        return  app('redis')->get($keyName);
    }

     /**
     * 检查给定 key 是否存在
     *
     * @param   $keyName
     * @return  int
     * @author  sunchanghao
     * @date    2017/8/15
     */
    public function exists($keyName)
    {
        return app('redis')->exists($keyName);
    }

    /**
     * redis EXPIRE
     *
     * @param   $keyName
     * @return  int
     * @author  dmy
     * @date    20170807
     */
    public function expire($keyName, $count)
    {
        return app('redis')->EXPIRE($keyName, $count);
    }

    /**
     * redis EXPIRE
     *
     * @param   $keyName
     * @return  int
     * @author  dmy
     * @date    20170807
     */
    public function flushdb()
    {
        return app('redis')->FLUSHDB();
    }

    /**
     * redis EXPIRE
     *
     * @param   $keyName
     * @return  int
     * @author  dmy
     * @date    20170807
     */
    public function hset($keyName ,$key, $value)
    {
        return app('redis')->HSET($keyName ,$key, $value);
    }

    /**
     * redis EXPIRE
     *
     * @param   $keyName
     * @return  int
     * @author  dmy
     * @date    20170807
     */
    public function hlen($keyName)
    {
        return app('redis')->HLEN($keyName);
    }

    /**
     * list添加操作
     *
     * @param   $keyName
     * @param   $value (数组或单个值)
     * @return  int     列表的长度
     * @author  jiaxiaofei
     * @date    20170411  Redis Lpush 命令将一个或多个值插入到列表头部(放入到表头)
     */
    public function listAddRight($keyName, $value)
    {
        return app('redis')->RPUSH($keyName, $value);
    }
    /**
     * sorted set将一个或多个 member 元素及其 score 值加入到有序集 key 当中。
     *
     * @param   $keyName
     * @param   $value (数组或单个值)
     * @return  bool
     * @author  sunchanghao
     * @date    201708128 将一个或多个 member 元素及其 score 值加入到有序集 key 当中
     */
    public function zadd($keyName,$number, $value)
    {
        return app('redis')->ZADD($keyName,$number ,$value);
    }
    /**
     * 通过索引区间返回有序集合成指定区间内的成员(从大到小)。
     *
     * @param   $keyName
     * @param   $value (数组或单个值)
     * @return  bool
     * @author  sunchanghao
     * @date    201708128
     */
    public function zRevRange($keyName,$number1=0, $number2=-1)
    {
        // return app('redis')->zRevRange($keyName, $number1, $number2,'WITHSCORES');
        return app('redis')->zRevRange($keyName, $number1, $number2);
    }
    /**
     * 通过索引区间返回有序集合成指定区间内的成员(从大到小)。
     *
     * @param   $keyName
     * @param   $value (数组或单个值)
     * @return  bool
     * @author  sunchanghao
     * @date    201708128
     */
    public function zRevRangeW($keyName,$number1=0, $number2=-1)
    {
        return app('redis')->zRevRange($keyName, $number1, $number2,'WITHSCORES');
    }
    /**
     * 获取有序集合的成员数。
     *
     * @param   $keyName
     * @param   $value (数组或单个值)
     * @return  bool
     * @author  sunchanghao
     * @date    201708128
     */
  	public function zCard($key)
    {
      return app('redis')->ZCARD($key);
    }

    /**
     * 为有序集 key 的成员 member 的 score 值加上增量 increment
     * @param $key
     * @param $score
     * @param $member
     * @return mixed
     */
    public function IncreZincrby($key, $score, $member)
    {
        return app('redis')->ZINCRBY($key, $score, $member);
    }

    /**
     * 为字符转设置过期时间
     *
     * @param $key
     * @param $time
     * @param $value
     * @return mixedTTL
     * @author zhangyuchao
     */
    public function sEteX($key,$time,$value)
    {
        return app('redis')->SETEX($key, $time, $value);
    }

    /**
     * 为字符转设置过期时间
     *
     * @param $key
     * @param $time
     * @param $value
     * @return mixed
     * @author zhangyuchao
     */
    public function TTL($key)
    {
        return app('redis')->TTL($key);
    }
}
