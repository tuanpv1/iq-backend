<?php
/**
 * Created by PhpStorm.
 * User: qhuy
 * Date: 6/23/15
 * Time: 9:23 AM
 */
namespace common\charging\models;

use common\helpers\CUtils;
use common\helpers\CVietnameseTools;
use SimpleXMLElement;
use Yii;
use yii\base\Exception;

class ChargingConnection
{

    const APP_CATEGORY = 'Charging connection';

    public $ch = null;
    public $charging_host = '10.84.73.61';
    public $charging_port = '9999';
    public $username = '';
    public $password = '';
    public $session = '';
    public $debug = false;
    const KEY_CHARGING_CACHE_SESSION = 'charging_gateway_cache_session';

    public function __construct($charging_host, $charging_port, $username, $password)
    {
        $this->charging_host = $charging_host;
        $this->charging_port = $charging_port;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param $str
     * @return null|SimpleXMLElement
     */
    private function parseXML($str)
    {
        if (empty($str)) {
            return null;
        }
        $xml_detail = substr($str, strpos($str, "<?xml"));
        $xmlParsed = new \SimpleXMLElement($xml_detail);
        return $xmlParsed;
    }

    /**
     *
     * @param String $xml
     * @return String | boolean $result
     */
    private function postToChargingGW($xml)
    {
        $charging_url = 'http://'.$this->charging_host.':'.$this->charging_port;
        Yii::info('Charging URL: ' . $charging_url, self::APP_CATEGORY);

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_URL, $charging_url);
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
        curl_setopt($this->ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->ch, CURLOPT_TIMEOUT,30); // 30s timeout

        \Yii::info('Post to ChargingGW: ' . $xml, self::APP_CATEGORY);
        $result = curl_exec($this->ch);
        if ($result === false) {
            \Yii::info('Post to ChargingGW error: ' . curl_error($this->ch), self::APP_CATEGORY);
        } else {
            \Yii::info('Return from ChargingGW: ' . $result, self::APP_CATEGORY);
        }
        curl_close($this->ch);
        return $result;
    }

    /**
     * @return array ['result','session','command']
     */
    protected function chargingLogin()
    {
        $array_vms = array();
        $value = \Yii::$app->cache->get(self::KEY_CHARGING_CACHE_SESSION);
        if ($value === false) {
            $xml_login = '<?xml version="1.0"?>
					<cp_request>
						<command>login</command>
						<login>' . $this->username . '</login>
						<password>' . $this->password . '</password>
					</cp_request>';

            try {
                $response = $this->postToChargingGW($xml_login);

                $result = $this->parseXML((string)$response);
                if ($result == null) {
                    return null;
                }

                $array_vms['result'] = isset($result->result) ? (string)$result->result : '';
                $array_vms['session'] = isset($result->session) ? (string)$result->session : '';
                $array_vms['command'] = isset($result->command) ? (string)$result->command : '';
                $this->session = $result->session;
                \Yii::$app->cache->set(self::KEY_CHARGING_CACHE_SESSION, $array_vms, 3600);
            } catch (Exception $ex) {
                return null;
            }

        } else {
            $array_vms = $value;
            $this->session = $array_vms['session'];
        }
        return $array_vms;
    }


    /**
     * @return array|null
     */
    private function chargingLogout()
    {
        $xml_logout = "<?xml version='1.0' encoding='UTF-8'?>
                    <cp_request>
                        <session>" . $this->session . "</session>
                        <command>logout</command>
                    </cp_request>";

        $response = $this->postToChargingGW($xml_logout);

        $result = $this->parseXML((string)$response);
        if ($result == null) {
            return null;
        }

        $array_vms = array();
        $array_vms['result'] = isset($result->result) ? (string)$result->reslut : '';
        $array_vms['session'] = isset($result->session) ? (string)$result->session : '';
        $array_vms['command'] = isset($result->command) ? (string)$result->command : '';
        return $array_vms;
    }

    /**
     * @param $charge_info  ChargingParams
     * @param bool $retry
     * @return null|SimpleXMLElement
     */
    public function charge($charge_info, $retry = true)
    {
        if(empty($this->session)){
            $this->chargingLogin();
        }
        if(empty($this->session)) return null;
        $result = null;
        $msisdn = CUtils::validateMobile($charge_info->msisdn, 2);
        if(empty($msisdn)) return null;

        $charge_info->promotion_note = CVietnameseTools::removeSigns2($charge_info->promotion_note);
        $xml_charging = '<?xml version="1.0"?>
						<cp_request>
							<session>' . $this->session . '</session>
							<cp_id>' . $charge_info->cp_id . '</cp_id>
							<application>' . $charge_info->application . '</application>
							<action>' . $charge_info->action . '</action>
							<cp_transaction_id>' . $charge_info->cp_transaction_id . '</cp_transaction_id>
							<op_transaction_id>' . $charge_info->op_transaction_id . '</op_transaction_id>
							<user_id type="MSISDN">' . $msisdn . '</user_id>
							<transaction_price>' . $charge_info->transaction_price . '</transaction_price>
							<original_price>' . $charge_info->original_price . '</original_price>
							<transaction_currency>1</transaction_currency>
							<bonus_price>0</bonus_price>
							<channel>' . $charge_info->channel . '</channel>
                            <reason>' . $charge_info->reason . '</reason>
                            <promotion>' . $charge_info->promotion . '</promotion>
                            <promotion_note>' . $charge_info->promotion_note . '</promotion_note>
                            <Content>
                                <item contenttype="' . $charge_info->content_type . '" subcontenttype="' . $charge_info->subcontent_type . '" contentid="'.$charge_info->content_id.'" contentname="'.$charge_info->content_name.'" cpname="'.$charge_info->cp_name.'" note="'.$charge_info->promotion_note.'" playtype="'.$charge_info->play_type.'" contentprice="'.$charge_info->content_price.'"/>
                            </Content>
						</cp_request>';

        $response = $this->postToChargingGW($xml_charging);
        $result = $this->parseXML((string)$response);
        if ($result == null) {
            return null;
        }

        if ($result->result == ChargingResult::CHARGING_NOK_INVALID_SESSION && $retry) {
            //Delete invalid session
            \Yii::$app->cache->delete(self::KEY_CHARGING_CACHE_SESSION);
            $logout = $this->chargingLogout();
            //Relogin to get Session ID
            $resultRetry = $this->chargingLogin();
            if ($resultRetry != null) {
                $login_result = $resultRetry['result'];
                $session = (string)$resultRetry['session'];

                // login thanh cong thi thuc hien charging
                if ($login_result == 0 && !empty($session)) {
                    $this->session = $session;
                    $result = $this->charge($charge_info, false);
                }
            }
        }

        if ($result == null) {
            return null;
        }

        $charginResult = new ChargingResult();
        $charginResult->cp_id = (string)$result->cp_id;
        $charginResult->result = (string)$result->result;
        $charginResult->charged_price = isset($result->charged_price) ? (string)$result->charged_price : '';
        $charginResult->cp_transaction_id = (string)$result->cp_transaction_id;
        if ($charginResult->result == ChargingResult::CHARGING_RESULT_OK) {
            $charginResult->error = ChargingResult::CHARGING_RESULT_OK;
        } else {
            $charginResult->error = (string)$result->error;
        }

        return $charginResult;
    }

}