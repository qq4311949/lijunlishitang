<?php
namespace app\index\validate;

use think\Validate;
use think\Db;

class Foods extends Validate {

    protected $rule = [
        'shitang' => 'require|integer|gt:0',
        'date' => 'require|dateFormat:Y-m-d',
        'shiduan' => 'require|integer|gt:0',
        'cate_id' => 'require|integer|gt:0|checkCateId',
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
        'cate_id.require' => '菜品分类id不能为空',
        'cate_id.integer' => '菜品分类id必须为整型',
        'cate_id.gt' => '菜品分类id大于0',
    ];

    protected $scene = [
        'index' => ['shitang', 'date', 'shiduan'],
        'getList' => ['cate_id', 'shitang', 'shiduan'],
    ];

    protected function checkCateId($value){
        $where = [];
        $where['FID'] = $value;
        $count = Db::table('LJL_FOODGROUP_L')->where($where)->count();
        if(!$count){
            return '非法菜品分类id';
        }
        return true;
    }
}