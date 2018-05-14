<?php

namespace app\marker\controller;

use app\common\controller\Base;
use app\common\event\Stat as StatEvent;

class Stat extends Base {

	private static $event = null;

    public function __construct() {
        parent::__construct();
        if(self::$event === null){
            self::$event = new StatEvent();
        }
    }

    /**
     * 统计首页
     * @return mixed
     */
    public function index(){
        return $this->fetch();
    }

    /**
     * 搜索
     * @param int type 类型：1，汇总；2，日统计；3，订单流水
     * @return mixed
     */
    public function search(){
        $data = $this->request->param();
        parent::filter($data);
        $res = self::$event->getSearchData($data);
        $this->assign('type', $data['type']);
        $this->assign('action', $res['action']);
        $this->assign('html', $res['html']);
        return $this->fetch();
    }

    /**
     * 汇总
     * @param string date1 开始日期
     * @param string date2 结束日期
     * @return mixed
     */
    public function summary(){
        $data = $this->request->post();
        parent::filter($data);
        $list = self::$event->getSummaryData($data);
        $this->assign('list', $list);
        $this->assign('date1', $data['date1']);
        $this->assign('date2', $data['date2']);
        return $this->fetch();
    }

    /**
     * 日统计
     * @param string date1 开始日期
     * @param string date2 结束日期
     * @return mixed
     */
    public function daily(){
        $data = $this->request->post();
        parent::filter($data);
        $list = self::$event->getDailyData($data);
        $this->assign('list', $list);
        $this->assign('date1', $data['date1']);
        $this->assign('date2', $data['date2']);
        return $this->fetch();
    }

    /**
     * 订单流水
     * @param string date1 开始日期
     * @return mixed
     */
    public function order(){
        $data = $this->request->post();
        parent::filter($data);
        $list = self::$event->getOrderData($data);
        $this->assign('list', $list);
        $this->assign('date1', $data['date1']);
        return $this->fetch();
    }

}
