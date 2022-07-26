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

use Exception;
use Nebula\Widgets\Option;

class Response
{
    /**
     * 单例实例
     *
     * @var Response
     */
    private static $instance;

    private function __construct()
    {
    }

    /**
     * 设置 cookie
     *
     * @param string $name
     * @param string $value
     * @param int $expiresOrOptions
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return $this
     */
    public function setCookie($name, $value, $expiresOrOptions, $path, $domain, $secure, $httponly)
    {
        setrawcookie($name, rawurlencode($value), $expiresOrOptions, $path, $domain, $secure, $httponly);
        return $this;
    }

    /**
     * 加载视图
     *
     * @param string|array $view 视图文件
     * @param array $data 视图数据
     * @return void
     */
    public function render($view, $data = [])
    {
        // 当前主题
        $theme = Option::factory()->get('theme');
        $data['theme_config'] = $theme['config'];

        header('Content-Type: text/html; charset=utf-8');

        ob_start();
        extract($data);

        $views = is_array($view) ? $view : [$view];
        foreach ($views as $view) {
            $filePath = NEBULA_ROOT_PATH . 'content/themes/' . $theme['name'] . '/' . $view . '.php';
            if (file_exists($filePath)) {
                include $filePath;
            } else {
                throw new Exception("主题缺少 {$view} 文件");
            }
        }

        $html = ob_get_contents();
        ob_end_clean();
        echo $html;
    }

    /**
     * 响应 JSON 数据
     *
     * @param mixed $data 数据
     * @param int $code 错误码
     * @param string $message 错误消息
     * @return void
     */
    public function sendJSON($data = null, $code = 0, $message = '处理成功')
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ]);
        exit;
    }

    /**
     * 重定向
     *
     * @param string $url 重定向地址
     * @param callable $callback 回调函数
     * @return void
     */
    public function redirect($url, $callback = null)
    {
        header('Location:' . $url);

        if (null !== $callback) {
            call_user_func($callback, $url);
        }

        exit;
    }

    /**
     * 获取单例实例
     *
     * @return Response
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
