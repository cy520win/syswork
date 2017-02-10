<?php
namespace Includes;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/17
 * Time: 15:43
 */
class IPtoCity
{
    public function getBaiduIp($ip)
    {
        // 117.144.208.50; 221.226.36.203
        $url = 'https://api.map.baidu.com/location/ip?ak=0Dcdec45c751dccb1ee1997b6c99cbd9&ip=' . $ip;
        $opts = array(
            'http' => array(
                'method' => 'GET',
                'timeout' => 5
            )
        );
        $context = stream_context_create($opts);
        $html = file_get_contents($url, false, $context);
        //fpassthru($html);
        return json_decode($html, true);
    }
}
