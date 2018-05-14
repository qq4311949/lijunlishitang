<?php
namespace app\common\logic;

use think\Db;

class ReservationLogic {

    /**
     * 获取员工订餐记录表自增id
     * @return int|string
     */
    public function getAutoIncId(){
        $data = [];
        $data['Column1'] = 1;
        $insertId = Db::table('Z_LJL_Reservation')->insertGetId($data);
        Db::table('Z_LJL_Reservation')->delete(['Id' => $insertId]);
        return $insertId;
    }
}