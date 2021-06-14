<?php

namespace App\Repositories;

/**
 * 基础的数据仓库类
 *
 * Class BaseRepository
 * @package App\Repositories
 */
trait BaseRepository
{
    // 静态变量
    protected static $db;

    /**
     * 依赖注入
     *
     * BaseRepository constructor.
     */
    public function __construct()
    {
        self::$db = app('db');
    }

    /**
     * 添加数据
     *
     * @param $param
     * @return bool
     */
    public function addData($param)
    {
        if (empty($param)) {
            return false;
        }

        return self::$db->table(static::$table)->insertGetId($param);
    }

    /**
     * 更新数据
     *
     * @param $where
     * @param $data
     * @return bool
     */
    public function updateData($where, $data)
    {
        if (empty($where) || empty($data)) {
            return false;
        }

        return  self::$db->table(static::$table)->where($where)->update($data);
    }

    /**
     * 查询一条记录
     *
     * @param $where
     * @return bool
     */
    public function getOneData($where)
    {
        if (empty($where)) {
            return false;
        }

        return self::$db->table(static::$table)->where($where)->first();
    }


    /**
     * 获取所有数据
     *
     * @param array $where
     * @param array $select
     * @return mixed
     */
    public function getAllData($where = [], $select = [])
    {
        if (empty($where)) {
            if (empty($select)) {
                return self::$db->table(static::$table)->get();
            }
            return self::$db->table(static::$table)->select($select)->get();
        }

        if (empty($select)) {
            return self::$db->table(static::$table)->where($where)->get();
        } else {
            return self::$db->table(static::$table)
                ->where($where)
                ->select($select)
                ->get();
        }
    }

    /**
     * 获取所有排序数据
     *
     * @param array $where
     * @param array $select
     * @param string $orderField
     * @param string $order
     * @return mixed
     */
    public function getAllOrderData($where = [], $select = [], $orderField = '', $order = 'asc')
    {
        if (empty($where)) {
            if (empty($select)) {
                return self::$db->table(static::$table)->get();
            }
            return self::$db->table(static::$table)->select($select)->get();
        }

        if (empty($select)) {
            if (empty($order)) {
                return self::$db->table(static::$table)->where($where)->get();
            } else {
                return self::$db->table(static::$table)
                    ->where($where)
                    ->orderBy($orderField, $order)
                    ->get();
            }
        } else {
            if (empty($order)) {
                return self::$db->table(static::$table)
                    ->where($where)
                    ->select($select)
                    ->get();
            } else {
                return self::$db->table(static::$table)
                    ->where($where)
                    ->select($select)
                    ->orderBy($orderField, $order)
                    ->get();
            }
        }
    }

    /**
     * 获取总条数
     *
     * @param array $where
     * @return mixed
     */
    public function getCount($where = [])
    {
        if (empty($where)) {
            return self::$db->table(static::$table)->count();
        } else {
            return self::$db->table(static::$table)->where($where)->count();
        }
    }

    /**
     * 获取分页数据
     *
     * @param $nowPage
     * @param $offset
     * @param string $where
     * @param string $field
     * @param string $asc
     * @return bool
     */
    public function getPageData($nowPage, $offset, $where = '', $field = '', $asc = 'desc')
    {
        if (empty($nowPage) || empty($offset)) {
            return false;
        }

        $db = self::$db->table(static::$table)->forPage($nowPage, $offset);
        if (!empty($where)) {
            $db = $db->where($where);
        }

        if (!empty($field)) {
            $db = $db->orderBy($field, $asc);
        }

        return $db->get();
    }

    /**
     * 不返回ID的添加数据
     *
     * @param $param
     * @return bool
     */
    public function insertData($param)
    {
        if (empty($param)) {
            return false;
        }

        return self::$db->table(static::$table)->insert($param);
    }

    /**
     * 删除(带where条件删除)
     *
     * @param $where
     * @return bool
     */
    public function delete($where)
    {
        if (empty($where)) {
            return false;
        }

        return self::$db->table(static::$table)->where($where)->delete();
    }

    /**
     * 删除(带whereIn条件删除)
     *
     * @param $where
     * @param string $field
     * @return bool
     */
    public function deleteWhereIn($where, $field = 'id')
    {
        if (empty($where)) {
            return false;
        }

        return self::$db->table(static::$table)->whereIn($field, $where)->delete();
    }

    /**
     * 更新数据(带whereIn条件更新)
     *
     * @param $where
     * @param $whereIn
     * @param $data
     * @param string $field
     * @return bool
     */
    public function updateWhereInData($whereIn, $data, $field = 'id', $where = [])
    {
        if (empty($whereIn) || empty($data)) {
            return false;
        }

        if (empty($where)) {
            return self::$db->table(static::$table)->whereIn($field, $whereIn)->update($data);
        } else {
            return self::$db->table(static::$table)
                ->where($where)
                ->whereIn($field, $whereIn)
                ->update($data);
        }
    }


    /**
     * 获取whereIn数据
     *
     * @param $where
     * @param $fieldName
     * @param $data
     * @param array $select
     * @return bool
     */
    public function getWhereInData($where, $fieldName, $data, $select = [])
    {
        if (empty($where)) {
            return false;
        }

        $db = self::$db->table(static::$table)->where($where);

        if (!empty($fieldName) && !empty($data)) {
            $db = $db->whereIn($fieldName, $data);
        }
        if (!empty($select)) {
            $db = $db->select($select);
        }

        return $db->get();
    }


