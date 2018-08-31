<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * This is the model class for table "ads".
 *
 * @property integer $id
 * @property integer $app_ads_id
 * @property integer $site_id
 * @property string $name
 * @property integer $type
 * @property string $image
 * @property string $target_url
 * @property string $extra
 * @property integer $status
 * @property integer $expired_date
 *
 * @property AppAds $appAds
 * @property Site $site
 */
class Ads extends \yii\db\ActiveRecord
{
    public $app_ads;
    public $app_name;

    const TYPE_BANNER = 1;
    const TYPE_ORTHER = 2;
    const TYPE_HTML   = 3;
    const TYPE_VIDEO  = 4;
    const TYPE_URL    = 5;

    const STATUS_ACTIVE   = 10;
    const STATUS_INACTIVE = 0;
    const STATUS_DELETED = 1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ads';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['app_ads_id', 'site_id', 'name', 'expired_date'], 'required', 'message' => Yii::t('app','{attribute} không thể để trống')],
            [['app_ads_id', 'site_id', 'type', 'status'], 'integer'],
            [['extra'], 'string'],
            [['name'], 'string', 'max' => 45],
            [['target_url'], 'string', 'max' => 255],
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg', 'maxSize' => 10 * 1024 * 1024],
            ['target_url', 'url'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'           => Yii::t('app','ID'),
            'app_ads_id'   => Yii::t('app','Ứng dụng'),
            'app_name'     => Yii::t('app','Tên ứng dụng'),
            'site_id'      => Yii::t('app','Site ID'),
            'name'         => Yii::t('app','Tên quảng cáo'),
            'type'         => Yii::t('app','Loại quảng cáo'),
            'image'        => Yii::t('app','Ảnh quảng cáo'),
            'target_url'   => Yii::t('app','Đường dẫn quảng cáo'),
            'extra'        => Yii::t('app','Thông tin bổ sung'),
            'status'       => Yii::t('app','Trạng thái'),
            'expired_date' => Yii::t('app','Ngày hết hạn'),
        ];
    }

    public function upload($file_name)
    {
        if ($this->validate()) {
            $this->image->saveAs(Yii::getAlias('@webroot') . '/' . Yii::getAlias('@content_images') . '/' . $file_name);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAppAds()
    {
        return $this->hasOne(AppAds::className(), ['id' => 'app_ads_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }

    /**
     * @param null $type
     * @return array
     */
    public function getType($type = null)
    {
        $list = [
            self::TYPE_BANNER => Yii::t('app','Banner'),
            self::TYPE_ORTHER => Yii::t('app','Orther App'),
            self::TYPE_HTML   => Yii::t('app','Html'),
            self::TYPE_VIDEO  => Yii::t('app','Video'),
            self::TYPE_URL    => Yii::t('app','Url'),
        ];

        if ($type) {
            return $list[$type];
        }

        return $list;
    }

    public function getListStatus($stt = null)
    {
        $list = [
            self::STATUS_ACTIVE   => Yii::t('app','Hoạt động'),
            self::STATUS_INACTIVE => Yii::t('app','Tạm dừng'),
        ];

        if ($stt !== null) {
            return $list[$stt];
        }

        return $list;
    }

    public function getImageLink()
    {
        return $this->image ? Url::to('@web/' . Yii::getAlias('@content_images') . '/' . $this->image, true) : '';
    }
}
