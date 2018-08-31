<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 25/02/2015
 * Time: 9:03 AM
 */

namespace api\models;

use common\helpers\CUtils;
use Yii;
use yii\helpers\Url;

class ActorDirector extends \common\models\ActorDirector {
    public function fields() {
        $fields = parent::fields();

        $fields['image'] = function ($model) {
            $link = Url::to( Yii::getAlias('@web').DIRECTORY_SEPARATOR.Yii::getAlias('@content_images') . DIRECTORY_SEPARATOR . $model->image, true);
            return $link;
        };
        $fields['shortname'] = function ($model) {
            $link = CUtils::parseTitleToKeyword($model->name);

            return $link;
        };
        $fields['ascii_name'] = function ($model) {
            $ascii_name = CUtils::convertVi2Eng($model->name);
            return $ascii_name;
        };

        return $fields;
    }

}