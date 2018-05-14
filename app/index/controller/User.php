<?php

namespace app\index\controller;

use app\common\event\User as UserEvent;

class User extends Common {

    private static $event = null;

    public function __construct() {
        parent::__construct();
        if(self::$event === null){
            self::$event = new UserEvent();
        }
    }

    /**
     * 完善个人信息
     */
    public function info(){
        if($this->request->isPost()){
            $data = $this->request->post();
            parent::filter($data);
            $res = self::$event->setInfo($data);
            return $res;
        }else{
            $info = self::$event->getInfo();
            $this->assign('info', $info);
            return $this->fetch();
        }
    }

    /**
     * 重置密码
     */
    public function reset() {
        if($this->request->isPost()){
            $data = $this->request->post();
            parent::filter($data);
            $res = self::$event->reset($data);
            return $res;
        }else{
            return $this->fetch();
        }
    }

    /**
     * 退出登录
     */
    public function logout() {
        session(null);
        $this->redirect(url('index/Login/login'));
    }
}
