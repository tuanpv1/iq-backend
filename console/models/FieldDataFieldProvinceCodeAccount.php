<?php

namespace console\models;

use Yii;

/**
 * This is the model class for table "field_data_field_province_code_account".
 *
 * @property string $entity_type
 * @property string $bundle
 * @property integer $deleted
 * @property string $entity_id
 * @property string $revision_id
 * @property string $language
 * @property string $delta
 * @property string $field_province_code_account_nid
 */
class FieldDataFieldProvinceCodeAccount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'field_data_field_province_code_account';
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
            [['deleted', 'entity_id', 'revision_id', 'delta', 'field_province_code_account_nid'], 'integer'],
            [['entity_type', 'bundle'], 'string', 'max' => 128],
            [['language'], 'string', 'max' => 32]
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
            'field_province_code_account_nid' => 'Field Province Code Account Nid',
        ];
    }
}
