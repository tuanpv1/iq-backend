<?php
namespace api\controllers;

use common\charging\helpers\ChargingGW;
use common\charging\models\ChargingConnection;
use common\charging\models\ChargingParams;
use common\helpers\CommonUtils;
use common\helpers\VasProvisioning;
use common\models\Content;
use common\models\ContentProfile;
use common\models\Service;
use common\models\Subscriber;
use common\models\SubscriberTransaction;
use Yii;
use yii\base\Exception;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;


/**
 * Site controller
 */
class ChargingController extends Controller
{
    private $subscriber;

    public function actionRegister($msisdn, $service_id, $promotion = 0, $promotion_note = '')
    {
        /**
         * @var $service Service
         */
        $service = Service::findOne($service_id);
        if($service == null){
//            return Yii::t('app','Service không tồn tại');
            return Yii::t('app','Service không tồn tại');
        }
        $site = $service->site;
        if($site == null){
//            return Yii::t('app','Service provider không tồn tại');
            return Yii::t('app','Service provider không tồn tại');
        }

        $charging_connection = new ChargingConnection($site->vivas_gw_host, $site->vivas_gw_port, $site->vivas_gw_username, $site->vivas_gw_password);
        $real_price = ($promotion)?0:intval($service->price);
        $charging_gw = ChargingGW::getInstance($charging_connection);
        $result = $charging_gw->registerPackage($msisdn, $real_price, $service, 123, ChargingParams::CHANNEL_WAP, $promotion, $promotion_note);
        var_dump($result->result);
    }

    public function actionCancel($msisdn, $service_id)
    {
        /**
         * @var $service Service
         */
        $service = Service::findOne($service_id);
        if($service == null){
            return Yii::t('app','Service không tồn tại');
        }
        $sp = $service->site;
        if($sp == null){
            return Yii::t('app','Service provider không tồn tại');
        }

        $charging_connection = new ChargingConnection($sp->vivas_gw_host, $sp->vivas_gw_port, $sp->vivas_gw_username, $sp->vivas_gw_password);
        $charging_gw = ChargingGW::getInstance($charging_connection);
        $result = $charging_gw->cancelPackage($msisdn, $service, 123, ChargingParams::CHANNEL_WAP);
        var_dump($result->result);
    }

    public function actionExtend($msisdn, $service_id)
    {
        /**
         * @var $service Service
         */
        $service = Service::findOne($service_id);
        if($service == null){
            return Yii::t('app','Service không tồn tại');
        }
        $sp = $service->site;
        if($sp == null){
            return Yii::t('app','Service provider không tồn tại');
        }

        $charging_connection = new ChargingConnection($sp->vivas_gw_host, $sp->vivas_gw_port, $sp->vivas_gw_username, $sp->vivas_gw_password);
        $real_price = intval($service->price);
        $charging_gw = ChargingGW::getInstance($charging_connection);
        $result = $charging_gw->extendPackage($msisdn, $real_price, $service, 123, ChargingParams::CHANNEL_WAP);
        var_dump(ArrayHelper::toArray($result));
    }

    public function actionBuyContent($username, $content_id, $promotion = 0, $promotion_note = '')
    {
        $this->subscriber = Subscriber::findOne(['username'=>$username]);
        if (!$this->subscriber) {
            return $this->responseError(Yii::t('app','Không tìm thấy thuê bao'));
        }

        if ($this->subscriber->status != Subscriber::STATUS_ACTIVE){
            return $this->responseError(Yii::t('app','Thuê bao chưa kích hoạt'));
        }

        $content = Content::findOne($content_id);
        if (!   $content) {
            return $this->responseError(Yii::t('app','Nội dung muốn mua ko có'));
        }
        $res = $this->subscriber->purchaseContent($this->subscriber->site, $content, SubscriberTransaction::CHANNEL_TYPE_MOBILEWEB, SubscriberTransaction::TYPE_CONTENT_PURCHASE);
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $res;
    }

    public function actionPlayContent($msisdn, $content_id, $service_id, $promotion = 0, $promotion_note = '')
    {
        /**
         * @var $content Content
         */
        $content = Content::findOne($content_id);
        if($content == null){
            return Yii::t('app','Service không tồn tại');
        }
        $sp = $content->serviceProvider;
        if($sp == null){
            return Yii::t('app','Service provider không tồn tại');
        }

        /**
         * @var $service Service
         */
        $service = Service::findOne($service_id);
        if($service == null){
            return Yii::t('app','Service không tồn tại');
        }

        $charging_connection = new ChargingConnection($sp->vivas_gw_host, $sp->vivas_gw_port, $sp->vivas_gw_username, $sp->vivas_gw_password);
        $real_price = ($promotion)?0:intval($content->price);
        $charging_gw = ChargingGW::getInstance($charging_connection);
        $result = $charging_gw->playContent($msisdn, $content, $service, 123, ChargingParams::CHANNEL_WAP);
        var_dump(ArrayHelper::toArray($result));
    }

//    public function actionGetPlayUrl($id, $protocol = ContentProfile::STREAMING_HLS){
//        /**
//         * @var $video Content
//         */
//        $video = Content::findOne($id);
//        if($video){
//            echo 'url: '.$video->getStreamUrl($protocol, true);
//        }else{
//            echo 'Null';
//        }
//    }

    /**
     * @param $msisdn
     * @param $service_id
     * @param int $promotion
     * @param string $promotion_note
     * @return string
     */
    public function actionVasRegister($msisdn, $service_id, $promotion = 0, $promotion_note = ''){
        $vas_provisioning = new VasProvisioning();
        $msisdn = CommonUtils::validateMobile($msisdn, 2);

        /**
         * @var $service Service
         */
        $service = Service::findOne($service_id);
        if($service == null){
            return Yii::t('app','Service không tồn tại');
        }

        $user = Subscriber::findByMsisdn($msisdn, $service->site_id, true);
        if (!$user) {
            return Yii::t('app','MSISDN không tồn tại');
        }

        $transaction = $user->newTransaction(SubscriberTransaction::TYPE_REGISTER, SubscriberTransaction::CHANNEL_TYPE_MOBILEWEB, $promotion_note, $service);
        $res = $vas_provisioning->vasRegisterPackage($msisdn, $service, $transaction->id, 'API', $promotion, $promotion_note);
        var_dump([
            'request_id' => $res->request_id,
            'error_code' => $res->error_id,
            'error_des' => $res->error_des
        ]);
    }


    public function actionVasCancel($msisdn, $service_id, $promotion = 0, $promotion_note = ''){
        $vas_provisioning = new VasProvisioning();
        /**
         * @var $service Service
         */
        $service = Service::findOne($service_id);
        if($service == null){
            return Yii::t('app','Service không tồn tại');
        }

        $msisdn = CommonUtils::validateMobile($msisdn, 2);
        $user = Subscriber::findByMsisdn($msisdn, $service->site_id);
        if (!$user) {
            return Yii::t('app','MSISDN không tồn tại');
        }

        $transaction = $user->newTransaction(SubscriberTransaction::TYPE_USER_CANCEL, SubscriberTransaction::CHANNEL_TYPE_MOBILEWEB, $promotion_note, $service);
        $res = $vas_provisioning->vasCancelPackage($msisdn, $service, $transaction->id, 'API');
        var_dump([
            'request_id' => $res->request_id,
            'error_code' => $res->error_id,
            'error_des' => $res->error_des
        ]);
    }

}
