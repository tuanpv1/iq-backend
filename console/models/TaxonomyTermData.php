<?php

namespace console\models;

use Yii;

/**
 * This is the model class for table "taxonomy_term_data".
 *
 * @property string $tid
 * @property string $vid
 * @property string $name
 * @property string $description
 * @property string $format
 * @property integer $weight
 */
class TaxonomyTermData extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'taxonomy_term_data';
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
            [['vid', 'weight'], 'integer'],
            [['description'], 'string'],
            [['name', 'format'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tid' => 'Tid',
            'vid' => 'Vid',
            'name' => 'Name',
            'description' => 'Description',
            'format' => 'Format',
            'weight' => 'Weight',
        ];
    }
}
