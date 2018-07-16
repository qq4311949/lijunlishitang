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
use think\Session;

class Stat extends Base {
    /**
     * 获取搜索页面数据
     * @param $params
     * @return array
     */
    public function getSearchData($params){
        $arr = [];
        switch ($params['type']) {
            case 3://订单流水
                $arr['action'] = url('marker/Stat/order');
                $arr['html'] = '<tr>
                                    <td>开始日期：</td>
                                    <td><input id="date1" name="date1" type="date" class="search_2"/></td>
                                </tr>';
                break;
            case 2://日统计
                $arr['action'] = url('marker/Stat/daily');
                $arr['html'] = '<tr>
                                    <td>开始日期：</td>
                                    <td><input id="date1" name="date1" type="date" class="search_2"/></td>
                                </tr>
                                <tr>
                                    <td>结束日期：</td>
                                    <td><input id="date2" name="date2" type="date" class="search_2"/></td>
                                </tr>';
                break;
            case 1://汇总
                $arr['action'] = url('marker/Stat/summary');
                $arr['html'] = '<tr>
                                    <td>开始日期：</td>
                                    <td><input id="date1" name="date1" type="date" class="search_2"/></td>
                                </tr>
                                <tr>
                                    <td>结束日期：</td>
                                    <td><input id="date2" name="date2" type="date" class="search_2"/></td>
                                </tr>';
                break;
        }
        return $arr;
    }

    /**
     * 获取汇总数据
     * @param $params
     * @return array
     */
    public function getSummaryData($params){
        if(config('app_status') == 'office'){
            $sql = "SELECT FID,CONVERT(varchar(100), FSALEDATE, 23) as FSALEDATE FROM LJL_Reservation WHERE FFOODMARKERID = '".Session::get('marker.id')."' AND CONVERT(varchar(100), FSALEDATE, 23) BETWEEN '". $params['date1'] ."' AND '". $params['date2'] ."'";
        }else{
            $sql = "SELECT FID,DATE_FORMAT(FSALEDATE, '%Y-%m-%d') as FSALEDATE FROM LJL_Reservation WHERE FFOODMARKERID = '".Session::get('marker.id')."' AND DATE_FORMAT(FSALEDATE, '%Y-%m-%d') BETWEEN '". $params['date1'] ."' AND '". $params['date2'] ."'";
        }
        $rows = Db::query($sql);
        $arr = [
            'total' => count($rows),
            'amount' => 0
        ];
        if(!empty($rows)){
            foreach($rows as $row){
                $arr['amount'] += Db::table('LJL_Reservationentry')->where(['FID' => $row['FID']])->sum('FAMOUNT');
            }
        }
        return $arr;
    }

    /**
     * 获取日统计数据
     * @param $params
     * @return array
     */
    public function getDailyData($params){
        if(config('app_status') == 'office'){
            $sql = "SELECT FID,CONVERT(varchar(100), FSALEDATE, 23) as FSALEDATE FROM LJL_Reservation WHERE FFOODMARKERID = '".Session::get('marker.id')."' AND CONVERT(varchar(100), FSALEDATE, 23) BETWEEN '". $params['date1'] ."' AND '". $params['date2'] ."'";
        }else{
            $sql = "SELECT FID,DATE_FORMAT(FSALEDATE, '%Y-%m-%d') as FSALEDATE FROM LJL_Reservation WHERE FFOODMARKERID = '".Session::get('marker.id')."' AND DATE_FORMAT(FSALEDATE, '%Y-%m-%d') BETWEEN '". $params['date1'] ."' AND '". $params['date2'] ."'";
        }
        $rows = Db::query($sql);
        $arr = [];
        if(!empty($rows)){
            foreach($rows as &$row){
                $sql = "SELECT SUM(FAMOUNT) as amount FROM LJL_Reservationentry WHERE FID = ".$row['FID'];
                $res = Db::query($sql);
                if(empty($res)){
                    continue;
                }
                if(!isset($arr[$row['FSALEDATE']])){
                    $arr[$row['FSALEDATE']] = [
                        'FSALEDATE' => $row['FSALEDATE'],
                        'total' => 1,
                        'amount' => $res[0]['amount']
                    ];
                }else{
                    $arr[$row['FSALEDATE']] = [
                        'FSALEDATE' => $row['FSALEDATE'],
                        'total' => $arr[$row['FSALEDATE']]['total'] + 1,
                        'amount' => decimal2($arr[$row['FSALEDATE']]['amount'] + $res[0]['amount'])
                    ];
                }
            }
        }
        return array_values($arr);
    }

    /**
     * 获取订单流水
     * @param $params
     * @return array
     */
    public function getOrderData($params){
        if(config('app_status') == 'office'){
            $sql = "SELECT FID,CONVERT(varchar(100), FSALEDATE, 23) as FSALEDATE,FBILLNO FROM LJL_Reservation WHERE FFOODMARKERID = '".Session::get('marker.id')."' AND CONVERT(varchar(100), FSALEDATE, 23) = '". $params['date1'] ."'";
        }else{
            $sql = "SELECT FID,DATE_FORMAT(FSALEDATE, '%Y-%m-%d') as FSALEDATE,FBILLNO FROM LJL_Reservation WHERE FFOODMARKERID = '".Session::get('marker.id')."' AND DATE_FORMAT(FSALEDATE, '%Y-%m-%d') = '". $params['date1'] ."'";
        }
        $rows = Db::query($sql);
        $arr = [];
        if(!empty($rows)){
            foreach($rows as &$row) {
                $sql = "SELECT SUM(FAMOUNT) as amount FROM LJL_Reservationentry WHERE FID = ".$row['FID'];
                $res = Db::query($sql);
                if(empty($res)){
                    continue;
                }
                $arr[] = [
                    'FSALEDATE' => $row['FSALEDATE'],
                    'FBILLNO' => $row['FBILLNO'],
                    'amount' => $res[0]['amount']
                ];
            }
        }
        return $arr;
    }
}
