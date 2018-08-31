<?php

namespace console\models;

use Yii;

/**
 * This is the model class for table "field_data_field_picture_slideshow".
 *
 * @property string $entity_type
 * @property string $bundle
 * @property integer $deleted
 * @property string $entity_id
 * @property string $revision_id
 * @property string $language
 * @property string $delta
 * @property string $field_picture_slideshow_fid
 * @property string $field_picture_slideshow_alt
 * @property string $field_picture_slideshow_title
 * @property string $field_picture_slideshow_width
 * @property string $field_picture_slideshow_height
 */
class FieldDataFieldPictureSlideshow extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'field_data_field_picture_slideshow';
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
            [['deleted', 'entity_id', 'revision_id', 'delta', 'field_picture_slideshow_fid', 'field_picture_slideshow_width', 'field_picture_slideshow_height'], 'integer'],
            [['entity_type', 'bundle'], 'string', 'max' => 128],
            [['language'], 'string', 'max' => 32],
            [['field_picture_slideshow_alt'], 'string', 'max' => 512],
            [['field_picture_slideshow_title'], 'string', 'max' => 1024]
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
            'field_picture_slideshow_fid' => 'Field Picture Slideshow Fid',
            'field_picture_slideshow_alt' => 'Field Picture Slideshow Alt',
            'field_picture_slideshow_title' => 'Field Picture Slideshow Title',
            'field_picture_slideshow_width' => 'Field Picture Slideshow Width',
            'field_picture_slideshow_height' => 'Field Picture Slideshow Height',
        ];
    }
}
