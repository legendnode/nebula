<?php

namespace {
    // 定义类片段别名
    define('NEBULA_CLASS_FRAGMENT_ALIASES', [
        'Nebula' => 'includes',
        'Content' => 'content',
        'Themes' => 'themes',
        'Plugins' => 'plugins',
    ]);

    // 自动加载
    spl_autoload_register(function ($className) {
        $classFragments = explode('\\', $className);

        array_walk($classFragments, function (&$fragment) {
            if (isset(NEBULA_CLASS_FRAGMENT_ALIASES[$fragment])) {
                $fragment = NEBULA_CLASS_FRAGMENT_ALIASES[$fragment];
            }
        });

        $filename = NEBULA_ROOT_PATH . implode('/', $classFragments) . '.php';
        if (file_exists($filename)) {
            include $filename;
        }
    });
}

namespace Nebula {
    class Common
    {
        /**
         * 初始化
         *
         * @return void
         */
        public static function init()
        {
            // 初始化异常处理
            if (defined(NEBULA_DEBUG) || false === NEBULA_DEBUG) {
                set_exception_handler(function ($e) {
                    ob_end_clean();
                    ob_start(function ($buffer) {
                        return $buffer;
                    });
                    Response::getInstance()->render('500');
                    exit;
                });
            }

            // 插件初始化
            Plugin::init(\Nebula\Widgets\Option::alloc()->plugins);
        }

        /**
         * 生成随机字符串
         *
         * @param int $length 字符串长度
         * @param bool $mixedCase 混合大小写
         * @param bool $specialChars 是否有特殊字符
         */
        public static function randString($length, $mixedCase = false, $specialChars = false)
        {
            $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';

            if ($mixedCase) {
                $chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            }
            if ($specialChars) {
                $chars .= '.!@#$%^&*()';
            }

            $result = '';
            $max = strlen($chars) - 1;
            for ($i = 0; $i < $length; $i++) {
                $result .= $chars[rand(0, $max)];
            }
            return $result;
        }

        /**
         * 对字符串进行 hash 加密
         *
         * @param string $string 目标字符串
         * @param null|string $salt 32 位扰码
         * @return string 哈希值
         */
        public static function hash($string, $salt = null)
        {
            $salt = null === $salt ? self::randString(32) : $salt;
            $hash = '';
            $pos = 0;
            $saltPos = 0;

            while ($pos < strlen($string)) {
                if ($saltPos === strlen($salt)) {
                    $saltPos = 0;
                }

                $hash .= chr(ord($string[$pos]) + ord($salt[$saltPos]));

                $pos++;
                $saltPos++;
            }

            return $salt . md5($hash);
        }

        /**
         * 验证 hash
         *
         * @param string $from 源字符串
         * @param string $to 目标字符串
         * @return bool 是否验证成功
         */
        public static function hashValidate($from, $to)
        {
            return self::hash($from, substr($to, 0, 32)) === $to;
        }
    }
}
