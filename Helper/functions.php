<?php
/**
 * 发送Email
 * @param $toemail
 * @param $toname
 * @param $subject
 * @param $body
 * @param string  $attachment
 * @return int
 * @throws phpmailerException
 */
function sendEmail($toemail, $toname, $subject, $body, $attachment = '')
{
    $mail = new \PHPMailer();
    //设定编码
    $mail->CharSet = "utf-8";
    $mail->IsSMTP(); // send via SMTP
    $mail->Host = EMAIL_HOST; // SMTP servers
    $mail->SMTPAuth = true; // turn on SMTP authentication
    $mail->Username = EMAIL_USERNAME; // SMTP USERNAME
    $mail->Password = EMAIL_PASSWORD; // SMTP password
    $mail->From = EMAIL_FROM;
    $mail->FromName = EMAIL_FROM_NAME;

    if ($attachment != '') {
        $mail->AddAttachment($attachment, $attachment);
    }
    if (is_array($toemail)) {
        for ($i = 0; $i < count($toemail); $i++) {
            $mail->AddAddress($toemail[$i]);
        }
    } else {
        $mail->AddAddress($toemail);
    }

    $mail->WordWrap = 50; // set word wrap
    $mail->IsHTML(true); // send as HTML
    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->AltBody = "高顿网校";
    if ($mail->Send()) {
        return 0;
    } else {
        return 1;
    }

}

function xmlToArray($xml)
{
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $values;
}

/**
 * @param $url
 * @param null $data
 * @return mixed|null
 */

function curl($url, $data = null)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    if ($result === false) {
        E(curl_error($ch), ERROR_SMS_SEND);
    } else {
        $data = $result;
    }
    curl_close($ch);
    return $data;
}


/**
 * 字符串分割
 * @param $str 字符串
 * @param int $l 分割长度
 * @return array 返回数组
 */
function str_split_unicode($str, $l = 0)
{
    if ($l > 0) {
        $ret = array();
        $len = mb_strlen($str, "UTF-8");
        for ($i = 0; $i < $len; $i += $l) {
            $ret[] = mb_substr($str, $i, $l, "UTF-8");
        }
        return $ret;
    }
    return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
}
