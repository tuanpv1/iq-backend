<?php

namespace console\models;

use Yii;

/**
 * This is the model class for table "map_live".
 *
 * @property integer $id
 * @property string $live_title
 * @property integer $channel_id
 * @property integer $version_id
 * @property integer $type
 */
class MapLive extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'map_live';
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
            [['channel_id', 'version_id', 'type'], 'integer'],
            [['live_title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'live_title' => 'Live Title',
            'channel_id' => 'Channel ID',
            'version_id' => 'Version ID',
            'type' => 'Type',
        ];
    }
}
