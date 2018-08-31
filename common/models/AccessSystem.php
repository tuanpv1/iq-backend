<?php

namespace common\models;

use api\helpers\Message;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%access_system}}".
 *
 * @property integer $id
 * @property integer $subscriber_id
 * @property string $ip_address
 * @property string $user_agent
 * @property integer $site_id
 * @property integer $access_date
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $action
 * @property string $request_detail
 * @property string $request_params
 *
 * @property Site $site
 * @property Subscriber $subscriber
 */
class AccessSystem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%access_system}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subscriber_id', 'site_id', 'access_date', 'created_at', 'updated_at'], 'integer'],
            [['access_date'], 'required'],
            [['request_params'], 'string'],
            [['ip_address'], 'string', 'max' => 45],
            [['user_agent', 'request_detail'], 'string', 'max' => 255],
            [['action'], 'string', 'max' => 126]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app','ID'),
            'subscriber_id' => Yii::t('app','Subscriber ID'),
            'ip_address' =>Yii::t('app', 'Địa chỉ IP'),
            'user_agent' => Yii::t('app','User Agent'),
            'site_id' => Yii::t('app','Site ID'),
            'access_date' =>Yii::t('app', 'Ngày truy cập'),
            'created_at' => Yii::t('app','Ngày tạo'),
            'updated_at' => Yii::t('app','Ngày thay đổi thông tin'),
            'action' =>Yii::t('app', 'Hành động'),
            'request_detail' => Yii::t('app','Request Detail'),
            'request_params' => Yii::t('app','Request Params'),
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
                'updatedAtAttribute' => 'updated_at',
            ],
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
    public function getSubscriber()
    {
        return $this->hasOne(Subscriber::className(), ['id' => 'subscriber_id']);
    }


    /**
     * @param $subscriber_id
     * @param $site_id
     * @return array
     */
    public static function createAccessSystem($subscriber_id,$site_id){
        $res = [];
        /** @var  $as AccessSystem*/
        $as = new AccessSystem();
        $as->subscriber_id = $subscriber_id;
        $as->ip_address = Yii::$app->request->getUserIP();
        $as->user_agent = Yii::$app->request->getUserAgent();
        $as->site_id = $site_id;
        $as->access_date = time();
        /** Validate và save, nếu có lỗi thì return message_error */
        if (!$as->validate() || !$as->save()) {
            $message = $as->getFirstMessageError();
            $res['status'] = false;
            $res['message'] = $message;
            return $res;
        }
        $res['status'] = true;
        $res['message'] = Message::getSuccessMessage();
        $res['item'] = $as;

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
