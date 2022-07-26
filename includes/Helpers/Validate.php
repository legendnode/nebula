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

class Validate
{
    /**
     * 验证数据
     *
     * @var array
     */
    private $data = [];

    /**
     * 验证规则
     *
     * @var array
     */
    private $rules = [];

    /**
     * 验证结果
     *
     * @var array
     */
    public $result = [];

    /**
     * @param array $data 待验证数据
     * @param array $rules 验证规则
     * @return void
     */
    public function __construct($data, $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * 验证是否为邮箱
     *
     * @param string $value 值
     * @return bool
     */
    public function email($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * 必填验证
     *
     * @param string $name 数据项名称
     * @return bool
     */
    public function required($value)
    {
        return '' !== $value;
    }

    /**
     * 运行验证
     *
     * @return bool|array 验证成功返回 true，失败返回错误消息
     */
    public function run()
    {
        $this->result = [];

        foreach ($this->rules as $key => $rule) {
            foreach ($rule as $ruleItem) {
                $value = $this->data[$key] ?? '';
                if ('custom' === $ruleItem['type']) {
                    // 自定义表单校验
                    call_user_func($ruleItem['validator'], $ruleItem, $value, function ($message = null) use ($key) {
                        if (null !== $message) {
                            array_push($this->result, [
                                'key' => $key,
                                'message' => $message,
                            ]);
                        }
                    });
                } else {
                    // 内置表单验证
                    if (!$this->{$ruleItem['type']}($value)) {
                        array_push($this->result, [
                            'key' => $key,
                            'message' => $ruleItem['message'],
                        ]);
                    }
                }
            }
        }

        return count($this->result) === 0;
    }
}
