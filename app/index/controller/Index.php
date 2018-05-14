<?php

namespace app\index\controller;

use app\common\event\User as UserEvent;

class Index extends Common {

    public function index() {
        $userEvent = new UserEvent();
        $info = $userEvent->getInfo();
        $this->assign('info', $info);
        return $this->fetch();
    }
}
