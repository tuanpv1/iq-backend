<?php
/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 04-Aug-16
 * Time: 10:10 AM
 */

namespace api\models;


use yii\helpers\Url;

class KodiCategory extends \common\models\KodiCategory
{
    public function fields()
    {
        $fields = parent::fields();

        $fields['image'] = function($model){
            /** $model KodiCategory */
            return $model->image ? str_replace('api','backend',Url::to(\Yii::getAlias('@web') . DIRECTORY_SEPARATOR . \Yii::getAlias('@cat_image') . DIRECTORY_SEPARATOR . $model->image,true)) : '';
        };
        return $fields;
    }

}