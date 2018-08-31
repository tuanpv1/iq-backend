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

class City extends \common\models\City
{

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['site_id']);
        unset($fields['id']);


        return $fields;
    }

}