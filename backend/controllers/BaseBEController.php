<?php
namespace backend\controllers;

use common\auth\filters\Yii2Auth;
use common\models\LoginForm;
use common\models\LogLanguage;
use common\models\Multilanguage;
use Yii;
use yii\web\Controller;

/**
 * Site controller
 */
class BaseBEController extends Controller
{
 

    public $audit_id = null;

    public function behaviors()
    {
        return [
            'auth' => [
                'class' => Yii2Auth::className(),
                'autoAllow' => false,
//                'authManager' => 'authManager',
            ],
        ];
    }

//    public function init(){
//        //Here you can add specific code for generating Menu, but the code to change the Yii's default language
//        $is_default = Multilanguage::getLanguage();
//        Yii::$app->language = $is_default;
//
//    }
    public function init(){

        $model = LogLanguage::find()->andWhere(['id_user'=>Yii::$app->user->id])->one();
        if($model){
            Yii::$app->language = Multilanguage::findOne(['id'=>$model->id_lang])->code;
            $_SESSION['mlanguage'] = Multilanguage::findOne(['id'=>$model->id_lang])->code;
        }else{
            $modelcheck = Multilanguage::find()->andWhere(['status'=>Multilanguage::STATUS_ACTIVE])->orderBy(['created_at'=>SORT_DESC])
                ->andWhere(['is_default'=>1])->one();
            if($modelcheck){
                Yii::$app->language  = $modelcheck->code;
                $_SESSION['mlanguage'] = $modelcheck->code;
            }else{
                Yii::$app->language  = 'vi';
                $_SESSION['mlanguage'] = 'vi';
            }

        }
    }
}
