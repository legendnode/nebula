<?php

/**
 * This file is part of Nebula.
 *
 * (c) 2022 nbacms <nbacms@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Nebula;

use Nebula\Helpers\MySQL;
use Nebula\Helpers\Fragment;

class Widget
{
    /**
     * widget 对象池
     *
     * @var array
     */
    private static $widgetPool = [];

    /**
     * 请求对象
     *
     * @var Request
     */
    public $request;

    /**
     * 响应对象
     *
     * @var Response
     */
    public $response;

    /**
     * 组件参数
     *
     * @var array
     */
    private $params;

    /**
     * @param Request $request Request 对象
     * @param Response $response Response 对象
     * @return void
     */
    public function __construct()
    {
        $this->db = MySQL::getInstance();
        $this->request = Request::getInstance();
        $this->response = Response::getInstance();
    }

    /**
     * 工厂方法
     *
     * @param array $params 参数
     * @param null|string $alias 组件别名
     * @return object 组件实例
     */
    public static function factory($params = [], $alias = null)
    {
        $alias = null === $alias ? static::class : static::class . '@' . $alias;

        // 判断组件池是否存在当前组件
        if (!isset(self::$widgetPool[$alias])) {
            $className = static::class;
            $widget = new $className();

            $widget->params = $params;
            $widget->init();

            self::$widgetPool[$alias] = $widget;
        }

        return self::$widgetPool[$alias];
    }

    /**
     * 获取参数
     *
     * @param null|string $name 参数名
     * @param mixed $defaultValue 默认值
     * @return mixed
     */
    public function params($name = null, $defaultValue = null)
    {
        if (null === $name) {
            return $this->params;
        } else {
            return $this->params[$name] ?? $defaultValue;
        }
    }

    /**
     * 行动绑定
     *
     * @param bool $condition
     * @return $this
     */
    public function on($condition)
    {
        if ($condition) {
            return $this;
        } else {
            return Fragment::getInstance();
        }
    }

    /**
     * 初始化
     *
     * @return void
     */
    protected function init()
    {
    }
}
