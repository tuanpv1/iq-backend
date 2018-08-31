<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%sms_mt_template}}".
 *
 * @property integer $id
 * @property string $code_name
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $params
 * @property integer $status
 * @property integer $type
 * @property string $content
 *
 * @property SmsMtTemplateContent[] $smsMtTemplateContents
 */
class SmsMtTemplate extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 0;

    const TYPE_SUBSCRIPTION_BASE = 1;
    const TYPE_GENERAL = 2;

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
        return '{{%sms_mt_template}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code_name'], 'required'],
            [['created_at', 'updated_at','status','type'], 'integer'],
            [['code_name', 'params'], 'string', 'max' => 255],
            [['description','content'], 'string', 'max' => 4000],
            [['code_name'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'code_name' => Yii::t('app', 'Code Name'),
            'description' => Yii::t('app', 'Mô tả'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'updated_at' => Yii::t('app', 'Ngày thay đổi thôn tin'),
            'params' => Yii::t('app', 'Params'),
            'status'=>Yii::t('app', 'Trạng thái'),
            'content' => Yii::t('app', 'Nội dung'),
            'type' => Yii::t('app', 'Type'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSmsMtTemplateContents()
    {
        return $this->hasMany(SmsMtTemplateContent::className(), ['sms_mt_template_id' => 'id']);
    }

    public static function getListStatus()
    {
        return [
            self::STATUS_ACTIVE => Yii::t('app','Hiệu lực'),
            self::STATUS_INACTIVE => Yii::t('app','Hết hiệu lưc')
        ];
    }

    public function getStatusName()
    {
        $listStatus = self::getListStatus();
        if (isset($listStatus[$this->status])) {
            return $listStatus[$this->status];
        }
        return '';
    }

    public static function getListType()
    {
        return [
            self::TYPE_SUBSCRIPTION_BASE => 'Subscription base',
            self::TYPE_GENERAL => 'General'
        ];
    }

    public function getTypeName()
    {
        $listType = self::getListType();
        if (isset($listType[$this->type])) {
            return $listType[$this->type];
        }
        return '';
    }

}
