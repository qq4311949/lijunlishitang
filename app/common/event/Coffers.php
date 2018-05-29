<?php
namespace app\common\event;

use app\common\event\User as UserEvent;
use app\common\logic\BillNosLogic;
use think\Db;
use think\Session;

class Coffers extends Base {

    /**
     * 获取余额详情
     * @return null|static
     */
    public function getBalanceInfo(){
        $where = [];
        $where['FEMPID'] = Session::get('user.id');
        $info = Db::table('LJL_EMPBalance')->where($where)->find();
        return $info;
    }

    /**
     * 获取交易记录列表
     * @param array $params
     * @return array
     */
    public function getRecordList($params){
        $sql = "SELECT * FROM LJL_RechargesOrPay WHERE FEMPID = " . Session::get('user.id');
        if(isset($params['date'])){
            if(config('app_status') == 'office'){
                $sql .= " AND CONVERT(varchar(100), FDATETIME, 23) = '" . $params['date'] . "'";
            }else{
                $sql .= " AND DATE_FORMAT(FDATETIME, '%Y-%m-%d') = '" . $params['date'] . "'";
            }
        }
        $rows = Db::query($sql);
        if(!empty($rows)){
            foreach($rows as & $row){
                $row['FAMOUNT'] = floatval($row['FAMOUNT']);
                $row['FDATETIME'] = substr($row['FDATETIME'], 0, 10);
            }
        }
        return $rows ?: [];
    }

    /**
     * 充值
     * @param array $params
     * @return \think\response\Json
     */
    public function recharge($params){
        $userEvent = new UserEvent();
        $info = $userEvent->getInfo();

        $billNosLogic = new BillNosLogic();
        $billNo = $billNosLogic->getBillNo(0);

        $data = [];
        $data['FEMPID'] = $info['FID'];
        $data['FEMPNUMBER'] = $info['FNUMBER'];
        $data['FBILLNO'] = $billNo;
        $data['FDATETIME'] = date('Y-m-d H:i:s');
        $data['FAMOUNT'] = $params['amount'];
        $data['FBILLTYPE'] = 0;//充值

        $res = Db::table('LJL_RechargesOrPay')->insert($data);
        if(!$res){
            failure(0, '充值失败');
        }
        $where = [];
        $where['FEMPID'] = Session::get('user.id');

        $res = Db::table('LJL_EMPBalance')->where($where)->setInc('FAMOUNT', $params['amount']);
        if(!$res){
            failure(0, '充值失败');
        }
        return success(1, [], url('index/Coffers/ok', ['sum' => $params['amount']]));
    }
}