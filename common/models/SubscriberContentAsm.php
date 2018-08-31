<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%subscriber_content_asm}}".
 *
 * @property integer $id
 * @property integer $content_id
 * @property integer $subscriber_id
 * @property string $msisdn
 * @property string $description
 * @property integer $activated_at
 * @property integer $expired_at
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $purchase_type
 * @property integer $subscriber2_id
 * @property integer $site_id
 *
 * @property Subscriber $subscriber
 * @property Content $content
 * @property Subscriber $subscriber2
 * @property ServiceProvider $serviceProvider
 * @property ContentProvider $contentProvider
 */
class SubscriberContentAsm extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 0;

    const TYPE_PURCHASE = 1;
    const TYPE_DOWNLOAD = 2;
    const TYPE_PURCHASE_FOR_PRESENT = 3;
    const TYPE_PRESENTED = 4; // DUOC TANG

    const TYPE_PURCHASE_SMS = 1;
    const TYPE_PURCHASE_COIN = 2;

    const EXPIRED_DEFAULT = 10;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%subscriber_content_asm}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content_id', 'subscriber_id', 'activated_at', 'expired_at', 'site_id'], 'required'],
            [['content_id', 'subscriber_id', 'activated_at', 'expired_at', 'status', 'created_at', 'updated_at', 'purchase_type', 'subscriber2_id', 'site_id'], 'integer'],
            [['description'], 'string'],
            [['msisdn'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'content_id' => Yii::t('app', 'Content ID'),
            'subscriber_id' => Yii::t('app', 'Subscriber ID'),
            'msisdn' => Yii::t('app', 'Msisdn'),
            'description' => Yii::t('app', 'Mô tả'),
            'activated_at' => Yii::t('app', 'Ngày bắt đầu'),
            'expired_at' => Yii::t('app', 'Ngày hết hạn'),
            'status' => Yii::t('app', 'Trạng thái'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'updated_at' => Yii::t('app', 'Ngày thay đổi thông tin'),
            'purchase_type' => Yii::t('app', 'Purchase Type'),
            'subscriber2_id' => Yii::t('app', 'Subscriber2 ID'),
            'site_id' => Yii::t('app', 'Service Provider ID'),
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
    public function getContent()
    {
        return $this->hasOne(Content::className(), ['id' => 'content_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriber2()
    {
        return $this->hasOne(Subscriber::className(), ['id' => 'subscriber2_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceProvider()
    {
        return $this->hasOne(ServiceProvider::className(), ['id' => 'site_id']);
    }

}
