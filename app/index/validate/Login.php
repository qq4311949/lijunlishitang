<?php
namespace app\index\validate;

use think\Validate;

class Login extends Validate {

    protected $rule = [
        'number' => 'require|isCreditNo:',
        'password' => 'require|min:6|max:12',
        'repassword' => 'require|confirm:password',
        'captcha' => 'require|regex:captcha',

    ];

    protected $regex = [
        'captcha' => '\d{6}',
    ];

    protected $message = [
        'number.require' => '身份证号不能为空',
        'number.isCreditNo' => '身份证号填写有误',
        'password.require' => '密码不能为空',
        'password.min' => '密码最短6位',
        'password.max' => '密码最长12位',
        'repassword.require' => '确认密码不能为空',
        'repassword.confirm' => '两次输入的密码不一致',
        'captcha.require' => '验证码必须',
        'captcha.regex' => '验证码必须为6位数字',
    ];

    /**
     * 判断是否为合法的身份证号码
     * @param $value
     * @return bool
     */
    protected function isCreditNo($value){
        $city = array(
            '11','12','13','14','15','21','22',
            '23','31','32','33','34','35','36',
            '37','41','42','43','44','45','46',
            '50','51','52','53','54','61','62',
            '63','64','65','71','81','82','91'
        );
        if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $value)) return false;
        if (!in_array(substr($value, 0, 2), $city)) return false;
        $value = preg_replace('/[xX]$/i', 'a', $value);
        $length = strlen($value);
        if ($length == 18) {
            $vBirthday = substr($value, 6, 4) . '-' . substr($value, 10, 2) . '-' . substr($value, 12, 2);
        } else {
            $vBirthday = '19' . substr($value, 6, 2) . '-' . substr($value, 8, 2) . '-' . substr($value, 10, 2);
        }
        if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
        if ($length == 18) {
            $vSum = 0;
            for ($i = 17 ; $i >= 0 ; $i--) {
                $vSubStr = substr($value, 17 - $i, 1);
                $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
            }
            if($vSum % 11 != 1) return false;
        }
        return true;
    }

    protected $scene = [
        'login' => ['number', 'password'],
        'register' => ['number', 'password', 'repassword'],
        'push' => ['number'],
        'forget' => ['number'],
        'reset' => ['password', 'repassword'],
    ];
}