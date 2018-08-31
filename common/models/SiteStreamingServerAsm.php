<?php

namespace common\models;

use api\helpers\Message;
use Yii;

/**
 * This is the model class for table "site_streaming_server_asm".
 *
 * @property integer $id
 * @property integer $site_id
 * @property integer $streaming_server_id
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Site $site
 * @property StreamingServer $streamingServer
 */
class SiteStreamingServerAsm extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'site_streaming_server_asm';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['site_id', 'streaming_server_id'], 'required'],
            [['site_id', 'streaming_server_id', 'created_at', 'updated_at'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app','ID'),
            'site_id' => Yii::t('app','Site ID'),
            'streaming_server_id' => Yii::t('app','Streaming Server ID'),
            'created_at' => Yii::t('app','Created At'),
            'updated_at' => Yii::t('app','Updated At'),
        ];
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

    /**
     * @param $streaming_server_id
     * @param $site_id
     * @param $site_id
     * @return array
     */
    public static function createSiteStreamingServerAsm($streaming_server_id, $site_id)
    {
        $res = [];
        /** @var  $sssa SiteStreamingServerAsm */
        $sssa = SiteStreamingServerAsm::findOne(['streaming_server_id' => $streaming_server_id, 'site_id' => $site_id]);
        if ($sssa) {
            $sssa->updated_at = time();
            if (!$sssa->save()) {
                $message = $sssa->getFirstMessageError();
                $res['success'] = false;
                $res['message'] = $message;
                return $res;
            }
            $res['success'] = true;
            $res['message'] = Message::getSuccessMessage();
            $res['item'] = $sssa;
            return $res;
        }

        $sssa = new SiteStreamingServerAsm();
        $sssa->streaming_server_id = $streaming_server_id;
        $sssa->site_id = $site_id;

        /** Validate và save, nếu có lỗi thì return message_error */
        if (!$sssa->validate() || !$sssa->save()) {
            $message = $sssa->getFirstMessageError();
            $res['success'] = false;
            $res['message'] = $message;
            return $res;
        }
        $res['success'] = true;
        $res['message'] = Message::getSuccessMessage();
        $res['item'] = $sssa;
        return $res;
    }

    private function getFirstMessageError()
    {
        $error = $this->firstErrors;
        $message = "";
        foreach ($error as $key => $value) {
            $message .= $value;
            break;
        }
        return $message;
    }

    /**
     * @description Lấy 1 server cache random
     * @param $site_id
     * @return bool|mixed
     */
    public static function getStreamingServerPriority($site_id){
        $lst = SiteStreamingServerAsm::find()
                    ->joinWith('streamingServer')
                    ->andWhere(['streaming_server.status' => StreamingServer::STATUS_ACTIVE])
                    ->andWhere(['site_streaming_server_asm.site_id' => $site_id])->all();

        if(count($lst) <=0){
            return false;
        }
        $index = array_rand($lst);
        return $lst[$index]->streamingServer;
    }
}
