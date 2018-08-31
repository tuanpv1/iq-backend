<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\swiftmailer\Message;

/**
 * This is the model class for table "content_attribute_value".
 *
 * @property integer $id
 * @property integer $content_id
 * @property integer $content_attribute_id
 * @property string $value
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Content $content
 * @property ContentAttribute $contentAttribute
 */
class ContentAttributeValue extends \yii\db\ActiveRecord
{
    public $actor;
    public $singer;

    const ORDER_AZ = 0;
    const ORDER_NEWEST = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'content_attribute_value';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content_id', 'content_attribute_id'], 'required'],
            [['content_id', 'content_attribute_id', 'created_at', 'updated_at'], 'integer'],
            [['value'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('app', 'ID'),
            'content_id' => \Yii::t('app', 'Content ID'),
            'content_attribute_id' => \Yii::t('app', 'Content Attribute ID'),
            'value' => \Yii::t('app', 'Value'),
            'created_at' => \Yii::t('app', 'Ngày tạo'),
            'updated_at' => \Yii::t('app', 'Ngày thay đổi thông tin'),
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
    public function getContentAttribute()
    {
        return $this->hasOne(ContentAttribute::className(), ['id' => 'content_attribute_id'])->orderBy(['order'=>SORT_DESC]);
    }

    public static function getListByFilter($param)
    {
        $name = ContentAttribute::findOne(['name' => $param]);
        if ($name) {
            $value = self::findOne(['content_attribute_id' => $name->id]);
            if (!$value) {
                return [
                    'status' => false,
                    'message' => \api\helpers\Message::getNotFoundContentMessage(),
                ];
            }
        } else {
            return [
                'status' => false,
                'message' => \api\helpers\Message::getNotFoundContentMessage(),
            ];
        }

        $query = self::find()
            ->innerJoin('content_attribute', 'content_attribute.id = content_attribute_value.content_attribute_id')
            ->andWhere(['content_attribute.name' => $param]);
        $defaultOrder['value'] = SORT_ASC;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => $defaultOrder,
            ],
            'pagination' => [
                'defaultPageSize' => 10
            ]
        ]);
        return [
            'status' => true,
            'dataProvider' => $dataProvider,
        ];
    }
}
