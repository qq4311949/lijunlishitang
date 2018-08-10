<?php

namespace app\index\controller;

use app\common\event\Comment as CommentEvent;
use app\common\event\Marker as MarkerEvent;

class Comment extends Common {

    private static $event = null;

    public function __construct() {
        parent::__construct();
        if(self::$event === null){
            self::$event = new CommentEvent();
        }
    }

    /**
     * 评论列表首页
     * @return mixed
     */
    public function index() {
        $list = self::$event->getList(1);
        $this->assign('list', $list);
        $total = self::$event->getListCount();
        $this->assign('total', $total);
        return $this->fetch();
    }

    /**
     * 获取评论列表
     * @param int page
     * @return \think\response\Json
     */
    public function getList() {
        $page = $this->request->post('page', 1, 'int');
        $res = self::$event->getList($page);
        return success(1, $res);
    }

    /**
     * 评论首页
     * @param int id 食堂id
     * @param string level 评级
     * @param string content 内容
     * @return mixed
     */
    public function comment() {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            parent::filter($data);
            $res = self::$event->comment($data);
            return $res;
        } else {
            $markerEvent = new MarkerEvent();
            $this->view->markers = $markerEvent->getMarkerList();
            return $this->fetch();
        }
    }
}
