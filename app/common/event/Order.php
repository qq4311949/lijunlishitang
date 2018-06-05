<?php

namespace app\common\event;

use app\common\event\User as UserEvent;
use app\common\logic\BillNosLogic;
use app\common\logic\ReservationLogic;
use Endroid\QrCode\QrCode;
use think\Db;
use think\Session;

class Order extends Base {

    /**
     * 获取菜品列表
     * @param $params
     * @return array
     */
    public function getFoodsList($params) {
        $where = [];
        $where['FID'] = $params['shitang'];
        $type = Db::table('LJL_FOODMARKER')->where($where)->value('FDCTYPE');
        if ($type == 1) {//次日
            if (config('app_status') == 'office') {
                $FDATE = date('Y-m-d', strtotime('+1 day')) . ' 23:59:59.999';
            } else {
                $FDATE = date('Y-m-d', strtotime('+1 day')) . ' 23:59:59';
            }
        } else {//当天
            if (config('app_status') == 'office') {
                $FDATE = date('Y-m-d') . ' 23:59:59.999';
            } else {
                $FDATE = date('Y-m-d') . ' 23:59:59';
            }
        }
        $carts = json_decode($params['carts'], true);
        $fields = 't1.FID,t1.FSALEMAXQTY,t1.FSALEMINQTY,t1.FIMAGE,t2.FNAME,t2.FDESCRIPTION';
        $rows = [];
        $total = 0;
        foreach ($carts as $cart) {
            $where['t1.FID'] = $cart['id'];
            $temp = Db::query("SELECT $fields FROM LJL_FOODS t1 JOIN LJL_FOODS_L t2 ON t2.FID = t1.FID WHERE t1.FID = '{$cart['id']}'");
            if (!empty($temp)) {
                $row = $temp[0];
                $where1 = [];
                $where1['t1.FMAKER'] = $params['shitang'];
                $where1['t1.FDOCUMENTSTATUS'] = 'C';
                $where1['t1.FDATE'] = ['LT', $FDATE];

                if (config('app_status') == 'office') {
                    $row['FIMAGE'] = hex2base64($row['FIMAGE']);
                    $sql = "SELECT TOP 1 t2.FSALEPRICE FROM LJL_FOODSALEPRICE t1 JOIN LJL_FOODSALEPRICEentry t2 ON t2.FID = t1.FID WHERE t1.FMAKER = '{$params['shitang']}' AND t1.FDOCUMENTSTATUS = 'C' AND t1.FDATE < '{$FDATE}' AND t2.FDOODID = '{$row['FID']}' ORDER BY t1.FDATE DESC,t1.FID DESC";
                } else {
                    $row['FIMAGE'] = hexChar($row['FIMAGE']);
                    $sql = "SELECT t2.FSALEPRICE FROM LJL_FOODSALEPRICE t1 JOIN LJL_FOODSALEPRICEentry t2 ON t2.FID = t1.FID WHERE t1.FMAKER = '{$params['shitang']}' AND t1.FDOCUMENTSTATUS = 'C' AND t1.FDATE < '{$FDATE}' AND t2.FDOODID = '{$row['FID']}' ORDER BY t1.FDATE DESC,t1.FID DESC LIMIT 1";
                }
                $arr = Db::query($sql);
                if (empty($arr)) {
                    unset($row);
                }
                if (isset($arr[0]['FSALEPRICE'])) {
                    $row['FSALEPRICE'] = floatval($arr[0]['FSALEPRICE']);
                    $row['FNUM'] = $cart['num'];
                    $row['FAMOUNT'] = $row['FSALEPRICE'] * $row['FNUM'];
                    $total += $row['FAMOUNT'];
                    $rows[] = $row;
                }
            }
        }
        return ['list' => $rows, 'total' => $total];
    }

