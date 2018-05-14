<?php
namespace app\marker\validate;

use think\Validate;

class Sign extends Validate {

    protected $rule = [
        'shitang' => 'require|integer|gt:0',
        'password' => 'require|min:6|max:12',
    ];

    protected $message = [
        'shitang.require' => '就餐食堂不能为空',
        'shitang.integer' => '就餐食堂必须为整型',
        'shitang.regex' => '就餐食堂id大于0',
        'password.require' => '密码不能为空',
        'password.min' => '密码最短6位',
        'password.max' => '密码最长12位',
    ];

    protected $scene = [
        'in' => ['shitang', 'password'],
    ];
}