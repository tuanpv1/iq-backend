<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%report_subscriber_service}}".
 *
 * @property integer $id
 * @property integer $report_date
 * @property integer $site_id
 * @property integer $cp_id
 * @property integer $dealer_id
 * @property integer $service_id
 * @property integer $subscriber_register
 * @property integer $subscriber_retry
 * @property integer $subscriber_expired
 * @property integer $subscriber_not_expiration
 * @property integer $white_list
 *
 * @property Site $site
 * @property Dealer $dealer
 */
class ReportSubscriberService extends \yii\db\ActiveRecord
{

    const NOT_WHITE_LIST = 2;
    const IS_WHITE_LIST  = 1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%report_subscriber_service}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_date', 'site_id'], 'required'],
            [['report_date', 'site_id', 'dealer_id', 'service_id', 'subscriber_register', 'subscriber_retry', 'subscriber_expired','subscriber_not_expiration', 'white_list','cp_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                  => \Yii::t('app', 'ID'),
            'report_date'         => \Yii::t('app', 'Ngày'),
            'site_id'             => \Yii::t('app', 'Nhà cung cấp dịch vụ'),
            'dealer_id'           => \Yii::t('app', 'Đại lý'),
            'service_id'          => \Yii::t('app', 'Gói cước'),
            'subscriber_register' => \Yii::t('app', 'Tổng số gói cước đăng kí mới'),
            'subscriber_retry'    => \Yii::t('app', 'Tổng số gói cước gia hạn'),
            'subscriber_expired'  => \Yii::t('app', 'Tổng số gói cước sẽ hết hạn'),
            'subscriber_not_expiration'  => \Yii::t('app', 'Tổng số gói cước còn hạn trong ngày'),
            'white_list'          => \Yii::t('app', 'Whitelist'),
            'cp_id'               => Yii::t('app','Nhà cung cấp nội dung')
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
        return $this->hasOne(Dealer::className(), ['id' => 'dealer_id']);
    }

    /**
     * @return array
     */
    public static function listWhitelistTypes()
    {
        $lst = [
            self::NOT_WHITE_LIST => Yii::t('app', 'Bình thường'),
            self::IS_WHITE_LIST  => Yii::t('app', 'Whitelist'),
        ];
        return $lst;
    }
}
