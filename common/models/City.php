<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "city".
 *
 * @property string $id
 * @property string $name
 * @property string $code
 * @property integer $site_id
 * @property integer $ascii_name
 *
 * @property Site $site
 */
class City extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'city';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['site_id'], 'required'],
            [['site_id'], 'integer'],
            [['name', 'code','ascii_name'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Tỉnh/thành phố'),
            'code' => Yii::t('app', 'Mã tỉnh/thành phố'),
            'site_id' => Yii::t('app', 'Nhà cung cấp dịch vụ'),
        ];
    }

    public static function createCityEmpty($site_id)
    {
        $city = new City();
        $city->site_id = $site_id;
        $city->id = null;
        $city->name = null;
        $city->code = '';
        $city->ascii_name = null;
        return $city;
    }

}
