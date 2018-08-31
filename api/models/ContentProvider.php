<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 23/05/2015
 * Time: 5:07 PM
 */

namespace api\models;


use yii\helpers\Url;

class ContentProvider extends \common\models\ContentProvider {
    public function fields() {


        $fields = parent::fields();
        unset($fields['site_id']);
        unset($fields['user_admin_id']);
        unset($fields['updated_at']);
        unset($fields['created_at']);
        $fields['logo'] = function ($model) {
            /* @var $model \common\models\ContentProvider */
            $link = [];
            if (!$model->logo) {
                return null;
            }
            return Url::to(\Yii::getAlias('@content_images') . DIRECTORY_SEPARATOR .$this->logo,true);

        };

        return $fields;
    }
}