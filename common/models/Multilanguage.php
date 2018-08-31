<?php

namespace common\models;

use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "multilanguage".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property integer $updated_at
 * @property integer $created_at
 * @property string $description
 * @property string $image
 * @property string $file_box
 * @property string $file_be
 * @property integer $status
 * @property integer $is_default
 */
class Multilanguage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'multilanguage';
    }

    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code'], 'required', 'message' => '{attribute} ' . Yii::t('app', 'không được để trống'), 'on' => 'admin_create_update'],
            [['updated_at', 'created_at', 'status', 'is_default'], 'integer'],
            [['name'], 'string', 'max' => 250],
            [['code'], 'string', 'max' => 50],
            [['description'], 'string', 'max' => 500],
            [['image'], 'string', 'max' => 255],
            [['image'], 'safe'],
            [['image'],
                'file',
                'tooBig' => Yii::t('app', 'File ảnh chưa đúng quy cách. Vui lòng thử lại'),
                'wrongExtension' => Yii::t('app', 'File ảnh chưa đúng quy cách. Vui lòng thử lại'),
                'uploadRequired' => Yii::t('app', '{attribute} không được để trống'),
                'skipOnEmpty' => true,
                'extensions' => 'png, jpg, jpeg', 'maxSize' => 10 * 1024 * 1024],
            [['file_be', 'file_box'], 'string', 'max' => 255],
            [['file_be', 'file_box'], 'safe'],
            [['file_be', 'file_box'],
                'file',
                'tooBig' => Yii::t('app', 'File upload chưa đúng quy cách. Vui lòng thử lại'),
                'wrongExtension' => Yii::t('app', 'File upload chưa đúng quy cách. Vui lòng thử lại'),
                'skipOnEmpty' => true,
                'extensions' => 'zip', 'maxSize' => 10 * 1024 * 1024],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Tên ngôn ngữ'),
            'code' => Yii::t('app', 'Mã'),
            'updated_at' => Yii::t('app', 'Ngày cập nhật'),
            'created_at' => Yii::t('app', 'Ngày tạo mới'),
            'description' => Yii::t('app', 'Mô tả'),
            'status' => Yii::t('app', 'Trạng thái'),
            'is_default' => Yii::t('app', 'Mặc định'),
            'file_be' => Yii::t('app', 'File ngôn ngữ BE'),
            'file_box' => Yii::t('app', 'File ngôn ngữ box'),
            'image' => Yii::t('app', 'Ảnh đại diện')
        ];
    }

    public static function getListStatus()
    {
        return [
            self::STATUS_ACTIVE => \Yii::t('app', 'Kích hoạt'),
            self::STATUS_INACTIVE => \Yii::t('app', 'Tạm dừng'),
        ];
    }

    public static function getLanguage()
    {
        $is_default = Multilanguage::find()
            ->andWhere(['status' => \common\models\Multilanguage::STATUS_ACTIVE])
            ->andWhere(['is_default' => 1])
            ->orderBy(['created_at' => SORT_DESC])->one();
        if ($is_default) {
            return $is_default->code;
        } else {
            return 'vi';
        }
    }

    public function getImageLink()
    {
        return $this->image ? Url::to(Yii::getAlias('@web') . DIRECTORY_SEPARATOR . Yii::getAlias('@cat_image') . DIRECTORY_SEPARATOR . $this->image, true) : '';
    }
}
