<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%report_subscriber_daily}}".
 *
 * @property integer $id
 * @property integer $report_date
 * @property integer $site_id
 * @property integer $user_admin_id
 * @property integer $dealer_id
 * @property integer $service_id
 * @property integer $total_subscriber
 * @property integer $total_active_subscriber
 * @property integer $subscriber_register_daily
 * @property integer $total_cancel_subscriber
 * @property integer $subscriber_cancel_daily
 *
 * @property Service $service
 * @property Site $site
 */
class ReportSubscriberDaily extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%report_subscriber_daily}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_date', 'site_id'], 'required'],
            [['report_date', 'site_id','user_admin_id','dealer_id', 'service_id', 'total_subscriber', 'total_active_subscriber', 'subscriber_register_daily', 'total_cancel_subscriber', 'subscriber_cancel_daily'], 'integer']
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
            'user_admin_id' => \Yii::t('app', 'Đại lí mẹ'),
            'dealer_id' => Yii::t('app', 'Đại lý'),
            'service_id' => \Yii::t('app', 'Gói cước'),
            'total_subscriber' => \Yii::t('app', 'Tổng thuê bao đăng ký'),
            'total_active_subscriber' => \Yii::t('app', 'Thuê bao đang hoạt động'),
            'subscriber_register_daily' => \Yii::t('app', 'Thuê bao đăng ký mới'),
            'total_cancel_subscriber' => \Yii::t('app', 'Tổng thuê bao hủy'),
            'subscriber_cancel_daily' => \Yii::t('app', 'Thuê bao hủy'),
        ];
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
    public function getSite()
    {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }
}
