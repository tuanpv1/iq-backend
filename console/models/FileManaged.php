<?php

namespace console\models;

use Yii;

/**
 * This is the model class for table "file_managed".
 *
 * @property string $fid
 * @property string $uid
 * @property string $filename
 * @property string $uri
 * @property string $filemime
 * @property string $filesize
 * @property integer $status
 * @property string $timestamp
 */
class FileManaged extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'file_managed';
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
            [['uid', 'filesize', 'status', 'timestamp'], 'integer'],
            [['filename', 'uri', 'filemime'], 'string', 'max' => 255],
            [['uri'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fid' => 'Fid',
            'uid' => 'Uid',
            'filename' => 'Filename',
            'uri' => 'Uri',
            'filemime' => 'Filemime',
            'filesize' => 'Filesize',
            'status' => 'Status',
            'timestamp' => 'Timestamp',
        ];
    }
}
