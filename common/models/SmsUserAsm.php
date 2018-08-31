<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "sms_user_asm".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $is_read
 * @property integer $updated_at
 * @property integer $created_at
 * @property integer $date_send
 * @property integer $date_received
 * @property integer $status
 * @property integer $sms_support_id
 *
 * @property SmsSupport $smsSupport
 */
class SmsUserAsm extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 0;

    const IS_READ = 1;
    const NOT_READ = 0;

    public static function tableName()
    {
        return 'sms_user_asm';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'is_read', 'updated_at', 'created_at', 'date_send', 'date_received', 'status', 'sms_support_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'is_read' => 'Is Read',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'date_send' => 'Date Send',
            'date_received' => 'Date Received',
            'status' => 'Status',
            'sms_support_id' => 'Sms Support ID',
        ];
    }

    public function getSmsSupport()
    {
        return $this->hasOne(SmsSupport::className(), ['id' => 'sms_support_id']);
    }
}
