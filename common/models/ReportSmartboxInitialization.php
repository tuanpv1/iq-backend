<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "report_smartbox_initialization".
 *
 * @property int $id
 * @property int $report_date
 * @property int $total
 * @property int $site_id
 * @property string $city
 * @property int $type_model
 */
class ReportSmartboxInitialization extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'report_smartbox_initialization';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_date', 'total', 'site_id', 'type_model'], 'integer'],
            [['city'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'report_date' => Yii::t('app','Ngày khởi tạo'),
            'total' => Yii::t('app','Số lượng Smartbox khởi tạo'),
            'site_id' => Yii::t('app','Thị trường'),
            'city' => Yii::t('app','Tỉnh/Thành phố'),
            'type_model' => Yii::t('app','Loại thiết bị'),
        ];
    }
}
