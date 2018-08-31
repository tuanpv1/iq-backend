<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%content_keyword}}".
 *
 * @property integer $id
 * @property integer $subscriber_id
 * @property string $keyword
 * @property integer $hit_count
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $site_id
 *
 * @property Subscriber $subscriber
 * @property ServiceProvider $serviceProvider
 */
class ContentKeyword extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%content_keyword}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subscriber_id', 'hit_count', 'created_at', 'updated_at', 'site_id'], 'integer'],
            [['keyword', 'site_id'], 'required'],
            [['keyword'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'subscriber_id' => Yii::t('app', 'Subscriber ID'),
            'keyword' => Yii::t('app', 'Keyword'),
            'hit_count' => Yii::t('app', 'Hit Count'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'site_id' => Yii::t('app', 'Service Provider ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriber()
    {
        return $this->hasOne(Subscriber::className(), ['id' => 'subscriber_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceProvider()
    {
        return $this->hasOne(ServiceProvider::className(), ['id' => 'site_id']);
    }
}
