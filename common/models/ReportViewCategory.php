<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "report_view_category".
 *
 * @property integer $id
 * @property string $report_date
 * @property integer $site_id
 * @property integer $content_provider_id
 * @property integer $category_id
 * @property integer $view_count
 * @property integer $download_count
 * @property integer $type
 * @property double $buy_revenues
 */
class ReportViewCategory extends \yii\db\ActiveRecord
{
    // Do 1 view thuoc nhieu category -> cong view cua cac category != tong view cua cp
    const TYPE_CATEGORY = 1; // Đếm số lượt view của 1 category
    const TYPE_FULL = 2; // Đếm tổng số lượt view của cp

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'report_view_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_date'], 'safe'],
            [['site_id', 'content_provider_id', 'category_id', 'view_count', 'download_count', 'type'], 'integer'],
            [['buy_revenues'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('app', 'ID'),
            'report_date' => \Yii::t('app', 'Ngày báo cáo'),
            'site_id' => \Yii::t('app', 'Service Provider ID'),
            'content_provider_id' => \Yii::t('app', 'Content Provider ID'),
            'category_id' => \Yii::t('app', 'ID Danh mục'),
            'view_count' => \Yii::t('app', 'Tổng xem'),
            'download_count' => \Yii::t('app', 'Tổng lượt tải'),
            'buy_revenues' => \Yii::t('app', 'Doanh thu mua'),
        ];
    }
}
