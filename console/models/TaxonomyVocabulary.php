<?php

namespace console\models;

use Yii;

/**
 * This is the model class for table "taxonomy_vocabulary".
 *
 * @property string $vid
 * @property string $name
 * @property string $machine_name
 * @property string $description
 * @property integer $hierarchy
 * @property string $module
 * @property integer $weight
 */
class TaxonomyVocabulary extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'taxonomy_vocabulary';
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
            [['description'], 'string'],
            [['hierarchy', 'weight'], 'integer'],
            [['name', 'machine_name', 'module'], 'string', 'max' => 255],
            [['machine_name'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'vid' => 'Vid',
            'name' => 'Name',
            'machine_name' => 'Machine Name',
            'description' => 'Description',
            'hierarchy' => 'Hierarchy',
            'module' => 'Module',
            'weight' => 'Weight',
        ];
    }
}
