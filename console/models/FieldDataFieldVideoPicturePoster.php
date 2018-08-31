<?php

namespace console\models;

use Yii;

/**
 * This is the model class for table "field_data_field_video_picture_poster".
 *
 * @property string $entity_type
 * @property string $bundle
 * @property integer $deleted
 * @property string $entity_id
 * @property string $revision_id
 * @property string $language
 * @property string $delta
 * @property string $field_video_picture_poster_fid
 * @property string $field_video_picture_poster_alt
 * @property string $field_video_picture_poster_title
 * @property string $field_video_picture_poster_width
 * @property string $field_video_picture_poster_height
 */
class FieldDataFieldVideoPicturePoster extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'field_data_field_video_picture_poster';
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
            [['deleted', 'entity_id', 'revision_id', 'delta', 'field_video_picture_poster_fid', 'field_video_picture_poster_width', 'field_video_picture_poster_height'], 'integer'],
            [['entity_type', 'bundle'], 'string', 'max' => 128],
            [['language'], 'string', 'max' => 32],
            [['field_video_picture_poster_alt'], 'string', 'max' => 512],
            [['field_video_picture_poster_title'], 'string', 'max' => 1024]
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
            'field_video_picture_poster_fid' => 'Field Video Picture Poster Fid',
            'field_video_picture_poster_alt' => 'Field Video Picture Poster Alt',
            'field_video_picture_poster_title' => 'Field Video Picture Poster Title',
            'field_video_picture_poster_width' => 'Field Video Picture Poster Width',
            'field_video_picture_poster_height' => 'Field Video Picture Poster Height',
        ];
    }
}
