<?php
namespace app\common\logic;

use think\Db;

class EmpinfoLogic {
    /**
     * 获取员工详情
     * @param array $where
     * @return array
     */
	public function getInfo(array $where){
        $fields = 't1.FID,t1.FNUMBER,t1.FPASSWORD,t1.FSEX,t1.FDEPARTMENTID,t1.FFORBIDSTATUS,t2.FNAME';
		$row = Db::table('LJL_EMPINFO')
            ->alias('t1')
            ->join('LJL_EMPINFO_L t2', 't2.FID = t1.FID')
            ->where($where)
            ->field($fields)
            ->find();
        return $row ?: [];
	}

    /**
     * 获取员工自增表id
     * @return int|string
     */
	public function getAutoIncId(){
        $data = [];
        $data['Column1'] = 1;
        $insertId = Db::table('Z_LJL_EMPINFO')->insertGetId($data);
        Db::table('Z_LJL_EMPINFO')->delete(['Id' => $insertId]);
        return $insertId;
    }

    /**
     * 获取员工自增扩展表id
     * @return int|string
     */
    public function getLAutoIncId(){
        $data = [];
        $data['Column1'] = 1;
        $insertId = Db::table('Z_LJL_EMPINFO_L')->insertGetId($data);
        Db::table('Z_LJL_EMPINFO_L')->delete(['Id' => $insertId]);
        return $insertId;
    }
}