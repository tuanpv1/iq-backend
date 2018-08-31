<?php
/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 27-Dec-16
 * Time: 3:04 PM
 */

namespace console\controllers;



use Yii;
use yii\console\Controller;
use api\models\SmsUserAsm;
use common\helpers\FileUtils;
use common\models\Subscriber;

class SmsSupportController extends  Controller
{
    public function actionRun()
    {

        $this->deleteSmsSupport();

    }

    private function deleteSmsSupport(){
        $this->infoLog('******* Bat dau xoa SMS ******');
        $this->errorLog('******** Bat dau xoa SMS ******');
        $listUser = SmsUserAsm::find()
            ->groupBy('user_id')
            ->having('count(user_id) > :id', [':id' => 5])
            ->all();
        foreach ($listUser as $user) {
            /** @var  $user SmsUserAsm */
            $arr = [];
            $queryId = SmsUserAsm::find()
                ->andWhere(['user_id' => $user->user_id])
                ->orderBy(['date_send' => SORT_DESC])
                ->limit(20)
                ->offset(0)->all();
            foreach ($queryId as $id) {
                $arr[] = $id->id;
            }
            $username = Subscriber::findOne(['id' => $user->user_id])->username;
            $this->infoLog('******* DANG XOA SMS TAI KHOAN ****** ' . $username);

            if (SmsUserAsm::deleteAll(['AND', ['NOT IN', 'id', $arr], ['user_id' => $user->user_id]])) {
                $this->errorLog('****** Xoa thanh cong SMS Tai khoan **** ' . $username);
            } else {
                $this->errorLog('****** Xoa khong thanh cong SMS Tai khoan ****** ' . $username);
            }

        }
    }

    public static function errorLog($txt)
    {
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/error_sms.log'), $txt);
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/info_sms.log'), $txt);
    }

    public static function infoLog($txt)
    {
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/info_sms.log'), $txt);
    }
}