<?php
namespace app\index\validate;

use think\Validate;

class User extends Validate {

    protected $rule = [
        'username' => 'require',
        'sex' => 'require|integer|between:0,1',
        'department' => 'require',
        'password' => 'require|min:6|max:12',
        'newpassword' => 'require|min:6|max:12',
        'repassword' => 'require|confirm:newpassword',
    ];

    protected $message = [
        'username.require' => '姓名不能为空',
        'sex.require' => '请选择性别',
        'sex.integer' => '性别必须为整型',
        'sex.between' => '性别必须在0,1之间',
        'department.require' => '部门不能为空',
        'password.require' => '密码不能为空',
        'password.min' => '密码最短6位',
        'password.max' => '密码最长12位',
        'newpassword.require' => '密码不能为空',
        'newpassword.min' => '密码最短6位',
        'newpassword.max' => '密码最长12位',
        'repassword.require' => '确认密码不能为空',
        'repassword.confirm' => '两次输入的密码不一致',
    ];

    protected $scene = [
        'info' => ['username', 'sex', 'department'],
        'reset' => ['password', 'newpassword', 'repassword'],
    ];
}