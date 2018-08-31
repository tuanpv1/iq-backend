<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "service_cp_asm".
 *
 * @property integer $id
 * @property integer $cp_id
 * @property integer $service_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class ServiceCpAsm extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service_cp_asm';
    }

    const STATUS_ACTIVE = 10; //hoat dong
    const STATUS_INACTIVE = 0; //khoa

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cp_id', 'service_id', 'status', 'created_at', 'updated_at'], 'integer'],
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
            'service_id' => 'Service ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
