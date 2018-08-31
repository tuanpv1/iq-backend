<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 25/02/2015
 * Time: 9:03 AM
 */

namespace api\models;

use Yii;

class SmsUserAsm extends \common\models\SmsUserAsm
{
    public function fields()
    {
        $fields = parent::fields();
//

        $fields['date_received'] = function ($model) {
            /* @var $model SmsUserAsm */
            if ($model->date_received) {
                return $model->date_received;
            }
            return time();

        };
        $fields['title'] = function ($model) {
            /* @var $model SmsUserAsm */
            return $model->smsSupport->title;
        };
        $fields['content'] = function ($model) {
            /** @var $model SmsUserAsm */
            return $model->smsSupport->content;
        };


        return $fields;
    }


}