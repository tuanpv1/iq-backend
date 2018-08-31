<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Url;
use yii\validators;
use yii\validators\FileValidator;

/**
 * This is the model class for table "actor_director".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $type
 * @property integer $content_type
 * @property string $image
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property ContentActorDirectorAsm[] $contentActorDirectorAsms
 */
class ActorDirector extends \yii\db\ActiveRecord
{

    const TYPE_ACTOR   = 1;
    const TYPE_DIRECTOR   = 2;

    /** content_type */
    const TYPE_VIDEO   = 1;
    const TYPE_LIVE    = 2;
    const TYPE_MUSIC   = 3;
    const TYPE_NEWS    = 4;
    const TYPE_CLIP    = 5;
    const TYPE_KARAOKE = 6;
    const TYPE_RADIO   = 7;

    const STATUS_ACTIVE         = 10; // Đã duyệt
    const STATUS_INACTIVE       = 0; // khóa
    const STATUS_DELETE       = 2; // xóa

    const LIST_EXTENSION = '.jpg,.png,.gif,.jpeg';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'actor_director';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'content_type', 'status', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 200,'tooLong' => Yii::t('app', '{attribute} giới hạn 200 ký tự')],
            [['name'], 'required', 'message' => Yii::t('app','{attribute} không được phép để trống') ],
//            [['image'], 'required',  'on' => ['create','update'], 'message' => '{attribute} không được phép để trống' ],
            [['description'], 'string', 'max' => 200],
            [['image'], 'string', 'max' => 500],
//            ['image', 'file', 'extensions' => 'jpg,png,gif,jpeg','maxSize' => 1024 * 1024 * 10,'tooBig' => 'File ảnh vượt quá dung lượng cho phép. Vui lòng thử lại.'],
            ['image', 'file',
                'extensions' => 'jpg,png,gif,jpeg',
                'maxSize' => 1024 * 1024 * 10,
                'tooBig' => Yii::t('app','File ảnh vượt quá dung lượng cho phép. Vui lòng thử lại.'),
                'wrongExtension' => Yii::t('app','File ảnh không đúng định dạng'),
            ],
            ['image', 'file', 'maxSize' => 1024 * 1024 * 10,'tooBig' =>Yii::t('app', 'File ảnh vượt quá dung lượng cho phép. Vui lòng thử lại.')],
            ['image','required','message'=> Yii::t('app','{attribute} không được phép để trống'),
//                'when'=>function($model){
//                    return empty($model->image);
//                },
                'on' => 'create'
            ],
//            ['image', 'file',
//                'skipOnEmpty' => false,
//                'maxSize' => 1024 * 1024 * 10,
//                'tooBig' => 'File ảnh vượt quá dung lượng cho phép. Vui lòng thử lại.',
////                'uploadRequired' => '{attribute} không được phép để trống',
//            ],
//            ['image','required','message'=> '{attribute} không được phép để trống',
//                'when'=>function($model){
//                    return empty($model->image);
//                },
//                'on' => ['create','update']
//
//            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Tên hiển thị'),
            'description' => Yii::t('app', 'Mô tả'),
            'type' => Yii::t('app', 'Loại'),
            'content_type' => Yii::t('app', 'Loại nội dung'),
            'image' => Yii::t('app', 'Ảnh đại diện'),
            'status' => Yii::t('app', 'Trạng thái'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'updated_at' => Yii::t('app', 'Ngày cập nhật'),
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
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentActorDirectorAsms()
    {
        return $this->hasMany(ContentActorDirectorAsm::className(), ['actor_director_id' => 'id']);
    }

    public static function listType($content_type)
    {
        $lst = [
            self::TYPE_DIRECTOR => $content_type==ActorDirector::TYPE_VIDEO?Yii::t('app','Đạo diễn'):Yii::t('app','Nhạc sĩ'),
            self::TYPE_ACTOR => $content_type==ActorDirector::TYPE_VIDEO?Yii::t('app','Diễn viên'):Yii::t('app','Ca sĩ'),
        ];
        return $lst;

    }

    /**
     * @return int
     */
    public function getTypeName()
    {
        $lst = self::listType($this->content_type);
        if (array_key_exists($this->type, $lst)) {
            return $lst[$this->type];
        }
        return $this->type;
    }

    public function getImage()
    {
        $link = '';
        if (!$this->image) {
            return;
        }
        $link = Url::to(Url::base() . DIRECTORY_SEPARATOR . Yii::getAlias('@content_images') . DIRECTORY_SEPARATOR . $this->image, true);
        return $link;
    }
}
