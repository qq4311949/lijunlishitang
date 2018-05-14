<?php

namespace app\index\controller;

use app\common\event\Foods as FoodsEvent;
use app\common\event\Marker as MarkerEvent;

/**
 * 订餐
 * Class Cart
 * @package app\index\controller
 */
class Foods extends Common {

    private static $event = null;

    public function __construct() {
        parent::__construct();
        if(self::$event === null){
            self::$event = new FoodsEvent();
        }
    }

    /**
     * 搜索
     * @return mixed
     */
    public function search() {
        // 食堂列表
        $markerEvent = new MarkerEvent();
        $foodMarkers = $markerEvent->getList();
        // 时间段列表
        $saleTimes = self::$event->getSaleTimeList();
        $this->assign('foodMarkers', $foodMarkers);
        $this->assign('saleTimes', $saleTimes);
        return $this->fetch();
    }

    /**
     * 选取菜品
     * @return mixed
     */
    public function index() {
        $data = $this->request->post();
        parent::filter($data);
        $markerEvent = new MarkerEvent();
        $markerInfo = $markerEvent->getInfo($data['shitang']);
        $cates = self::$event->getCateList();
        $this->assign('markerName', $markerInfo['FNAME']);
        $this->assign('date', date('n月d日', strtotime($data['date'])));
        $this->assign('cates', $cates);
        return $this->fetch();
    }

    /**
     * 获取菜品列表
     * @return array|\think\response\Json
     */
    public function getList(){
        $data = $this->request->post();
        parent::filter($data);
        $res = self::$event->getList($data);
        return $res;
    }
}
