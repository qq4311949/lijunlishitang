<?php
namespace app\index\validate;

use think\Validate;

class Order extends Validate {

    protected $rule = [
        'shitang' => 'require|integer|gt:0',
        'date' => 'require|dateFormat:Y-m-d',
        'shiduan' => 'require|integer|gt:0',
        'carts' => 'require|checkCarts:',
        'id' => 'require|integer|gt:0',
        'level' => 'require',
        'content' => 'require'
    ];

    protected $regex = [
        'amount' => '\d+(.\d{1,2})?',
    ];

    protected $message = [
        'shitang.require' => '就餐食堂不能为空',
        'shitang.integer' => '就餐食堂必须为整型',
        'shitang.regex' => '就餐食堂id大于0',
        'date.require' => '就餐日期不能为空',
        'date.dateFormat' => '就餐日期格式错误',
        'shiduan.require' => '就餐时段id不能为空',
        'shiduan.integer' => '就餐时段id必须为整型',
        'shiduan.gt' => '就餐时段id大于0',
        'carts.require' => '菜品必选',
        'carts.checkCarts' => '菜品必选',
        'id.require' => '订单id不能为空',
        'id.integer' => '订单id必须为整型',
        'id.gt' => '订单id大于0',
        'level.require' => '评级不能为空',
        'content.require' => '内容不能为空',
    ];

    protected $scene = [
        'confirm' => ['shitang', 'date', 'shiduan', 'carts'],
        'payment' => ['shitang', 'date', 'shiduan', 'carts'],
        'ok' => ['id'],
        'item' => ['id'],
        'cancel' => ['id'],
        'comment' => ['id'],
        'post' => ['id', 'level', 'content'],
        'scan' => ['id'],
    ];

    protected function checkCarts($value){
        $arr = json_decode($value, true);
        if(empty($arr)){
            return false;
        }
        return true;
    }
}