<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%sum_service_amount}}".
 *
 * @property integer $id
 * @property integer $service_id
 * @property integer $site_id
 * @property integer $type
 * @property double $amount
 * @property string $report_date
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Service $service
 * @property ServiceProvider $serviceProvider
 */
class SumServiceAmount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sum_service_amount}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_id', 'site_id', 'type', 'created_at', 'updated_at'], 'integer'],
            [['site_id'], 'required'],
            [['amount'], 'number'],
            [['report_date'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app','ID'),
            'service_id' => Yii::t('app','Service ID'),
            'site_id' => Yii::t('app','Service Provider ID'),
            'type' => Yii::t('app','Type'),
            'amount' => Yii::t('app','Doanh thu'),
            'report_date' => Yii::t('app','Report Date'),
            'created_at' => Yii::t('app','Created At'),
            'updated_at' => Yii::t('app','Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceProvider()
    {
        return $this->hasOne(ServiceProvider::className(), ['id' => 'site_id']);
    }
}
