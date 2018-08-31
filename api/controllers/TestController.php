<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 23/05/2015
 * Time: 4:37 PM
 */

namespace api\controllers;


use common\helpers\MTParam;
use common\helpers\SMSGW;
use common\models\BaseLogicCampaign;
use common\models\Content;
use common\models\Service;
use common\models\ServiceProvider;
use common\models\SiteStreamingServerAsm;
use common\models\SmsMtTemplate;
use common\models\Subscriber;
use Yii;

class TestController extends ApiController {
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = [
            'list-content',
            'detail',
            'test-register',
            'test',
        ];

        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'list-content' => ['GET'],
            'detail' => ['GET'],
            'related' => ['GET'],
        ];
    }
    public function actionTest(){

        return  BaseLogicCampaign::buyPackageGiftTimeExtend(1,'fcd5d901b36f',1);

    }


}