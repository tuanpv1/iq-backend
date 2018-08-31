<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "semantic_migrate".
 *
 * @property integer $id
 * @property integer $status
 * @property string $file
 * @property integer $time
 * @property integer $created_at
 * @property integer $updated_at
 */
class SemanticMigrate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'semantic_migrate';
    }
    const STATUS_ACTIVE = 10;
    const STATUS_DEACTIVE = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'time', 'created_at', 'updated_at'], 'integer'],
            [['file'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
            'file' => 'File',
            'time' => 'Time',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
