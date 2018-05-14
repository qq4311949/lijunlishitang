<?php

namespace app\index\controller;

use app\common\event\Coffers as CoffersEvent;

/**
 * 我的金库
 * Class Coffers
 * @package app\index\controller
 */
class Coffers extends Common {

    private static $event = null;

    public function __construct() {
        parent::__construct();
        if(self::$event === null){
            self::$event = new CoffersEvent();
        }
    }

    /**
     * 金库首页
     * @return mixed
     */
    public function index() {
        $data = [];
        if($this->request->isPost()){
            $data = $this->request->post();
            parent::filter($data);
        }
        $balance = self::$event->getBalanceInfo();
        $records = self::$event->getRecordList($data);
        $this->assign('balance', $balance);
        $this->assign('records', $records);
        return $this->fetch();
    }

    public function search() {
        return $this->fetch();
    }

    /**
     * 充值
     * @return mixed
     */
    public function recharge() {
        if($this->request->isPost()){
            $data = $this->request->post();
            parent::filter($data);
            $res = self::$event->recharge($data);
            return $res;
        }
        return $this->fetch();
    }

    /**
     * 充值成功
     * @return mixed
     */
    public function ok() {
        $data = $this->request->param();
        parent::filter($data);
        $this->assign('amount', $data['sum']);
        return $this->fetch();
    }
}
