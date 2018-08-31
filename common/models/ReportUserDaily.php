<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%report_user_daily}}".
 *
 * @property integer $id
 * @property integer $report_date
 * @property integer $site_id
 * @property integer $active_user
 * @property integer $active_user_package
 *
 * @property Site $site
 */
class ReportUserDaily extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%report_user_daily}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_date', 'site_id', 'active_user', 'active_user_package'], 'integer'],
            [['site_id'], 'required']
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
            'site_id' => \Yii::t('app', 'Site ID'),
            'active_user' =>\Yii::t('app',  'Người dùng hoạt động'),
            'active_user_package' => \Yii::t('app', 'Người dùng gói cước'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }
}
