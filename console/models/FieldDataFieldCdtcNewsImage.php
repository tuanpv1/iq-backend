<?php

namespace console\models;

use Yii;

/**
 * This is the model class for table "field_data_field_cdtc_news_image".
 *
 * @property string $entity_type
 * @property string $bundle
 * @property integer $deleted
 * @property string $entity_id
 * @property string $revision_id
 * @property string $language
 * @property string $delta
 * @property string $field_cdtc_news_image_fid
 * @property string $field_cdtc_news_image_alt
 * @property string $field_cdtc_news_image_title
 * @property string $field_cdtc_news_image_width
 * @property string $field_cdtc_news_image_height
 */
class FieldDataFieldCdtcNewsImage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'field_data_field_cdtc_news_image';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_tvod1');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entity_type', 'deleted', 'entity_id', 'language', 'delta'], 'required'],
            [['deleted', 'entity_id', 'revision_id', 'delta', 'field_cdtc_news_image_fid', 'field_cdtc_news_image_width', 'field_cdtc_news_image_height'], 'integer'],
            [['entity_type', 'bundle'], 'string', 'max' => 128],
            [['language'], 'string', 'max' => 32],
            [['field_cdtc_news_image_alt'], 'string', 'max' => 512],
            [['field_cdtc_news_image_title'], 'string', 'max' => 1024]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'entity_type' => 'Entity Type',
            'bundle' => 'Bundle',
            'deleted' => 'Deleted',
            'entity_id' => 'Entity ID',
            'revision_id' => 'Revision ID',
            'language' => 'Language',
            'delta' => 'Delta',
            'field_cdtc_news_image_fid' => 'Field Cdtc News Image Fid',
            'field_cdtc_news_image_alt' => 'Field Cdtc News Image Alt',
            'field_cdtc_news_image_title' => 'Field Cdtc News Image Title',
            'field_cdtc_news_image_width' => 'Field Cdtc News Image Width',
            'field_cdtc_news_image_height' => 'Field Cdtc News Image Height',
        ];
    }
}
