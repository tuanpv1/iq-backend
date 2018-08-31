<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "service_group_asm".
 *
 * @property integer $id
 * @property integer $service_id
 * @property integer $service_group_id
 * @property string $description
 * @property integer $created_at
 *
 * @property Service $service
 * @property ServiceGroup $serviceGroup
 */
class ServiceGroupAsm extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service_group_asm';
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ],

        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_id', 'service_group_id'], 'required'],
            [['service_id', 'service_group_id', 'created_at'], 'integer'],
            [['description'], 'string', 'max' => 1024]
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
            'service_group_id' => Yii::t('app','Service Group ID'),
            'description' => Yii::t('app','Description'),
            'created_at' => Yii::t('app','Created At'),
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
    public function getServiceGroup()
    {
        return $this->hasOne(ServiceGroup::className(), ['id' => 'service_group_id']);
    }
}
