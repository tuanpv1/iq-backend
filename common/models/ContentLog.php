<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "content_log".
 *
 * @property integer $id
 * @property integer $content_id
 * @property integer $created_at
 * @property string $ip_address
 * @property integer $status
 * @property integer $type
 * @property string $description
 * @property string $user_agent
 * @property integer $site_id
 * @property integer $user_id
 * @property integer $updated_at
 * @property String $content_name
 *
 * @property Content $content
 * @property User $user
 * @property Site $site
 */
class ContentLog extends \yii\db\ActiveRecord
{
    const TYPE_CREATE = 1; // tao content
    const TYPE_UPLOAD = 2;
    const TYPE_CONVERT = 3;
    const TYPE_EDIT = 4;
    const STATUS_SUCCESS = 10;
    const STATUS_FAIL = 0;

    public static function getListStatus(){
        $listStatus = [
            self::STATUS_SUCCESS => \Yii::t('app', 'Thành công'),
            self::STATUS_FAIL => \Yii::t('app', 'Thất bại'),
        ];
        return $listStatus;
    }

    public function getStatusName()
    {
        $lst = self::getListStatus();
        if (array_key_exists($this->status, $lst)) {
            return $lst[$this->status];
        }
        return $this->status;
    }

    public static $listType = [
        self::TYPE_UPLOAD => 'Upload',
        self::TYPE_EDIT => 'Edit',
        self::TYPE_CREATE => 'Create',
        self::TYPE_CONVERT => 'Convert'
    ];

    public function getTypeName()
    {
        if (isset(ContentLog::$listType[$this->type])) {
            return ContentLog::$listType[$this->type];
        }
        return $this->type;
    }

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
        return 'content_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content_id', 'site_id'], 'required'],
            [['content_id', 'created_at', 'status', 'type', 'site_id', 'user_id', 'updated_at'], 'integer'],
            [['description'], 'string'],
            [['ip_address'], 'string', 'max' => 45],
            [['content_name'], 'string', 'max' => 200],
            [['user_agent'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'content_id' => Yii::t('app', 'ID Nội dung'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'ip_address' => Yii::t('app', 'Địa chỉ IP'),
            'status' => Yii::t('app', 'Trạng thái'),
            'type' => Yii::t('app', 'Type'),
            'description' => Yii::t('app', 'Mô tả'),
            'user_agent' => Yii::t('app', 'User Agent'),
            'site_id' => Yii::t('app', 'Site'),
            'user_id' => Yii::t('app', 'ID người dùng'),
            'content_name' => Yii::t('app', 'Nội dung'),
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
    public function getSite()
    {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

}
