<?php

namespace common\models;

use Yii;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "migrate_status".
 *
 * @property integer $id
 * @property string $type
 * @property integer $max_id
 * @property integer $started_at
 * @property integer $finished_at
 * @property integer $status
 * @property string $desc
 */
class MigrateStatus extends \yii\db\ActiveRecord
{
    const TYPE_CATCHUP = 'CATCHUP';
    const TYPE_VIDEO = 'VIDEO';
    const TYPE_VIDEO_VERSION = 'VIDEO_VERSION';
    const TYPE_CHANNEL_VERSION = 'CHANNEL_VERSION';

    const STATUS_RUNNING = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = -1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'migrate_status';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'max_id'], 'required'],
            [['max_id', 'started_at', 'finished_at', 'status'], 'integer'],
            [['type'], 'string', 'max' => 50],
            [['desc'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'max_id' => 'Max ID',
            'last_migrated_at' => 'Last Migrated At',
            'last_data_at' => 'Last Data At',
        ];
    }

    public static function createOrFinish($type, $maxId = 0, $desc = null, $status = self::STATUS_RUNNING)
    {
        $migrateStatus = MigrateStatus::find()
            ->where(['type' => $type, 'status' => self::STATUS_RUNNING])
            ->orderBy('started_at DESC')
            ->one();
        if (!$migrateStatus) {
            $migrateStatus = new MigrateStatus();
            $migrateStatus->started_at = time();
            $migrateStatus->type = $type;
        }
        $migrateStatus->status = $status;
        $migrateStatus->max_id = $maxId;
        if ($status != self::STATUS_RUNNING) {
            $migrateStatus->finished_at = time();
        }
        if ($desc) {
            $migrateStatus->desc = $desc;
        }
        if (!$migrateStatus->save()) {
            echo 'Cannot save migrate_status \n';
            VarDumper::dump($migrateStatus->errors);
            return;
        }
        return $migrateStatus;
    }

    public static function getRunningMigration($type)
    {
        return MigrateStatus::findOne(['status' => MigrateStatus::STATUS_RUNNING, 'type' => $type]);
    }

    public static function getLastSuccessMigration($type)
    {
        return MigrateStatus::find()
            ->where(['type' => $type, 'status' => self::STATUS_SUCCESS])
            ->orderBy('started_at DESC')
            ->one();
    }

    public function finish($status, $maxId, $desc = '')
    {
        $this->status = $status;
        $this->desc = $desc;
        $this->max_id = $maxId;
        $this->finished_at = time();
        $this->save();
    }
}
