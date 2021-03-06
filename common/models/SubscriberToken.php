<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%subscriber_token}}".
 *
 * @property integer $id
 * @property integer $subscriber_id
 * @property string $msisdn
 * @property string $token
 * @property integer $type
 * @property string $ip_address
 * @property integer $created_at
 * @property integer $expired_at
 * @property string $cookies
 * @property integer $status
 * @property integer $channel
 *
 * @property Subscriber $subscriber
 */
class SubscriberToken extends \yii\db\ActiveRecord
{
    const TYPE_WIFI_PASSWORD = 1;
    const TYPE_ACCESS_TOKEN = 2;
    const TYPE_FACTORY_TOKEN = 3;

    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%subscriber_token}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subscriber_id', 'token'], 'required'],
            [['subscriber_id', 'type', 'created_at', 'expired_at', 'status', 'channel'], 'integer'],
            [['msisdn'], 'string', 'max' => 20],
            [['token'], 'string', 'max' => 100],
            [['ip_address'], 'string', 'max' => 45],
            [['cookies'], 'string', 'max' => 1000]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'subscriber_id' => Yii::t('app', 'Subscriber ID'),
            'msisdn' => Yii::t('app', 'Msisdn'),
            'token' => Yii::t('app', 'Token'),
            'type' => Yii::t('app', 'Type'),
            'ip_address' => Yii::t('app', 'Ip Address'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'expired_at' => Yii::t('app', 'Expired At'),
            'cookies' => Yii::t('app', 'Cookies'),
            'status' => Yii::t('app', 'Trạng thái'),
            'channel' => Yii::t('app', 'Channel'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriber()
    {
        return $this->hasOne(Subscriber::className(), ['id' => 'subscriber_id']);
    }

    /**
     * @param $subscriber_id
     * @param $channel
     * @return SubscriberToken|null
     * @throws \Exception
     */
    public static function generateToken($subscriber_id, $channel){
        /** @var  $st SubscriberToken*/
        $st = SubscriberToken::find()->where(['subscriber_id' => $subscriber_id,'channel'=>$channel])->one();
        if($st){
            $st->token = Yii::$app->security->generateRandomString();
            $st->created_at = time();
            $st->expired_at = time() + Yii::$app->params['api.AccessTokenExpire'];
            $st->status = SubscriberToken::STATUS_ACTIVE;
            $st->ip_address = Yii::$app->request->getUserIP();;
            if($st->update()){
                return $st;
            }
            return null;
        }else{
            $s = new SubscriberToken();
            $s->subscriber_id = $subscriber_id;
            $s->token = Yii::$app->security->generateRandomString();
            $s->created_at = time();
            $s->expired_at = time() + Yii::$app->params['api.AccessTokenExpire'];
            $s->type = SubscriberToken::TYPE_ACCESS_TOKEN;
            $s->status = self::STATUS_ACTIVE;
            $s->channel = $channel;
            $s->ip_address = Yii::$app->request->getUserIP();
            if($s->save()){
                return $s;
            }
            return null;
        }
    }

    public static function findByAccessToken($token)
    {
        return SubscriberToken::find()
            ->andWhere(['status' => SubscriberToken::STATUS_ACTIVE, 'token' => $token])
            ->andWhere("expired_at is null OR expired_at > :time", [":time" => time()])
            ->one();
    }


}
