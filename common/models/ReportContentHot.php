<?php
/**
 * Created by PhpStorm.
 * User: mycon
 * Date: 12/23/2016
 * Time: 4:43 PM
 */
namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%report_content}}".
 *
 * @property integer $id
 * @property integer $report_date
 * @property integer $site_id
 * @property integer $content_type
 * @property integer $category_id
 * @property integer $content_id
 * @property integer $total_content_view
 * @property integer $cp_id
 *
 * @property Site $site
 * @property Category $category
 */
class ReportContentHot extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%report_content_hot}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_date', 'site_id', 'content_type', 'category_id', 'content_id', 'total_content_view','cp_id'], 'integer']
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
            'content_type' => \Yii::t('app', 'Loại nội dung'),
            'category_id' => \Yii::t('app', 'Danh mục'),
            'content_id' => \Yii::t('app', 'Tên nội dung'),
            'total_content_view' => \Yii::t('app', 'Tổng lượt xem'),
            'cp_id'=>Yii::t('app','Nhà cung cấp nội dung')
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
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }
}