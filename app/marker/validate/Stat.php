<?php
namespace app\marker\validate;

use think\Validate;

class Stat extends Validate {

    protected $rule = [
        'type' => 'require|integer|in:1,2,3',
        'date1' => 'require|dateFormat:Y-m-d',
        'date2' => 'require|dateFormat:Y-m-d',
    ];

    protected $message = [
        'type.require' => '搜索类型不能为空',
        'type.integer' => '搜索类型必须为整型',
        'type.in' => '搜索类型必须在1,2,3范围内',
        'date1.require' => '开始日期不能为空',
        'date1.dateFormat' => '开始日期格式错误',
        'date2.require' => '结束日期不能为空',
        'date2.dateFormat' => '结束日期格式错误',
    ];

    protected $scene = [
        'search' => ['type'],
        'summary' => ['date1', 'date2'],
        'daily' => ['date1', 'date2'],
        'order' => ['date1'],
    ];
}