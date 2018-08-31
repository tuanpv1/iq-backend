<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 25/02/2015
 * Time: 9:03 AM
 */

namespace api\models;

class Subscriber extends \common\models\Subscriber
{
    public function fields()
    {
        $fields = parent::fields();
        $fields['id'] = function ($model) {
            /* @var $model Subscriber */
            return $model->id;
        };

        $fields['username'] = function ($model) {
            /* @var $model Subscriber */
            return $model->username;
        };

        $fields['machine_name'] = function ($model) {
            /* @var $model Subscriber */
            return $model->machine_name;
        };

        $fields['full_name'] = function ($model) {
            /* @var $model Subscriber */
            return $model->full_name;
        };

        $fields['balance'] = function ($model) {
            /* @var $model Subscriber */
            return $model->balance;
        };

        $fields['msisdn'] = function ($model) {
            /* @var $model Subscriber */
            return $model->msisdn;
        };

        $fields['city'] = function ($model) {
            /* @var $model Subscriber */
            return $model->getProvinceName();
        };
        $fields['province_code'] = function ($model) {
            /* @var $model Subscriber */
            return $model->province_code;
        };
        $fields['ip_to_location'] = function ($model) {
            /* @var $model Subscriber */
            return $model->ip_to_location;
        };
        $fields['address'] = function ($model) {
            /* @var $model Subscriber */
            return $model->address;
        };
        $fields['status'] = function ($model) {
            /* @var $model Subscriber */
            return $model->status;
        };
        $fields['birthday'] = function ($model) {
            /* @var $model Subscriber */
            return $model->birthday;
        };
        $fields['sex'] = function ($model) {
            /* @var $model Subscriber */
            return $model->sex;
        };
        $fields['email'] = function ($model) {
            /* @var $model Subscriber */
            return $model->email;
        };
        $fields['site_id'] = function ($model) {
            /* @var $model Subscriber */
            return $model->site_id;
        };
        $fields['created_at'] = function ($model) {
            /* @var $model Subscriber */
            return $model->created_at;
        };
        $fields['updated_at'] = function ($model) {
            /* @var $model Subscriber */
            return $model->updated_at;
        };

        return $fields;
    }

}