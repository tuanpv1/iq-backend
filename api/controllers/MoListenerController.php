<?php

namespace api\controllers;

use api\helpers\Message;
use common\components\ActionPrivateFilter;
use common\helpers\CommonConst;
use common\helpers\CommonUtils;
use common\helpers\CUtils;
use common\helpers\ResMessage;
use common\helpers\SMSGW;
use common\models\Content;
use common\models\Service;
use common\models\Site;
use common\models\SmsMessage;
use common\models\SmsMoSyntax;
use common\models\Subscriber;
use common\models\SubscriberServiceAsm;
use common\models\SubscriberTransaction;
use common\models\User;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class MoListenerController extends Controller
{
    /**
     * @var $subscriber Subscriber
     */
    private $subscriber;
    const APP = 'MOListener';

    const CONTENT_TEXT = 0;

    const MESSAGE_TYPE_VALID = 71;
    const MESSAGE_TYPE_INVALID = 72;


//    public function behaviors()
//    {
//        return [
//            'auth' => [
//                'class' => ActionPrivateFilter::className(),
//                'enable_authentication' => false
//            ],
//        ];
//    }

    private function createTransaction($type, $desc, $status)
    {
        $tr = $this->subscriber->newTransaction(
            $type,
            SubscriberTransaction::CHANNEL_TYPE_SMS,
            $desc,
            null, null, null, 0, 0, $status
        );
        return $tr;
    }

    /**
     * @param string $userId
     * @param string $serviceNumber
     * @param string $commandCode
     * @param string $info
     * @param string $user
     * @param string $password
     * @return array
     * @throws \Exception
     */
    public function actionReceiveSms($userId = '', $serviceNumber = '', $commandCode = '', $info = '', $user = '', $password = '')
    {


        if ($user == Yii::$app->params['sms_charging']['username'] && $password == Yii::$app->params['sms_charging']['password']) {
            if (empty($userId)) return $this->responseError('MSISDN Empty');
            if (empty($info)) return $this->responseError('Message Empty');
            if (empty($serviceNumber)) return $this->responseError('Service Number Empty');

            Yii::info('Request with: ' . $userId . '|' . $commandCode . '|' . $info . '|' . $serviceNumber, self::APP);

//            $userId = CommonUtils::validateMobile(trim($userId), 0);
            if (empty($userId)) {
                return $this->responseError(Message::getNotSeeSubscriberMessage());
            }
            /** @var Site $site */
            $serviceNumberPattern = substr_replace($serviceNumber, 'x', 1, 1);
            $site = Site::findOne(['service_sms_number' => $serviceNumberPattern, 'status' => Site::STATUS_ACTIVE]);
            if ($site) {
                $sms = new SmsMessage();

                $sms->type = SmsMessage::TYPE_MO;
                $sms->source = $userId;
                $sms->msisdn = $userId;
                $sms->destination = $serviceNumber;
                $sms->site_id = $site->id;
                $sms->message = $info;
                $sms->mo_status = SMSGW::MO_STATUS;
                $sms->received_at = time();
                $sms->sent_at = time();
                if (!$sms->save()) {
                    Yii::error($sms->getErrors());
                }

                /** @var SmsMoSyntax $mo */
                $mo = SmsMoSyntax::getMoBySyntax(trim(strtoupper($commandCode)), $site->id);

                if ($mo) {
                    switch ($mo->event) {
                        case SmsMoSyntax::MO_EVENT_CHARGE_COIN:
                            $chargingInfo = explode(" ", $info, 2);
                            if (count($chargingInfo) < 2) {
                                $res['error'] = CommonConst::API_ERROR_INVALID_PARAMS;
                                $res['message'] = ResMessage::errorSyntax($userId, $site->id, true, $serviceNumber);
                                return $this->inetResponse($res, $userId);
                            }
                            $username = trim(strtolower($chargingInfo[1]));
                            $amount = $this->getSmsPrice($serviceNumber);
                            $res = Subscriber::chargeCoin($username, $amount, 'VND', $amount, SubscriberTransaction::CHANNEL_TYPE_SMS, $userId, $site->id, true, $sms, $serviceNumber);
                            $inetRes = $this->inetResponse($res, $userId);
                            return $inetRes;
                        case SmsMoSyntax::MO_EVENT_CANCEL:
                            $cancelInfo = explode(" ", $info, 3);
                            if (count($cancelInfo) < 3) {
                                $res['error'] = CommonConst::API_ERROR_INVALID_PARAMS;
                                $res['message'] = ResMessage::errorSyntax($userId, $site->id, true, $serviceNumber);
                                return $this->inetResponse($res, $userId);
                            }
                            $username = $cancelInfo[1];
                            $s = Subscriber::find()
                                ->where('username = :username AND status != :notStatus AND site_id = :site_id',
                                    ['username' => $username, 'notStatus' => Subscriber::STATUS_DELETED, 'site_id' => $site->id])->one();
                            if($s) {
                                $msisdn = CUtils::validateMobile($s->msisdn);
                                Yii::trace($msisdn);
                                if ($msisdn == $userId) {
                                    $this->subscriber = $s;
                                }
                            }
                            if (!$this->subscriber) {
                                $res['error'] = CommonConst::API_ERROR_INVALID_MSISDN;
                                $res['message'] = ResMessage::cancelServiceFailNotFoundSubscriber($userId, $site->id, true, $serviceNumber);
                                return $this->inetResponse($res, $userId);
                            } else {
                                $sms->subscriber_id = $this->subscriber->id;
                                $sms->update();
                            }
                            $serviceCode = $cancelInfo[2];
                            $service = Service::find()
                                ->innerJoinWith(['subscriberServiceAsms'])
                                ->andFilterWhere([
                                    'service.name' => $serviceCode,
                                    'service.site_id' => $site->id,
                                    'subscriber_service_asm.subscriber_id' => $this->subscriber->id,
                                    'subscriber_service_asm.status' => SubscriberServiceAsm::STATUS_ACTIVE
                                ])
                                ->one();

                            if (!$service) {
                                $service = Service::findOne([
                                    'name' => $serviceCode,
                                    'site_id' => $site->id,
                                    'status' => Service::STATUS_ACTIVE
                                ]);
                            }
                            if (!$service) {
                                $service = Service::findOne([
                                    'name' => $serviceCode,
                                    'site_id' => $site->id,
                                ]);
                            }
                            if (!$service) {
                                $res['error'] = CommonConst::API_ERROR_INVALID_SERVICE_PACKAGE;
                                $res['message'] = ResMessage::cancelServiceFailNotFoundService($this->subscriber, $serviceCode, true, $serviceNumber);
                                return $this->inetResponse($res, $userId);
                            }

                            $res = $this->subscriber->cancelServicePackage($service, SubscriberTransaction::CHANNEL_TYPE_SMS, SubscriberTransaction::TYPE_CANCEL, true, $serviceNumber);
                            $inetRes = $this->inetResponse($res, $userId);
                            return $inetRes;
                        default:
                            ResMessage::errorSyntax($userId, $site->id, $serviceNumber);
                            break;
                    }

                } else {
                    ResMessage::errorSyntax($userId, $site->id);
                }
            } else {
                Yii::info("Error: Not found MO: $info in system");
            }

        } else {
            \Yii::error('Sai ten dang nhap hoac mat khau');
        }
    }

    private function responseError($string)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'error' => true,
            'message' => $string,
            'code' => 400
        ];
    }

    private function inetResponse($result, $msisdn)
    {
        $messageType = $result['error'] == CommonConst::API_ERROR_NO_ERROR ? 71 : 72;
        return '0' . ';' . $messageType . ';' . $result['message'] . ';' . $msisdn;
    }

    private function getSmsPrice($serviceNumber)
    {
        $priceNumber = substr($serviceNumber, 1, 1);
        switch ($priceNumber) {
            case 0:
                return 500;
            case 1:
                return 1000;
            case 2:
                return 2000;
            case 3:
                return 3000;
            case 4:
                return 4000;
            case 5:
                return 5000;
            case 6:
                return 10000;
            case 7:
                return 15000;
        }
        return 0;
    }

}