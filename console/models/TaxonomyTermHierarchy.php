<?php

namespace console\models;

use Yii;

/**
 * This is the model class for table "taxonomy_term_hierarchy".
 *
 * @property string $tid
 * @property string $parent
 */
class TaxonomyTermHierarchy extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'taxonomy_term_hierarchy';
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
            [['tid', 'parent'], 'required'],
            [['tid', 'parent'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tid' => 'Tid',
            'parent' => 'Parent',
        ];
    }
}