    /**
     * 支付订单
     * @param array $params
     * @return \think\response\Json
     */
    public function payment($params) {
        $arr = $this->getFoodsList($params);
        if (empty($arr['list'])) {
            failure(0, '请选择菜品');
        }
        if (date('G') >= '18') {
            failure(0, '18点以后不能订餐');
        }
        $reservationLogic = new ReservationLogic();
        $FID = $reservationLogic->getAutoIncId();
        $data = [];
        $data['FID'] = $FID;
        $billNosLogic = new BillNosLogic();
        $billNo = $billNosLogic->getBillNo(1);
        $data['FBILLNO'] = $billNo;
        $data['FDATE'] = $params['date'];
        $data['FFOODMARKERID'] = $params['shitang'];
        $data['FDCTIME'] = $params['shiduan'];
        $data['FEMPID'] = Session::get('user.id');
        $userEvent = new UserEvent();
        $info = $userEvent->getInfo();
        $data['FEMPNUMBER'] = $info['FNUMBER'];
        $data['FCLOSE'] = 0;//0未取 1已取
        $data['FSALEDATE'] = date('Y-m-d H:i:s');
        Db::startTrans();
        $res = Db::table('LJL_Reservation')->insert($data);
        if (!$res) {
            Db::rollback();
            failure(0, '下单失败');
        }
        $data = [];
        $total = 0;
        foreach ($arr['list'] as $k => $item) {
            $data[$k]['FID'] = $FID;
            $data[$k]['FSEQ'] = 0;
            $data[$k]['FFOODID'] = $item['FID'];
            $data[$k]['FQTY'] = $item['FNUM'];
            $data[$k]['FPRICE'] = $item['FSALEPRICE'];
            $data[$k]['FAMOUNT'] = $item['FNUM'] * $item['FSALEPRICE'];
            $total += $data[$k]['FAMOUNT'];
        }
        $res = Db::table('LJL_Reservationentry')->insertAll($data);
        if (!$res) {
            Db::rollback();
            failure(0, '下单失败');
        }
        // 用户余额
        $where = [];
        $where['FEMPID'] = Session::get('user.id');
        $balance = Db::table('LJL_EMPBalance')->where($where)->value('FAMOUNT');
        if ($balance < $total) {
            Db::rollback();
            failure(0, '余额不足，请充值');
        }
        $res = Db::table('LJL_EMPBalance')->where($where)->setDec('FAMOUNT', $total);
        if (!$res) {
            Db::rollback();
            failure(0, '用户余额操作失败，请重试');
        }
        $data = [];
        $data['FEMPID'] = Session::get('user.id');
        $data['FEMPNUMBER'] = $info['FNUMBER'];
        $data['FBILLNO'] = $billNo;
        $data['FDATETIME'] = date('Y-m-d H:i:s');
        $data['FAMOUNT'] = $total;
        $data['FBILLTYPE'] = 1;//消费
        $res = Db::table('LJL_RechargesOrPay')->insert($data);
        if (!$res) {
            Db::rollback();
            failure(0, '员工交易记录插入失败，请重试');
        }
        $this->qrCode($FID);
        Db::commit();
        return success(1, [], url('index/Order/ok', ['id' => $FID]));
    }

