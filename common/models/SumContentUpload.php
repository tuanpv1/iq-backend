<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%sum_content_upload}}".
 *
 * @property integer $id
 * @property integer $site_id
 * @property integer $content_provider_id
 * @property integer $upload_count
 * @property integer $type
 * @property string $report_date
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property ServiceProvider $serviceProvider
 * @property ContentProvider $contentProvider
 */
class SumContentUpload extends \yii\db\ActiveRecord
{
    const TYPE_VIDEO = 1;
    const TYPE_LIVE = 2;
    const TYPE_MUSIC = 3;
    const TYPE_NEWS = 4;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sum_content_upload}}';
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
    public function rules()
    {
        return [
            [['site_id', 'content_provider_id'], 'required'],
            [['site_id', 'content_provider_id', 'upload_count', 'type', 'created_at', 'updated_at'], 'integer'],
            [['report_date'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app','ID'),
            'site_id' => Yii::t('app','Service Provider ID'),
            'content_provider_id' => Yii::t('app','Content Provider ID'),
            'upload_count' => Yii::t('app','Upload Count'),
            'type' => Yii::t('app','Type'),
            'report_date' => Yii::t('app','Report Date'),
            'created_at' => Yii::t('app','Created At'),
            'updated_at' => Yii::t('app','Updated At'),
        ];
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
    public function getContentProvider()
    {
        return $this->hasOne(ContentProvider::className(), ['id' => 'content_provider_id']);
    }

    /**
     * @return array
     */
    public static function listType()
    {
        $lst = [
            self::TYPE_VIDEO => 'Video',
            self::TYPE_LIVE => 'Live',
            self::TYPE_MUSIC => 'Music',
            self::TYPE_NEWS => 'News',

        ];
        return $lst;
    }

    /**
     * @return int
     */
    public function getTypeName()
    {
        $lst = self::listType();
        if (array_key_exists($this->type, $lst)) {
            return $lst[$this->type];
        }
        return $this->type;
    }
}
