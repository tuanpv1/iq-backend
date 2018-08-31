<?php

namespace api\controllers;

use api\helpers\Message;
use common\components\ActionPrivateFilter;
use common\helpers\CommonUtils;
use common\helpers\ResMessage;
use common\helpers\SMSGW;
use common\models\ServiceProvider;
use common\models\SmsMessage;
use common\models\SmsMoSyntax;
use common\models\Subscriber;
use common\models\SubscriberTransaction;
use common\models\User;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class OldMoListenerController extends Controller
{
    /**
     * @var $subscriber Subscriber
     */
    private $subscriber;
    const APP = 'MOListener';

    public function behaviors()
    {
        return [
            'auth' => [
                'class' => ActionPrivateFilter::className(),
                'enable_authentication' => false
            ],
        ];
    }

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
     * @param string $msisdn
     * @param string $mocontent
     * @param string $servicenumber
     * @throws \Exception
     */
    public function actionReceiveSms($msisdn = '', $mocontent = '', $servicenumber = '')
    {
        if (empty($msisdn)) return $this->responseError(Yii::t('app','MSISDN trống'));
        if (empty($mocontent)) return $this->responseError(Yii::t('app','Nội dung tin nhắn trống'));
        if (empty($servicenumber)) return $this->responseError(Yii::t('app','Đầu số dịch vụ trống'));
        $time = time();
        $result = array();

        Yii::info('Request with: ' . $msisdn . '|' . $mocontent . '|' . $servicenumber, self::APP);

        /**
         * TODO doi lai tham so
         */
//        $tmp = $msisdn;
//        $msisdn = urldecode($servicenumber);
//        $servicenumber = $tmp;

        //$msisdn = substr($msisdn, 0, 1) == '+' ? substr($msisdn, 1, strlen($msisdn) - 1) : $msisdn;
        Yii::info('Request with: ' . $msisdn . '|' . $mocontent . '|' . $servicenumber, self::APP);

        $msisdn = CommonUtils::validateMobile(trim($msisdn), 0);
        if (empty($msisdn)) {
            return $this->responseError(Message::getNotSeeSubscriberMessage());
        }
        /** @var ServiceProvider $sp */
        $sp = ServiceProvider::findOne(['service_sms_number' => $servicenumber, 'status' => ServiceProvider::STATUS_ACTIVE]);


        if ($sp) {
            $sms = new SmsMessage();

            $sms->type = SmsMessage::TYPE_MO;
            $sms->source = $msisdn;
            $sms->destination = $servicenumber;
            $sms->site_id = $sp->id;
            $sms->message = $mocontent;
            $sms->mo_status = SMSGW::MO_STATUS;
            $sms->received_at = time();
            if (!$sms->save()) {
                Yii::error($sms->getErrors());
            }

            /** @var SmsMoSyntax $mo */
            $mo = SmsMoSyntax::getMoBySyntax(trim(strtoupper($mocontent)), $sp->id);
            if ($mo) {
                switch ($mo->event) {
                    case SmsMoSyntax::MO_EVENT_HELP:
                        ResMessage::help($msisdn, $sp->id);
                        break;
                    case SmsMoSyntax::MO_EVENT_REGISTER:
                        /* @var User $user */
                        $first = true;

                        $subscriber = Subscriber::findOne([
                            'msisdn' => $msisdn,
                            'status' => Subscriber::STATUS_ACTIVE,
                            'site_id' => $sp->id
                        ]);
                        if($subscriber){
                            $first = false;
                        }

                        $this->subscriber = Subscriber::findByMsisdn($msisdn, $sp->id);
//                        var_dump($this->subscriber);exit;
                        if ($this->subscriber) {
                            $this->subscriber->status = User::STATUS_ACTIVE;
                            $this->subscriber->update();
                        }
                        $this->subscriber->purchaseServicePackage($mo->service,
                            SubscriberTransaction::CHANNEL_TYPE_SMS,
                            SubscriberTransaction::TYPE_REGISTER, $mo->id, true, true, $first);


                        break;
                    case SmsMoSyntax::MO_EVENT_CANCEL:
                        /* @var User $user */
                        $this->subscriber = Subscriber::findByMsisdn($msisdn, $sp->id);
                        if ($this->subscriber) {
                            $this->subscriber->status = User::STATUS_ACTIVE;
                            $this->subscriber->update();
                        }
                        $this->subscriber->cancelServicePackage($mo->service,
                            SubscriberTransaction::CHANNEL_TYPE_SMS,
                            SubscriberTransaction::TYPE_USER_CANCEL, $mo->id, true, true);
                        break;
                    default:
                        ResMessage::errorSyntax($msisdn, $sp->id);
                        break;
                }
            } else {
                ResMessage::errorSyntax($msisdn, $sp->id);
            }
        } else {
            Yii::info("Error: Not found MO: $mocontent in system");
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

}