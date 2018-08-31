<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%sms_message}}".
 *
 * @property integer $id
 * @property integer $subscriber_id
 * @property integer $sms_template_id
 * @property string $msisdn
 * @property integer $type
 * @property integer $status
 * @property integer $type_mt
 * @property string $source
 * @property string $destination
 * @property string $message
 * @property integer $received_at
 * @property integer $sent_at
 * @property integer $mo_id
 * @property string $mt_status
 * @property string $mo_status
 * @property integer $site_id
 *
 * @property SmsMessage $mo
 * @property SmsMessage[] $smsMessages
 * @property Subscriber $subscriber
 * @property SmsMtTemplateContent $smsTemplate
 * @property ServiceProvider $serviceProvider
 */
class SmsMessage extends \yii\db\ActiveRecord
{
    const TYPE_MO = 1;
    const TYPE_MT = 2;

    // add them TuanPV 13/01/2017
    const TYPE_MT_OTP = 1;

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    const STATUS_SUCCESS = 10;
    const STATUS_FAIL = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sms_message}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subscriber_id', 'sms_template_id','type_mt', 'type', 'status', 'received_at',
                'sent_at', 'mo_id', 'site_id'], 'integer'],
            [['site_id'], 'required'],
            [['msisdn', 'source', 'destination'], 'string', 'max' => 20],
            [['message'], 'string', 'max' => 1000],
            [['mt_status'], 'string', 'max' => 500],
            [['mo_status'], 'string', 'max' => 200]
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
            'sms_template_id' => Yii::t('app', 'Sms Template ID'),
            'msisdn' => Yii::t('app', 'Msisdn'),
            'type' => Yii::t('app', 'Type'),
            'status' => Yii::t('app', 'Status'),
            'source' => Yii::t('app', 'Source'),
            'destination' => Yii::t('app', 'Destination'),
            'message' => Yii::t('app', 'Message'),
            'received_at' => Yii::t('app', 'Received At'),
            'sent_at' => Yii::t('app', 'Sent At'),
            'mo_id' => Yii::t('app', 'Mo ID'),
            'mt_status' => Yii::t('app', 'Mt Status'),
            'mo_status' => Yii::t('app', 'Mo Status'),
            'site_id' => Yii::t('app', 'Service Provider ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMo()
    {
        return $this->hasOne(SmsMessage::className(), ['id' => 'mo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSmsMessages()
    {
        return $this->hasMany(SmsMessage::className(), ['mo_id' => 'id']);
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
    public function getSmsTemplate()
    {
        return $this->hasOne(SmsMtTemplateContent::className(), ['id' => 'sms_template_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceProvider()
    {
        return $this->hasOne(ServiceProvider::className(), ['id' => 'site_id']);
    }

    /**
     * ******************************** MY FUNCTION ***********************
     */

    /**
     * @return array
     */
    public static function listStatus()
    {
        $lst = [
            self::STATUS_SUCCESS => Yii::t('app', 'Thành công'),
            self::STATUS_FAIL => Yii::t('app', 'Thất bại'),
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
    public static function listType()
    {
        $lst = [
            self::TYPE_MO => 'MO',
            self::TYPE_MT => 'MT',
        ];
        return $lst;
    }

    /**
     * @return int
     */
    public function getTypeName()
    {
        $lst = self::listType();
        if (array_key_exists($this->type, $lst)) {
            return $lst[$this->type];
        }
        return $this->type;
    }

    /**
     * @param $subscriber Subscriber
     * @param $site_id
     * @param $message
     * @param $time
     * @param int $type
     * @return SmsMessage|null
     */
    public static function newMessage($subscriber, $site_id, $message, $time, $type = self::TYPE_MT)
    {
        $sms = new SmsMessage();
        $sms->subscriber_id = $subscriber->id;
        $sms->msisdn = $subscriber->msisdn;
        $sms->type = $type;
        $sms->type_mt = self::TYPE_MT_OTP;
        $sms->site_id = $site_id;
        $sms->source = 'VIVAS';
        $sms->destination = $subscriber->msisdn;
        $sms->message = $message;
        $sms->received_at = $time;
        $sms->sent_at = $time;
        if ($sms->save()) {
            return $sms;
        }else{
            Yii::error($sms->getErrors());
        }
        // thêm migrate 1 trường type MT_OTP khi báo cáo lọc theo MT_OTP này TP 13012017
        return null;
    }

    // format số điện thoại

    public static function getPhone($phone){
        $cat = substr($phone,0,2);
        $cat2 = substr($phone,2, strlen($phone)-2);
        if($cat == '84'){
            return '0'.$cat2;
        }else{
            return $phone;
        }
    }
}
