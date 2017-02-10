<?php

namespace Action;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/17
 * Time: 15:36
 */
use Action\Base;

use Model\CourseLiveStudentLogModel as clslModel;
use Model\TsLogModel as tslogModel;

use Action\WxIptoCity as WxIptoCity;

class ZhiboUpdateIp extends Base
{

    /**
     * @return mixed
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getCity($starttime, $endtime)
    {
        $res = clslModel::getAll($starttime, $endtime);

        foreach ($res as $k => $v) {
            $data = [];
            $ip = $v['ip'];
            if (!empty($ip)) {
                $ipInfosArr = WxIptoCity::ipGetCity($ip);
                if (!empty($ipInfosArr['province'])) {
                   /* $data['province'] = $ipInfosArr['province'];
                    $data['city'] = $ipInfosArr['city'];*/
                   //var_dump($ipInfosArr);
                   $data['modifydate'] = time();
                    $where['id'] = $v['id'];
                    try {
                        clslModel::updateInfo($data, $where);
                    } catch (Exception $exception) {
                        tslogModel::addLog('live update city fail:' . $v['id']);
                        //throw new \Exception("失败~", 100000);
                    }
                }
            }
        }
    }
}
