<?php

/**
 * This file is part of Nebula.
 *
 * (c) 2022 nbacms <nbacms@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Nebula\Helpers;

use PDO;
use PDOException;

class MySQL
{
    /**
     * 单例实例
     *
     * @var MySQL
     */
    private static $instance;

    /**
     * @var PDO
     */
    private $mysql;

    /**
     * @var string
     */
    private $prefix;

    /**
     * 操作类型
     *
     * @var string
     */
    private $type;

    /**
     * SQL 语句
     *
     * @var string
     */
    private $query = '';

    /**
     * SQL 参数
     *
     * @var array
     */
    private $params = [];

    /**
     * 执行过的 SQL 语句
     *
     * @var array
     */
    public $sqls = [];

    private function __construct()
    {
    }

    /**
     * 初始化
     *
     * @param array $options 数据库选项
     * @return void
     */
    public function init($options)
    {
        try {
            $this->mysql = new PDO('mysql:dbname=' . $options['dbname'] . ';host=' . $options['host'] . ';port=' . $options['port'], $options['username'], $options['password']);
        } catch (PDOException $e) {
            throw $e;
        }

        $this->prefix = $options['prefix'];
    }

    /**
     * 表名解析
     *
     * @param string $table 表名
     * @return string
     */
    private function tableParse($table)
    {
        return '`' . $this->prefix . $table . '`';
    }

    /**
     * 列名解析
     *
     * @param string $column
     * @return string
     */
    private function columnParse($column)
    {
        $columnArray = explode('.', $column);

        if (2 === count($columnArray)) {
            $columnArray[0] = $this->tableParse($columnArray[0]);
            preg_match('/^(.*)\[(.*)\]$/', $columnArray[1], $matches);
            $columnArray[1] = '`' . ($matches[1] ?? $columnArray[1]) . '`' . (isset($matches[2]) ? ' AS `' . $matches[2] . '`' : '');
        } else {
            array_walk($columnArray, function (&$part) {
                $part = '`' . $part . '`';
            });
        }

        return implode('.', $columnArray);
    }

    /**
     * 多项列名解析
     *
     * @param string $column
     * @return string
     */
    private function columnsParse($columns)
    {
        array_walk($columns, function (&$column) {
            $column = $this->columnParse($column);
        });

        return implode(', ', $columns);
    }

    /**
     * where 格式化
     *
     * @param array $wheres
     * @param string $separator
     * @return string
     */
    private function whereParse($wheres, $separator = 'AND')
    {
        $where = '';

        foreach ($wheres as $key => $value) {
            if ('AND' === $key || 'OR' === $key) {
                end($wheres);
                if ($key === key($wheres)) {
                    $where .= '(' . $this->whereParse($value, $key) . ')';
                } else {
                    $where .= '(' . $this->whereParse($value, $key) . ') ' . $separator . ' ';
                }
            } else {
                preg_match('/^(.*)\[(.*)\]$/', $key, $matches);
                $column = $this->columnParse($matches[1] ?? $key);
                $operator = $matches[2] ?? '=';

                if (in_array($operator, ['IN', 'NOT IN'])) {
                    $placeholders = rtrim(str_repeat('?, ', count($value)), ', ');
                    $where .=  $column . ' ' . $operator . ' (' . $placeholders . ') ' . $separator . ' ';
                    $this->params = array_merge($this->params, $value);
                } else {
                    $where .=  $column . ' ' . $operator . ' ? ' . $separator . ' ';
                    array_push($this->params, $value);
                }
            }
        }

        return rtrim($where, ' ' . $separator . ' ');
    }

    /**
     * on 格式化
     *
     * @param array $on
     * @param string $separator
     * @return string
     */
    private function onParse($ons, $separator = 'AND')
    {
        $on = '';

        foreach ($ons as $key => $value) {
            if ('AND' === $key || 'OR' === $key) {
                end($ons);
                if ($key === key($ons)) {
                    $on .= '(' . $this->whereParse($value, $key) . ')';
                } else {
                    $on .= '(' . $this->whereParse($value, $key) . ') ' . $separator . ' ';
                }
            } else {
                preg_match('/^(.*)\[(.*)\]$/', $key, $matches);
                $column = $this->columnParse($matches[1] ?? $key);
                $operator = $matches[2] ?? '=';
                $value = $this->columnParse($value);

                $on .=  $column . ' ' . $operator . ' ' . $value . ' ' . $separator . ' ';
            }
        }

        return rtrim($on, ' ' . $separator . ' ');
    }

    /**
     * 查询构造器
     *
     * @param string $table 表名
     * @param array $columns
     * @return $this
     */
    public function select($table, $columns)
    {
        $this->type = 'SELECT';

        $this->query = 'SELECT ' . $this->columnsParse($columns) . ' FROM ' . $this->tableParse($table);

        return $this;
    }

    /**
     * 查询数据条数
     *
     * @param string $table 表名
     * @return $this
     */
    public function count($table)
    {
        $this->type = 'COUNT';

        $this->query = 'SELECT COUNT(*) AS `count` FROM ' . $this->tableParse($table);

        return $this;
    }

    /**
     * 只获取一条数据
     *
     * @param string $table 表名
     * @param array $columns
     * @return $this
     */
    public function get($table, $columns)
    {
        $this->type = 'GET';

        $this->query = 'SELECT ' . $this->columnsParse($columns) . ' FROM ' . $this->tableParse($table);

        return $this;
    }

    /**
     * 验证数据是否存在
     *
     * @param string $table 表名
     * @return $this
     */
    public function has($table)
    {
        $this->type = 'HAS';

        $this->query = 'SELECT COUNT(*) AS `count` FROM ' . $this->tableParse($table);

        return $this;
    }

    /**
     * 更新数据
     *
     * @param string $table 表名
     * @param array $values 数据
     * @return $this
     */
    public function update($table, $values)
    {
        $this->type = 'UPDATE';

        $stack = [];
        foreach ($values as $key => $value) {
            array_push($stack, $this->columnParse($key) . ' = ?');
            array_push($this->params, $value);
        }

        $this->query = 'UPDATE ' . $this->tableParse($table) . ' SET ' . implode(', ', $stack);

        return $this;
    }

    /**
     * 插入数据
     *
     * @param string $table 表名
     * @param array $values 数据
     * @return bool
     */
    public function insert($table, $values)
    {
        if (!isset($values[0])) {
            $values = [$values];
        }

        $columns = $this->columnsParse(array_keys($values[0]));
        $stack = [];
        $params = [];

        foreach ($values as $value) {
            $values = array_map(function () {
                return '?';
            }, $value);

            array_push($stack, '(' . implode(', ', $values) . ')');

            $params = array_merge($params, array_values($value));
        }

        $query = 'INSERT INTO ' . $this->tableParse($table) . ' (' . $columns . ') VALUES ' . implode(', ', $stack);
        array_push($this->sqls, $query);

        $sth = $this->mysql->prepare($query);

        return $sth->execute($params);
    }

    /**
     * 删除数据
     *
     * @param string $table 表名
     * @return $this
     */
    public function delete($table)
    {
        $this->type = 'DELETE';
        $this->query = 'DELETE FROM ' . $this->tableParse($table);

        return $this;
    }

    /**
     * 创建表
     *
     * @param string $table 表名
     * @param array $columns 列定义
     * @return bool
     */
    public function create($table, $columns)
    {
        $options = [];
        foreach ($columns as $column => $option) {
            array_push($options, $this->columnParse($column) . ' ' . implode(' ', $option));
        }

        $table = $this->tableParse($table);

        $statement = 'CREATE TABLE ' . $table . ' (' . implode(', ', $options) . ')';
        array_push($this->sqls, $statement);

        return false !== $this->mysql->exec($statement);
    }

    /**
     * 删除表
     *
     * @param string $table 表名
     * @return bool
     */
    public function drop($table)
    {
        $table = $this->tableParse($table);

        $statement = 'DROP TABLE ' . $table;
        array_push($this->sqls, $statement);

        return false !== $this->mysql->exec($statement);
    }

    /**
     * where 构造
     *
     * @param array $wheres 条件列表
     * @param string $rel 关系
     * @return $this
     */
    public function where($wheres)
    {
        $this->query .= ' WHERE ' . $this->whereParse($wheres);

        return $this;
    }

    /**
     * 返回指定范围数据
     *
     * @param int $start 开始位置
     * @param int $length 长度
     * @return $this
     */
    public function limit($start, $length)
    {
        $this->query .= ' LIMIT ' . $start . ', ' . $length;

        return $this;
    }

    /**
     * 联表查询
     *
     * @param string $table 联表
     * @param string $type 联表方式
     * @return $this
     */
    public function join($table, $on, $type = 'LEFT JOIN')
    {
        $this->query .= ' ' . $type . ' ' . $this->tableParse($table) . ' ON ' . $this->onParse($on);

        return $this;
    }

    /**
     * 排序
     *
     * @param array $columns 排序字段
     * @param string $type 排序方式
     * @return $this
     */
    public function order($columns, $type = 'DESC')
    {
        $this->query .= ' ORDER BY ' . $this->columnsParse($columns) . ' ' . $type;

        return $this;
    }

    /**
     * 执行
     *
     * @return mixed
     */
    public function execute()
    {
        $sth = $this->mysql->prepare($this->query);

        $result = $sth->execute($this->params);

        array_push($this->sqls, $this->query);

        // 清空上次查询条件
        $this->query = '';
        $this->params = [];

        if ('SELECT' === $this->type) {
            return json_decode(json_encode($sth->fetchAll(PDO::FETCH_CLASS)), true);
        } else if ('GET' === $this->type) {
            return json_decode(json_encode($sth->fetchAll(PDO::FETCH_CLASS)), true)[0] ?? [];
        } else if ('HAS' === $this->type) {
            return 0 < $sth->fetch(PDO::FETCH_ASSOC)['count'];
        } else if ('COUNT' === $this->type) {
            return $sth->fetch(PDO::FETCH_ASSOC)['count'];
        } else {
            return $result;
        }
    }

    /**
     * 获取单例实例
     *
     * @return MySQL
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
