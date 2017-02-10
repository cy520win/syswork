<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/17
 * Time: 18:26
 */

namespace Model;

use Model\BaseModel;

class TsLogModel extends BaseModel
{
    private $table;

    /**
     * @return mixed
     */
    public function __construct()
    {
        global $configInfo;
        $this->table = $configInfo['pre'] . 'ts_log';
    }

    /**
     * @return boolean
     */
    public function addLog($mess, $act = '', $mod = 'Erp')
    {
        $dd["model"] = $act;
        $dd["action"] = $mod;
        $dd["message"] = $mess;
        $dd["addtime"] = time();
        DB::insert($this->table, $dd);
        return '';
    }
}
