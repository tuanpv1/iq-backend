<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "price_card".
 *
 * @property int $id
 * @property int $site_id
 * @property string $price
 * @property string $description
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property int $updated_status_at
 * @property Site $site
 */
class PriceCard extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public static function getListStatus()
    {
        $pricing_status = [
            self::STATUS_ACTIVE => \Yii::t('app', 'Đã duyệt'),
            self::STATUS_INACTIVE => \Yii::t('app', 'Tạm dừng'),
        ];
        return $pricing_status;
    }

    public static function getListStatusNameByStatus($status)
    {
        $lst = self::getListStatus();
        if (array_key_exists($status, $lst)) {
            return $lst[$status];
        }
        return $status;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'price_card';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['site_id'], 'required'],
            [['price'], 'required','message'=> \Yii::t("app",'Mức tiền nạp là thông tin bắt buộc, không được để trống')],
            [['site_id', 'status',  'created_at', 'updated_at','updated_status_at'], 'integer'],
            [['price'],'number','message'=>\Yii::t("app",'Mức tiền nạp sai định dạng, vui lòng nhập lại!')],
            [['description'], 'string', 'max' => 1000,'tooLong'=>\Yii::t("app",'Mô tả sai định dạng, vui lòng nhập lại!')],
            [['price',], 'validateUnique', 'on' => 'create']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('app', 'ID'),
            'site_id' => \Yii::t('app', 'Nhà cung cấp dịch vụ'),
            'price' => \Yii::t('app', 'Mức tiền'),
            'description' => \Yii::t('app', 'Mô tả'),
            'status' => \Yii::t('app', 'Trạng thái'),
            'created_at' => \Yii::t('app', 'Ngày tạo'),
            'updated_at' => \Yii::t('app', 'Ngày cập nhật'),
            'updated_status_at' => \Yii::t('app', 'Ngày cập nhật trạng thái'),
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
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }


    public static function getListSp()
    {
        $arrSP = [];
        $listSp = Site::find()->all();
        foreach ($listSp as $item) {
            /** @var $item Site */
            $arrSP[$item->id] = $item->name;
        }
        return $arrSP;
    }

    public function getNameSP($site_id)
    {
        $lst = self::getListSp();
        if (array_key_exists($site_id, $lst)) {
            return $lst[$site_id];
        }
        return $site_id;
    }

    public function getStatusClassCss()
    {
        switch ($this->status) {
            case self::STATUS_INACTIVE:
                return 'info';
            case self::STATUS_ACTIVE:
                return "success";
        }
    }

    public static function getCurrency($site_id){
        $model = Site::findOne($site_id);
        if( $model){
            return $model->currency;
        }else{
            return "";
        }
    }

    public function validateUnique($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $price = PriceCard::findOne(['price' => $this->price,'site_id'=>$this->site_id]);
            if ($price) {
                $this->addError($attribute, Yii::t('app', 'Mức giá đã tồn tại. Vui lòng nhập mức giá khác!'));
            }
        }
    }
}
