<?php
namespace app\marker\validate;

use think\Validate;

class Order extends Validate {

    protected $rule = [
        'id' => 'require|integer|gt:0',
    ];


    protected $message = [
        'id.require' => '订单id不能为空',
        'id.integer' => '订单id必须为整型',
        'id.gt' => '订单id大于0',
    ];

    protected $scene = [
        'scan' => ['id'],
        'handle' => ['id'],
    ];
}