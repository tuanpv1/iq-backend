<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 25/02/2015
 * Time: 9:03 AM
 */

namespace api\models;

class SubscriberServiceAsm extends \common\models\SubscriberServiceAsm
{
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['id']);
        unset($fields['subscriber_id']);
        unset($fields['site_id']);
        unset($fields['msisdn']);
        unset($fields['description']);
        unset($fields['renewed_at']);
        unset($fields['last_renew_fail_at']);
        unset($fields['renew_fail_count']);
        unset($fields['created_at']);
        unset($fields['updated_at']);
        unset($fields['pending_date']);
        unset($fields['view_count']);
        unset($fields['download_count']);
        unset($fields['gift_count']);
        unset($fields['watching_time']);
        unset($fields['subscriber2_id']);
        unset($fields['transaction_id']);
        unset($fields['cancel_transaction_id']);
        unset($fields['last_renew_transaction_id']);
        unset($fields['canceled_at']);
        unset($fields['auto_renew']);

        $fields['group_name'] = function ($model) {
            /** @var  $model SubscriberServiceAsm */
            $items = $model->service->serviceGroupAsms;
            foreach ($items as $item) {
                if ($item->service_id = $this->service_id) {
                    return $item->serviceGroup->name;
                }
            }
            return "";

        };
//        $fields['service_id'] = function ($model) {
//            /**  $model SubscriberServiceAsm*/
//            return isset($model->service)?$model->service->id:0;
//        };
//        $fields['name'] = function ($model) {
//            /**  $model SubscriberServiceAsm*/
//            return isset($model->service)?$model->service->name:"";
//        };
        $fields['display_name'] = function ($model) {
            /**  $model SubscriberServiceAsm*/
            return isset($model->service) ? $model->service->display_name : "";
        };
        $fields['price_coin'] = function ($model) {
            /**  $model SubscriberServiceAsm*/
            return isset($model->service) ? $model->service->pricing->price_coin : 0;
        };
        $fields['price_sms'] = function ($model) {
            return isset($model->service) ? $model->service->pricing->price_sms : 0;
        };
        \Yii::trace($fields);
        return $fields;
    }


}