<?php

namespace Content\Plugins\HelloWorld;

use Nebula\Plugin;

/**
 * name: 你好世界
 * url: https://www.nbacms.com/
 * description: 这是默认插件，在底部输出一段毒鸡汤
 * version: 1.0
 * author: Noah Zhang
 * author_url: http://www.nbacms.com/
 */
class Main
{
    /**
     * 启用插件
     *
     * @return void
     */
    public static function activate()
    {
        Plugin::factory('admin/copyright.php')->begin = __CLASS__ . '::render';
        Plugin::factory('admin/common-js.php')->script = __CLASS__ . '::script';
    }

    /**
     * 停用插件
     *
     * @return void
     */
    public static function deactivate()
    {
    }

    /**
     * 插件配置
     *
     * @param $renderer 渲染器
     * @return void
     */
    public static function config($renderer)
    {
        $renderer->setValue('api', 'https://api.oick.cn/dutang/api.php');
        $renderer->setTemplate(function ($data) {
            include __DIR__ . '/config.php';
        });
    }

    public static function render()
    {
        include __DIR__ . '/views/copyright.php';
    }

    public static function script($data)
    {
        include __DIR__ . '/views/script.php';
    }
}
