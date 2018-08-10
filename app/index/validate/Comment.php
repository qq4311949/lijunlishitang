<?php
namespace app\index\validate;

use think\Validate;

class Comment extends Validate {

    protected $rule = [
        'marker' => 'require|integer|gt:0',
        'level' => 'require',
        'content' => 'require'
    ];

    protected $message = [
        'marker.require' => '食堂id不能为空',
        'marker.integer' => '食堂id必须为整型',
        'marker.gt' => '食堂id大于0',
        'level.require' => '评级不能为空',
        'content.require' => '内容不能为空',
    ];

    protected $scene = [
        'comment' => ['marker', 'level', 'content']
    ];
}