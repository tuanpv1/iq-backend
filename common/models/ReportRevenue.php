<?php

namespace common\models;

use common\helpers\CommonUtils;
use Yii;

/**
 * This is the model class for table "{{%report_revenue}}".
 *
 * @property integer $id
 * @property integer $report_date
 * @property integer $site_id
 * @property integer $cp_id
 * @property integer $service_id
 * @property integer $total_revenues
 * @property integer $renew_revenues
 * @property integer $register_revenues
 * @property integer $content_buy_revenues
 * @property integer $white_list
 * @property integer $revenues
 *
 * @property Site $site
 * @property Service $service
 */
class ReportRevenue extends \yii\db\ActiveRecord
{


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%report_revenue}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_date', 'site_id'], 'required'],
            [['report_date', 'site_id', 'service_id', 'total_revenues',
                'renew_revenues', 'register_revenues', 'content_buy_revenues',
                'white_list','cp_id', 'revenues'], 'safe']
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
            'service_id' => \Yii::t('app', 'Gói cước'),
            'total_revenues' => \Yii::t('app', 'Tổng doanh thu (coin)'),
            'renew_revenues' => \Yii::t('app', 'Doanh thu gia hạn (coin)'),
            'register_revenues' => \Yii::t('app', 'Doanh thu đăng ký (coin)'),
            'content_buy_revenues' => \Yii::t('app', 'Doanh thu mua nội dung lẻ (coin)'),
            'white_list' => \Yii::t('app', 'Whitelist'),
            'cp_id'=>Yii::t('app','Nhà cung cấp nội dung'),
            'revenues'=>Yii::t('app','Doanh thu mua gói')
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
