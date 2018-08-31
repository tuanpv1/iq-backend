<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "campaign_promotion".
 *
 * @property integer $id
 * @property integer $campaign_id
 * @property integer $type
 * @property integer $content_id
 * @property integer $service_id
 * @property integer $time_extend_service
 * @property integer $price_gift
 * @property integer $honor
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $price_unit
 * @property integer $status
 * @property integer $number_month
 *
 * @property Campaign $campaign
 * @property Content $content
 * @property Service $service
 * @property LogCampaignPromotion[] $logCampaignPromotions
 */
class CampaignPromotion extends \yii\db\ActiveRecord
{
    const TYPE_SERVICE = 1;
    const TYPE_CASH = 2;
    const TYPE_CONTENT = 3;
    const TYPE_TIME = 4;

    const TYPE_FREE_SERVICE = 1;
    const TYPE_FREE_COIN = 2;
    const TYPE_FREE_CONTENT = 3;
    const TYPE_FREE_TIME = 4; // tang thoi gian co dinh
    const TYPE_GIFT_TIME_SIGNAL = 5; // tang thoi gian chu ky goi

    const PRICE_UNIT_ITEM = 1; // donvi
    const PRICE_UNIT_PERCENT = 2; // %

    public static $priceUnit = [
        self::PRICE_UNIT_ITEM => 'Coin',
        self::PRICE_UNIT_PERCENT => '%',
    ];

    const STATUS_ACTIVE = 10;
    const STATUS_DELETED = 0;

    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'campaign_promotion';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['campaign_id', 'type'], 'required'],
            [['number_month', 'status', 'campaign_id', 'type', 'content_id', 'service_id', 'time_extend_service', 'price_gift', 'created_at', 'updated_at', 'honor', 'price_unit'], 'integer'],
            // [['campaign_id'], 'exist', 'skipOnError' => true, 'targetClass' => Campaign::className(), 'targetAttribute' => ['campaign_id' => 'id']],
            // [['content_id'], 'exist', 'skipOnError' => true, 'targetClass' => Content::className(), 'targetAttribute' => ['content_id' => 'id']],
            // [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Service::className(), 'targetAttribute' => ['service_id' => 'id']],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [

            'id' => Yii::t('app', 'ID'),
            'campaign_id' => Yii::t('app', 'Campaign ID'),
            'type' => Yii::t('app', 'Type'),
            'content_id' => Yii::t('app', 'Content ID'),
            'honor' => Yii::t('app', 'Honor'),
            'service_id' => Yii::t('app', 'Service ID'),
            'time_extend_service' => Yii::t('app', 'Time Extend Service'),
            'price_gift' => Yii::t('app', 'Price Gift'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'number_month' => Yii::t('app', 'Số tháng khuyễn mãi'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCampaign()
    {
        return $this->hasOne(Campaign::className(), ['id' => 'campaign_id']);
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
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogCampaignPromotions()
    {
        return $this->hasMany(LogCampaignPromotion::className(), ['campaign_promotion_id' => 'id']);
    }

    public function getType()
    {
        return [
            self::TYPE_CONTENT => \Yii::t('app', 'Tặng nội dung lẻ'),
            self::TYPE_SERVICE => \Yii::t('app', 'Tặng gói nội dung'),
            self::TYPE_CASH => \Yii::t('app', 'Tặng tiền'),
        ];
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
}
