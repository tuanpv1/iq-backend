<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%sum_service}}".
 *
 * @property integer $id
 * @property integer $service_id
 * @property integer $site_id
 * @property integer $status
 * @property integer $subscriber_count
 * @property integer $register_count_success
 * @property integer $register_count_false
 * @property integer $renew_count
 * @property integer $user_cancel_count
 * @property integer $provider_cancel_count
 * @property string $report_date
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Service $service
 * @property ServiceProvider $serviceProvider
 */
class SumService extends \yii\db\ActiveRecord
{
    const STATUS_SUCCESS = 10;
    const STATUS_FALSE = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sum_service}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_id', 'site_id', 'report_date'], 'required'],
            [['service_id', 'site_id', 'status', 'subscriber_count', 'register_count_success', 'register_count_false', 'renew_count', 'user_cancel_count', 'provider_cancel_count', 'created_at', 'updated_at'], 'integer'],
            [['report_date'], 'safe']
        ];
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app','ID'),
            'service_id' => Yii::t('app','Service ID'),
            'site_id' => Yii::t('app','Service Provider ID'),
            'status' =>Yii::t('app', 'Status'),
            'subscriber_count' => Yii::t('app','Số lượng thuê bao lũy kế'),
            'register_count_success' => Yii::t('app','ĐK Thành công'),
            'register_count_false' => Yii::t('app','ĐK Thất bại'),
            'renew_count' => Yii::t('app','Gia hạn'),
            'user_cancel_count' => Yii::t('app','Người dùng hủy'),
            'provider_cancel_count' => Yii::t('app','Provider hủy'),
            'report_date' => Yii::t('app','Report Date'),
            'created_at' => Yii::t('app','Created At'),
            'updated_at' => Yii::t('app','Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceProvider()
    {
        return $this->hasOne(ServiceProvider::className(), ['id' => 'site_id']);
    }

    /**
     * @return array
     */
    public static function listStatus()
    {
        $lst = [
            self::STATUS_SUCCESS => 'Success',
            self::STATUS_FALSE => 'False',
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
}
