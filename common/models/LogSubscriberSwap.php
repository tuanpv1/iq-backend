<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * This is the model class for table "log_subscriber_swap".
 *
 * @property int $id
 * @property int $subscriber_id
 * @property int $device_id_old
 * @property int $device_id_new
 * @property int $number_change
 * @property string $description
 * @property int $status
 * @property int $actor_id
 * @property int $created_at
 * @property int $updated_at
 */
class LogSubscriberSwap extends \yii\db\ActiveRecord
{

    const STATUS_ACTIVE = 10;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'log_subscriber_swap';
    }

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
            [['id', 'subscriber_id', 'device_id_old', 'device_id_new', 'number_change', 'status', 'actor_id', 'created_at', 'updated_at'], 'integer'],
            [['description'], 'string', 'max' => 100],
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
            'device_id_old' => Yii::t('app', 'Địa chỉ MAC cũ'),
            'device_id_new' => Yii::t('app', 'Địa chỉ MAC mới'),
            'number_change' => Yii::t('app', 'Number Change'),
            'description' => Yii::t('app', 'Ghi chú'),
            'status' => Yii::t('app', 'Status'),
            'actor_id' => Yii::t('app', 'Người đổi'),
            'created_at' => Yii::t('app', 'Ngày thay đổi'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    public static function getDevice($id)
    {
        $device = Device::findOne($id);
        return $device->device_id;
    }
    public static function getUser($id)
    {
        $user = User::findOne($id);
        return $user->username;
    }
}
