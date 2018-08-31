<?php
/**
 * Created by PhpStorm.
 * User: thuc
 * Date: 11/6/14
 * Time: 12:02 PM
 */

namespace api\helpers;

use api\helpers\authentications\IdentifyMsisdn;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

class UserHelpers {
    public static function manualLogin() {
        $auth = new IdentifyMsisdn();
        $identity = $auth->authenticate(\Yii::$app->user, \Yii::$app->request, \Yii::$app->response);

        if ($identity) return true;

        $auth = new HttpBearerAuth();
        $identity = $auth->authenticate(\Yii::$app->user, \Yii::$app->request, \Yii::$app->response);

        if ($identity) return true;

        $auth = new QueryParamAuth();
        $identity = $auth->authenticate(\Yii::$app->user, \Yii::$app->request, \Yii::$app->response);

        return $identity?true:false;
    }
} 