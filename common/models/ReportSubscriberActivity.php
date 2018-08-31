<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%report_subscriber_activity}}".
 *
 * @property integer $id
 * @property integer $report_date
 * @property integer $site_id
 * @property integer $via_site_daily
 * @property integer $total_via_site
 * @property integer $content_type
 * @property integer $via_smb
 * @property integer $via_android
 * @property integer $via_ios
 * @property integer $via_website
 * @property Site $site
 */
class ReportSubscriberActivity extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%report_subscriber_activity}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_date', 'site_id', 'via_site_daily', 'total_via_site','content_type'], 'integer'],
            [['site_id','content_type'], 'required']
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
            'site_id' => \Yii::t('app', 'Site ID'),
            'via_site_daily' => \Yii::t('app', 'Số lượt truy cập trong ngày'),
            'total_via_site' => \Yii::t('app', 'Tổng lượt truy cập'),
            'via_smb' => \Yii::t('app', 'Từ Smart box'),
            'via_android' => \Yii::t('app', 'Từ ứng dụng Android'),
            'via_ios' => \Yii::t('app', 'Từ ứng dụng IOS'),
            'via_website' => \Yii::t('app', 'Từ website'),
            'content_type' => \Yii::t('app', 'loại nội dung'),




        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }
}
