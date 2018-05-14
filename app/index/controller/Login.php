<?php

namespace app\index\controller;

use app\common\controller\Base;
use app\common\event\Login as LoginEvent;
use think\Cookie;

class Login extends Base {

	private static $event = null;

    public function __construct() {
        parent::__construct();
        if(self::$event === null){
            self::$event = new LoginEvent();
        }
    }

    /**
     * 登录
     * @return mixed
     */
    public function login() {
        if($this->request->isPost()){
            $data = $this->request->post();
            parent::filter($data);
            $res = self::$event->login($data);
            return $res;
        }else{
            $userNo = '';
            if(Cookie::has('userNo')){
                $userNo = Cookie::get('userNo');
            }
            $this->assign('userNo', $userNo);
            return $this->fetch();
        }
    }

    /**
     * 注册
     * @return mixed
     */
    public function register() {
        if($this->request->isPost()){
            $data = $this->request->post();
            parent::filter($data);
            $res = self::$event->register($data);
            return $res;
        }else{
            return $this->fetch();
        }
    }

    /**
     * 忘记密码
     * @return mixed
     */
    public function forget() {
        if($this->request->isPost()){
            $data = $this->request->post();
            parent::filter($data);
            $res = self::$event->forget($data);
            return $res;
        }else{
            return $this->fetch();
        }
    }
}
