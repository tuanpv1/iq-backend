<?php
/**
 * Created by PhpStorm.
 * User: Thuc
 * Date: 4/22/2015
 * Time: 4:30 PM
 */

namespace common\helpers;


use Yii;
use yii\helpers\Json;

class VNPHelper
{
    //TODO: fix this
    const XMLGW_SERVICE_NAME = "VGAME";

    static function getMsisdn($ip_validation = true, $debug = false)
    {
        $headers = [];

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } elseif (function_exists('http_get_request_headers')) {
            $headers = http_get_request_headers();
        } else {
            foreach ($_SERVER as $name => $value) {
                if (strncmp($name, 'HTTP_', 5) === 0) {
                    $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $headers[$name] = $value;
                }
            }
        }

        $lcHeaders = [];
        foreach ($headers as $name => $value) {
            $lcHeaders[strtolower($name)] = $value;
        }

        $headers = $lcHeaders;

        $clientIp = $_SERVER['REMOTE_ADDR'];
//        $msisdn = isset($headers['msisdn']) ? $headers['msisdn'] : "";
        $msisdn = Yii::$app->request->headers->get('msisdn');
        $xIpAddress = isset($headers['x-ipaddress']) ? $headers['x-ipaddress'] : "";
        $xForwardedFor = isset($headers['x-forwarded-for']) ? $headers['x-forwarded-for'] : "";
        $userIp = isset($headers['user-ip']) ? $headers['user-ip'] : "";
//        $xWapMsisdn = isset($headers['x-wap-msisdn']) ? $headers['x-wap-msisdn'] : "";
        $xWapMsisdn =  Yii::$app->request->headers->get('x-wap-msisdn');
        if ($debug) {
            return $msisdn;
        }

//        $clientIp = "113.186.0.123";
        if ($ip_validation) {
            $valid = preg_match('/10\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $clientIp);
            $valid |= preg_match('/113\.185\.\d{1,3}\.\d{1,3}/', $clientIp);
            $valid |= preg_match('/172\.16\.30\.\d{1,3}/', $clientIp);
            if (!$valid) {
//                echo "IP invalid";
                return "";
            } else {
//                echo "IP valid";
            }
        }

        if ($msisdn) {
            return $msisdn;
        }

        if ($xWapMsisdn) {
            return $xWapMsisdn;
        }

        return "";

//        echo "client IP: " . $clientIp;

//        Yii::$app->request->getHeaders()->get();
//        VarDumper::dump($lcHeaders);
//        VarDumper::dump($_SERVER);
//        echo $headers["X-Wap-MSISDN"];
    }

    /**
     * ma loi:
     * 0|success
     * 1|unknown ip
     * 2|invalid subscriber
     * 3|otpaready sent
     * 4|other error
     * @param $msisdn
     * @return array ["success": true, "error" : "1"]
     */
    static function sendOtpPassword($msisdn)
    {
        $ch = new MyCurl();
        $response = $ch->get('http://10.1.10.47/otp/getotp', array(
            'msisdn' => $msisdn,
            'servicename' => static::XMLGW_SERVICE_NAME,
        ));

        Yii::info("Send otp to $msisdn");
        Yii::info($response);

        $error = -1;
        if (!$response)
            $error = -1;
        else {
            $arrResponse = explode('|', $response->body);
            if (count($arrResponse) > 1) {
                $error = $arrResponse[0];
            }
        }

        return ['success' => $error == 0, 'error' => $error . ""];
    }

    /**
     * 0|MSISDN
     * 1|unknown ip
     * 2|wrong otp token
     * 3|max retry
     * 4|other error
     * @param $msisdn
     * @param $password
     * @return array
     */
    static function verifyOtpPassword($msisdn, $password)
    {
        $ch = new MyCurl();
        $response = $ch->get('http://10.1.10.47/otp/checkotp', array(
            'msisdn' => $msisdn,
            'otptoken' => $password,
            'servicename' => static::XMLGW_SERVICE_NAME,
        ));

        Yii::info("Check otp of $msisdn: $password");
        Yii::info($response);

        $error = -1;
        if (!$response)
            $error = -1;
        else {
            $arrResponse = explode('|', $response->body);
            if (count($arrResponse) > 1) {
                $error = $arrResponse[0];
            }
        }

        return ['success' => $error == 0, 'error' => $error . ""];
    }
}