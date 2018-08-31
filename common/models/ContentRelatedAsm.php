<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "content_related_asm".
 *
 * @property integer $id
 * @property integer $content_id
 * @property integer $content_related_id
 * @property integer $updated_at
 * @property integer $created_at
 *
 * @property Content $content
 * @property Content $contentRelated
 */
class ContentRelatedAsm extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'content_related_asm';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content_id', 'content_related_id'], 'required'],
            [['content_id', 'content_related_id', 'updated_at', 'created_at'], 'integer']
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
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content_id' => 'Content ID',
            'content_related_id' => 'Content Related ID',
            'updated_at' => 'Ngày tạo',
            'created_at' => 'Ngày thay đổi thông tin',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->hasOne(Content::className(), ['id' => 'content_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentRelated()
    {
        return $this->hasOne(Content::className(), ['id' => 'content_related_id']);
    }
}
