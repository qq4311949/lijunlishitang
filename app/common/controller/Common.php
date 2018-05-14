<?php

namespace app\common\controller;

use think\Session;

class Common extends Base {

    public function __construct() {
        parent::__construct();
        if (!Session::get('user.id')) {
            $toLoginUrl = url('index/Login/login');
        	if($this->request->isAjax()){
                return ['ret' => -1, 'url' => $toLoginUrl, 'msg' => '请登录'];
        	}else{
            	$this->redirect($toLoginUrl);
        	}
        }
    }
}
