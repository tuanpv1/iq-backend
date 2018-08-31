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

class Service extends \common\models\Service
{

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['site_id']);
        unset($fields['pricing_id']);
        unset($fields['status']);
        unset($fields['created_at']);
        unset($fields['updated_at']);
        unset($fields['full_types']);
        unset($fields['auto_renew']);
        unset($fields['max_daily_retry']);
        unset($fields['max_day_failure_before_cancel']);
        unset($fields['day_register_again']);
        unset($fields['admin_note']);
        unset($fields['root_service_id']);

        $fields['price_coin'] = function ($model) {
            /**  $model Service*/
            return isset($model->pricing)?$model->pricing->price_coin:1;
        };
        $fields['price_sms'] = function ($model) {
            return isset($model->pricing)?$model->pricing->price_sms:1;
        };


        return $fields;
    }

}