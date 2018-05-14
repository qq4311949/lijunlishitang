<?php

namespace app\index\controller;

use app\common\controller\Base;
use think\Session;

class Common extends Base {

    public function __construct() {
        parent::__construct();
        if (!Session::get('user.id')) {
            $this->redirect(url('index/Login/login'));
        }
    }
}
