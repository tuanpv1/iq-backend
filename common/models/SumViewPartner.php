<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%sum_view_partner}}".
 *
 * @property integer $id
 * @property integer $subscriber_id
 * @property integer $site_id
 * @property integer $content_provider_id
 * @property integer $cp_view_count
 * @property integer $sp_view_count
 * @property string $report_date
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property ContentProvider $contentProvider
 * @property ServiceProvider $serviceProvider
 * @property Subscriber $subscriber
 */
class SumViewPartner extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sum_view_partner}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subscriber_id', 'site_id', 'content_provider_id'], 'required'],
            [['subscriber_id', 'site_id', 'content_provider_id', 'cp_view_count', 'sp_view_count', 'created_at', 'updated_at'], 'integer'],
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
            'subscriber_id' => Yii::t('app','Subscriber ID'),
            'site_id' => Yii::t('app','Service Provider ID'),
            'content_provider_id' => Yii::t('app','Content Provider ID'),
            'cp_view_count' => Yii::t('app','Cp View Count'),
            'sp_view_count' => Yii::t('app','Sp View Count'),
            'report_date' => Yii::t('app','Report Date'),
            'created_at' => Yii::t('app','Created At'),
            'updated_at' => Yii::t('app','Updated At')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentProvider()
    {
        return $this->hasOne(ContentProvider::className(), ['id' => 'content_provider_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceProvider()
    {
        return $this->hasOne(ServiceProvider::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriber()
    {
        return $this->hasOne(Subscriber::className(), ['id' => 'subscriber_id']);
    }
}
