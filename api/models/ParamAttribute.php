<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 25/02/2015
 * Time: 9:03 AM
 */

namespace api\models;

use common\helpers\CUtils;

class ParamAttribute extends \common\models\ParamAttribute
{
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['type']);

        $fields['type_category'] = function ($model) {
            /**  $model ParamAttribute*/
            return $model->type;
        };

        return $fields;
    }

}