<?php
namespace common\charging\helpers;
/**
 * Description of ChargingGW
 *
 * @author Nguyen Chi Thuc
 * @dev Duong Quoc Huy
 * @email gthuc.nguyen@gmail.com
 * @skype ngoaho85
 */
use common\charging\models\ChargingParams;
use common\charging\models\ChargingResult;
use common\charging\models\ChargingConnection;
use common\models\Category;
use common\models\Content;
use common\models\Service;
use SimpleXMLElement;
use Yii;

/**
 * Class ChargingGW
 * @property $ch MyCurl
 * @property $username
 * @property $password
 * @property $chargingUrl
 */
class ChargingGW
{
    const CHARGING_ACTION_IMMEDIATE_DEBIT = 0;
    const CHARGING_ACTION_IMMEDIATE_CREDIT = 1;
    const APP_CATEGORY = "ChargingGW";

    public $charging_connection = null;
    private $session = '';
    public $debug = true;


    /**
     * @var ChargingGW
     */
    private static $_instance = null;

    /**
     * @param $connection ChargingConnection
     * @return ChargingGW
     */
    public static function  getInstance($connection)
    {
        if (self::$_instance == null) {
            self::$_instance = new ChargingGW($connection);
        }else{
            self::$_instance->charging_connection = $connection;
        }

        return self::$_instance;
    }

    /**
     * @param $connection ChargingConnection
     */
    protected function __construct($connection)
    {
        $this->charging_connection = $connection;
        $this->debug = isset(Yii::$app->params['charging_debug'])?Yii::$app->params['charging_debug']:true;
    }

    /**
     * @param String $pricePlan ID of the subscription plan. CP will define this value, such as: 01 = VM1, 02 = VM2 (Code name servicePackage)
     * @param String $serviceID ID of CP’s service. In use when one account is used for multiple services. (001)
     * @param String $categoryID Category of charging. May be defined differently for each
     * services   001: subscription charge,
     * 002: content charge
     * 003: renew charge.
     * 004: add time
     * @param String $contentID ID of content (A0A1A2: ID Category, A3->A9: ID Content, 0000000000: charge service)
     * @return string Fix length 24 character
     */
    private function //                    $ssa->first_retry_fail = CUtils::dbNow(); --> cai nay ko dc vi lenh strtotime($ssa->first_retry_fail) dung o phia duoi se ra 0
    createCPID($short_code, $pricePlan, $serviceID, $categoryID, $contentID)
    {
        $short_code = substr("000000" . $short_code, -6);
        $pricePlan = substr("000000" . $pricePlan, -2);
        $serviceID = substr("000000" . $serviceID, -3);
        $categoryID = substr("000000" . $categoryID, -3);
        $contentID = substr("0000000000" . $contentID, -10);
        $cpid = $short_code . $pricePlan . $serviceID . $categoryID . $contentID;
        return $cpid;
    }

    /**
     * API Mua goi cuoc
     * @param $msisdn
     * @param $real_price
     * @param $service Service
     * @param $transaction_id
     * @param $channel_type
     * @param bool $promotion
     * @param string $promotion_node
     * @return ChargingResult
     */
    public function registerPackage($msisdn, $real_price, $service, $transaction_id, $channel_type, $promotion = false, $promotion_node = '')
    {
        if ($this->debug) {
            return $this->chargingNoError();
        }
        Yii::info("ChargingGW: Register Subscriber $msisdn price $real_price VND with service $service->display_name", self::APP_CATEGORY);

        $charging_info = new ChargingParams();
        $service_number = ($service->site)?$service->site->service_sms_number:'';
        /* Bo sung de cp_id du 24 ky tu */
        $pricePlan = $service->id;
        $serviceID = '001';
        $categoryID = $service->id;
        $contentID = $service->id;
        $charging_info->cp_id = $this->createCPID($service_number, $pricePlan, $serviceID, $categoryID, $contentID);
        $charging_info->cp_transaction_id = $transaction_id;
        $charging_info->op_transaction_id = $channel_type;
        $charging_info->msisdn = $msisdn;
        $charging_info->transaction_price = $real_price;
        $charging_info->original_price = intval($service->price);
        $charging_info->channel = $channel_type;
        $charging_info->promotion = ($promotion)?'1':'0';
        $charging_info->promotion_note = $promotion_node;
        $charging_info->reason = $service->vnp_reason_reg;
        $charging_info->content_type = $service->vnp_contenttype;
        $charging_info->content_name = $service->vnp_contentname;
        $charging_info->content_id = $service->id;

        $results = $this->charging_connection->charge($charging_info);
        if ($results == null) {
            return $this->unknownChargingError();
        }
        return $results;
    }

