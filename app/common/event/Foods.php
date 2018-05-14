<?php
namespace app\common\event;

use think\Db;

class Foods extends Base {

    /**
     * 获取时间段列表
     * @param array $params
     * @return array
     */
    public function getSaleTimeList($params = []){
    	$where['t1.FDOCUMENTSTATUS'] = 'C';
        $buildSql = Db::table('LJL_SALETIMES')
            ->alias('t1')
            ->join('LJL_SALETIMES_L t2', 't2.FID = t1.FID')
            ->field('t2.*');
        if(isset($params['shiduan'])){
            $where['t2.FID'] = $params['shiduan'];
            $rows = $buildSql->where($where)->find();
        }else{
            $rows = $buildSql->where($where)->select();
        }
        return $rows ?: [];
    }

    /**
     * 获取菜品列表
     * @param $params
     * @return array|\think\response\Json
     */
    public function getList($params){
        $where = [];
        $where['FID'] = $params['shitang'];
        $type = Db::table('LJL_FOODMARKER')->where($where)->value('FDCTYPE');
        if($type == 1){//次日
            if(config('app_status') == 'office'){
                $FDATE = date('Y-m-d', strtotime('+1 day')) . ' 23:59:59.999';
            }else{
                $FDATE = date('Y-m-d', strtotime('+1 day')) . ' 23:59:59';
            }
        }else{//当天
            if(config('app_status') == 'office'){
                $FDATE = date('Y-m-d') . ' 23:59:59.999';
            }else{
                $FDATE = date('Y-m-d') . ' 23:59:59';
            }
        }
        $hour = date('G');
		// $sql = "SELECT t1.FID,t1.FSALEMAXQTY,t1.FSALEMINQTY,t1.FIMAGE,t2.FNAME,t2.FDESCRIPTION FROM LJL_FOODS t1 
  //           INNER JOIN LJL_FOODS_L t2 ON t2.FID = t1.FID 
  //           INNER JOIN LJL_FOODSALETIMES t3 ON t3.FID = t1.FID 
  //           INNER JOIN LJL_SALETIMES t4 ON t4.FID = t3.FSALETIME
  //           WHERE t1.FMAKER = '{$params['shitang']}' AND t1.FFOODGROUPID = '{$params['cate_id']}' 
  //           AND t3.FSALETIME = '{$params['shiduan']}' AND '{$hour}' BETWEEN t4.FSTRHOUR AND t4.FENDHOUR";
        $sql = "SELECT t1.FID,t1.FSALEMAXQTY,t1.FSALEMINQTY,t1.FIMAGE,t2.FNAME,t2.FDESCRIPTION FROM LJL_FOODS t1 
            INNER JOIN LJL_FOODS_L t2 ON t2.FID = t1.FID 
            WHERE t1.FMAKER = '{$params['shitang']}' AND t1.FFOODGROUPID = '{$params['cate_id']}'";
        $rows = Db::query($sql);
        if(!empty($rows)){
            $where1 = [];
            $where1['t1.FMAKER'] = $params['shitang'];
            $where1['t1.FDOCUMENTSTATUS'] = 'C';
            $where1['t1.FDATE'] = ['LT', $FDATE];
            foreach($rows as &$row){
                $row['FSALEMAXQTY'] = floatval($row['FSALEMAXQTY']);
                $row['FSALEMINQTY'] = floatval($row['FSALEMINQTY']);
                if(config('app_status') == 'office'){
                    $row['FIMAGE'] = hex2base64($row['FIMAGE']);
                    $sql = "SELECT TOP 1 t2.FSALEPRICE FROM LJL_FOODSALEPRICE t1 JOIN LJL_FOODSALEPRICEentry t2 ON t2.FID = t1.FID WHERE t1.FMAKER = '{$params['shitang']}' AND t1.FDOCUMENTSTATUS = 'C' AND t1.FDATE < '{$FDATE}' AND t2.FDOODID = '{$row['FID']}' ORDER BY t1.FDATE DESC,t1.FID DESC";
                }else{
                    $row['FIMAGE'] = hexChar($row['FIMAGE']);
                    $sql = "SELECT t2.FSALEPRICE FROM LJL_FOODSALEPRICE t1 JOIN LJL_FOODSALEPRICEentry t2 ON t2.FID = t1.FID WHERE t1.FMAKER = '{$params['shitang']}' AND t1.FDOCUMENTSTATUS = 'C' AND t1.FDATE < '{$FDATE}' AND t2.FDOODID = '{$row['FID']}' ORDER BY t1.FDATE DESC,t1.FID DESC LIMIT 1";
                }
                $arr = Db::query($sql);
                if(!empty($arr) && isset($arr[0]['FSALEPRICE'])) {
                    $row['FSALEPRICE'] = floatval($arr[0]['FSALEPRICE']);
                    $res[] = $row;
                }
            }
        }else{
            $res = [];
        }
        return success(1, $res);
    }

    /**
     * 获取食堂详情
     * @param $params
     * @return array
     */
    public function getMarkerInfo($params){
        $row = Db::table('LJL_FOODMARKER_L')->where('FID', $params['shitang'])->find();
        return $row ?: [];
    }

    /**
     * 获取菜品分类列表
     * @return array
     */
    public function getCateList(){
        //$sql = "select t.FMASTERID,t1.FDATAVALUE from T_BAS_ASSISTANTDATAENTRY t 
        //   inner join T_BAS_ASSISTANTDATAENTRY_L t1 on t.FENTRYID=t1.FENTRYID  
        //   inner join T_BAS_ASSISTANTDATA_L t2 on t.fid=t2.fid where FNAME='菜品分类'";
		$sql = "select FID,FNAME from LJL_FOODGROUP_L";
		$rows = Db::query($sql);
        return $rows;
    }
}
