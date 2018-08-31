<?php
/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 04-Aug-16
 * Time: 10:36 AM
 */

namespace api\models;


use yii\helpers\Url;

class ItemKodi extends \common\models\ItemKodi
{
    public function fields()
    {
        $fields = parent::fields();

        $fields['image'] = function($model){
            /** $model ItemKodi */
            return $model->image ? str_replace('api','backend',Url::to(\Yii::getAlias('@web') . DIRECTORY_SEPARATOR . \Yii::getAlias('@cat_image') . DIRECTORY_SEPARATOR . $model->image,true)) : '';
        };
        $fields['file_download'] = function($model){
            /** $model ItemKodi */
            return $model->file_download ? str_replace('api','backend',Url::to(\Yii::getAlias('@web') . DIRECTORY_SEPARATOR . \Yii::getAlias('@file_downloads') . DIRECTORY_SEPARATOR . $model->file_download,true)) : '';
        };
        return $fields;
    }
}