    /**
     * API Mua goi cuoc
     * @param $msisdn
     * @param $real_price
     * @param $service Service
     * @param $transaction_id
     * @param $channel_type
     * @param bool $promotion
     * @param string $promotion_node
     * @return ChargingResult
     */
    public function cancelPackage($msisdn, $service, $transaction_id, $channel_type)
    {
        if ($this->debug) {
            return $this->chargingNoError();
        }
        Yii::info("ChargingGW: Cancel Subscriber $msisdn with service $service->display_name", self::APP_CATEGORY);

        $service_number = ($service->site)?$service->site->service_sms_number:'';

        $charging_info = new ChargingParams();

        /* Bo sung de cp_id du 24 ky tu */
        $pricePlan = $service->id;
        $serviceID = '001';
        $categoryID = $service->id;
        $contentID = $service->id;
        $charging_info->cp_id = $this->createCPID($service_number, $pricePlan, $serviceID, $categoryID, $contentID);
        $charging_info->cp_transaction_id = $transaction_id;
        $charging_info->op_transaction_id = $channel_type;
        $charging_info->msisdn = $msisdn;
        $charging_info->transaction_price = 0;
        $charging_info->original_price = intval($service->price);
        $charging_info->channel = $channel_type;
        $charging_info->reason = $service->vnp_reason_unreg;
        $charging_info->content_type = $service->vnp_contenttype;
        $charging_info->content_name = $service->vnp_contentname;
        $charging_info->content_id = $service->id;

        $results = $this->charging_connection->charge($charging_info);
        if ($results == null) {
            return $this->unknownChargingError();
        }
        return $results;
    }

    /**
     * @param $msisdn
     * @param $service Service
     * @param $transaction_id
     * @param $channel_type
     * @return ChargingResult|null|SimpleXMLElement
     */
    public function extendPackage($msisdn, $real_price, $service, $transaction_id, $channel_type)
    {
        if ($this->debug) {
            return $this->chargingNoError();
        }
        Yii::info("ChargingGW: Extend Subscriber $msisdn price $real_price VND with service $service->display_name", self::APP_CATEGORY);
        $service_number = ($service->site)?$service->site->service_sms_number:'';

        $charging_info = new ChargingParams();

        /* Bo sung de cp_id du 24 ky tu */
        $pricePlan = $service->id;
        $serviceID = '001';
        $categoryID = $service->id;
        $contentID = $service->id;
        $charging_info->cp_id = $this->createCPID($service_number, $pricePlan, $serviceID, $categoryID, $contentID);
        $charging_info->cp_transaction_id = $transaction_id;
        $charging_info->op_transaction_id = $channel_type;
        $charging_info->msisdn = $msisdn;
        $charging_info->transaction_price = $real_price;
        $charging_info->original_price = intval($service->price);
        $charging_info->channel = $channel_type;
        $charging_info->reason = $service->vnp_reason_renew;
        $charging_info->content_type = $service->vnp_contenttype;
        $charging_info->content_name = $service->vnp_contentname;
        $charging_info->content_id = $service->id;

        $results = $this->charging_connection->charge($charging_info);
        if ($results == null) {
            return $this->unknownChargingError();
        }
        return $results;
    }

