<?php

namespace dbhelper;

/**
 * 核心小助手
 *
 * @version        $Id: util.helper.php 4 19:20 2010年7月6日Z tianya $
 * @package        DedeCMS.Helpers
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

if (!function_exists('_exception_handler')) {
    function _exception_handler($errno, $errstr, $errfile, $errline)
    {
        echo "<b>Custom error:</b> [$errno] $errstr<br />";
        echo " Error on line $errline in $errfile<br />";
        die();
    }
}


set_error_handler('_exception_handler');

/**
 *  获得当前的脚本网址
 *
 * @return    string
 */
if (!function_exists('GetCurUrl')) {
    function GetCurUrl()
    {
        if (!empty($_SERVER["REQUEST_URI"])) {
            $scriptName = $_SERVER["REQUEST_URI"];
            $nowurl = $scriptName;
        } else {
            $scriptName = $_SERVER["PHP_SELF"];
            if (empty($_SERVER["QUERY_STRING"])) {
                $nowurl = $scriptName;
            } else {
                $nowurl = $scriptName . "?" . $_SERVER["QUERY_STRING"];
            }
        }
        return $nowurl;
    }
}

/**
 *  获取用户真实地址
 *
 * @return    string  返回用户ip
 */
if (!function_exists('GetIP')) {
    function GetIP()
    {
        static $realip = null;
        if ($realip !== null) {
            return $realip;
        }
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                /* 取X-Forwarded-For中第x个非unknown的有效IP字符? */
                foreach ($arr as $ip) {
                    $ip = trim($ip);
                    if ($ip != 'unknown') {
                        $realip = $ip;
                        break;
                    }
                }
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                if (isset($_SERVER['REMOTE_ADDR'])) {
                    $realip = $_SERVER['REMOTE_ADDR'];
                } else {
                    $realip = '0.0.0.0';
                }
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('HTTP_CLIENT_IP')) {
                $realip = getenv('HTTP_CLIENT_IP');
            } else {
                $realip = getenv('REMOTE_ADDR');
            }
        }
        preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
        $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
        return $realip;
    }
}


/**
 *  生成一个随机字符
 *
 * @access    public
 * @param     string $ddnum
 * @return    string
 */
if (!function_exists('dd2char')) {
    function dd2char($ddnum)
    {
        $ddnum = strval($ddnum);
        $slen = strlen($ddnum);
        $okdd = '';
        $nn = '';
        for ($i = 0; $i < $slen; $i++) {
            if (isset($ddnum[$i + 1])) {
                $n = $ddnum[$i] . $ddnum[$i + 1];
                if (($n > 96 && $n < 123) || ($n > 64 && $n < 91)) {
                    $okdd .= chr($n);
                    $i++;
                } else {
                    $okdd .= $ddnum[$i];
                }
            } else {
                $okdd .= $ddnum[$i];
            }
        }
        return $okdd;
    }
}

/**
 *  json_encode兼容函数
 *
 * @access    public
 * @param     string $data
 * @return    string
 */
if (!function_exists('json_encode')) {
    function format_json_value(&$value)
    {
        if (is_bool($value)) {
            $value = $value ? 'TRUE' : 'FALSE';
        } elseif (is_int($value)) {
            $value = intval($value);
        } elseif (is_float($value)) {
            $value = floatval($value);
        } elseif (defined($value) && $value === null) {
            $value = strval(constant($value));
        } elseif (is_string($value)) {
            $value = '"' . addslashes($value) . '"';
        }
        return $value;
    }

    function json_encode($data)
    {
        if (is_object($data)) {
            //对象转换成数组
            $data = get_object_vars($data);
        } elseif (!is_array($data)) {
            // 普通格式直接输出
            return format_json_value($data);
        }
        // 判断是否关联数组
        if (empty($data) || is_numeric(implode('', array_keys($data)))) {
            $assoc = false;
        } else {
            $assoc = true;
        }
        // 组装 Json字符串
        $json = $assoc ? '{' : '[';
        foreach ($data as $key => $val) {
            if (!is_NULL($val)) {
                if ($assoc) {
                    $json .= "\"$key\":" . json_encode($val) . ",";
                } else {
                    $json .= json_encode($val) . ",";
                }
            }
        }
        if (strlen($json) > 1) {// 加上判断 防止空数组
            $json = substr($json, 0, -1);
        }
        $json .= $assoc ? '}' : ']';
        return $json;
    }
}

