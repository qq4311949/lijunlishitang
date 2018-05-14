<?php

namespace app\marker\controller;

use app\common\controller\Base;
use app\common\event\Marker as MarkerEvent;

class Sign extends Base {

	private static $event = null;

    public function __construct() {
        parent::__construct();
        if(self::$event === null){
            self::$event = new MarkerEvent();
        }
    }

    /**
     * 登录
     * @return mixed
     */
    public function in() {
        if($this->request->isPost()){
            $data = $this->request->post();
            parent::filter($data);
            $res = self::$event->signin($data);
            return $res;
        }else{
            $markers = self::$event->getList();
            $this->assign('markers', $markers);
            return $this->fetch();
        }
    }

    /**
     * 退出登录
     */
    public function out() {
        session(null);
        $this->redirect(url('marker/Sign/in'));
    }
}
