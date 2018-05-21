<?php
namespace app\common\event;

use app\common\model\LjlEmpinfo as LjlEmpinfoModel;
use app\common\model\LjlEmpinfoL as LjlEmpinfoLModel;
use app\common\model\Empbalance as EmpbalanceModel;
use app\common\logic\EmpinfoLogic;
use app\common\event\Order as OrderEvent;
use org\wechat\Jssdk;
use think\Session;
use think\Db;

class Marker extends Base {

    /**
     * 登录
     * @param array $params
     * @return \think\response\Json
     */
    public function signin($params){
        $where = [];
        $where['FID'] = $params['shitang'];
        $where['FDOCUMENTSTATUS'] = 'C';
        $info = Db::table('LJL_FOODMARKER')->where($where)->find();
        if(empty($info)){
            failure(0, '该食堂不存在');
        }
        if($info['FDOCUMENTSTATUS'] != 'C'){
            failure(0, '该食堂已停用');
        }
        if(strcasecmp($params['password'], $info['FPASSWORD']) != 0){
            failure(0, '密码错误');
        }
        Session::set('marker.id', $info['FID']);
        return success(1, [], url('marker/Index/index'));
    }

    /**
     * 获取食堂列表
     * @return array
     */
    public function getList(){
        if(config('app_status') == 'office'){
            $sql = "SELECT t1.FID,t1.FDCTYPE,t2.FNAME FROM LJL_FOODMARKER t1 JOIN LJL_FOODMARKER_L t2 ON t2.FID = t1.FID 
                WHERE (CONVERT(varchar(100), t1.FDCLASTTIME, 108) > '".date('H:i:s')."' OR t1.FDCTYPE = 1) AND t1.FDOCUMENTSTATUS = 'C'";
        }else{
            $sql = "SELECT t1.FID,t1.FDCTYPE,t2.FNAME FROM LJL_FOODMARKER t1 JOIN LJL_FOODMARKER_L t2 ON t2.FID = t1.FID 
                WHERE (DATE_FORMAT(t1.FDCLASTTIME, '%H:%i:%s') > '".date('H:i:s')."' OR t1.FDCTYPE = 1) AND t1.FDOCUMENTSTATUS = 'C'";
        }
        $rows = Db::query($sql);
        return $rows ?: [];
    }

    /**
     * 获取食堂详情
     * @param $params
     * @return array
     */
    public function getInfo($params){
        if(is_array($params)){
            $where = $params;
        } else {
            $where['FID'] = $params;
        }
        $row = Db::table('LJL_FOODMARKER_L')->where($where)->find();
        return $row ?: [];
    }

    public function search($params){
        $orders = Db::query("SELECT FID FROM LJL_Reservation WHERE FDATE = '{$params['date']}' AND FFOODMARKERID = '".Session::get('marker.id')."'");
        if(empty($orders)){
            failure(0, '今天没有订单');
        }
        return success(1, [], url('marker/Index/meal', ['date' => $params['date']]));
    }

    /**
     * 统计
     * @param array $params
     * @return array
     */
    public function stats($params){
        $where = [];
        $where['FDATE'] = $params['date'];
        $where['FFOODMARKERID'] = Session::get('marker.id');
        $times = Db::table('LJL_Reservation')->where($where)->group('FDCTIME')->column('FDCTIME');
        if(empty($times)){
            return [];
        }
        if(config('app_status') == 'office'){
            $times = array_keys($times);
        }
        $rows = [];
        foreach ($times as $time) {
            $key = Db::table('LJL_SALETIMES_L')->where('FID', $time)->value('FNAME');
            $where['FDCTIME'] = $time;
            $orderIds = Db::table('LJL_Reservation')->where($where)->column('FID');
            if(config('app_status') == 'office'){
                $orderIds = array_keys($orderIds);
            }
            $items = Db::table('LJL_Reservationentry')->where('FID', 'in', $orderIds)->group('FFOODID')->field('FFOODID,SUM(FQTY) as FFOODNUM,SUM(FAMOUNT) AS FAMOUNT')->select();
            foreach($items as &$item){
                $item['FNAME'] = Db::table('LJL_FOODS_L')->where('FID', $item['FFOODID'])->value('FNAME');
                $item['FFOODNUM'] = $item['FFOODNUM'];
                $item['FAMOUNT'] = strpos($item['FAMOUNT'], '.') == 0 ? '0'.$item['FAMOUNT'] : $item['FAMOUNT'];
                unset($item['FFOODID']);
            }
            $rows[$key] = $items;
        }
        if(empty($rows)){
            return [];
        }
        return $rows;
    }

    public function getSignPackage(){
        $jssdk = new Jssdk(config('jssdk.appId'), config('jssdk.appSecret'));
        $arr = $jssdk->getSignPackage();
        return $arr;
    }
}
