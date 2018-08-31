<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "pricing".
 *
 * @property int $id
 * @property int $site_id
 * @property float $price_coin
 * @property float $price_sms
 * @property string $description
 * @property int $type
 * @property int $watching_period
 * @property int $created_at
 * @property int $updated_at
 * @property Content[] $contents
 * @property Site $site
 * @property Service[] $services
 */
class Pricing extends \yii\db\ActiveRecord
{
    const TYPE_SERVICE = 1;
    const TYPE_CONTENT = 2;

    public static function getListType(){
        $pricing_types = [
            self::TYPE_CONTENT => \Yii::t('app', 'Nội dung'),
            self::TYPE_SERVICE => \Yii::t('app', 'Gói cước'),
        ];
        return $pricing_types;
    }

    public static function getListTypeNameByType($type)
    {
        $lst = self::getListType();
        if (array_key_exists($type, $lst)) {
            return $lst[$type];
        }
        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pricing';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['site_id', 'price_coin', 'price_sms', 'watching_period', 'type'], 'required'],
            [['site_id', 'type', 'watching_period', 'created_at', 'updated_at'], 'integer'],
            [['price_coin', 'price_sms'], 'number'],
            [['description'], 'string', 'max' => 4000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => \Yii::t('app', 'ID'),
            'site_id'         => \Yii::t('app', 'Site ID'),
            'price_coin'      => \Yii::t('app', 'Giá Coin'),
            'price_sms'       => \Yii::t('app', 'Giá SMS'),
            'description'     => \Yii::t('app', 'Mô tả'),
            'type'            => \Yii::t('app', 'Loại'),
            'watching_period' => \Yii::t('app', 'Thời gian xem'),
            'created_at'      => \Yii::t('app', 'Ngày tạo'),
            'updated_at'      => \Yii::t('app', 'Ngày cập nhật'),
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
    public function getContents()
    {
        return $this->hasMany(ContentSiteAsm::className(), ['pricing_id' => 'id']);
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
    public function getServices()
    {
        return $this->hasMany(Service::className(), ['pricing_id' => 'id']);
    }

    public function getPriceInfo()
    {
        $currency = ($this->site->currency) ? ($this->site->currency) : '';
        return "coin:$this->price_coin $currency, sms:$this->price_sms $currency";
    }

    public static function listPrice($site_id = null, $allowFree = false)
    {
        $currency = '';
        if ($site_id) {
            $site = Site::findOne($site_id);
            if ($site) {
                $currency = $site->currency;
            }

            $prices = self::findAll(['site_id' => $site_id, 'type' => self::TYPE_CONTENT]);
        } else {
            $prices = self::findAll(['type' => self::TYPE_CONTENT]);
        }

        if ($allowFree) {
            $listPrices = [null => \Yii::t('app','Miễn phí')];
        } else {
            $listPrices = [];
        }

        foreach ($prices as $price) {
            $listPrices[$price->id] = \Yii::t('app','Xu: ') . $price->price_coin . \Yii::t('app',", Sms: ") . $price->price_sms . \Yii::t('app',", Xem: ") . $price->watching_period . "h";
        }

        arsort($listPrices);

        return $listPrices;
    }
}
