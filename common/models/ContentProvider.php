<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "content_provider".
 *
 * @property integer $id
 * @property string $cp_name
 * @property string $cp_address
 * @property string $cp_mst
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class ContentProvider extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'content_provider';
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 10;
    public $username;
    public $phone;
    public $email;
    public $fullname;

    const IS_ADMIN_CP = 1;
    const IS_NOT_ADMIN_CP = 0;

    public static function getListStatus()
    {
        return
            $sp_status = [
                self::STATUS_ACTIVE => Yii::t('app', 'Hoạt động'),
                self::STATUS_INACTIVE => Yii::t('app', 'Tạm dừng'),
            ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cp_name'], 'required'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['cp_name', 'cp_address'], 'string', 'max' => 500],
            [['cp_mst'], 'string', 'max' => 100],
            [['username', 'phone', 'email', 'fullname'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cp_name' => Yii::t('app', 'Tên'),
            'cp_address' => Yii::t('app', 'Địa chỉ'),
            'cp_mst' => Yii::t('app', 'Mã số thuế'),
            'status' => Yii::t('app', 'Trạng thái'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'updated_at' => Yii::t('app', 'Ngày cập nhật'),
        ];
    }

    public static function getListStatusNameByStatus($status)
    {
        $lst = self::getListStatus();
        if (array_key_exists($status, $lst)) {
            return $lst[$status];
        }
        return $status;
    }

    public static function listContentProvider()
    {
        $listCP = ContentProvider::find()->all();
        $lst  = [];
        foreach ($listCP as $cp) {
            $lst[$cp->id] = $cp->cp_name;
        }
        return $lst;
    }
}
