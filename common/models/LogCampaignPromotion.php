<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "log_campaign_promotion".
 *
 * @property integer $id
 * @property integer $site_id
 * @property integer $type_campaign
 * @property integer $campaign_id
 * @property string $campaign_name
 * @property integer $type
 * @property integer $white_list
 * @property integer $subscriber_id
 * @property string $subscriber_name
 * @property integer $device_id
 * @property string $mac_address
 * @property integer $event_count
 * @property integer $status
 * @property integer $campaign_promotion_id
 * @property integer $campaign_condition_id
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Campaign $campaign
 * @property Site $site
 * @property CampaignPromotion $campaignPromotion
 * @property CampaignCondition $campaignCondition
 * @property Subscriber $subscriber
 * @property Device $device
 */
class LogCampaignPromotion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    const STATUS_ACTIVE  = 10;
    const STATUS_INACTIVE = 0;


    public static function tableName()
    {
        return 'log_campaign_promotion';
    }


    public function behaviors()
        {
            return [
                [
                    'class'              => TimestampBehavior::className(),
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
            [['site_id', 'campaign_id', 'type'], 'required'],
            [['site_id', 'campaign_id', 'type', 'type_campaign', 'white_list', 'subscriber_id', 'device_id', 'status', 'campaign_promotion_id', 'campaign_condition_id', 'created_at', 'updated_at','event_count'], 'integer'],
            [['campaign_name', 'subscriber_name', 'mac_address'], 'string', 'max' => 255],
            [['campaign_id'], 'exist', 'skipOnError' => true, 'targetClass' => Campaign::className(), 'targetAttribute' => ['campaign_id' => 'id']],
            [['site_id'], 'exist', 'skipOnError' => true, 'targetClass' => Site::className(), 'targetAttribute' => ['site_id' => 'id']],
            [['campaign_promotion_id'], 'exist', 'skipOnError' => true, 'targetClass' => CampaignPromotion::className(), 'targetAttribute' => ['campaign_promotion_id' => 'id']],
            [['campaign_condition_id'], 'exist', 'skipOnError' => true, 'targetClass' => CampaignCondition::className(), 'targetAttribute' => ['campaign_condition_id' => 'id']],
            [['subscriber_id'], 'exist', 'skipOnError' => true, 'targetClass' => Subscriber::className(), 'targetAttribute' => ['subscriber_id' => 'id']],
            [['device_id'], 'exist', 'skipOnError' => true, 'targetClass' => Device::className(), 'targetAttribute' => ['device_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'site_id' => Yii::t('app', 'Site ID'),
            'campaign_id' => Yii::t('app', 'Campaign ID'),
            'campaign_name' => Yii::t('app', 'Campaign Name'),
            'type' => Yii::t('app', 'Type'),
            'subscriber_id' => Yii::t('app', 'Subscriber ID'),
            'subscriber_name' => Yii::t('app', 'Subscriber Name'),
            'device_id' => Yii::t('app', 'Device ID'),
            'mac_address' => Yii::t('app', 'Mac Address'),
            'status' => Yii::t('app', 'Status'),
            'campaign_promotion_id' => Yii::t('app', 'Campaign Promotion ID'),
            'campaign_condition_id' => Yii::t('app', 'Campaign Condition ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'type_campaign' => Yii::t('app', 'Loai chien dich'),
            'white_list' => Yii::t('app', 'white_list'),
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
    public function getSite()
    {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCampaignPromotion()
    {
        return $this->hasOne(CampaignPromotion::className(), ['id' => 'campaign_promotion_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCampaignCondition()
    {
        return $this->hasOne(CampaignCondition::className(), ['id' => 'campaign_condition_id']);
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
}
