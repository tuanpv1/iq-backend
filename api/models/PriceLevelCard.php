<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 25/02/2015
 * Time: 9:03 AM
 */

namespace api\models;

class PriceLevelCard extends \common\models\PriceCard
{
    public function fields()
    {
        return [
            'id',
            'price' => function ($model) {

                return intval($model->price);
            },
            'description'
        ];
    }
}