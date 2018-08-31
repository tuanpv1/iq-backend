<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "log_language".
 *
 * @property integer $id
 * @property integer $id_lang
 * @property integer $id_user
 * @property integer $updated_at
 * @property integer $created_at
 */
class LogLanguage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'log_language';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_lang', 'id_user'], 'required'],
            [['id_lang', 'id_user', 'updated_at', 'created_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_lang' => 'Id Lang',
            'id_user' => 'Id User',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }
}
