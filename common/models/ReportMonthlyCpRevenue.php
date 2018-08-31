<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%report_monthly_cp_revenue}}".
 *
 * @property integer $id
 * @property integer $site_id
 * @property integer $content_provider_id
 * @property double $revenue
 * @property double $revenue_percent
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $report_date
 *
 * @property ServiceProvider $serviceProvider
 * @property ContentProvider $contentProvider
 */
class ReportMonthlyCpRevenue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%report_monthly_cp_revenue}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['site_id', 'content_provider_id', 'report_date'], 'required'],
            [['site_id', 'content_provider_id', 'created_at', 'updated_at'], 'integer'],
            [['revenue', 'revenue_percent'], 'number'],
            [['report_date'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'site_id' => Yii::t('app', 'Service Provider ID'),
            'content_provider_id' => Yii::t('app', 'Content Provider ID'),
            'revenue' => Yii::t('app', 'Revenue'),
            'revenue_percent' => Yii::t('app', 'Revenue Percent'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'updated_at' => Yii::t('app', 'Ngày thay đổi thông tin'),
            'report_date' => Yii::t('app', 'Ngày báo cáo'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceProvider()
    {
        return $this->hasOne(ServiceProvider::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentProvider()
    {
        return $this->hasOne(ContentProvider::className(), ['id' => 'content_provider_id']);
    }
}
