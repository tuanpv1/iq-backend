<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "program".
 *
 * @property int $id
 * @property string $name
 * @property int $order
 * @property int $level
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class Program extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'program';
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 10;
    const STATUS_REMOVE = -1;

    public static function getListStatus()
    {
        return
            $sp_status = [
                self::STATUS_ACTIVE   => Yii::t('app', 'Hoạt động'),
                self::STATUS_INACTIVE => Yii::t('app', 'Tạm ngừng'),
            ];
    }

    public static function getListStatusNameByStatus($status)
    {
        $lst = self::getListStatus();
        if (array_key_exists($status, $lst)) {
            return $lst[$status];
        }
        return $status;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order', 'level', 'status', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'order' => 'Order',
            'level' => 'Level',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
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
}
