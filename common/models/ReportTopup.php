<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%report_topup}}".
 *
 * @property integer $id
 * @property integer $report_date
 * @property integer $site_id
 * @property integer $channel
 * @property integer $count
 * @property integer $white_list
 * @property double $revenue
 * @property double $revenue_pending
 * @property double $revenue_error
 */
class ReportTopup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%report_topup}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_date'], 'required'],
            [['report_date', 'site_id', 'channel', 'count','white_list'], 'integer'],
            [['revenue','revenue_pending','revenue_error'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'report_date' => Yii::t('app', 'Report Date'),
            'site_id' => Yii::t('app', 'Site ID'),
            'channel' => Yii::t('app', 'Channel'),
            'count' => Yii::t('app', 'Count'),
            'revenue' => Yii::t('app', 'Revenue'),
            'revenue_pending' => Yii::t('app', 'Revenue_Pending'),
            'revenue_error' => Yii::t('app', 'Revenue_Error'),
            'white_list' => Yii::t('app', 'White List'),
        ];
    }
}
