<?php

namespace app\common\controller;

use think\Controller;

class Base extends Controller {

    /**
     * 过滤层
     * @param $data
     * @param $code 2：非法参数
     */
    protected function filter($data, $code = 0) {
        $validate = validate($this->request->controller());
        if (!$validate->scene($this->request->action())->check($data)) {
            // 非法参数
            failure($code, $validate->getError());
        }
    }
}
