<?php

namespace common\models;

use api\helpers\Message;
use Yii;

/**
 * This is the model class for table "param_attribute".
 *
 * @property integer $id
 * @property string $display_name
 * @property integer $type
 * @property integer $status
 * @property string $param
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $type_app
 */
class ParamAttribute extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'param_attribute';
    }

    const TYPE_WATCH_AGAIN = 1;
    const TYPE_NEW_FILM = 3;
    const TYPE_FAVORITE = 2;

    const TYPE_APP_FILM = 1;
    const TYPE_APP_MUSIC = 2;

    const STATUS_ACTIVE   = 10;
    const STATUS_INACTIVE = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['display_name','status','param'],'required'],
            [['type', 'status', 'created_at', 'updated_at','type_app'], 'integer'],
            [['display_name', 'param'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'display_name' => Yii::t('app','Tên danh mục con'),
            'type' => Yii::t('app','Loại danh mục'),
            'type_app' => Yii::t('app','Loại app'),
            'status' => Yii::t('app','Trạng thái'),
            'param' => Yii::t('app','Đường dẫn'),
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    public static function getTypeName($type)
    {
        $types = self::getListType();
        if (isset($types[$type])) {
            return $types[$type];
        }
        return '';
    }

    public static function getTypeAppName($type)
    {
        $types = self::getListTypeApp();
        if (isset($types[$type])) {
            return $types[$type];
        }
        return '';
    }

    public static function getListStatus()
    {
        return [
            self::STATUS_ACTIVE   => Yii::t('app','Đang hoạt động'),
            self::STATUS_INACTIVE => Yii::t('app','Tạm khóa'),
        ];
    }

    public static function getListType()
    {
        return [
            self::TYPE_WATCH_AGAIN  => Yii::t('app','Xem gần đây'),
            self::TYPE_NEW_FILM => Yii::t('app','Phim mới'),
            self::TYPE_FAVORITE => Yii::t('app','Yêu thích'),
        ];
    }
    public static function getListTypeApp()
    {
        return [
            Content::TYPE_VIDEO => Yii::t('app','Film'),
            Content::TYPE_MUSIC => Yii::t('app','Âm nhạc'),
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
}
