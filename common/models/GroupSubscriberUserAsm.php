<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "group_subscriber_user_asm".
 *
 * @property integer $id
 * @property integer $group_subscriber_id
 * @property integer $subscriber_id
 * @property string $username
 * @property integer $site_id
 * @property integer $status
 * @property string $mac_address
 * @property integer $device_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $type
 *
 * @property Site $site
 * @property GroupSubscriber $groupSubscriber
 * @property Subscriber $subscriber
 * @property Device $device
 */
class GroupSubscriberUserAsm extends \yii\db\ActiveRecord
{
    const TYPE_USERNAME = 2;
    const TYPE_MAC = 1;

    const STATUS_ACTIVE = 10;
    const STATUS_DEACTIVE = 0;
    const STATUS_DELETE = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'group_subscriber_user_asm';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_subscriber_id', 'site_id', 'status'], 'required'],
            [['group_subscriber_id', 'subscriber_id', 'site_id', 'status', 'device_id', 'created_at', 'updated_at', 'type'], 'integer'],
            [['username', 'mac_address'], 'string', 'max' => 255],
            [['site_id'], 'exist', 'skipOnError' => true, 'targetClass' => Site::className(), 'targetAttribute' => ['site_id' => 'id']],
            [['group_subscriber_id'], 'exist', 'skipOnError' => true, 'targetClass' => GroupSubscriber::className(), 'targetAttribute' => ['group_subscriber_id' => 'id']],
            [['subscriber_id'], 'exist', 'skipOnError' => true, 'targetClass' => Subscriber::className(), 'targetAttribute' => ['subscriber_id' => 'id']],
            [['device_id'], 'exist', 'skipOnError' => true, 'targetClass' => Device::className(), 'targetAttribute' => ['device_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'group_subscriber_id' => Yii::t('app', 'Group Subscriber ID'),
            'subscriber_id' => Yii::t('app', 'Subscriber ID'),
            'username' => Yii::t('app', 'Username'),
            'site_id' => Yii::t('app', 'Site ID'),
            'status' => Yii::t('app', 'Status'),
            'mac_address' => Yii::t('app', 'Mac Address'),
            'device_id' => Yii::t('app', 'Device ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'type' => Yii::t('app', 'Type'),
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
    public function getGroupSubscriber()
    {
        return $this->hasOne(GroupSubscriber::className(), ['id' => 'group_subscriber_id']);
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
    public function getDevice()
    {
        return $this->hasOne(Device::className(), ['id' => 'device_id']);
    }


}