    /**
     * @param $msisdn
     * @param $price
     * @param $content Content
     * @param $transaction_id
     * @param $channel_type
     * @param bool $promotion
     * @param string $promotion_node
     * @return ChargingResult
     */
    public function buyContent($msisdn, $real_price, $content, $transaction_id, $channel_type, $promotion = false, $promotion_node = '')
    {
        if ($this->debug) {
            return $this->chargingNoError();
        }
        Yii::info("ChargingGW: Buy Content Subscriber $msisdn price $real_price VND with content $content->display_name", self::APP_CATEGORY);
        $service_number = '';

        $charging_info = new ChargingParams();

        /* Bo sung de cp_id du 24 ky tu */
        $pricePlan = '000';
        $serviceID = '001';
        $categoryID = '000';
        $contentID = '000';
        $charging_info->cp_id = $this->createCPID($service_number, $pricePlan, $serviceID, $categoryID, $contentID);
        $charging_info->cp_transaction_id = $transaction_id;
        $charging_info->op_transaction_id = $channel_type;
        $charging_info->msisdn = $msisdn;
        $charging_info->transaction_price = $real_price;
        $charging_info->original_price = intval($content->price);
        $charging_info->channel = $channel_type;
        $charging_info->promotion = ($promotion)?'1':'0';
        $charging_info->promotion_note = $promotion_node;
        $charging_info->reason = ChargingParams::REASON_CONTENT;
        $charging_info->content_type = $this->getContentType($content);
        $charging_info->subcontent_type = ChargingParams::SUB_CONTENT_TYPE_VIDEO_VI;
        $charging_info->content_name = $content->ascii_name;
        $charging_info->content_id = $content->id;
        $charging_info->cp_name = ($content->serviceProvider)?$content->serviceProvider->name:'UNKNOWN';
        $charging_info->content_price = intval($content->price);

        $results = $this->charging_connection->charge($charging_info);
        if ($results == null) {
            return $this->unknownChargingError();
        }
        return $results;
    }

    /**
     * @param $msisdn
     * @param $content Content
     * @param $service Service
     * @param $transaction_id
     * @param $channel_type
     * @param bool $promotion
     * @param string $promotion_node
     * @return ChargingResult
     */
    public function playContent($msisdn, $content, $service, $transaction_id, $channel_type)
    {
        if ($this->debug) {
            return $this->chargingNoError();
        }
        Yii::info("ChargingGW: Subscriber $msisdn play Content $content->display_name", self::APP_CATEGORY);
        $service_number = ($service->site)?$service->site->service_sms_number:'';
        $charging_info = new ChargingParams();

        /* Bo sung de cp_id du 24 ky tu */
        $pricePlan = '000';
        $serviceID = '001';
        $categoryID = '000';
        $contentID = '000';
        $charging_info->cp_id = $this->createCPID($service_number, $pricePlan, $serviceID, $categoryID, $contentID);
        $charging_info->cp_transaction_id = $transaction_id;
        $charging_info->op_transaction_id = $channel_type;
        $charging_info->msisdn = $msisdn;
        $charging_info->transaction_price = 0;
        $charging_info->original_price = 0;
        $charging_info->channel = $channel_type;
        $charging_info->reason = ($service)?$service->vnp_contentname:"UNKNOWN";
        $charging_info->content_type = $this->getContentType($content);
        $charging_info->subcontent_type = ChargingParams::SUB_CONTENT_TYPE_VIDEO_VI;
        $charging_info->content_name = $content->ascii_name;
        $charging_info->content_id = $content->id;
        $charging_info->cp_name = ($content->serviceProvider)?$content->serviceProvider->name:'UNKNOWN';
        $charging_info->content_price = 0;

        $results = $this->charging_connection->charge($charging_info);
        if ($results == null) {
            return $this->unknownChargingError();
        }
        return $results;
    }

    private function chargingNoError()
    {
        $chargeRes = new ChargingResult();
        $chargeRes->result = ChargingResult::CHARGING_RESULT_OK;
        $chargeRes->error = ChargingResult::CHARGING_RESULT_OK;
        return $chargeRes;
    }

    private function unknownChargingError()
    {
        $chargeRes = new ChargingResult();
        $chargeRes->result = ChargingResult::CHARGING_RESULT_UNKNOWN;
        $chargeRes->error = "UNKNOWN";
        return $chargeRes;
    }

    /**
     * @param $content Content
     */
    private function getContentType($content)
    {
        switch($content->type){
            case Category::TYPE_CLIP:
            case Category::TYPE_FILM:
            case Category::TYPE_LIVE:
                return ChargingParams::CONTENT_TYPE_VIDEO;
            case Category::TYPE_MUSIC:
                return ChargingParams::CONTENT_TYPE_MUSIC;
            case Category::TYPE_NEWS:
                return ChargingParams::CONTENT_TYPE_NEWS;
            default:
                return ChargingParams::CONTENT_TYPE_VIDEO;
        }
    }


}

?>
