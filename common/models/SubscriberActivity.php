<?php

namespace common\models;

use api\helpers\Message;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%subscriber_activity}}".
 *
 * @property integer $id
 * @property integer $subscriber_id
 * @property string $msisdn
 * @property integer $action
 * @property integer $device_id
 * @property string $params
 * @property integer $created_at
 * @property string $ip_address
 * @property integer $status
 * @property integer $target_id
 * @property integer $target_type
 * @property integer $type
 * @property integer $type_subscriber
 * @property string $description
 * @property string $user_agent
 * @property integer $channel
 * @property integer $site_id
 *
 * @property Subscriber $subscriber
 * @property ServiceProvider $serviceProvider
 * @property SubscriberTransaction[] $subscriberTransactions
 */
class SubscriberActivity extends \yii\db\ActiveRecord
{

    const ACTION_LOGIN = 1;
    const ACTION_LOGOUT = 2;
//    const ACTION_REGISTER = 3;

    const STATUS_SUCCESS = 10;
    const STATUS_FAIL = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%subscriber_activity}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subscriber_id', 'site_id'], 'required'],
            [['id', 'subscriber_id', 'action', 'created_at','device_id', 'status', 'target_id', 'target_type', 'type', 'channel', 'site_id','type_subscriber'], 'integer'],
            [['params', 'description'], 'string'],
            [['msisdn'], 'string', 'max' => 20],
            [['ip_address'], 'string', 'max' => 45],
            [['user_agent'], 'string', 'max' => 255]
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
            'action' => Yii::t('app', 'Action'),
            'params' => Yii::t('app', 'Params'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'ip_address' => Yii::t('app', 'Ip Address'),
            'status' => Yii::t('app', 'Trạng thái'),
            'target_id' => Yii::t('app', 'Target ID'),
            'target_type' => Yii::t('app', 'Target Type'),
            'type' => Yii::t('app', 'Type'),
            'description' => Yii::t('app', 'Mô tả'),
            'user_agent' => Yii::t('app', 'User Agent'),
            'channel' => Yii::t('app', 'Channel'),
            'site_id' => Yii::t('app', 'Service Provider ID'),
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'created_at',
            ],
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
     * @return \yii\db\ActiveQuery
     */
    public function getServiceProvider()
    {
        return $this->hasOne(ServiceProvider::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberTransactions()
    {
        return $this->hasMany(SubscriberTransaction::className(), ['subscriber_activity_id' => 'id']);
    }

    /**
     * @return array
     */
    public static function listStatus()
    {
        $lst = [
            self::STATUS_SUCCESS => 'Thành công',
            self::STATUS_FAIL => 'Thất bại',
        ];
        return $lst;
    }

    /**
     * @return int
     */
    public function getStatusName()
    {
        $lst = self::listStatus();
        if (array_key_exists($this->status, $lst)) {
            return $lst[$this->status];
        }
        return $this->status;
    }

    /**
     * @return array
     */
    public static function listActions()
    {
        $lst = [
//            self::ACTION_REGISTER => 'Register',
            self::ACTION_LOGIN => \Yii::t('app', 'Đăng nhập'),
            self::ACTION_LOGOUT => \Yii::t('app', 'Đăng xuất'),
        ];
        return $lst;
    }

    /**
     * @return int
     */
    public function getActionName()
    {
        $lst = self::listActions();
        if (array_key_exists($this->action, $lst)) {
            return $lst[$this->action];
        }
        return $this->action;
    }

    /**
     * @return int
     */
    public function getChannelName()
    {
        $lst = Subscriber::listChannelType();
        if (array_key_exists($this->channel, $lst)) {
            return $lst[$this->channel];
        }
        return $this->channel;
    }

    /**
     * @param $subscriber
     * @param $description
     * @param $channel_type
     * @param $site_id
     * @param int $action
     * @param int $status
     * @return array
     */
    public static function createSubscriberActivity($subscriber, $description, $channel_type, $site_id, $action = SubscriberActivity::ACTION_LOGIN, $status = SubscriberActivity::STATUS_SUCCESS, $device_id = null, $type_subscriber=null){
        $res = [];

        $r = new SubscriberActivity();
        $r->subscriber_id = $subscriber->id;
        $r->msisdn = $subscriber->msisdn;
        $r->description = $description;
        $r->action = $action;
        $r->type = $action;
        $r->channel = $channel_type;
        $r->site_id = $site_id;
        $r->status = $status;
        $r->device_id = $device_id;

        if($type_subscriber){
            $r->type_subscriber = $type_subscriber;
        }

        $r->ip_address = Yii::$app->request->getUserIP();
        $r->user_agent = Yii::$app->request->getUserAgent();

        if (!$r->validate() || !$r->save()) {
            $message = $r->getFirstMessageError();
            $res['status'] = false;
            $res['message'] = $message;
            return $res;
        }
        $res['status'] = true;
        $res['message'] = Message::getSuccessMessage();
        $res['item'] = $r;

        return $res;
    }

    private function getFirstMessageError(){
        $error = $this->firstErrors;
        $message = "";
        foreach ($error as $key => $value) {
            $message .= $value;
            break;
        }
        return $message;
    }

}
