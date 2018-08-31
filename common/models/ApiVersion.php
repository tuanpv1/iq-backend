<?php

namespace common\models;

use api\helpers\Message;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "api_version".
 *
 * @property integer $id
 * @property string $name
 * @property integer $version
 * @property integer $type
 * @property string $description
 * @property integer $site_id
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Site $site
 */
class ApiVersion extends \yii\db\ActiveRecord
{
    /** content_type */
    const TYPE_VIDEO   = 1;
    const TYPE_LIVE    = 2;
    const TYPE_MUSIC   = 3;
    const TYPE_NEWS    = 4;
    const TYPE_CLIP    = 5;
    const TYPE_KARAOKE = 6;
    const TYPE_RADIO   = 7;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'api_version';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['version', 'type','site_id'], 'required'],
            [['version', 'type', 'site_id', 'created_at', 'updated_at'], 'integer'],
            [['name', 'description'], 'string', 'max' => 255],
            [['type', 'site_id'], 'unique', 'targetAttribute' => ['type', 'site_id'], 'message' => Yii::t('app','Đã tồn tại Type và Site ID')]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Nội dung'),
            'version' => Yii::t('app', 'Phiên bản'),
            'type' => Yii::t('app', 'Loại nội dung'),
            'description' => Yii::t('app', 'Description'),
            'site_id' => Yii::t('app', 'Nhà cung cấp dịch vụ'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'updated_at' => Yii::t('app', 'Ngày cập nhật'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }

    /**
     * {@inheritdoc}
     */
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

    public static function listType()
    {
        return [
            self::TYPE_VIDEO   => 'Video',
            self::TYPE_CLIP    => 'Clip',
            self::TYPE_KARAOKE => 'Karaoke',
            self::TYPE_LIVE    => 'Live',
            self::TYPE_MUSIC   => 'Music',
            self::TYPE_NEWS    => 'News',
            self::TYPE_RADIO   => 'Radio',

        ];
    }

    public function getTypeName()
    {
        $lst = self::listType();
        if (array_key_exists($this->type, $lst)) {
            return $lst[$this->type];
        }
        return $this->type;
    }

    public static function createApiVersion($name,$description, $site_id, $type = ApiVersion::TYPE_KARAOKE){
        $model = ApiVersion::findOne(['type' => ApiVersion::TYPE_KARAOKE, 'site_id' => $site_id]);
        if (!$model) {
            $model          = new ApiVersion();
            $model->name    = $name;
            $model->version = 1;
            $model->type    = $type;
            $model->site_id = $site_id;
            $model->description = $description;
        } else {
            $model->version++;
        }

        if ($model->save()) {
            $res['success'] = true;
            $res['message'] = Message::getSuccessMessage();
            return $res;
        } else {
            $res['success'] = false;
            $res['message'] = Message::getSuccessMessage();
            return $res;
        }
    }
}
