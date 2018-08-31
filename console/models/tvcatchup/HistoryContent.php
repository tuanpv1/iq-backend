<?php

namespace console\models\tvcatchup;

use Yii;

/**
 * This is the model class for table "history_content".
 *
 * @property integer $id
 * @property string $mac
 * @property integer $video_id
 * @property integer $created_at
 * @property integer $position
 * @property integer $duration
 * @property string $packageName
 * @property integer $type
 */
class HistoryContent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'history_content';
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
            [['mac', 'video_id'], 'required'],
            [['video_id', 'created_at', 'position', 'duration', 'type'], 'integer'],
            [['mac', 'packageName'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mac' => 'Mac',
            'video_id' => 'Video ID',
            'created_at' => 'Created At',
            'position' => 'Position',
            'duration' => 'Duration',
            'packageName' => 'Package Name',
            'type' => 'Type',
        ];
    }
}
