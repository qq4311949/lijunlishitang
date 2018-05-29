<?php
namespace app\common\logic;

use think\Db;

class BillNosLogic {
    /**
     * 获取序列号 存储过程
     * @param $type
     * @return mixed
     */
    public function getBillNo($type){
        if(config('app_status') == 'office'){
            if($type == 0){//充值
                $sql = "DECLARE @BILLNO VARCHAR(40)
                    exec GETMAXNUM 'Recharges',@BILLNO output  --'Recharges', 'PAY', 'Refund'
                    select @BILLNO";
            }elseif($type == 1){//消费
                $sql = "DECLARE @BILLNO VARCHAR(40)
                    exec GETMAXNUM 'PAY',@BILLNO output  --'Recharges', 'PAY', 'Refund
                    select @BILLNO";
            }elseif($type == 2){//退款
                $sql = "DECLARE @BILLNO VARCHAR(40)
                    exec GETMAXNUM 'Refund',@BILLNO output  --'Recharges', 'PAY', 'Refund'
                    select @BILLNO";
            }
            $res = Db::query($sql);
            return $res[0][''];
        }else{
            if($type == 0){//充值
                $where['NOTYPE'] = 'Recharges';
                $where['ID'] = 2;
                $value = Db::table('LJL_BILLNOS')->where($where)->value('VALUE');
                $data['VALUE'] = str_pad(intval($value) + 1, 8, '0', STR_PAD_LEFT);
                Db::table('LJL_BILLNOS')->where($where)->update($data);
            }elseif($type == 1){//消费
                $where['NOTYPE'] = 'PAY';
                $where['ID'] = 2;
                $value = Db::table('LJL_BILLNOS')->where($where)->value('VALUE');
                $data['VALUE'] = str_pad(intval($value) + 1, 8, '0', STR_PAD_LEFT);
                Db::table('LJL_BILLNOS')->where($where)->update($data);
            }
            return $data['VALUE'];
        }
    }
}