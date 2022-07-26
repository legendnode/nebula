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

class Router
{
    /**
     * 路由组件映射
     *
     * @var array
     */
    private static $routerWidgetMap = [
        'option' => [
            'widget' => '\Nebula\Widgets\Option',
            'params' => [
                'action' => '',
                'optionName' => ''
            ],
        ],
        'plugin' => [
            'widget' => '\Nebula\Widgets\Plugin',
            'params' => [
                'action' => '',
                'pluginName' => '',
            ],
        ],
        'theme' => [
            'widget' => '\Nebula\Widgets\Theme',
            'params' => [
                'action' => '',
                'themeName' => '',
            ],
        ],
        'user' => [
            'widget' => '\Nebula\Widgets\User',
            'params' => [
                'action' => '',
                'uid' => null,
            ],
        ],
        'content' => [
            'widget' => '\Nebula\Widgets\Content',
            'params' => ['action' => ''],
        ],
        'article' => [
            'widget' => '\Nebula\Widgets\Content',
            'action' => 'article',
            'params' => ['id' => ''],
        ],
        'common' => [
            'widget' => '\Nebula\Widgets\Common',
            'params' => ['action' => ''],
        ],
        'index' => [
            'widget' => '\Nebula\Widgets\Index',
            'action' => 'render',
            'params' => [],
        ],
    ];

    /**
     * 路由分发
     *
     * @return void
     */
    public static function dispatch()
    {
        $pathInfo = Request::getInstance()->pathinfo;
        $routeFragment = explode('/', $pathInfo);

        $actionOption = self::$routerWidgetMap[isset($routeFragment[0]) && '' !== $routeFragment[0] ? array_shift($routeFragment) : 'index'] ?? null;
        if (null !== $actionOption) {
            // 参数键
            $paramkeys = array_keys($actionOption['params']);
            // 默认值
            $defaultValues = array_values($actionOption['params']);
            // 合并值
            $mergedValues = [];

            foreach ($defaultValues as $index => $value) {
                $mergedValues[$index] = $routeFragment[$index] ?? $value;
            }

            // 行动方法
            $action =  $actionOption['action'] ?? 'action';

            // 执行组件
            $actionOption['widget']::factory(array_combine($paramkeys, $mergedValues), 'dispatch')->$action();
        } else {
            Response::getInstance()->render('404');
        }
    }
}
