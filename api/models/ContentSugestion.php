<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 25/02/2015
 * Time: 9:03 AM
 */

namespace api\models;

use api\helpers\UserHelpers;
use common\models\ContentCategoryAsm;
use common\models\ContentProfile;
use common\models\site;
use common\models\SubscriberContentAsm;
use Yii;
use yii\helpers\Url;

class ContentSugestion extends \common\models\Content
{
    public function fields()
    {
        $fields['id'] = function($model){
            /* @var $model Content */
            return $model->id;
        };
        $fields['display_name'] = function ($model) {
            /* @var $model Content */
            return $model->display_name;
        };
        $fields['ascii_name'] = function ($model) {
            /* @var $model Content */
            return $model->ascii_name;
        };
        $fields['is_series'] = function ($model) {
            /* @var $model Content */
            return $model->is_series;
        };
        return $fields;
    }


}