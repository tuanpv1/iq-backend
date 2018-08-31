<?php

namespace console\models;

use Yii;

/**
 * This is the model class for table "field_data_field_video_picture".
 *
 * @property string $entity_type
 * @property string $bundle
 * @property integer $deleted
 * @property string $entity_id
 * @property string $revision_id
 * @property string $language
 * @property string $delta
 * @property string $field_video_picture_fid
 * @property string $field_video_picture_alt
 * @property string $field_video_picture_title
 * @property string $field_video_picture_width
 * @property string $field_video_picture_height
 */
class FieldDataFieldVideoPicture extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'field_data_field_video_picture';
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
            [['deleted', 'entity_id', 'revision_id', 'delta', 'field_video_picture_fid', 'field_video_picture_width', 'field_video_picture_height'], 'integer'],
            [['entity_type', 'bundle'], 'string', 'max' => 128],
            [['language'], 'string', 'max' => 32],
            [['field_video_picture_alt'], 'string', 'max' => 512],
            [['field_video_picture_title'], 'string', 'max' => 1024]
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
            'field_video_picture_fid' => 'Field Video Picture Fid',
            'field_video_picture_alt' => 'Field Video Picture Alt',
            'field_video_picture_title' => 'Field Video Picture Title',
            'field_video_picture_width' => 'Field Video Picture Width',
            'field_video_picture_height' => 'Field Video Picture Height',
        ];
    }
}
