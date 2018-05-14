<?php

namespace app\marker\controller;

use app\common\event\Order as OrderEvent;

/**
 * 订单
 * Class Order
 * @package app\index\controller
 */
class Order extends Common {

    private static $event = null;

    public function __construct() {
        parent::__construct();
        if (self::$event === null) {
            self::$event = new OrderEvent();
        }
    }

    /**
     * 扫码执行
     * @param int id 订单id
     * @return mixed
     */
    public function scan() {
        $data = $this->request->param();
        parent::filter($data);
        $info = self::$event->scan($data);
        $this->assign('info', $info);
        return $this->fetch();
    }

    /**
     * 取餐
     * @param int id 订单id
     * @return mixed
     */
    public function handle() {
        $data = $this->request->post();
        parent::filter($data);
        $res = self::$event->handle($data);
        return $res;
    }
}
