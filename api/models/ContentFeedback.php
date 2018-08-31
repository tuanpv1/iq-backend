<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 25/02/2015
 * Time: 9:03 AM
 */

namespace api\models;

class ContentFeedback extends \common\models\ContentFeedback {
    public function fields() {
        $fields = parent::fields();
        unset($fields['site_id']);
        unset($fields['content_provider_id']);
        unset($fields['status']);



        $fields['msisdn'] = function ($model) {
            /* @var $model ContentFeedback */
            return $model->subscriber->msisdn;
        };

        $fields['content_name'] = function ($model){
            /* @var $model ContentFeedback */
            return $model->content0? $model->content0->display_name: '';
        };

        return $fields;
    }

}