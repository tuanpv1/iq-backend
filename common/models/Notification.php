<?php

namespace common\models;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * This is the model class for table "notification".
 *
 * @property int $id
 * @property string $name
 * @property string $function
 * @property string $type
 * @property string $content
 * @property int $updated_at
 * @property string $updated_by
 */

class Notification extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%notification}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[ 'content',], 'required'],
            [['updated_at'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['function'], 'string', 'max' => 50],
            [['updated_by'], 'string', 'max' => 50],
            [['content'], 'string',],
            [['type'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => \Yii::t('app', 'ID'),
            'name'            => \Yii::t('app', 'Mã thông báo'),
            'function'        => \Yii::t('app', 'Chức năng'),
            'type'            => \Yii::t('app', 'Loại thông báo'),
            'content'         => \Yii::t('app', 'Nội dung thông báo'),
            'updated_by'      => \Yii::t('app', 'Người cập nhật'),
            'updated_at'      => \Yii::t('app', 'Ngày cập nhật'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }

}