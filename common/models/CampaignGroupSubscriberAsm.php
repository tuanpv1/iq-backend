<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "campaign_group_subscriber_asm".
 *
 * @property integer $id
 * @property integer $campaign_id
 * @property integer $group_subscriber_id
 * @property integer $site_id
 * @property integer $status
 * @property integer $type
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $channel
 *
 * @property Campaign $campaign
 * @property Site $site
 * @property GroupSubscriber $groupSubscriber
 */
class CampaignGroupSubscriberAsm extends \yii\db\ActiveRecord
{

    const TYPE_REAL = 1;
    const TYPE_DEMO = 2;

    const STATUS_ACTIVE        = 10; //Đang hoạt động
    const STATUS_INACTIVE      = 0; //Ngừng hoạt động
    const STATUS_DELETE        = 1; // Xóa

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'campaign_group_subscriber_asm';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['campaign_id', 'group_subscriber_id', 'site_id', 'status', 'type'], 'required'],
            [['campaign_id', 'group_subscriber_id', 'site_id', 'status', 'type', 'created_at', 'updated_at', 'channel'], 'integer'],
            [['site_id'], 'exist', 'skipOnError' => true, 'targetClass' => Site::className(), 'targetAttribute' => ['site_id' => 'id']],
            [['group_subscriber_id'], 'exist', 'skipOnError' => true, 'targetClass' => GroupSubscriber::className(), 'targetAttribute' => ['group_subscriber_id' => 'id']],
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
            'group_subscriber_id' => Yii::t('app', 'Group Subscriber ID'),
            'site_id' => Yii::t('app', 'Site ID'),
            'status' => Yii::t('app', 'Status'),
            'type' => Yii::t('app', 'Type'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'channel' => Yii::t('app', 'Channel'),
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
    public function getGroupSubscriber()
    {
        return $this->hasOne(GroupSubscriber::className(), ['id' => 'group_subscriber_id']);
    }

    /**
     * @param $campaignSource
     * @param $campaignTarget
     * @return bool
     */
    public static function checkIdentical($campaignSource,$campaignTarget ){
        foreach ($campaignSource as $itemSource){
            foreach ($campaignTarget as $itemTarget){
                if($itemSource->group_subscriber_id == $itemTarget->group_subscriber_id){
                    return true;
                }
            }
        }
        return false;
    }
}
