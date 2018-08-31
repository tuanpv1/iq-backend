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

class Ads extends \common\models\Ads {
    public function fields() {
        $fields = parent::fields();

        $fields['package_name'] = function ($model)  {
            /* @var $model Ads */
            return $model->appAds?$model->appAds->package_name:null;
        };
        $fields['app_name'] = function ($model)  {
            /* @var $model Ads */
            return $model->appAds?$model->appAds->app_name:null;
        };
        $fields['app_key'] = function ($model)  {
            /* @var $model Ads */
            return $model->appAds?$model->appAds->app_key:null;
        };

        $fields['image'] = function ($model) {
//            $link = Url::to(Yii::getAlias('@ads_images') . DIRECTORY_SEPARATOR . $model->image, true);
            $link = Url::to( Yii::getAlias('@web').DIRECTORY_SEPARATOR.Yii::getAlias('@content_images') . DIRECTORY_SEPARATOR . $model->image, true);
            return $link;
        };
        return $fields;
    }

}