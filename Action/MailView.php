<?php
/**
 * Created by PhpStorm.
 * User: gaodun
 * Date: 2017/01/17
 * Time: 下午1:26
 */

namespace Action;

use DB;

class MailView extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function view()
    {
        /*  ob_start();
         require_once './View/view.php';
         $body = ob_get_contents();
         ob_flush();
         ob_end_clean();

         $response = sendEmail(
             $REPORT_EMAIL,
             '',
             '高顿网校 - 短信报告 - ' . date('Y-m-d', $this->nowDate),
             $body,
             ''
         );
         if ($response == 1) {
             throw new \Exception("生成失败~", 100000);
         } */
    }

}