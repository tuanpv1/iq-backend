<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "cp_sysnc".
 *
 * @property integer $id
 * @property integer $cp_id
 * @property integer $transaction_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $error_code
 * @property string $message_code
 * @property integer $subscriber_id
 * @property integer $service_id
 * @property integer $cost
 * @property string $param
 * @property integer $type
 */
class CpSysnc extends \yii\db\ActiveRecord
{
    const STATUS_SUCCESS = 0;
    const STATUS_ERROR = 1;

    const ERROR_CODE_NOT_SAVE_TRANSACTION = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cp_sysnc';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cp_id', 'transaction_id', 'status', 'created_at', 'updated_at', 'error_code', 'subscriber_id', 'service_id', 'cost', 'type'], 'integer'],
            [['param'], 'string'],
            [['message_code'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cp_id' => 'Cp ID',
            'transaction_id' => 'Transaction ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'error_code' => 'Error Code',
            'message_code' => 'Message Code',
            'subscriber_id' => 'Subscriber ID',
            'service_id' => 'Service ID',
            'cost' => 'Cost',
            'param' => 'Param',
            'type' => 'Type', // đăng ký, gia hạn, hủy
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }

    public static function saveCpSysnc($cp_id, $service_id, $transaction_id, $status, $error_code, $message_code, $cost, $param, $type, $sub_id)
    {
        $cpSysnc = new  CpSysnc();
        $cpSysnc->cp_id = $cp_id;
        $cpSysnc->service_id = $service_id;
        $cpSysnc->subscriber_id = $sub_id;
        $cpSysnc->transaction_id = $transaction_id;
        $cpSysnc->status = $status;
        $cpSysnc->error_code = $error_code;
        $cpSysnc->message_code = $message_code;
        $cpSysnc->cost = $cost;
        $cpSysnc->param = $param;
        $cpSysnc->type = $type;
        if (!$cpSysnc->save()) {
            \Yii::info($cpSysnc->getErrors());
            return false;
        }
        return true;
    }
}
