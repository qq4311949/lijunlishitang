<?php
namespace app\index\validate;

use think\Validate;

class Coffers extends Validate {

    protected $rule = [
        'date' => 'dateFormat:Y-m-d',
        'amount' => 'require|number|regex:amount',
        'sum' => 'require|integer|gt:0',
    ];

    protected $regex = [
        'amount' => '\d+(.\d{1,2})?',
    ];

    protected $message = [
        'date.dateFormat' => '就餐日期格式错误',
        'amount.require' => '充值金额不能为空',
        'amount.number' => '充值金额必须为数字',
        'amount.regex' => '充值金额格式错误',
        'sum.require' => '充值金额id不能为空',
        'sum.integer' => '充值金额id必须为整型',
        'sum.gt' => '充值金额id必须大于0',
    ];

    protected $scene = [
        'index' => ['date'],
        'recharge' => ['amount'],
        'ok' => ['sum'],
    ];
}