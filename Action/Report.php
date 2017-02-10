<?php

namespace Action;

use DB;
use Library\GetBalance as Balance;

class Report extends Base
{

    protected $strMsgSplit;//短信内容分割数

    public function __construct()
    {
        parent::__construct();

        $this->strMsgSplit = [
            'smsXiao' => 61,
        ];
    }

    public function getReport()
    {
        $nowTime = time();
        $allChannels = self::getAllChannel();
        $balance = new Balance();

        //删除当天之前的数据
        $this->toDeleteByCondition('gd_sms_count_channel');
        $ids = "";
        foreach ($allChannels as $val) {
            //成功量
            $result_s = self::getSendSuccess('gd_sms_send_detail', $this->nowDate, $nowTime, $val['alias']);
            //失败量
            $result_f = self::getSendSuccess('gd_sms_send_detail', $this->nowDate, $nowTime, $val['alias'], 1);

            $countArr['channel'] = $val['name'];
            $countArr['success_num'] = $result_s;
            $countArr['fail_num'] = $result_f;
            $countArr['date'] = date("Y-m-d", $this->nowDate);
            $countArr['regdate'] = time();
            $countArr['balance'] = $balance->toGetChannelBalance($val); //余额
            $ids .= DB::insert('gd_sms_count_channel', $countArr);
        }
        return $ids;
    }

    /**
     * 每天统计每个来源发送的短信成功量、失败量、总量和消费总额
     */
    public function toCountSourceSends()
    {
        $nowTime = time();
        /*$filter['regdate'] = array(array('EGT', $this->nowDate), array('ELT', $nowTime), 'and');
        $sourceArr = D('detail')->getAllSource($filter);*/
        $sourceArr = self::getAllSource($this->nowDate,$nowTime);

        //删除当天之前的数据
        $this->toDeleteByCondition('gd_sms_count_sends');
        $ids = "";
        foreach ($sourceArr as $val) {
            //成功量
            $result_s = self::getSendSuccessByAppId('gd_sms_send_detail', $this->nowDate, $nowTime, $val['app_id']);
            //失败量
            $result_f = self::getSendSuccessByAppId('gd_sms_send_detail', $this->nowDate, $nowTime, $val['app_id'], 1);
            $countArr['app_id'] = $val['app_id'];
            $countArr['success_num'] = $result_s;
            $countArr['fail_num'] = $result_f;
            $countArr['date'] = date("Y-m-d", $this->nowDate);
            $countArr['regdate'] = time();
            $ids .= DB::insert('gd_sms_count_sends',$countArr);
        }
        return $ids;
    }

    public static function getAllSource($startTime, $endTime)
    {
        return DB::query("select `app_id` from `gd_sms_send_detail` where  `regdate` >= %s and `regdate` <= %s group by `app_id`",$startTime, $endTime);
    }

    public function toDeleteByCondition($table)
    {
        if (empty($table)) {
            return false;
        }
        return DB::delete($table, "date=%s", date("Y-m-d", $this->nowDate));
    }

    //根据渠道统计发送成功失败量
    public static function getSendSuccess($table, $startTime, $endTime, $channel, $status = 0)
    {
        if (empty($table) || empty($startTime) || empty($endTime)) {
            return false;
        }
        DB::query("select `id` from $table where `channel` = %s and `status` = %d and `regdate` >= %s and `regdate` <= %s", $channel, $status, $startTime, $endTime);
        return DB::count();
    }

    //根据APPID统计发送成功失败量
    public static function getSendSuccessByAppId($table, $startTime, $endTime, $appId, $status = 0)
    {
        if (empty($table) || empty($startTime) || empty($endTime)) {
            return false;
        }
        DB::query("select `id` from $table where `app_id` = %s and `status` = %d and `regdate` >= %s and `regdate` <= %s", $appId, $status, $startTime, $endTime);
        return DB::count();
    }

}