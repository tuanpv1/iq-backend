<?php
/**
 * Created by PhpStorm.
 * User: bibon
 * Date: 4/21/2016
 * Time: 9:30 AM
 */

namespace common\helpers;


use DOMDocument;
use Yii;

class Brandname
{
    const USERNAME = 'tvod2';
    const PASSWORD = 'Y2fEjdGT1W6nsLqtJbGUVeUp9e4=';
    const BRANDNAME_MOBI = 'VNPT.Tech';
    const BRANDNAME_VT = 'VIVAS1';
    const BRANDNAME_VINA = 'VIVAS';
    const SHAREKEY = '789987';
    const TYPE = '1';
    const MT_THANKS = "Chan thanh cam on nha hao tam {username} da ung ho {money}VND cho chien dich tu thien {campaign_id}";
    const MT_REGISTER = "Ma xac thuc dang ky cua ban la: {auth_code}";
    const MT_END_OF_CAMPAIGN = "Chuong trinh thien nguyen {campaign_code} da ket thuc. Cam on ban da ung ho chuong trinh.";
    const MT_CREATE_CAMPAIGN = "Yeu cau cua ban da duoc khoi tao trong chuong trinh {campaign_code}";
    const MT_CAMPAIGN_APPROVED = "Chien dich {campaign_code} da duoc phe duyet";

    public static function login()
    {
        $loginXml = "<RQST><USERNAME>" . self::USERNAME . "</USERNAME><PASSWORD>" . self::PASSWORD . "</PASSWORD></RQST>";
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

        $cookies = array();
        if (sizeof($ms) > 1) {
            foreach ($ms[1] as $item) {
                parse_str($item, $cookies);
            }
        }
        $cookie = "";
        if (sizeof($cookies) > 0) {
            var_dump($cookies['JSESSIONID']);
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
        $brandName = self::getBrandnameByTel($msisdn);
        $sharekey = self::SHAREKEY;
        $username = self::USERNAME;
        $pass = self::PASSWORD;
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
        $ch_result = curl_exec($ch);
        curl_close($ch);
        echo "done" . $ch_result;
    }

    /**
     *
     * @param $transaction Transaction
     */
    public static function sendSms($transaction)
    {
        $username = $transaction->user ? $transaction->user->getName() : "";
        $username = CVietnameseTools::removeSigns($username);
        $phone = $transaction->user ? $transaction->user->phone_number : "";
        $campaign_code = $transaction->campaign ? $transaction->campaign->campaign_code : "";
        if ($username != '' && $campaign_code != '' && $phone != '') {
            $mt_msg = self::replaceParamMT(self::MT_THANKS, ['username', 'money', 'campaign_id'],
                [$username, CommonUtils::formatNumber($transaction->amount), $campaign_code]);

            if (substr($phone, 0, 2) != '84') {
                if (substr($phone, 0, 1) == '0') {
                    $phone = "84" . substr($phone, 1, strlen($phone) - 1);
                }
            }
            Brandname::send("$mt_msg", $phone);
        }
    }

    /**
     * @param $user User
     */
    public static function sendRegisterSms($user)
    {
        $phone = $user->phone_number;
        if ($phone) {
            $mt_msg = self::replaceParamMT(self::MT_REGISTER, ['auth_code'],
                [CUtils::generateRandomNumber()]);

            if (substr($phone, 0, 2) != '84') {
                if (substr($phone, 0, 1) == '0') {
                    $phone = "84" . substr($phone, 1, strlen($phone) - 1);
                }
            }
            Brandname::send("$mt_msg", $phone);
        }
    }

    /**
     * @param $user User
     */
    public static function sendRegisterSmsTo($msisdn, $auth_code)
    {
        if ($msisdn) {
            $mt_msg = self::replaceParamMT(self::MT_REGISTER, ['auth_code'],
                [$auth_code]);

            if (substr($msisdn, 0, 2) != '84') {
                if (substr($msisdn, 0, 1) == '0') {
                    $msisdn = "84" . substr($msisdn, 1, strlen($msisdn) - 1);
                }
            }
            return Brandname::send("$mt_msg", $msisdn);
        }
    }

    /**
     * @param $campaign Campaign
     */
    public static function sendCampaignCreateSms($campaign)
    {
        if ($campaign->donationRequest) {
            if ($campaign->donationRequest->createdBy) {
                $phone = $campaign->donationRequest->createdBy->phone_number;
                if ($phone) {
                    $mt_msg = self::replaceParamMT(self::MT_CREATE_CAMPAIGN, ['campaign_code'], [$campaign->campaign_code]);

                    if (substr($phone, 0, 2) != '84') {
                        if (substr($phone, 0, 1) == '0') {
                            $phone = "84" . substr($phone, 1, strlen($phone) - 1);
                        }
                    }
                    Brandname::send("$mt_msg", $phone);
                }
            }
        }
    }

    /**
     * @param $campaign Campaign
     */
    public static function sendCampaignApprovedSms($campaign)
    {
        if ($campaign->createdBy) {
            $phone = $campaign->createdBy->phone_number;
            if ($phone) {
                $mt_msg = self::replaceParamMT(self::MT_CAMPAIGN_APPROVED, ['campaign_code'], [$campaign->campaign_code]);

                if (substr($phone, 0, 2) != '84') {
                    if (substr($phone, 0, 1) == '0') {
                        $phone = "84" . substr($phone, 1, strlen($phone) - 1);
                    }
                }
                Brandname::send("$mt_msg", $phone);
            }

        }
    }

    public static function sendCampaignEndSms($campaign, $phone)
    {
        if ($phone) {
            $mt_msg = self::replaceParamMT(self::MT_END_OF_CAMPAIGN, ['campaign_code'], [$campaign->campaign_code]);

            if (substr($phone, 0, 2) != '84') {
                if (substr($phone, 0, 1) == '0') {
                    $phone = "84" . substr($phone, 1, strlen($phone) - 1);
                }
            }
            Brandname::send("$mt_msg", $phone);
        }
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

    private static function getBrandnameByTel($msisdn) {
        if (substr($msisdn, 0, 2) != '84') {
            if (substr($msisdn, 0, 1) == '0') {
                $msisdn = "84" . substr($msisdn, 1, strlen($msisdn) - 1);
            }
        }
        $mobiRegex = '/^(8490|8493|84120|84122|84126|84128|8489)/';
        if (preg_match($mobiRegex, $msisdn)) {
            return self::BRANDNAME_MOBI;
        }
        $vinaRegex = '/^(8491|8494|84123|84124|84125|84127|84129|8488)/';
        if (preg_match($vinaRegex, $msisdn)) {
            return self::BRANDNAME_VINA;
        }
        return self::BRANDNAME_VT;
    }
}