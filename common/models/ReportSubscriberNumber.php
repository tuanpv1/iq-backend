<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "report_subscriber_number".
 *
 * @property integer $id
 * @property integer $report_date
 * @property integer $site_id
 * @property string $city
 * @property integer $subscriber_register_smb
 * @property integer $subscriber_register_apps
 * @property integer $subscriber_register_web
 * @property integer $total_subscriber
 * @property integer $subscriber_active
 * @property integer $subscriber_register
 * @property integer $total_subscriber_destroy
 * @property integer $subscriber_destroy
 */
class ReportSubscriberNumber extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'report_subscriber_number';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_date', 'site_id'], 'required'],
            [['report_date', 'site_id', 'subscriber_register_smb', 'subscriber_register_apps', 'subscriber_register_web','total_subscriber','subscriber_active','subscriber_register','total_subscriber_destroy','subscriber_destroy'], 'integer'],
            [['city'],'string'],
            [['site_id'], 'exist', 'skipOnError' => true, 'targetClass' => Site::className(), 'targetAttribute' => ['site_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'report_date' => 'Ngày',
            'site_id' => 'Site ID',
            'city' => 'Nơi đăng ký',
            'subscriber_register_smb' => 'ĐK qua Smartbox',
            'subscriber_register_apps' => 'ĐK qua ứng dụng',
            'subscriber_register_web' => 'ĐK qua website',
            'total_subscriber' =>'Tổng thuê bao',
            'subscriber_active' =>'Thuê bao đang hoạt động',
            'subscriber_register' => 'Thuê  bao đăng ký mới',
            'total_subscriber_destroy' => 'Tổng thuê  bao hủy',
            'subscriber_destroy' =>'Thuê  bao hủy'
        ];
    }
}
