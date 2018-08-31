<?php

namespace console\models;

use Yii;

/**
 * This is the model class for table "field_data_field_message".
 *
 * @property string $entity_type
 * @property integer $status
 * @property string $entity_id
 * @property string $delta
 * @property string $language
 * @property string $field_message_title
 * @property string $field_message_value
 * @property string $sender
 * @property string $send_date
 */
class FieldDataFieldMessage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'field_data_field_message';
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
            [['entity_type', 'entity_id', 'language', 'send_date'], 'required'],
            [['status', 'entity_id', 'sender'], 'integer'],
            [['send_date'], 'safe'],
            [['entity_type'], 'string', 'max' => 128],
            [['language'], 'string', 'max' => 32],
            [['field_message_title', 'field_message_value'], 'string', 'max' => 256]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'entity_type' => 'Entity Type',
            'status' => 'Status',
            'entity_id' => 'Entity ID',
            'delta' => 'Delta',
            'language' => 'Language',
            'field_message_title' => 'Field Message Title',
            'field_message_value' => 'Field Message Value',
            'sender' => 'Sender',
            'send_date' => 'Send Date',
        ];
    }
}