    /**
     * 生成二维码
     * @param $id
     */
    public function qrCode($id) {
        $qrCode = new QrCode();
        $url = 'http://' . $_SERVER['HTTP_HOST'] . url('marker/Order/scan', ['id' => $id]);
        $qrCode
            ->setText($url)
            ->setSize(280)
            ->setPadding(10)
            ->setErrorCorrection('high')
            ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0])
            ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0])
            ->setLabelFontSize(16)
            ->setImageType(QrCode::IMAGE_TYPE_PNG);

        $filename = $id . '.png';
        // save it to a file
        //$qrCode->save($filename);
        $dirname = './static/qrcode/';
        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }
        $path = $dirname . $filename;
        $fp = fopen($path, 'wr');
        fwrite($fp, $qrCode->get());
        fclose($fp);
    }

    /**
     * 获取取餐列表(今天)
     * @return array
     */
    public function getTodayList() {
        $where['FEMPID'] = Session::get('user.id');
        $where['FDATE'] = date('Y-m-d');
        $rows = $this->getOrderList($where);
        return $rows;
    }

    /**
     * 获取订单列表
     * @return array
     */
    public function getList() {
        $where['FEMPID'] = Session::get('user.id');
        $where['FDATE'] = ['egt', date('Y-m-d', strtotime('+1 day'))];
        $rows = $this->getOrderList($where);
        return $rows;
    }

    /**
     * 获取订单列表
     */
    private function getOrderList($where) {
        $rows = Db::table('LJL_Reservation')
            ->where($where)
            ->field('FID,FDATE,FFOODMARKERID,FDCTIME,FCLOSE')
            ->order('FDATE desc,FID desc')
            ->select();
        if (!empty($rows)) {
            foreach ($rows as &$row) {
                $row['FMARKER'] = Db::table('LJL_FOODMARKER_L')->where('FID', $row['FFOODMARKERID'])->value('FNAME');
                $row['FDCTIME'] = Db::table('LJL_SALETIMES_L')->where('FID', $row['FDCTIME'])->value('FNAME');
                $row['FQTY'] = Db::table('LJL_Reservationentry')->where('FID', $row['FID'])->value('FQTY');
                $row['FTOTAL'] = 0;
                $row['FDATE'] = date('n月d日', strtotime($row['FDATE']));
                $row['FFOODS'] = '';
                $items = Db::table('LJL_Reservationentry')->where('FID', $row['FID'])->select();
                if (!empty($items)) {
                    foreach ($items as $k => &$item) {
                        $row['FTOTAL'] += floatval($item['FAMOUNT']);
                        if ($k < 3) {
                            if ($k < 2) {
                                $foodName = Db::table('LJL_FOODS_L')->where('FID', $item['FFOODID'])->value('FNAME');
                            } else {
                                $foodName = '等';
                            }
                            $row['FFOODS'] .= $foodName . ' ';
                        }
                        if ($k == 0) {
                            $FIMAGE = Db::table('LJL_FOODS')->where('FID', $item['FFOODID'])->value('FIMAGE');
                            if (config('app_status') == 'office') {
                                $row['FIMAGE'] = hex2base64($FIMAGE);
                            } else {
                                $row['FIMAGE'] = hexChar($FIMAGE);
                            }
                        }
                    }
                }
                unset($row['FFOODMARKERID']);
            }
        }
        return $rows ?: [];
    }

    /**
     * 获取订单详情
     * @param $params
     * @return array
     */
    public function getInfo($params) {
        $where['FID'] = $params['id'];
        $where['FEMPID'] = Session::get('user.id');
        $where['FCLOSE'] = 0;
        $row = Db::table('LJL_Reservation')
            ->where($where)
            ->field('FID,FDATE,FFOODMARKERID,FSALEDATE')
            ->find();
        $is_can_cancel = 0;
        if (!empty($row)) {
            if (date('Y-m-d', strtotime($row['FSALEDATE'])) == date('Y-m-d')) {
                if (strtotime($row['FSALEDATE']) <= strtotime(date('Y-m-d 17:00:00'))) {
                    $is_can_cancel = 1;
                }
            }
            $row['FMARKER'] = Db::table('LJL_FOODMARKER_L')->where('FID', $row['FFOODMARKERID'])->value('FNAME');
            $row['FDATE'] = date('n月d日', strtotime($row['FDATE']));
            $row['FFOODS'] = '';
            $items = Db::table('LJL_Reservationentry')->where('FID', $row['FID'])->select();
            if (!empty($items)) {
                foreach ($items as $k => &$item) {
                    $foodName = Db::table('LJL_FOODS_L')->where('FID', $item['FFOODID'])->value('FNAME');
                    $row['FFOODS'] .= $foodName . ' ';
                }
            }
            unset($row['FFOODMARKERID']);
        } else {
            $row = [];
        }
        return ['data' => $row, 'is_can_cancel' => $is_can_cancel];
    }

    /**
     * 取消订单
     * @param $params
     * @return array
     */
    public function cancel($params) {
        $where = [];
        $where['FID'] = $params['id'];
        $where['FEMPID'] = Session::get('user.id');
        $where['FCLOSE'] = 0;
        if (config('app_status') == 'office') {
            $where[] = ['exp', "CONVERT(varchar(100), FSALEDATE, 23) = '" . date('Y-m-d') . "'"];
        } else {
            $where[] = ['exp', "DATE_FORMAT(FSALEDATE, '%Y-%m-%d') = '" . date('Y-m-d') . "'"];
        }
        $row = Db::table('LJL_Reservation')->where($where)->find();
        if (empty($row)) {
            return '该订单不存在';
        }
        if (strtotime($row['FSALEDATE']) > strtotime(date('Y-m-d 18:00:00'))) {
            return '当前时间不能取消订单';
        }
        $amount = Db::table('LJL_Reservationentry')->where('FID', $params['id'])->sum('FAMOUNT');
        // 用户余额
        $where = [];
        $where['FEMPID'] = Session::get('user.id');
        $res = Db::table('LJL_EMPBalance')->where($where)->setInc('FAMOUNT', $amount);
        if (!$res) {
            return '用户余额操作失败，请重试';
        }

        $data = [];
        $data['FEMPID'] = Session::get('user.id');
        $data['FEMPNUMBER'] = $row['FEMPNUMBER'];
        $billNosLogic = new BillNosLogic();
        $data['FBILLNO'] = $billNosLogic->getBillNo(2);
        $data['FDATETIME'] = date('Y-m-d H:i:s');
        $data['FAMOUNT'] = $amount;
        $data['FBILLTYPE'] = 2;//取消订单 退款
        $res = Db::table('LJL_RechargesOrPay')->insert($data);
        if (!$res) {
            return '员工交易记录插入失败，请重试';
        }

        $where = [];
        $where['FID'] = $params['id'];
        $res = Db::table('LJL_Reservation')->where($where)->delete();
        if (!$res) {
            return '取消订单失败';
        }
        return '取消订单成功';
    }

    /**
     * 扫描二维码取餐
     * @param $params
     * @return \think\response\Json
     */
    public function scan($params) {
        $where['t1.FID'] = $params['id'];
        $where['t1.FFOODMARKERID'] = Session::get('marker.id');
        $where['t1.FCLOSE'] = 0;
        $row = Db::table('LJL_Reservation')
            ->alias('t1')
            ->join('LJL_FOODMARKER_L t2', 't2.FID = t1.FFOODMARKERID')
            ->join('LJL_SALETIMES_L t3', 't3.FID = t1.FDCTIME')
            ->where($where)
            ->field('t1.FID,t1.FDATE,t1.FSALEDATE,t2.FNAME as FMARKER,t3.FNAME as SALETIME')
            ->find();
        if (!empty($row)) {
            $row['FDATE'] = date('n月d日', strtotime($row['FDATE']));
            $row['total'] = 0;
            $row['list'] = [];

            $items = Db::table('LJL_Reservationentry')->where('FID', $row['FID'])->select();
            if (!empty($items)) {
                foreach ($items as $k => &$item) {
                    $sql = "SELECT t1.FIMAGE,t2.FNAME FROM LJL_FOODS t1 JOIN LJL_FOODS_L t2 ON t2.FID = t1.FID WHERE t1.FID = {$item['FFOODID']}";
                    $res = Db::query($sql);
                    if(!empty($res)){
                        if (config('app_status') == 'office') {
                            $item['FIMAGE'] = hex2base64($res[0]['FIMAGE']);
                        } else {
                            $item['FIMAGE'] = hexChar($res[0]['FIMAGE']);
                        }
                        $item['FNAME'] = $res[0]['FNAME'];
                        $item['FAMOUNT'] = strpos($item['FAMOUNT'], '.') == 0 ? '0'.$item['FAMOUNT'] : $item['FAMOUNT'];
                    }
                    $row['total'] += $item['FAMOUNT'];
                    $row['list'][] = $item;
                }
            }
            unset($row['FFOODMARKERID']);
        }
        if (empty($row)) {
            failure(0, '该订单不存在');
        }
        return $row;
    }

    /**
     * 获取订单详情
     * @param $params
     * @return array
     */
    public function getOrderInfo($params) {
        $sql = "SELECT t1.FID,t1.FDATE,t2.FNAME FROM LJL_Reservation t1 JOIN LJL_FOODMARKER_L t2 ON t2.FID = t1.FFOODMARKERID WHERE t1.FID = '" . $params['id'] . "' AND t1.FEMPID = '" . Session::get('user.id') . "'";
        $rows = Db::query($sql);
        return $rows ? $rows[0] : [];
    }

    /**
     * 取餐操作
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function handle($params) {
        $where['FID'] = $params['id'];
        $where['FCLOSE'] = 0;
        $row = Db::table('LJL_Reservation')->where($where)->find();
        if (empty($row)) {
            failure(0, '该订单不存在');
        }
        $saleTimeInfo = Db::table('LJL_SALETIMES')->where('FID', $row['FDCTIME'])->find();
        if (date('G') < $saleTimeInfo['FSTRHOUR'] || date('G') > $saleTimeInfo['FENDHOUR']) {
            failure(0, '该订单当前时间不能取餐，请于'. $saleTimeInfo['FSTRHOUR'] .'至'. $saleTimeInfo['FENDHOUR'].'取餐');
        }
        $res = Db::table('LJL_Reservation')->where(['FID' => $params['id']])->update(['FCLOSE' => 1]);
        if($res === false){
            failure(0, '取餐失败');
        }
        // 取餐成功
        return success(1, [], url('marker/Index/index'));
    }
}