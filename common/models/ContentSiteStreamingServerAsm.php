<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "content_site_streaming_server_asm".
 *
 * @property integer $id
 * @property integer $content_id
 * @property integer $site_id
 * @property integer $streaming_server_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $time_sync_sent
 * @property integer $time_sync_received
 *
 * @property Content $content
 * @property Site $site
 * @property StreamingServer $streamingServer
 */
class ContentSiteStreamingServerAsm extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'content_site_streaming_server_asm';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content_id', 'site_id', 'streaming_server_id', 'status'], 'required'],
            [['content_id', 'site_id', 'streaming_server_id', 'status', 'created_at', 'updated_at', 'time_sync_sent', 'time_sync_received'], 'integer'],
            [['content_id'], 'exist', 'skipOnError' => true, 'targetClass' => Content::className(), 'targetAttribute' => ['content_id' => 'id']],
            [['site_id'], 'exist', 'skipOnError' => true, 'targetClass' => Site::className(), 'targetAttribute' => ['site_id' => 'id']],
            [['streaming_server_id'], 'exist', 'skipOnError' => true, 'targetClass' => StreamingServer::className(), 'targetAttribute' => ['streaming_server_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'content_id' => Yii::t('app', 'Content ID'),
            'site_id' => Yii::t('app', 'Site ID'),
            'streaming_server_id' => Yii::t('app', 'Streaming Server ID'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'time_sync_sent' => Yii::t('app', 'Time Sync Sent'),
            'time_sync_received' => Yii::t('app', 'Time Sync Received'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->hasOne(Content::className(), ['id' => 'content_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStreamingServer()
    {
        return $this->hasOne(StreamingServer::className(), ['id' => 'streaming_server_id']);
    }
}
