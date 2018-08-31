<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%sum_content_download}}".
 *
 * @property integer $id
 * @property integer $content_id
 * @property integer $site_id
 * @property integer $content_provider_id
 * @property integer $download_count
 * @property double $amount
 * @property integer $is_free
 * @property integer $type
 * @property string $report_date
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Content $content
 * @property ServiceProvider $serviceProvider
 * @property ContentProvider $contentProvider
 */
class SumContentDownload extends \yii\db\ActiveRecord
{
    const TYPE_VIDEO = 1;
    const TYPE_LIVE = 2;
    const TYPE_MUSIC = 3;
    const TYPE_NEWS = 4;

    const IS_FREE = 0;
    const NOT_FREE = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sum_content_download}}';
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
            [['content_id', 'site_id', 'content_provider_id'], 'required'],
            [['content_id', 'site_id', 'content_provider_id', 'download_count', 'is_free', 'type', 'created_at', 'updated_at'], 'integer'],
            [['amount'], 'number'],
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
            'content_id' => Yii::t('app','Content ID'),
            'site_id' => Yii::t('app','Service Provider ID'),
            'content_provider_id' => Yii::t('app','Content Provider ID'),
            'download_count' => Yii::t('app','Download Count'),
            'amount' => Yii::t('app','Amount'),
            'is_free' => Yii::t('app','Is Free'),
            'type' => Yii::t('app','Type'),
            'report_date' => Yii::t('app','Report Date'),
            'created_at' => Yii::t('app','Created At'),
            'updated_at' => Yii::t('apP','Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->hasOne(Content::className(), ['id' => 'content_id']);
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

    /**
     * @return array
     */
    public static function listPrice()
    {
        $lst = [
            self::IS_FREE => 'Miễn phí',
            self::NOT_FREE => 'Mất phí',
        ];
        return $lst;
    }

    /**
     * @return int
     */
    public function getPriceName()
    {
        $lst = self::listPrice();
        if (array_key_exists($this->is_free, $lst)) {
            return $lst[$this->is_free];
        }
        return $this->is_free;
    }
}
