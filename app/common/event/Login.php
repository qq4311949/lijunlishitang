<?php
namespace app\common\event;

use app\common\model\LjlEmpinfo as LjlEmpinfoModel;
use app\common\model\LjlEmpinfoL as LjlEmpinfoLModel;
use app\common\model\Empbalance as EmpbalanceModel;
use app\common\logic\EmpinfoLogic;
use think\Session;
use think\Cookie;
use think\Db;

class Login extends Base {

    /**
     * 登录
     * @param array $params
     * @return \think\response\Json
     */
    public function login($params){
        $where = [];
        $where['FNUMBER'] = $params['number'];
        $empinfoLogic = new EmpinfoLogic();
        $info = $empinfoLogic->getInfo($where);
        if(empty($info)){
            failure(0, '该用户不存在');
        }
        if(strcasecmp($params['password'], $info['FPASSWORD']) != 0){
            failure(0, '密码错误');
        }
        Session::set('user.id', $info['FID']);
        Cookie::set('userNo', $params['number']);
        return success(1, [], url('index/Index/index'));
    }

    /**
     * 注册
     * @param array $params
     * @return \think\response\Json
     */
    public function register($params){
        $where = [];
        $where['FNUMBER'] = $params['number'];
        $empInfoLogic = new EmpinfoLogic();
        $info = $empInfoLogic->getInfo($where);
        if(!empty($info)){
            failure(0, '该用户已存在');
        }
        $empInfoId = $empInfoLogic->getAutoIncId();
        $data = [];
        $data['FID'] = $empInfoId;
        $data['FNUMBER'] = $params['number'];
        $data['FPASSWORD'] = $params['password'];
        $data['FSEX'] = substr($params['number'], (strlen($params['number'] )==15 ? -2 : -1), 1) % 2 ? '0' : '1';//0男 1女
        $res = Db::table('LJL_EMPINFO')->insert($data);
        if(!$res){
            failure(0, '注册失败');
        }
        $empInfoLId = $empInfoLogic->getLAutoIncId();
        $data = [];
        $data['FPKID'] = $empInfoLId;
        $data['FID'] = $empInfoId;
        $data['FLocaleID'] = 2052;
        $data['FNAME'] = substr($params['number'], -4);
        $res = Db::table('LJL_EMPINFO_L')->insert($data);
        if(!$res){
            failure(0, '注册失败');
        }
        $data = [];
        $data['FEMPID'] = $empInfoId;
        $data['FAMOUNT'] = 999999;
        $res = Db::table('LJL_EMPBalance')->insert($data);
        if(!$res){
            failure(0, '注册失败');
        }
        return success(1, [], url('index/Login/login'));
    }

    /**
     * 忘记密码
     * @param array $params
     * @return \think\response\Json
     */
    public function forget($params){
        $where = [];
        $where['FNUMBER'] = $params['number'];
        $empinfoLogic = new EmpinfoLogic();
        $info = $empinfoLogic->getInfo($where);
        if(empty($info)){
            failure(0, '该用户不存在');
        }
        $res = Db::table('LJL_EMPINFO')->where($where)->update(['FPASSWORD' => config('default_password')]);
        if($res === false){
            failure(0, '重置密码失败');
        }
        // TODO 微信指定用户推送
        return success(1, [], url('index/Login/login'));
    }
}