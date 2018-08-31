<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%report_subscriber_service}}".
 *
 * @property integer $id
 * @property integer $report_date
 * @property integer $site_id
 * @property integer $service_id
 * @property integer $device_type
 * @property string $ip_to_location
 * @property integer $total_subscriber_initialize
 *
 * @property Site $site
 * @property Device $device
 * @property Service $service
 */
class ReportSubscriberInitialize extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%report_subscriber_initialize}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_date', 'site_id','device_type','total_subscriber_initialize','service_id'], 'required'],
            [['report_date', 'site_id','device_type','total_subscriber_initialize','service_id','ip_to_location'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                  => \Yii::t('app', 'ID'),
            'report_date'         => \Yii::t('app', 'Ngày khởi tạo'),
            'site_id'             => \Yii::t('app', 'Nhà cung cấp dịch vụ'),
            'device_type'         => \Yii::t('app', 'Model'),
            'service_id'          => \Yii::t('app', 'Gói cước'),
            'ip_to_location'      => \Yii::t('app', 'Tỉnh / thành phố'),
            'total_subscriber_initialize'      => \Yii::t('app', 'Số lượng thuê bao khởi tạo'),
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
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
    }

}
