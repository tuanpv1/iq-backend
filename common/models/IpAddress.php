<?php

namespace common\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "ip_address".
 *
 * @property string $ip_start
 * @property string $ip_end
 * @property string $country
 * @property string $city
 * @property string $stateprov
 * @property integer $type
 *
 */
class IpAddress extends \yii\db\ActiveRecord
{

    const TYPE_NSX = 0;// ip của nhà máy sản xuất
    const TYPE_IPV4 = 1;
    const TYPE_IPV6 = 2;

    public $ip=null;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ip_address';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type','country','city','stateprov','ip_start','ip_end','ip'], 'required'],
            [['type'], 'integer'],
            [['country','city','stateprov'], 'string', 'max' => 100],
            [['ip_start','ip_end'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type' => Yii::t('app', 'Kiểu IP'),
            'city' => Yii::t('app', 'Tỉnh/thành phố'),
            'stateprov' => Yii::t('app', 'Quận/huyện'),
            'ip_start' => Yii::t('app', 'Địa chỉ IP bắt đầu'),
            'ip_end' => Yii::t('app', 'Địa chỉ IP kết thúc'),
            'country' => Yii::t('app', 'Đất nước'),
            'ip'=>Yii::t('app','Địa chỉ IP')
        ];
    }


    public static function listType()
    {
        $lst = [
            self::TYPE_IPV4 => Yii::t('app', 'IPv4'),
            self::TYPE_IPV6 => Yii::t('app', 'IPv6'),
        ];
        return $lst;
    }
}
