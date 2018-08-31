<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "report_monthly_dealer_revenue_detail".
 *
 * @property integer $id
 * @property integer $subscriber_id
 * @property integer $site_id
 * @property integer $dealer_id
 * @property integer $view_count
 * @property double $view_percent
 * @property double $revenue
 * @property double $revenue_percent
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $report_date
 *
 * @property Dealer $dealer
 * @property Site $site
 * @property Subscriber $subscriber
 */
class ReportMonthlyDealerRevenueDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'report_monthly_dealer_revenue_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subscriber_id', 'site_id', 'dealer_id', 'report_date'], 'required'],
            [['subscriber_id', 'site_id', 'dealer_id', 'view_count', 'created_at', 'updated_at'], 'integer'],
            [['view_percent', 'revenue', 'revenue_percent'], 'number'],
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
            'subscriber_id' =>\Yii::t('app',  'ID thuê bao'),
            'site_id' => \Yii::t('app', 'Site ID'),
            'dealer_id' => \Yii::t('app', 'Đại lý ID'),
            'view_count' => \Yii::t('app', 'Tổng xem'),
            'view_percent' => \Yii::t('app', 'Giá xem'),
            'revenue' => \Yii::t('app', 'Doanh thu'),
            'revenue_percent' => \Yii::t('app', '% doanh thu'),
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriber()
    {
        return $this->hasOne(Subscriber::className(), ['id' => 'subscriber_id']);
    }
}
