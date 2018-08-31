<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%price_level}}".
 *
 * @property integer $id
 * @property integer $price
 * @property string $description
 * @property integer $type
 * @property integer $created_at
 * @property integer $updated_at
 */
class PriceLevel extends \yii\db\ActiveRecord
{
    const TYPE_SERVICE = 1;
    const TYPE_CONTENT = 2;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%price_level}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['price'], 'required'],
            [['price', 'type'], 'unique', 'targetAttribute' => ['price', 'type']],
            [['price', 'type', 'created_at', 'updated_at'], 'integer'],
            [['description'], 'string', 'max' => 4000]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'price' => Yii::t('app', 'Giá'),
            'description' => Yii::t('app', 'Mô tả'),
            'type' => Yii::t('app', 'Type'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'updated_at' => Yii::t('app', 'Ngày thay đổi thông tin'),
        ];
    }
}
