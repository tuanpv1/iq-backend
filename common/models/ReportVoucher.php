<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%report_voucher}}".
 *
 * @property integer $id
 * @property integer $report_date
 * @property integer $site_id
 * @property integer $dealer_id
 * @property integer $total_revenues
 * @property integer $revenues_voucher
 * @property integer $total_voucher_created
 *
 * @property Site $site
 * @property Dealer $dealer
 */
class ReportVoucher extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%report_voucher}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_date', 'site_id'], 'required'],
            [['report_date', 'site_id', 'dealer_id', 'total_revenues', 'revenues_voucher', 'total_voucher_created'], 'safe']
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
            'dealer_id' => \Yii::t('app', 'Đại lý'),
            'total_revenues' => \Yii::t('app', 'Tổng doanh thu'),
            'revenues_voucher' => \Yii::t('app', 'Doanh thu thẻ nạp'),
            'total_voucher_created' => \Yii::t('app', 'Số lượng thẻ nạp đã dùng'),
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
}
