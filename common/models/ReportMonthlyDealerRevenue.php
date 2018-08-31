<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "report_monthly_dealer_revenue".
 *
 * @property integer $id
 * @property integer $site_id
 * @property integer $dealer_id
 * @property double $revenue
 * @property double $revenue_percent
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $report_date
 *
 * @property Dealer $dealer
 * @property Site $site
 */
class ReportMonthlyDealerRevenue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'report_monthly_dealer_revenue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['site_id', 'dealer_id', 'report_date'], 'required'],
            [['site_id', 'dealer_id', 'created_at', 'updated_at'], 'integer'],
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
            'id' => \Yii::t('app', 'ID'),
            'site_id' => \Yii::t('app', 'Site ID'),
            'dealer_id' => \Yii::t('app', 'ID Đại lý'),
            'revenue' => \Yii::t('app', 'Doanh thu'),
            'revenue_percent' => \Yii::t('app', 'Revenue Percent'),
            'created_at' => \Yii::t('app', 'Ngày tạo'),
            'updated_at' => \Yii::t('app', 'Ngày thay đổi thông tin'),
            'report_date' => \Yii::t('app', 'Ngày báo cáo'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDealer()
    {
        return $this->hasOne(Dealer::className(), ['id' => 'dealer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }
}
