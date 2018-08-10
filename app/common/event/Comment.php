<?php
// +----------------------------------------------------------------------
// | WebService 
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.ucaijia.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Mr.Maybe <260591808@qq.com> <http://www.ucaijia.com>
// +----------------------------------------------------------------------

namespace app\common\event;

use think\Db;

class Comment extends Base {

    const PAGE_SIZE = 20;

    public function getListCount() {
        $count = Db::table('LJL_BILLEVALUATE')->count();
        return ceil($count / self::PAGE_SIZE);
    }

    /**
     * 获取评论列表
     * @param int $page
     * @return array
     */
    public function getList($page) {
        $length = self::PAGE_SIZE;
        $offset = ($page - 1) * $length;
        $rows = Db::table('LJL_BILLEVALUATE')->order('')->limit($offset, $length)->select();
        if (!empty($rows)) {
            foreach ($rows as &$row) {
                if (!isset($row['FDATETIME'])) {
                    $row['FDATETIME'] = date('Y-m-d');
                } else {
                    // TODO
                }
                $row['EVALUATIONS'] = mb_substr($row['EVALUATIONS'], 0, 25);
                if (strlen($row['EVALUATIONS']) > 25) {
                    $row['EVALUATIONS'] .= '...';
                }
            }
        }
        return !empty($rows) ? $rows: [];
    }

    /**
     * 评论
     * @param $params
     * @return \think\response\Json
     */
    public function comment($params) {
        $data['FID'] = $params['marker'];
        $data['EVALUATE'] = $params['level'];
        $data['EVALUATIONS'] = $params['content'];
        // TODO 评论时间
        //$data[''] = '';
        $res = Db::table('LJL_BILLEVALUATE')->insert($data);
        if (!$res) {
            failure(0, '提交评论失败');
        }
        return success(1, [], url('index/Comment/index'));
    }

}