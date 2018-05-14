<?php

namespace app\marker\controller;

use app\common\controller\Base;
use think\Session;

class Common extends Base {

    public function __construct() {
        parent::__construct();
        if (!Session::get('marker.id')) {
            $toLoginUrl = url('marker/Sign/in');
            if($this->request->isAjax()){
                return ['ret' => -1, 'url' => $toLoginUrl, 'msg' => '请登录'];
            }else{
                $this->redirect($toLoginUrl);
            }
        }
    }
}
