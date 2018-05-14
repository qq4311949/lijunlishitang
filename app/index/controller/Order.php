<?php

namespace app\index\controller;

use app\common\event\Order as OrderEvent;
use app\common\event\Foods as FoodsEvent;
use app\common\event\Marker as MarkerEvent;

/**
 * 订单
 * Class Order
 * @package app\index\controller
 */
class Order extends Common {

    private static $event = null;

    public function __construct() {
        parent::__construct();
        if(self::$event === null){
            self::$event = new OrderEvent();
        }
    }

    /**
     * 去结算 确认订单
     */
    public function confirm() {
        $data = $this->request->post();
        parent::filter($data);
        $markerEvent = new MarkerEvent();
        $markerInfo = $markerEvent->getInfo($data['shitang']);
        $foodsEvent = new FoodsEvent();
        $saleTimeInfo = $foodsEvent->getSaleTimeList($data);
        $res = self::$event->getFoodsList($data);
        $this->assign('markerName', $markerInfo['FNAME']);
        $this->assign('date', date('Ymd', strtotime($data['date'])));
        $this->assign('saleTime', $saleTimeInfo['FNAME']);
        $this->assign('list', $res['list']);
        $this->assign('total', $res['total']);
        return $this->fetch();
    }

    /**
     * 付款
     */
    public function payment() {
        $data = $this->request->post();
        parent::filter($data);
        $res = self::$event->payment($data);
        return $res;
    }

    /**
     * 支付成功页面
     * @param int id 订单id
     * @return mixed
     */
    public function ok() {
        $data = $this->request->param();
        parent::filter($data);
        $info = self::$event->getOrderInfo($data);
        $this->assign('info', $info);
        return $this->fetch();
    }

    /**
     * 去取餐
     */
    public function today() {
        $list = self::$event->getTodayList();
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 订单列表
     * @return mixed
     */
    public function index() {
        $list = self::$event->getList();
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 订单详情
     * @param int id 订单id
     * @return mixed
     */
    public function item() {
        $data = $this->request->param();
        parent::filter($data);
        $res = self::$event->getInfo($data);
        $this->assign('info', $res['data']);
        $this->assign('is_can_cancel', $res['is_can_cancel']);
        $is_show_qrcode = getRouteByHttpReferer() == 'order/today' ? 1 : 0;
        $this->assign('is_show_qrcode', $is_show_qrcode);
        return $this->fetch();
    }

    /**
     * 取消订单
     * @param int id 订单id
     * @return \think\response\Json
     */
    public function cancel() {
        $data = $this->request->param();
        parent::filter($data);
        $msg = self::$event->cancel($data);
        $this->assign('msg', $msg);
        return $this->fetch();
    }
}
