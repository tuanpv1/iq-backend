<?php

namespace console\models\tvcatchup;

use Yii;

/**
 * This is the model class for table "{{%field_data_field_channel_date}}".
 *
 * @property string $entity_type
 * @property string $bundle
 * @property integer $deleted
 * @property string $entity_id
 * @property string $revision_id
 * @property string $language
 * @property string $delta
 * @property string $field_channel_date_value
 * @property string $field_channel_date_format
 */
class FieldDataFieldChannelDate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%field_data_field_channel_date}}';
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
            [['field_channel_date_value'], 'string', 'max' => 10],
            [['field_channel_date_format'], 'string', 'max' => 255]
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
            'field_channel_date_value' => 'Field Channel Date Value',
            'field_channel_date_format' => 'Field Channel Date Format',
        ];
    }
}