/**
 *  json_decode兼容函数
 *
 * @access    public
 * @param     string $json json数据
 * @param     string $assoc 当该参数为 TRUE 时，将返回 array 而非 object
 * @return    string
 */
if (!function_exists('json_decode')) {
    function json_decode($json, $assoc = false)
    {
        // 目前不支持二维数组或对象
        $begin = substr($json, 0, 1);
        if (!in_array($begin, array('{', '['))) {
            // 不是对象或者数组直接返回
            return $json;
        }
        $parse = substr($json, 1, -1);
        $data = explode(',', $parse);
        if ($flag = $begin == '{') {
            // 转换成PHP对象
            $result = new stdClass();
            foreach ($data as $val) {
                $item = explode(':', $val);
                $key = substr($item[0], 1, -1);
                $result->$key = json_decode($item[1], $assoc);
            }
            if ($assoc) {
                $result = get_object_vars($result);
            }
        } else {
            // 转换成PHP数组
            $result = array();
            foreach ($data as $val) {
                $result[] = json_decode($val, $assoc);
            }
        }
        return $result;
    }
}


/**
 * @desc  im:十进制数转换成三十六机制数
 * @param (int)$num 十进制数
 * return 返回：三十六进制数
 */
if (!function_exists('get_char')) {
    function get_char($num)
    {
        $num = intval($num);
        if ($num <= 0) {
            return false;
        }
        $charArr = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i',
            'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
        $char = '';
        do {
            $key = ($num - 1) % 36;
            $char = $charArr[$key] . $char;
            $num = floor(($num - $key) / 36);
        } while ($num > 0);
        return $char;
    }
}

/**
 * @desc  im:三十六进制数转换成十机制数
 * @param (string)$char 三十六进制数
 * return 返回：十进制数
 */
if (!function_exists('get_num')) {
    function get_num($char)
    {
        $array = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
            'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
        $len = strlen($char);
        for ($i = 0; $i < $len; $i++) {
            $index = array_search($char[$i], $array);
            $sum += ($index + 1) * pow(36, $len - $i - 1);
        }
        return $sum;
    }
}

if (!function_exists('createFolder')) {
    function createFolder($path)
    {
        if (!file_exists($path)) {
            createFolder(dirname($path));
            mkdir($path, 0777);
        }
    }
}

if (!function_exists('createQR')) {
    function createQR($url, $filename)
    {
        require_once(dirname(__FILE__) . "/phpqrcode/qrlib.php");

        $qrdir = '/data/qrs/' . date('Y/m/d', time()) . "/";
        createFolder(dirname(dirname(__FILE__)) . $qrdir);

        $filelocation = $qrdir . $filename;
        QRcode::png($url, dirname(dirname(__FILE__)) . $filelocation, 'L', 4, 2);

        return $filelocation;
    }
}


/**
 *  设置Cookie记录
 *
 * @param     string $key 键
 * @param     string $value 值
 * @param     string $kptime 保持时间
 * @param     string $pa 保存路径
 * @return    void
 */
if (!function_exists('PutCookie')) {
    function PutCookie($key, $value, $kptime = 0, $pa = "/")
    {
        global $cfg_cookie_encode, $cfg_domain_cookie;
        setcookie($key, $value, time() + $kptime, $pa, $cfg_domain_cookie);
        setcookie($key . '__ckMd5', substr(md5($cfg_cookie_encode . $value), 0, 16), time()
            + $kptime, $pa, $cfg_domain_cookie);
    }
}


/**
 *  清除Cookie记录
 *
 * @param     $key   键名
 * @return    void
 */
if (!function_exists('DropCookie')) {
    function DropCookie($key)
    {
        global $cfg_domain_cookie;
        setcookie($key, '', time() - 360000, "/", $cfg_domain_cookie);
        setcookie($key . '__ckMd5', '', time() - 360000, "/", $cfg_domain_cookie);
    }
}

/**
 *  获取Cookie记录
 *
 * @param     $key   键名
 * @return    string
 */
if (!function_exists('GetCookie')) {
    function GetCookie($key)
    {
        global $cfg_cookie_encode;
        if (!isset($_COOKIE[$key]) || !isset($_COOKIE[$key . '__ckMd5'])) {
            return '';
        } else {
            if ($_COOKIE[$key . '__ckMd5'] != substr(md5($cfg_cookie_encode . $_COOKIE[$key]), 0, 16)) {
                return '';
            } else {
                return $_COOKIE[$key];
            }
        }
    }
}
