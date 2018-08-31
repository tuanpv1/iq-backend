<?php

/**
 * Swiss army knife to work with user and rbac in command line
 * @author: Nguyen Chi Thuc
 * @email: gthuc.nguyen@gmail.com
 */
namespace console\controllers;

use common\auth\helpers\AuthHelper;
use common\helpers\ResMtParams;
use common\helpers\ResMessage;
use common\helpers\StringUtils;
use common\models\BeAuthItem;
use common\models\BeUser;
use common\models\ContentPackage;
use common\models\MtTemplate;
use common\models\SmsMtTemplate;
use ReflectionClass;
use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\StringHelper;
use yii\helpers\VarDumper;
use yii\rbac\DbManager;
use yii\rbac\Item;

/**
 * PortMt create user in commandline
 */
class PortMtController extends Controller
{
    public function actionPort()
    {
        $class = new ReflectionClass("common\helpers\ResMessage");
        $constants = $class->getConstants();
        $listParams= ResMtParams::listMTParams();
        foreach ($constants as $key => $value) {
            if (!SmsMtTemplate::findOne(['code_name' => $value])) {
                $mtTemplateModel = new SmsMtTemplate();
                $mtTemplateModel->code_name = $value;
                $mtTemplateModel->content = 'invalid content';
                $mtTemplateModel->status = SmsMtTemplate::STATUS_ACTIVE;
                $params = isset($listParams[$value])? $listParams[$value]:[];
                $mtTemplateModel->params=implode(',',$params);
                if (!$mtTemplateModel->save()) {
                    var_dump($mtTemplateModel->getAttributes());
                    var_dump($mtTemplateModel->getErrors());
                    die();
                }
            }


        }
    }


}
