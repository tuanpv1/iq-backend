<?php
namespace common\helpers;
/**
 * Ket noi Vas Provisioning cua Vinaphone
 *
 * @author Nguyen Chi Thuc
 * @dev Duong Quoc Huy
 * @email gthuc.nguyen@gmail.com
 * @skype ngoaho85
 */
use backend\controllers\VnpController;
use common\charging\helpers\ChargingGW;
use common\models\Service;
use common\models\Subscriber;
use common\models\SubscriberTransaction;
use common\models\User;
use SimpleXMLElement;
use Yii;
use yii\base\Component;
use yii\base\Controller;
use yii\base\Exception;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;

/**
 * Class ChargingGW
 * @property $ch MyCurl
 * @property $username
 * @property $password
 * @property $chargingUrl
 */
class VasProvisioning
{

    const ERROR_NONE = 0; // Su dung khi ko phan biet dc tru tien thanh cong hay ko. Hoac truong hop huy thanh cong hoac huy thanh cong
    const FREE_REGISTER_SUCCESS = 3; // Đăng ký thành công dịch vụ và không bị trừ cước đăng ký (có thể free chu kỳ đầu hoặc là đk trong thời gian được miễn phí)
    const PRICE_REGISTER_SUCCESS = 4; // Đã trừ cước thành công
    const ERROR_SUBSCRIBER_NOT_EXIST = 1;
    const ERROR_ALREADY_REGISTER = 2;
    const ERROR_NOT_ENOUGH_MONEY = 5; // Đăng ký không thành công do không đủ tiền trong tài khoản
    const UPGRADE_PACKAGE_SUCCESS = 6;//Nâng cấp gói thành công nhưng không rõ bị trừ tiền hay không ( Dùng cho đăng ký bundle)
    const FREE_UPGRADE_PACKAGE_SUCCESS = 7; // Nâng cấp gói thành công và không bị trừ tiền ( Dùng cho đăng ký bundle)
    const PRICE_UPGRADE_PACKAGE_SUCCESS = 8; // Nâng cấp gói thành công và bị trừ tiền ( Dùng cho đăng ký bundle)
    const ERROR_SUBSCRIBER_NOT_PROMOTION = 9; // Đăng ký không thành công do thuê bao không thuộc đối tượng khuyến mãi
    const ERORR_REGISTER_OTHER_PACKAGE = 10; // Đăng ký không thành công do thuê bao đang sử dụng 1 gói khác của dịch vụ Hoac chua dang ky goi cuoc
    const ERORR_SYSTEM = 99; // Loi do ko ket noi dc voi vas provisioning hoac format tra ve sai

    public $ch = null;
    public $vasprov_url = 'http://10.84.70.22:7777/';
    public $app = 'VIDEOPLUS'; // VNP Cung cap
    public $svc = 'MSP'; // VNP Cung cap
    public $username = "vgame";
    public $user_ip = '';
    private $session = '';
    private $debug = true;


    /**
     * @var ChargingGW
     */
    private static $_instance = null;

