<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "campaign_condition".
 *
 * @property integer $id
 * @property integer $campaign_id
 * @property integer $type
 * @property integer $service_id
 * @property integer $price_level
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $event_time
 * @property integer $status
 * @property integer $number_month
 *
 * @property Campaign $campaign
 * @property Service $service
 * @property LogCampaignPromotion[] $logCampaignPromotions
 */
class CampaignCondition extends \yii\db\ActiveRecord
{

    const TYPE_SERVICE = 1;
    const TYPE_CASH = 2;

    const STATUS_ACTIVE = 10;
    const STATUS_DELETED = 0;

//    const TYPE_RECHARGE = 1;
//    const TYPE_BUY_SERVICE = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'campaign_condition';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['campaign_id', 'type'], 'required'],
            [['number_month', 'status', 'campaign_id', 'type', 'service_id', 'price_level', 'created_at', 'updated_at', 'start_time', 'end_time', 'event_time'], 'integer'],
            ['number_month', 'default', 'value' => 0],
            // [['campaign_id'], 'exist', 'skipOnError' => true, 'targetClass' => Campaign::className(), 'targetAttribute' => ['campaign_id' => 'id']],
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
            'service_id' => Yii::t('app', 'Service ID'),
            'price_level' => Yii::t('app', 'Price Level'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'start_time' => Yii::t('app', 'Thời gian bắt đầu'),
            'end_time' => Yii::t('app', 'Thời gian kết thúc'),
            'event_time' => Yii::t('app', 'Thời điểm diễn ra sự kiện'),
            'number_month' => Yii::t('app', 'Số tháng điều kiện'),
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
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogCampaignPromotions()
    {
        return $this->hasMany(LogCampaignPromotion::className(), ['campaign_condition_id' => 'id']);
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
