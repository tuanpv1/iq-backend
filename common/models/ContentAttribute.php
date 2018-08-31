<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "content_attribute".
 *
 * @property int $id
 * @property string $name
 * @property int $content_type
 * @property int $data_type
 * @property int $order
 * @property int $created_at
 * @property int $updated_at
 * @property ContentAttributeValue[] $contentAttributeValues
 */
class ContentAttribute extends \yii\db\ActiveRecord
{
    const TYPE_STRING = 1;
    const TYPE_INT    = 2;
    const TYPE_DOUBLE = 3;
    const TYPE_ARRAY  = 4;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'content_attribute';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required', 'message' =>\Yii::t('app', '{attribute} không thể để trống')],
            [['content_type', 'data_type','order'], 'integer'],
            [['name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'           => \Yii::t('app', 'ID'),
            'name'         => \Yii::t('app', 'Tên thuộc tính'),
            'content_type' => \Yii::t('app', 'Loại nội dung'),
            'data_type'    => \Yii::t('app', 'Dạng dữ liệu'),
            'created_at'   => \Yii::t('app', 'Ngày tạo'),
            'updated_at'   => \Yii::t('app', 'Ngày cập nhật'),
            'order'   => \Yii::t('app', 'Sắp xếp'),
        ];
    }

    public function getDatatype($type = null)
    {
        $listType = [
            self::TYPE_STRING => 'String',
            self::TYPE_INT    => 'Integer',
            self::TYPE_DOUBLE => 'Double',
        ];

        if ($type) {
            return $listType[$type];
        }

        return $listType;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentAttributeValues()
    {
        return $this->hasMany(ContentAttributeValue::className(), ['content_attribute_id' => 'id']);
    }
}
