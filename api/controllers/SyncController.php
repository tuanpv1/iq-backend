<?php
/**
 * Created by PhpStorm.
 * User: Hoan
 * Date: 3/21/2017
 * Time: 3:45 PM
 */

namespace api\controllers;


use common\models\Device;
use common\models\SmsMessage;
use common\models\Subscriber;
use common\models\SubscriberDeviceAsm;
use Yii;

use yii\web\Controller;
use yii\web\Response;

class SyncController extends Controller
{
    const CHECK_ACCOUNT_MAINTAIN = 1;
    const CHECK_ACCOUNT_LINKED = 2;
    const CHECK_ACCOUNT_FALSE = 3;

    const CHECK_ACCOUNT_REGISTER_OK = 0;
    const CHECK_ACCOUNT_REGISTER_FALSE = 5;

    public function actionSyncSms()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $msisdn = Yii::$app->request->get('msisdn', '');
        $status = Yii::$app->request->get('status', 0);
        $error_code = Yii::$app->request->get('error_code', '');
        $sent_at = Yii::$app->request->get('sent_at', 0);
        $content = Yii::$app->request->get('mt_content', '');

        $sms = new SmsMessage();
        $sms->msisdn = $msisdn;
        $sms->type = SmsMessage::TYPE_MT;
        $sms->status = $status;
        $sms->mt_status = $error_code;
        $sms->message = $content;
        $sms->sent_at = $sms->received_at = $sent_at;
        $sms->site_id = Yii::getAlias('@default_site_id');
        $sms->source = "TVOD";
        $sms->destination = $msisdn;
        $sms->type_mt = SmsMessage::TYPE_MT_OTP;
        if ($sms->save()) {
            return ['success' => true, 'error_code' => 0];
        } else {
            \Yii::error($sms->getErrors());
            return ['success' => false, 'error_code' => 1];
        }
    }


    public function actionCheckAccount($user)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $subscriber = Subscriber::findOne(['username' => $user]);
        if ($subscriber) {
            if ($subscriber->status == Subscriber::STATUS_MAINTAIN) {
                return ['result' => true, 'error_code' => self::CHECK_ACCOUNT_MAINTAIN];
            } else {
                if ($subscriber->status == Subscriber::STATUS_ACTIVE) {
                    return ['result' => false, 'error_code' => self::CHECK_ACCOUNT_LINKED];
                } else {
                    return ['result' => false, 'error_code' => self::CHECK_ACCOUNT_FALSE];
                }
            }
        } else {
            return ['result' => false, 'error_code' => self::CHECK_ACCOUNT_FALSE];
        }
    }


    public function actionCheckAccountRegister($mac)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $device = Device::findOne(['device_id' => $mac]);
        if ($device) {
            $subscriber_device_asm = SubscriberDeviceAsm::findOne(['device_id' => $device->id, 'status'=>SubscriberDeviceAsm::STATUS_ACTIVE]);
            if ($subscriber_device_asm) {
                if($subscriber_device_asm->subscriber){
                    if($subscriber_device_asm->subscriber->authen_type == Subscriber::AUTHEN_TYPE_ACCOUNT){
                        return ['result' => false, 'error_code' => self::CHECK_ACCOUNT_REGISTER_FALSE];
                    }
                }
                return ['result' => true, 'error_code' => self::CHECK_ACCOUNT_REGISTER_OK];

//                return ['result' => false, 'error_code' => self::CHECK_ACCOUNT_REGISTER_FALSE];
            } else {
                return ['result' => true, 'error_code' => self::CHECK_ACCOUNT_REGISTER_OK];
            }
        } else {
            return ['result' => true, 'error_code' => self::CHECK_ACCOUNT_REGISTER_OK];
        }
    }

} 