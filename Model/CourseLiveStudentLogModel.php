<?php
namespace Model;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/17
 * Time: 18:26
 */
use Model\BaseModel as baseModel;

class CourseLiveStudentLogModel extends BaseModel
{
    protected $liveTable;
    protected $bM;

    /**
     * @return mixed
     */
    public function __construct()
    {
        parent::__construct();
        global $configInfo;
        $this->liveTable = $configInfo['pre'] . 'course_live_student_log';
        $this->bM = new baseModel();
        $this->bM->table = $this->liveTable;
    }

    /**
     * @return array
     */
    public function getAll($starttime, $endtime)
    {
        $starttime = strtotime($starttime);
        $endtime = strtotime($endtime);
        if (empty($starttime) && !empty($endtime)) {
            $starttime = strtotime("2016-01-01 0:0:0");
            $map = " regdate>='$starttime' and regdate<='$endtime' ";
        } elseif (!empty($starttime) && empty($endtime)) {
            $map = " regdate>='$starttime' ";
        } elseif (!empty($starttime) && !empty($endtime)) {
            $map = " regdate>='$starttime' and regdate<='$endtime' ";
        } else {
            $starttime = time() - 60 * 60 * 24;
            $map = " regdate>='$starttime' ";
        }
        $columns = 'id, ip'; #, province, city

        global $configInfo;
        $this->liveTable = $configInfo['db_pre'] . 'course_live_student_log';
        $this->bM = new baseModel($this->liveTable);
        //$this->bM->table = $this->liveTable;

        $res = $this->bM->select($columns, $map);
        return $res;
    }

    /**
     * @return boolean
     */
    public function updateInfo($data, $where)
    {
        return $this->bM->update($data, $where);
    }

    function __destruct()
    {
    }
}
