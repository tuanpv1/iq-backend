<?php

namespace console\models\tvcatchup;

use Yii;

/**
 * This is the model class for table "{{%field_data_field_channel_id_tvod_cms}}".
 *
 * @property string $entity_type
 * @property string $bundle
 * @property integer $deleted
 * @property string $entity_id
 * @property string $revision_id
 * @property string $language
 * @property string $delta
 * @property string $field_channel_id_tvod_cms_value
 * @property string $field_channel_id_tvod_cms_format
 */
class FieldDataFieldChannelIdTvodCms extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%field_data_field_channel_id_tvod_cms}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_tvcatchup');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entity_type', 'deleted', 'entity_id', 'language', 'delta'], 'required'],
            [['deleted', 'entity_id', 'revision_id', 'delta'], 'integer'],
            [['entity_type', 'bundle'], 'string', 'max' => 128],
            [['language'], 'string', 'max' => 32],
            [['field_channel_id_tvod_cms_value'], 'string', 'max' => 20],
            [['field_channel_id_tvod_cms_format'], 'string', 'max' => 255]
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
            'field_channel_id_tvod_cms_value' => 'Field Channel Id Tvod Cms Value',
            'field_channel_id_tvod_cms_format' => 'Field Channel Id Tvod Cms Format',
        ];
    }
}
