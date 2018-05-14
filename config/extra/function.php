<?php

if (!function_exists('failure')) {
    /**
     * 错误抛出函数
     * @param $code
     * @param $msg
     * @return \think\response\Json
     */
    function failure($code, $msg = '') {
        if ($msg == '') {
            $msg = config("msg.{$code}");
        }
        $arr = [
            'ret' => (string)$code,
            'msg' => $msg,
            'time' => (string)time()
        ];
        echo json_encode($arr);
        die;
    }
}

if (!function_exists('success')) {
    /**
     * 返回函数
     * @param $code
     * @param $data
     * @param $url
     * @return \think\response\Json
     */
    function success($code, $data = [], $url = '') {
        $msg = config("msg.{$code}");
        $url = $url ?: $_SERVER['HTTP_REFERER'];
        $arr = [
            'ret' => (string)$code,
            'msg' => $msg,
            'url' => $url,
            'data' => is_array($data) ? $data : [],
            'time' => (string)time()
        ];
        return json($arr);
    }
}

if (!function_exists('randNumber')) {
    /**
     * 获取随机位数验证码
     * @param  integer $len 长度
     * @return string
     */
    function randNumber($len = 6) {
        $chars = str_repeat('0123456789', $len);
        $chars = str_shuffle($chars);
        $str = substr($chars, 0, $len);
        return $str;
    }
}

if (!function_exists('array_column')) {
    /**
     * array_column
     * @param $input
     * @param $columnKey
     * @param null $indexKey
     * @return array
     */
    function array_column($input, $columnKey, $indexKey = NULL) {
        $columnKeyIsNumber = (is_numeric($columnKey)) ? TRUE : FALSE;
        $indexKeyIsNull = (is_null($indexKey)) ? TRUE : FALSE;
        $indexKeyIsNumber = (is_numeric($indexKey)) ? TRUE : FALSE;
        $result = array();

        foreach ((array)$input AS $key => $row) {
            if ($columnKeyIsNumber) {
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : NULL;
            } else {
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : NULL;
            }
            if (!$indexKeyIsNull) {
                if ($indexKeyIsNumber) {
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && !empty($key)) ? current($key) : NULL;
                    $key = is_null($key) ? 0 : $key;
                } else {
                    $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }
            $result[$key] = $tmp;
        }
        return $result;
    }
}


if (!function_exists('decimal2')) {
    /**
     * 两位小数
     * @param $num
     * @return string
     */
    function decimal2($num) {
        $Num = sprintf('%.2f', $num);
        return (string)$Num;
    }
}

if (!function_exists('transTime')) {
    /**
     * 时间戳转化函数
     * @param $timestamp
     * @return false|string
     */
    function transTime($timestamp) {
        if (date('Ymd') == date('Ymd', $timestamp)) {
            return '今天 ' . date('H:i', $timestamp);
        } else {
            return date('Y-m-d H:i', $timestamp);
        }
    }
}

if (!function_exists('hexChar')) {
    function hexChar($hex) {
        return preg_replace_callback('/\\\x([0-9a-fA-F]{2})/', function ($matches) {
            return chr(hexdec($matches[1]));
        }, $hex);
    }
}

if (!function_exists('hex2base64')) {
    function hex2base64($image) {
        return 'data:image/png;base64,' . base64_encode(pack('H*', $image));
    }
}

if (!function_exists('array_utf8_encode')) {
    /**
     * Encode array to utf8 recursively
     * @param $dat
     * @return array|string
     */
    function array_utf8_encode($dat) {
        if (is_string($dat))
            return utf8_encode($dat);
        if (!is_array($dat))
            return $dat;
        $ret = array();
        foreach ($dat as $i => $d)
            $ret[$i] = array_utf8_encode($d);
        return $ret;
    }
}

function delFile($dir, $file_type = '') {
    if (is_dir($dir)) {
        $files = scandir($dir);
        //打开目录 //列出目录中的所有文件并去掉 . 和 ..
        foreach ($files as $filename) {
            //获取文件创建时间
            if ($filename != '.' && $filename != '..') {
                if (!is_dir($dir . '/' . $filename)) {
                    if (empty($file_type)) {
                        unlink($dir . '/' . $filename);
                    } else {
                        if (is_array($file_type)) {
                            //正则匹配指定文件
                            if (preg_match($file_type[0], $filename)) {
                                unlink($dir . '/' . $filename);
                            }
                        } else {
                            //指定包含某些字符串的文件
                            if (false != stristr($filename, $file_type)) {
                                unlink($dir . '/' . $filename);
                            }
                        }
                    }
                } else {
                    delFile($dir . '/' . $filename);
                    rmdir($dir . '/' . $filename);
                }
            }
        }
    } else {
        if (file_exists($dir)) unlink($dir);
    }
}

if(!function_exists('getRouteByHttpReferer')){
    function getRouteByHttpReferer(){
        return str_replace(['http://'.$_SERVER['HTTP_HOST'].'/', '.'.config('url_html_suffix')], '', $_SERVER['HTTP_REFERER']);
    }
}