    /**
     * 获取 Like 数据
     * 模糊查找
     *
     * @param $where
     * @param $fieldName
     * @param $data
     * @param array $select
     * @return bool
     */
    public function getLikeData($like, $select = [])
    {
        $db = self::$db->table(static::$table);

        if (!empty($select)) {
            $db = $db->select($select);
        }

        if (!empty($like)) {
            $db = $db->where('name', 'like', '%' . $like . '%');
        }
        return $db->get();
    }

    /**
     * 获取whereIn总数目数据
     *
     * @param $where
     * @param $fieldName
     * @param $data
     * @param array $select
     * @return bool
     */
    public function getWhereInCount($where, $fieldName, $data, $select = [])
    {
        if (empty($where) || empty($fieldName)) {
            return false;
        }

        $db = self::$db->table(static::$table)->where($where);

        if (!empty($data)) {
            $db = $db->whereIn($fieldName, $data);
        }
        if (!empty($select)) {
            $db = $db->select($select);
        }
        return $db->count();
    }

    /**
     * 获取区间数据
     *
     * @param $where
     * @param $field
     * @param $operation
     * @param $value
     * @return int
     */
    public function getIntervalData($where, $field, $operation, $value)
    {
        // 判断条件
        if (empty($field) || empty($value)) {
            return 0;
        }

        if (empty($where)) {
            return app('db')->table(static::$table)
                ->where($field, $operation, $value)
                ->count();
        } else {
            return app('db')->table(static::$table)
                ->where($where)
                ->where($field, $operation, $value)
                ->count();
        }
    }

    /**
     * 获取加总值
     *
     * @param $where
     * @param $field
     * @return int
     */
    public function getSum($where, $field)
    {
        // 判断条件
        if (empty($field)) {
            return 0;
        }

        if (empty($where)) {
            return app('db')->table(static::$table)->sum($field);
        } else {
            return app('db')->table(static::$table)
                ->where($where)
                ->sum($field);
        }
    }

    /**
     * 获取最大值
     *
     * @param $where
     * @param $field
     * @return int
     */
    public function getMax($where, $field)
    {
        // 判断条件
        if (empty($field)) {
            return 0;
        }

        if (empty($where)) {
            return app('db')->table(static::$table)->max($field);
        } else {
            return app('db')->table(static::$table)
                ->where($where)
                ->max($field);
        }
    }

    /**
     * 获取whereIn数据
     *
     * @param $whereIn
     * @param $field
     * @param string $select
     * @return bool
     */
    public function getWhereIn($whereIn, $field, $select = '')
    {
        // 判断条件
        if (empty($field) || empty($whereIn)) {
            return false;
        }

        $db = app('db')->table(static::$table)
            ->whereIn($field, $whereIn);

        if (!empty($select)) {
            $db = $db->select($select);
        }

        return $db->get();
    }

    /**
     * 查询最后一条记录
     *
     * @param $where
     * @return bool
     */
    public function getLast($where)
    {
        if (empty($where)) {
            return false;
        }

        return self::$db->table(static::$table)->where($where)->orderBy('id', 'desc')->first();;
    }

    /**
     * 递增操作
     *
     * @param $where
     * @param $number
     * @param string $field
     * @return bool
     */
    public function incrementWhere($where, $number, $field = '')
    {
        if (empty($field)) {
            return false;
        }
        $db = app('db')->table(self::$table);

        if (!empty($where)) {
            $db = $db->where($where);
        }

        return $db->increment($field, $number);
    }


    /**
     * 递减 操作
     *
     * @param $where
     * @param $number
     * @param string $field
     * @return bool
     */
    public function decrementWhere($where, $number, $field = '')
    {
        if (empty($field)) {
            return false;
        }
        $db = app('db')->table(self::$table);

        if (!empty($where)) {
            $db = $db->where($where);
        }

        return $db->decrement($field, $number);
    }


    /**
     * 获取某条件下数量
     *
     * @param $data
     * @param $fieldName
     * @param $where
     * @return mixed
     */
    public function getWhereNum($data, $fieldName, $where = '')
    {
        if (empty($fieldName) || empty($data)) {
            return false;
        }

        $db = app('db')->table(self::$table)->whereIn($fieldName, $data);

        if (!empty($where)) {
            $db->where($where);
        }

        return $db->count();
    }

    /**
     * 获取某条件加总值
     *
     * @param array $whereIn
     * @param string $field
     * @param $sumField
     * @param string $where
     * @return int
     */
    public function getWhereInSum($whereIn = [], $field = '', $sumField, $where = '')
    {
        // 判断条件
        if (empty($field)) {
            return 0;
        }

        $db = app('db')->table(static::$table);

        if (!empty($where)) {
            $db->where($where);
        }

        if (!empty($where) && !empty($field)) {
            $db->whereIn($field, $whereIn);
        }
        return $db->sum($sumField);
    }

    // 获取某一列的所有值
    public function getColumData($where)
    {
        if (empty($where)) {
            return false;
        }

        return self::$db->table(static::$table)->pluck($where);
    }
}
