<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "delay".
 *
 * @property integer $id
 * @property integer $site_id
 * @property double $delay
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class Delay extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 1;
    const STATUS_DELETED = 0;

    public static function listStatus()
    {
        $lst = [
            self::STATUS_ACTIVE => \Yii::t('app', 'Kích hoạt'),
            self::STATUS_INACTIVE => \Yii::t('app', 'Tạm dừng'),
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
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'delay';
    }

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
    public function rules()
    {
        return [
            ['site_id','required','message'=>Yii::t('app','{attribute} không được để trống')],
            ['delay','required','message'=>Yii::t('app','{attribute} không được để trống')],
            [['site_id', 'status', 'created_at', 'updated_at'], 'integer'],

            [['delay'], 'number','message'=>Yii::t('app','{attribute} phải là kiểu số')],
            [['delay'], 'number', 'max' => 24, 'message'=>Yii::t('app','{attribute} không quá 24h')],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'site_id' => Yii::t('app','Nhà cung cấp dịch vụ'),

            'delay' => Yii::t('app','Thời gian trễ (giờ)'),

//            'delay' => Yii::t('app','Thời gian trễ'),

            'status' => Yii::t('app','Trạng thái'),
            'created_at' => Yii::t('app','Ngày tạo'),
            'updated_at' => Yii::t('app','Ngày thay đổi thông tin'),
        ];
    }
}
