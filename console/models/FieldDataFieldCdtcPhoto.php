<?php

namespace console\models;

use Yii;

/**
 * This is the model class for table "field_data_field_cdtc_photo".
 *
 * @property string $entity_type
 * @property string $bundle
 * @property integer $deleted
 * @property string $entity_id
 * @property string $revision_id
 * @property string $language
 * @property string $delta
 * @property string $field_cdtc_photo_fid
 * @property string $field_cdtc_photo_alt
 * @property string $field_cdtc_photo_title
 * @property string $field_cdtc_photo_width
 * @property string $field_cdtc_photo_height
 */
class FieldDataFieldCdtcPhoto extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'field_data_field_cdtc_photo';
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
            [['deleted', 'entity_id', 'revision_id', 'delta', 'field_cdtc_photo_fid', 'field_cdtc_photo_width', 'field_cdtc_photo_height'], 'integer'],
            [['entity_type', 'bundle'], 'string', 'max' => 128],
            [['language'], 'string', 'max' => 32],
            [['field_cdtc_photo_alt'], 'string', 'max' => 512],
            [['field_cdtc_photo_title'], 'string', 'max' => 1024]
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
            'field_cdtc_photo_fid' => 'Field Cdtc Photo Fid',
            'field_cdtc_photo_alt' => 'Field Cdtc Photo Alt',
            'field_cdtc_photo_title' => 'Field Cdtc Photo Title',
            'field_cdtc_photo_width' => 'Field Cdtc Photo Width',
            'field_cdtc_photo_height' => 'Field Cdtc Photo Height',
        ];
    }
}
