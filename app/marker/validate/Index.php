<?php
namespace app\marker\validate;

use think\Validate;

class Index extends Validate {

    protected $rule = [
        'date' => 'require|dateFormat:Y-m-d',
    ];

    protected $message = [
        'date.require' => '就餐日期不能为空',
        'date.dateFormat' => '就餐日期格式错误',
    ];

    protected $scene = [
        'search' => ['date'],
        'index' => ['date'],
    ];
}