    /**
     * @return ChargingGW
     */
    public static function  getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new VasProvisioning();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        $config = \Yii::$app->params['vas_provisioning'];
        if (isset($config['vas_url'])) {
            $this->vasprov_url = $config['vas_url'];
        }
        if (isset($config['vas_app'])) {
            $this->app = $config['vas_app'];
        }
        if (isset($config['vas_svc'])) {
            $this->svc = $config['vas_svc'];
        }
        if (isset($config['debug'])) {
            $this->debug = $config['debug'];
        }
        $this->user_ip = \Yii::$app->request->getHostInfo();
    }

    /**
     *
     * @param String $xml
     * @return mixed
     */
    private function postToVasProvisioning($xml)
    {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_URL, $this->vasprov_url);
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
        curl_setopt($this->ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30); // 30s timeout
        Yii::info('Post to VasProvisioning: ' . $xml);
        $result = curl_exec($this->ch);
        if ($result === false) {
            CUtils::log('Post to VasProvisioning error: ' . curl_error($this->ch));
        } else {
            CUtils::log('Return from VasProvisioning: ' . $result);
        }
        curl_close($this->ch);
        return $result;
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
     * @param $msisdn
     * @param $service Service
     * @param $transaction_id
     * @param $channel_type // Channel type theo vnp
     * @param int $promotion
     * @param string $promotion_node
     * @param int $trial
     * @return VasResult
     */
    public function vasRegisterPackage($msisdn, $service, $transaction_id, $channel_type, $promotion = 0, $promotion_node = '', $trial = 0)
    {
        Yii::info("ChargingGW: Register subsriber $msisdn with service $service->name");
        if($service->serviceProvider){
            $this->app = $service->serviceProvider->provisioning_app;
        }
        // Login to vnp
        //1. Đăng ký dịch vụ
        $xml_charging = "<RQST>" .
            "<name>subscribe</name>" .
            "<requestid>$transaction_id</requestid>" .  //id ngẫu nhiên
            "<msisdn>" . $msisdn . "</msisdn>" .  //số thuê bao
            "<service>" . $this->svc . "</service>" .
            "<package>" . $service->name . "</package>" .
            "<promotion>" . $promotion . "</promotion>" .
            "<trial>" . $trial . "</trial>" .
            "<bundle>0</bundle>" .
            "<note>$promotion_node</note>" . //nếu có
            "<application>" . $this->app . "</application>" .
            "<channel>" . $channel_type . "</channel>" .
            "<username>" . $this->username . "</username>" .
            "<userip>" . $this->user_ip . "</userip>" .
            "</RQST>";
        if ($this->debug) {
            $response = $this->simulatorVasProvisioning('subscribe', $transaction_id, $msisdn, $service, $channel_type, $promotion, $promotion_node, $trial, $this->app, $this->username, $this->user_ip);
            Yii::info($response);
            return $response;
        } else {
            $response = $this->postToVasProvisioning($xml_charging);
            $result = $this->parseXML((string)$response);
        }

        if ($result) {
            $vasResult = new VasResult();
            $vasResult->request_id = $transaction_id;
            $vasResult->error_id = isset($result->error) ? $result->error : self::ERORR_SYSTEM;
            $vasResult->error_des = isset($result->error_desc) ? $result->error_desc : '';
            $vasResult->extra_infomation = isset($result->extra_information) ? $result->extra_information : '';
            return $vasResult;
        } else {
            return $this->vasUnknownError($transaction_id);
        }
    }

    /**
     * charge huy goi cuoc
     * @param $msisdn
     * @param $price
     * @param $original_price
     * @param Service $service
     * @param $transaction_id
     * @param $channel_type // Channel type theo vnp
     * @param bool $promotion
     * @param string $promotion_node
     * @return VasResult
     */
    public function vasCancelPackage($msisdn, $service, $transaction_id, $channel_type)
    {
        CUtils::log("ChargingGW: Cancel subsriber $msisdn with service $service->name");
        if($service->serviceProvider){
            $this->app = $service->serviceProvider->provisioning_app;
        }
        // Login to vnp
        //1. Đăng ký dịch vụ
        $xml_charging = "<RQST>" .
            "<name>unsubscribe</name>" .
            "<requestid>$transaction_id</requestid>" .  //id ngẫu nhiên
            "<msisdn>" . $msisdn . "</msisdn>" . //số thuê bao
            "<service>" . $this->svc . "</service>" .
            "<package>" . $service->name . "</package>" .
            "<policy>0</policy>" .
            "<promotion>0</promotion>" .
            "<note>note</note>" . //nếu có
            "<application>" . $this->app . "</application>" .
            "<channel>" . $channel_type . "</channel>" .
            "<username>" . $this->username . "</username>" .
            "<userip>" . $this->user_ip . "</userip>" .
            "</RQST>";

        if ($this->debug) {
            $response = $this->simulatorVasProvisioning('unsubscribe', $transaction_id, $msisdn, $service, $channel_type, 0, '', 0, $this->app, $this->username, $this->user_ip);
            Yii::info($response);
            return $response;
        } else {
            $response = $this->postToVasProvisioning($xml_charging);
            $result = $this->parseXML((string)$response);
        }

        if ($result) {
            $vasResult = new VasResult();
            $vasResult->request_id = $transaction_id;
            $vasResult->error_id = isset($result->error) ? $result->error : self::ERORR_SYSTEM;
            $vasResult->error_des = isset($result->error_desc) ? $result->error_desc : '';
            $vasResult->extra_infomation = isset($result->extra_information) ? $result->extra_information : '';
            return $vasResult;
        } else {
            return $this->vasUnknownError($transaction_id);
        }
    }


    private function vasNoError($request_id)
    {
        $chargeRes = new VasResult();
        $chargeRes->request_id = $request_id;
        $chargeRes->error_id = self::ERROR_NONE;
        $chargeRes->error_des = "Tru cuoc thanh cong";
        return $chargeRes;
    }

    private function vasUnknownError($request_id)
    {
        $chargeRes = new VasResult();
        $chargeRes->request_id = $request_id;
        $chargeRes->error_id = self::ERORR_SYSTEM;
        $chargeRes->error_des = "Can not connect vas provisioning";
        return $chargeRes;
    }

    /**
     * Mo phong tao loop khi goi vas provisioning
     * @param $action
     * @param $transaction_id
     * @param $msisdn
     * @param $service Service
     * @param $channel_type
     * @param $promotion
     * @param $promotion_node
     * @param $trial
     * @param $app
     * @param $username
     * @param $user_ip
     * @return null | VasResult
     */
    private function simulatorVasProvisioning(
        $action,
        $transaction_id,
        $msisdn,
        $service,
        $channel_type,
        $promotion,
        $promotion_node,
        $trial
    )
    {
        if ($action == 'subscribe') {
            $user = Subscriber::findByMsisdn($msisdn, $service->site_id, true);
            $transaction = SubscriberTransaction::findOne(['id' => $transaction_id]);

            $res = $user->coreBuyPackage($service, $transaction, $channel_type, $promotion, $trial, false);
            $vasResult = new VasResult();
            $vasResult->request_id = $transaction_id;
            $vasResult->error_id = isset($res['error_code']) ? $this->convertVasResponse($res['error_code']) : self::ERORR_SYSTEM;
            $vasResult->error_des = isset($res['message']) ? $res['message'] : '';
            return $vasResult;
        } else if ($action == 'unsubscribe') {
            $user = Subscriber::findByMsisdn($msisdn, $service->site_id);
            $transaction = SubscriberTransaction::findOne(['id' => $transaction_id]);

            $res = $user->coreCancelPackage($service, $transaction, $channel_type, false);
            $vasResult = new VasResult();
            $vasResult->request_id = $transaction_id;
            $vasResult->error_id = isset($res['error_code']) ? $this->convertVasResponse($res['error_code']) : self::ERORR_SYSTEM;
            $vasResult->error_des = isset($res['message']) ? $res['message'] : '';
            return $vasResult;
        }
    }

    private function convertVasResponse($error_code)
    {
        switch ($error_code) {
            case VnpController::ERROR_SUCCESS_PROMOTION:
                return self::FREE_REGISTER_SUCCESS;
            case VnpController::ERROR_SUCCESS_REGISTER:
                return self::PRICE_REGISTER_SUCCESS;
            case VnpController::ERROR_REGISTERING:
                return self::ERORR_REGISTER_OTHER_PACKAGE;
            case VnpController::ERROR_NOT_ENOUGH_MONEY:
                return self::ERROR_NOT_ENOUGH_MONEY;
            case VnpController::CANCEL_USER_ERROR_ALREADY_CANCELED:
                return self::ERORR_REGISTER_OTHER_PACKAGE;
            case VnpController::ERROR_NONE:
                return self::ERROR_NONE;
            default:
                return self::ERORR_SYSTEM;
        }
    }


}

class VasResult
{
    public $request_id;
    public $error_id;
    public $error_des;
    public $extra_infomation = '';
}

?>
