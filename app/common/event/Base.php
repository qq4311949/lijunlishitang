<?php
namespace app\common\event;

class Base {

	public function error($code, $msg = ''){
        if($msg == ''){
            $msg = config("msg.{$code}");
        }
        $arr = [
            'ret' => (string) $code,
            'msg' => $msg,
            'time' => (string) time()
        ];
        return $arr;
	}

    public function success($code, $msg = ''){
        if($msg == ''){
            $msg = config("msg.{$code}");
        }
        $arr = [
            'ret' => (string) $code,
            'msg' => $msg,
            'time' => (string) time()
        ];
        return $arr;
    }
}