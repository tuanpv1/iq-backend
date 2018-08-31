<?php
/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 12-Apr-17
 * Time: 3:14 PM
 */

namespace common\helpers;


use common\models\SmsMessage;
use common\models\Subscriber;
use DOMDocument;
use Yii;

class BrandnameVacAccount
{
    const USERNAME = 'TVOD2016';
    const PASSWORD = '2nIkB5jTGQgcTimVRvvHr9QIsMA=';
    const BRANDNAME_MOBI = 'VIVAS';
    const BRANDNAME_VT = 'VIVAS';
    const BRANDNAME_VINA = 'VIVAS';
    const SHAREKEY = 'vivas123';
    const TYPE = '1';
    const MT_OTP = "Ma xac thuc tai khoan TVOD la: {otp}";


    public static function login()
    {
        $loginXml = "<RQST><USERNAME>" . Yii::$app->params['Brandname']['username'] . "</USERNAME><PASSWORD>" . Yii::$app->params['Brandname']['password'] . "</PASSWORD></RQST>";
        $url = "http://mkt.vivas.vn:9080/SMSBNAPI/login";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml;charset=UTF-8'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $loginXml);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
        $ch_result = curl_exec($ch);
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $ch_result, $ms);

        \Yii::info('Post to BrandNameLogin: ' . $loginXml);
        if ($ch_result === false) {
            \Yii::info('Post to BrandNameLogin error: ' . curl_error($ch));
        } else {
            \Yii::info('Return from BrandNameLogin: ' . $ch_result);
        }


        $cookies = array();
        if (sizeof($ms) > 1) {
            foreach ($ms[1] as $item) {
                parse_str($item, $cookies);
            }
        }
        $cookie = "";
        if (sizeof($cookies) > 0) {
            Yii::info("Cookies Session: " . $cookies['JSESSIONID']);
        }
        curl_close($ch);
        return $cookie;
    }

    public static function logout()
    {
        $url = "http://mkt.vivas.vn:9080/SMSBNAPI/logout";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml;charset=UTF-8'));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
        $ch_result = curl_exec($ch);
        curl_close($ch);
        return $ch_result;
    }

    public static function send($message, $msisdn)
    {

        self::login();

        //send sms
        $ch = curl_init();
        /* create a dom document with encoding utf8 */
        $domtree = new DOMDocument('1.0', 'UTF-8');

        /* create the root element of the AmazonEnvelope tree */
        $xmlRoot = $domtree->createElement("RQST");
        /* append it to the document created */
        $xmlRoot = $domtree->appendChild($xmlRoot);
        $time = date("YmdHis", time());
        /* you should enclose the following two lines in a cicle */
        $requestId = rand();
        $msgid = rand();
//        $brandName = self::getBrandnameByTel($msisdn);
        $brandName = Yii::$app->params['Brandname']['brandname'];
        $sharekey = Yii::$app->params['Brandname']['sharekey'];
        $username = Yii::$app->params['Brandname']['username'];
        $pass = Yii::$app->params['Brandname']['password'];
        //
        $xmlRoot->appendChild($domtree->createElement('REQID', $requestId));
        $xmlRoot->appendChild($domtree->createElement('BRANDNAME', $brandName));
        $xmlRoot->appendChild($domtree->createElement('TEXTMSG', $message));
        $xmlRoot->appendChild($domtree->createElement('SENDTIME', $time));
        $xmlRoot->appendChild($domtree->createElement('TYPE', 1));

        $destination = $domtree->createElement("DESTINATION");
        $destination = $xmlRoot->appendChild($destination);
        $checkSumStr = "username={$username}&password={$pass}&brandname={$brandName}&sendtime={$time}&msgid={$msgid}&msg={$message}&msisdn={$msisdn}&sharekey={$sharekey}";
//        echo $checkSumStr . "<br>";
        $cheksum = md5($checkSumStr);

        $destination->appendChild($domtree->createElement('MSGID', $msgid));
        $destination->appendChild($domtree->createElement('MSISDN', $msisdn));
        $destination->appendChild($domtree->createElement('CHECKSUM', $cheksum));

        /* get the xml printed */
        $xmlSend = $domtree->saveXML();

        $url = "http://mkt.vivas.vn:9080/SMSBNAPI/send_sms";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml;charset=UTF-8'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlSend);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");

        \Yii::info('Post to BrandName: ' . $xmlSend);

        $ch_result = curl_exec($ch);
        if ($ch_result === false) {
            \Yii::info('Post to BrandName error: ' . curl_error($ch));
            $rs = false;
        } else {
            \Yii::info('Return from BrandName av: ' . $ch_result);
            $xml = (array)simplexml_load_string($ch_result);
//            var_dump($xml->STATUS);exit;
//            var_dump($xml);
//            var_dump($xml['STATUS']);
//            exit;
            if ($xml === false) {
                $rs = false;
            } else {

                if (isset($xml['STATUS'])) {

                    Yii::info($xml['STATUS']);
                    if ($xml['STATUS'] == 0) {
                        $rs = true;
                    } else {
                        $rs = false;
                    }
                } else {
                    $rs = false;
                }

            }
        }
        curl_close($ch);

        Yii::info("done" . $ch_result);
        return $rs;
    }

    public static function sendOTP($sub,$site_id,$otp)
    {
        /** @var  $sub Subscriber */
        $phone = $sub->msisdn;
        if ($phone) {
            $mt_msg = self::replaceParamMT(Yii::$app->params['Brandname']['otp_content'], ['otp'],
                [$otp]);
            if (substr($phone, 0, 2) != '84') {
                if (substr($phone, 0, 1) == '0') {
                    $phone = "84" . substr($phone, 1, strlen($phone) - 1);
                }
            }

            $rs = BrandnameVacAccount::send("$mt_msg", $phone);
            if ($rs) {
                SmsMessage::newMessage($sub,$site_id, $mt_msg,time());
                return true;
            }
        }

        return false;
    }

    public static function replaceParamMT($message, $params, $values)
    {
        if (is_array($params)) {
            $cnt = count($params);
            for ($i = 0; $i < $cnt; $i++) {
                $message = str_replace('{' . $params[$i] . '}', $values[$i], $message);
            }
        }
        return $message;
    }

    private static function getBrandnameByTel($msisdn)
    {
        if (substr($msisdn, 0, 2) != '84') {
            if (substr($msisdn, 0, 1) == '0') {
                $msisdn = "84" . substr($msisdn, 1, strlen($msisdn) - 1);
            }
        }
        $mobiRegex = '/^(8490|8493|84120|84122|84126|84128|8489)/';
        if (preg_match($mobiRegex, $msisdn)) {
            return Yii::$app->params['Brandname']['brand_mobi'];
        }
        $vinaRegex = '/^(8491|8494|84123|84124|84125|84127|84129|8488)/';
        if (preg_match($vinaRegex, $msisdn)) {
            return Yii::$app->params['Brandname']['brand_vina'];
        }
        return Yii::$app->params['Brandname']['brand_viettel'];
    }
}