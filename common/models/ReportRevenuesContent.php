<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "report_revenues_content".
 *
 * @property integer $id
 * @property string $report_date
 * @property integer $site_id
 * @property integer $buy_content_number
 * @property integer $renew_number
 * @property integer $register_number
 * @property double $content_revenues
 * @property double $register_revenues
 * @property double $renew_revenues
 * @property double $total_revenues
 */
class ReportRevenuesContent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'report_revenues_content';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_date'], 'safe'],
            [['site_id', 'buy_content_number','renew_number','register_number'], 'integer'],
            [['content_revenues', 'register_revenues', 'renew_revenues', 'total_revenues'], 'number']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('app', 'ID'),
            'report_date' => \Yii::t('app', 'Ngày báo cáo'),
            'site_id' => \Yii::t('app', 'Service Provider'),
            'content_revenues' => \Yii::t('app', 'Mua nội dung lẻ (VND)'),
            'register_revenues' => \Yii::t('app', 'Đăng ký (VND)'),
            'renew_revenues' => \Yii::t('app', 'Gia hạn (VND)'),
            'total_revenues' => \Yii::t('app', 'Tổng (VND)'),
            'buy_content_number' => \Yii::t('app', 'Luợt mua lẻ'),
        ];
    }
}
