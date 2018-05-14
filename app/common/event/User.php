<?php
namespace app\common\event;

use app\common\logic\EmpinfoLogic;
use think\Db;
use think\Session;

class User extends Base {
    /**
     * 获取个人信息
     * @return array
     */
    public function getInfo(){
        $where = [];
        $where['t1.FID'] = Session::get('user.id');
        $empInfoLogic = new EmpinfoLogic();
        $info = $empInfoLogic->getInfo($where);
        if(empty($info)){
            failure(0, '该用户不存在');
        }
        return $info;
    }

    /**
     * 设置个人信息
     * @param array $params
     * @return \think\response\Json
     */
    public function setInfo($params){
        $where = [];
        $where['t1.FID'] = Session::get('user.id');
        $empInfoLogic = new EmpinfoLogic();
        $info = $empInfoLogic->getInfo($where);
        if(empty($info)){
            failure(0, '该用户不存在');
        }
        $where = [];
        $where['FID'] = Session::get('user.id');
        $data = [];
        $data['FSEX'] = $params['sex'];
        $data['FDEPARTMENTID'] = $params['department'];
        $res = Db::table('LJL_EMPINFO')->where($where)->update($data);
        if($res === false){
            failure(0, '完善个人信息失败');
        }
        $data = [];
        $data['FNAME'] = $params['username'];
        $res = Db::table('LJL_EMPINFO_L')->where($where)->update($data);
        if($res === false){
            failure(0, '完善个人信息失败');
        }
        return success(1, [], url('index/Index/index'));
    }

    /**
     * 修改密码
     * @param array $params
     * @return \think\response\Json
     */
    public function reset($params){
        $where = [];
        $where['FID'] = Session::get('user.id');
        $info = Db::table('LJL_EMPINFO')->where($where)->find();
        if(empty($info)){
            failure(0, '该用户不存在');
        }
        if(strcasecmp($params['password'], $info['FPASSWORD']) != 0){
            failure(0, '旧密码错误');
        }
        $data = [];
        $data['FPASSWORD'] = $params['newpassword'];
        $res = Db::table('LJL_EMPINFO')->where($where)->update($data);
        if($res === false){
            failure(0, '修改密码失败');
        }
        return success(1, [], url('index/Index/index'));
    }

}