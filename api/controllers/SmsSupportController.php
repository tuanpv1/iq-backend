<?php
/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 27-Dec-16
 * Time: 9:48 AM
 */

namespace api\controllers;

use api\models\SmsUserAsm;
use common\helpers\FileUtils;
use common\models\SmsSupport;
use common\models\Subscriber;
use Yii;
use yii\data\ActiveDataProvider;

class SmsSupportController extends ApiController
{
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = [
//            'index',
            'get-all-message',
            'test',
            'number-sms',
            'is-read',
            'delete',
            'get-delay'
        ];

        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'index' => ['GET'],
        ];
    }

    public function actionGetDelay()
    {
        $delay = Yii::$app->params['delay'];
        return $delay;
    }

//api lay danh sach tin nhan is_read = null la tat ca, 1 la da doc, 0 chua doc
    public function actionGetAllMessage($id, $is_read = null)
    {

        $orderDefault = [];
        $orderDefault['date_send'] = SORT_ASC;

        $smsUserAsm = SmsUserAsm::find()
            ->innerJoin('sms_support', 'sms_user_asm.sms_support_id = sms_support.id')
            ->andWhere(['sms_user_asm.user_id' => $id])
            ->andWhere(['sms_support.status' => SmsSupport::STATUS_ACTIVE])
            ->andWhere(['sms_support.type' => SmsSupport::TYPE_CSKH_INTERNAL])
            ->andWhere(['sms_user_asm.status' => SmsUserAsm::STATUS_ACTIVE]);
        if ($is_read != null) {
            if ($is_read == SmsUserAsm::IS_READ || $is_read == SmsUserAsm::NOT_READ) {
                $smsUserAsm->andWhere(['sms_user_asm.is_read' => $is_read]);
            }
        }
        $smsUserAsm->orderBy(['sms_support.updated_at'=>SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $smsUserAsm,
            'sort' => [
                'defaultOrder' => $orderDefault,
            ],

        ]);
        if($smsUserAsm->count()>= 20){
            $dataProvider->setTotalCount(20);
        }else{
            $dataProvider->setTotalCount(intval($smsUserAsm->count()));
        }
        foreach ($smsUserAsm->all() as $item) {
            $smsUser = SmsUserAsm::findOne(['id' => $item->id]);
            if (!$smsUser->date_received) {
                $smsUser->date_received = time();
                $smsUser->save(false);
            }
        }

        return $dataProvider;

    }

    //api lay so luong tin nhan chua doc
    public function actionNumberSms($id)
    {
        $numberIsRead = SmsUserAsm::find()
            ->innerJoin('sms_support', 'sms_user_asm.sms_support_id = sms_support.id')
            ->andWhere(['sms_user_asm.user_id' => $id])
            ->andWhere(['sms_support.type' => SmsSupport::TYPE_CSKH_INTERNAL])
            ->andWhere(['sms_user_asm.status' => SmsUserAsm::STATUS_ACTIVE])
            ->andWhere(['sms_support.status' => SmsSupport::STATUS_ACTIVE])
            ->andWhere(['sms_user_asm.is_read' => SmsUserAsm::NOT_READ])
            ->count();
        return intval($numberIsRead);
    }

    public function actionTest()
    {
        return 1;
    }


    //api doc tin nhan
    public function actionIsRead($id)
    {
        $smsUserAsm = SmsUserAsm::findOne(['id' => $id]);
        $smsUserAsm->is_read = SmsUserAsm::IS_READ;
        if ($smsUserAsm->save(false)) {
            return true;
        }
        return false;
    }

    public function actionDelete()
    {

        $this->infoLog('******* Bat dau xoa SMS ******');
        $this->errorLog('******** Bat dau xoa SMS ******');
        $listUser = SmsUserAsm::find()
            ->groupBy('user_id')
            ->having('count(user_id) > :id', [':id' => 20])
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