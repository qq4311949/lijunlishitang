<?php

namespace app\marker\controller;

use app\common\event\Marker as MarkerEvent;
use think\Session;

class Index extends Common {

	private static $event = null;

    public function __construct() {
        parent::__construct();
        if(self::$event === null){
            self::$event = new MarkerEvent();
        }
    }

    /**
     * 首页
     * @return mixed
     */
    public function index(){
        $markerInfo = self::$event->getInfo(Session::get('marker.id'));
        $this->assign('markerName', $markerInfo['FNAME']);
//        $sign = self::$event->getSignPackage();
//        $this->assign('sign', $sign);
        return $this->fetch();
    }


    /**
     * 搜索
     * @return mixed
     */
    public function search() {
        if($this->request->isPost()){
            $data = $this->request->post();
            parent::filter($data);
            $res = self::$event->search($data);
            return $res;
        }else{
            return $this->fetch();
        }
    }

    /**
     * 订餐管理
     * @return mixed
     */
    public function meal(){
        $data = $this->request->param();
        parent::filter($data);
        $markerInfo = self::$event->getInfo(Session::get('marker.id'));
        $list = self::$event->stats($data);
        $this->assign('markerName', $markerInfo['FNAME']);
        $this->assign('date', date('Ymd', strtotime($data['date'])));
        $this->assign('list', $list);
        return $this->fetch();
    }
}
