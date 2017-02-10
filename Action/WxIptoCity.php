<?php
namespace Action;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/17
 * Time: 15:33
 */

use \Includes\IPtoCity as IptoCity;

class WxIptoCity extends Base
{
    public function ipGetCity($ip)
    {
        $ipInfos = [];
        if (!empty($ip)) {
            $iptoCity = new IptoCity();
            $ipInfosArr = $iptoCity->getBaiduIp($ip);
            $content = @$ipInfosArr['content'];
            $cityInfo = $content['address_detail'];
            $province = str_replace('市', '', $cityInfo['province']);
            $ipInfos['province'] = str_replace('省', '', $province);
            $ipInfos['city'] = str_replace('市', '', $cityInfo['city']);
        }

        return $ipInfos;
    }
}
