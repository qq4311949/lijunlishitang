<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__miss__'    => 'index/Error/index',

    'home'        => ['index/Index/index', ['method' => 'get']],

    'login'       => ['index/Login/login', ['method' => 'get|post']],
    'register'    => ['index/Login/register', ['method' => 'get|post']],
    'push'        => ['index/Login/push', ['method' => 'post']],
    'forget'      => ['index/Login/forget', ['method' => 'get|post']],
    'reset'       => ['index/Login/reset', ['method' => 'get|post']],

    '[user]'      => [
        'reset'  => ['index/User/reset', ['method' => 'get|post']],
        'logout' => ['index/User/logout', ['method' => 'get']],
        'info'   => ['index/User/info', ['method' => 'get|post']],
    ],

    '[foods]'     => [
        'search' => ['index/Foods/search', ['method' => 'get']],
        'index'  => ['index/Foods/index', ['method' => 'get|post']],
        'list'   => ['index/Foods/getList', ['method' => 'post']],
    ],

    '[order]'     => [
        'index'      => ['index/Order/index', ['method' => 'get']],
        'today'      => ['index/Order/today', ['method' => 'get']],
        'item/:id'   => ['index/Order/item', ['method' => 'get'], ['id' => '\d+']],
        'cancel/:id' => ['index/Order/cancel', ['method' => 'get'], ['id' => '\d+']],
        'confirm'    => ['index/Order/confirm', ['method' => 'post']],
        'payment'    => ['index/Order/payment', ['method' => 'post']],
        'ok/:id'     => ['index/Order/ok', ['method' => 'get'], ['id' => '\d+']],
    ],

    '[coffers]'   => [
        'index'    => ['index/Coffers/index', ['method' => 'get|post']],
        'search'   => ['index/Coffers/search', ['method' => 'get']],
        'recharge' => ['index/Coffers/recharge', ['method' => 'get|post']],
        'ok/:sum'  => ['index/Coffers/ok', ['method' => 'get'], ['sum' => '\d+(\.\d{1,2})?']],
    ],
    // 食堂登录
    'signin'      => ['marker/Sign/in', ['method' => 'get|post']],
    'signout'     => ['marker/Sign/out', ['method' => 'get']],

    '[marker]'    => [
        'index'             => ['marker/Index/index', ['method' => 'get']],
        'search'            => ['marker/Index/search', ['method' => 'get|post']],
        'meal'              => ['marker/Index/meal', ['method' => 'get|post']],
        'stat/index'        => ['marker/Stat/index', ['method' => 'get']],
        'stat/search/:type' => ['marker/Stat/search', ['method' => 'get'], ['type' => '\d+']],
        'stat/summary'      => ['marker/Stat/summary', ['method' => 'post']],
        'stat/daily'        => ['marker/Stat/daily', ['method' => 'post']],
        'stat/order'        => ['marker/Stat/order', ['method' => 'post']],
        'scan/:id'          => ['marker/Order/scan', ['method' => 'get'], ['id' => '\d+']],
        'handle'            => ['marker/Order/handle', ['method' => 'post']],
    ],
];
