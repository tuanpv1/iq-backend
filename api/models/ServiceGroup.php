<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 25/02/2015
 * Time: 9:03 AM
 */

namespace api\models;

use Yii;
use yii\helpers\Url;

class ServiceGroup extends \common\models\ServiceGroup
{
    public $children;

    public function fields()
    {
        $fields = parent::fields();

        $fields['icon'] = function ($model) {
            /* @var $model ServiceGroup */
            return Url::to(Yii::getAlias('@web').DIRECTORY_SEPARATOR.Yii::getAlias('@content_images') . DIRECTORY_SEPARATOR . $model->icon, true);
        };

        $fields['packages'] = function ($model) {
            $serviceGroup = $model->services;
            $list = [];
            /** @var  $service Service*/
            foreach($serviceGroup as $service){
                $row = [];
                    $row['name'] = $service->name;
                    $row['id'] = $service->id;
                    $row['price_coin'] = isset($service->pricing)?$service->pricing->price_coin:0;
                    $row['price_sms'] = isset($service->pricing)?$service->pricing->price_sms:0;
                    $list[] = $row;
            }
            return $list;

        };

        return $fields;
    }

}