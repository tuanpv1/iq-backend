<?php
/**
 * Created by PhpStorm.
 * User: cuongvm
 * Date: 28/11/2016
 * Time: 10:44 AM
 */

namespace api\controllers;


use api\helpers\Message;
use common\helpers\CUtils;
use common\models\Device;
use common\models\Languages;
use Yii;
use yii\base\InvalidValueException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class AuthController extends BaseController
{


//    public function actionRegisterSecretKey($mac_address){
//        if (empty($mac_address)) {
//            throw new InvalidValueException(CUtils::replaceParam(Message::getNullValueMessage(), ['mac_address']));
//        }
//        $device = Device::findOne(['device_id' => $mac_address, 'status' => Device::STATUS_ACTIVE]);
//        if(!$device){
//            throw new NotFoundHttpException(Message::MSG_DEVICE_NOT_EXIST);
//        }
//        if($device->secret_key){
//            throw new NotFoundHttpException(Message::MSG_VERIFY_TOKEN_EXISTED);
//        }
//
//    }



    /**
     * @param $mac_address ex: fcd5d901b36f
     * @param $token
     * @param string $language
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionCheckMac($mac_address, $token, $language = Languages::DEFAULT_LANGUAGE){
        if (empty($mac_address)) {
            throw new InvalidValueException(CUtils::replaceParam(Message::getNullValueMessage(), ['mac_address']));
        }
        if (empty($token)) {
            throw new InvalidValueException(CUtils::replaceParam(Message::getNullValueMessage(), ['token']));
        }

        /** Verify Device */
        $res = Device::verifyDevice($mac_address,$token);
        if($res['success'] == false){
            throw new ServerErrorHttpException($res['message']);
        }

        return  [
                    'message' => Message::getSuccessMessage()
                ];
    }


}