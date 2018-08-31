<?php

namespace common\models;

use api\helpers\Message;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "subscriber_device_asm".
 *
 * @property integer $id
 * @property integer $subscriber_id
 * @property integer $device_id
 * @property integer $status
 * @property string $decscription
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $first_login
 *
 * @property Subscriber $subscriber
 * @property Device $device
 */
class SubscriberDeviceAsm extends \yii\db\ActiveRecord
{

    const STATUS_ACTIVE = 1;
    const STATUS_REMOVED = -1;

    const IS_FIRST_LOGIN = 1;

    public $new_device_id;
    public $description;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'subscriber_device_asm';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subscriber_id', 'device_id', ], 'required'],
            [['new_device_id', 'old_device_id'],'required','on'=>'swapDevice'],
            [['subscriber_id', 'device_id', 'created_at', 'updated_at', 'first_login'], 'integer'],
            [['decscription'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 100],
            [
                'new_device_id',
                'match', 'pattern' => '/^([0-9A-Fa-f]{2}){6}$/',
                'message' => \Yii::t('app', '{attribute} không đúng định dạng. Ví dụ định dạng đúng: 1ff2acbd2a3c')
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'subscriber_id' => Yii::t('app', 'Subscriber ID'),
            'device_id' => Yii::t('app', 'Device ID'),
            'decscription' => Yii::t('app', 'Mô tả'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'updated_at' => Yii::t('app', 'Ngày thay đổi thông tin'),
            'new_device_id' => Yii::t('app', 'Địa chỉ MAC mới'),
            'description' => Yii::t('app', 'Ghi chú'),
            'old_device_id' => Yii::t('app', 'Địa chỉ MAC cũ'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriber()
    {
        return $this->hasOne(Subscriber::className(), ['id' => 'subscriber_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDevice()
    {
        return $this->hasOne(Device::className(), ['id' => 'device_id']);
    }

    public function getOld_device_id()
    {
        return $this->hasOne(Device::className(), ['id' => 'device_id'])->one()->device_id;
    }

    public function getSubscribers($subscriberId, $siteId, $dealerId)
    {
        if ($subscriberId) {
            return Subscriber::find()->where(['id' => $subscriberId, 'site_id' => $siteId, 'dealer_id' => $dealerId])->all();
        } else {
            return Subscriber::find()->all();
        }
    }

    public function getNotOwnedDevices($subscriberId, $site_id, $dealer_id)
    {
        $lst = SubscriberDeviceAsm::find()
            ->select(['device_id as id'])
            ->andWhere(["subscriber_id" => $subscriberId])
            ->asArray()
            ->all();

        return Device::find()->andWhere(['status' => Device::STATUS_ACTIVE, 'site_id' => $site_id, 'dealer_id' => $dealer_id])->andOnCondition(['not in', 'id', $lst])->all();
    }

    /**
     * @param $subscriber_id
     * @param $device_id
     * @return array
     */
    public static function createSubscriberDeviceAsm($subscriber_id, $device_id)
    {
        $res = [];

        /** @var  $sda SubscriberDeviceAsm */
        $sda = SubscriberDeviceAsm::findOne(['subscriber_id' => $subscriber_id, 'device_id' => $device_id]);
        if ($sda) {
            $sda->updated_at = time();
            $sda->status = SubscriberDeviceAsm::STATUS_ACTIVE;
            if (!$sda->save()) {
                $message = $sda->getFirstMessageError();
                $res['status'] = false;
                $res['message'] = $message;
                return $res;
            }
            $res['status'] = true;
            $res['message'] = Message::getSuccessMessage();
            $res['item'] = $sda;
            return $res;
        }

        $sda = new SubscriberDeviceAsm();
        $sda->subscriber_id = $subscriber_id;
        $sda->device_id = $device_id;
        $sda->status = SubscriberDeviceAsm::STATUS_ACTIVE;

        $check = SubscriberDeviceAsm::findOne(['first_login' => SubscriberDeviceAsm::IS_FIRST_LOGIN, 'device_id' => $device_id, 'status' => SubscriberDeviceAsm::STATUS_ACTIVE]);
        if (isset($check) && !empty($check)) {
            $sda->first_login = null;
        } else {
            $sda->first_login = SubscriberDeviceAsm::IS_FIRST_LOGIN;
        }

        /** Validate và save, nếu có lỗi thì return message_error */
        if (!$sda->validate() || !$sda->save()) {
            $message = $sda->getFirstMessageError();
            $res['status'] = false;
            $res['message'] = $message;
            return $res;
        }
        $res['status'] = true;
        $res['message'] = Message::getSuccessMessage();
        $res['item'] = $sda;
        return $res;
    }

    private function getFirstMessageError()
    {
        $error = $this->firstErrors;
        $message = "";
        foreach ($error as $key => $value) {
            $message .= $value;
            break;
        }
        return $message;
    }
}
