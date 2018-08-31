<?php

namespace common\models;

/**
 * This is the model class for table "report_campaign".
 *
 * @property integer $id
 * @property integer $site_id
 * @property integer $campaign_id
 * @property integer $white_list
 * @property integer $report_date
 * @property integer $total_mac_address
 * @property integer $total_username
 *
 * @property Site $site
 * @property Campaign $campaign
 */

class ReportCampaign extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%report_campaign}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_date', 'site_id','campaign_id'], 'required'],
            [['report_date', 'site_id', 'campaign_id', 'total_username','total_mac_address','white_list'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('app', 'ID'),
            'report_date' => \Yii::t('app', 'Ngày'),
            'site_id' => \Yii::t('app', 'Nhà cung cấp dịch vụ'),
            'campaign_id' => \Yii::t('app', 'Gói cước'),
            'total_username' => \Yii::t('app', 'Tổng thuê bao'),
            'total_mac_address' => \Yii::t('app', 'Tổng thiết bị'),
            'white_list' => \Yii::t('app', 'White List'),
        ];
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
    public function getCampaign()
    {
        return $this->hasOne(Campaign::className(), ['id' => 'campaign_id']);
    }
}