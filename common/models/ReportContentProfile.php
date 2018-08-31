<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%report_content_profile}}".
 *
 * @property integer $id
 * @property integer $report_date
 * @property integer $total_content_profile
 */
class ReportContentProfile extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%report_content_profile}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_date', 'total_content_profile'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'report_date' => Yii::t('app', 'Ngày'),
            'total_content_profile' => Yii::t('app', 'Tổng số phiên bản'),
        ];
    }
}
