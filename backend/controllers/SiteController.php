<?php

namespace backend\controllers;

use backend\models\LoginForm;
use common\helpers\CheckLogin;
use common\models\LogLanguage;
use common\models\Multilanguage;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Site controller
 */
class SiteController extends BaseBEController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }


    public function actionIndex($lang = '')
    {
        if ($lang == '') {
            Yii::$app->language = $_SESSION['mlanguage'];
        } else {
            Yii::$app->language = $lang;
            $_SESSION['mlanguage'] = $lang;
        }
        $isExist = LogLanguage::findOne(['id_user' => Yii::$app->user->id]);
        if ($isExist) {
            $isExist->id_lang = Multilanguage::findOne(['code' => $_SESSION['mlanguage']])->id;
            $isExist->updated_at = time();
            $isExist->save();
        } else {
            $model = new LogLanguage();
            $model->id_lang = Multilanguage::findOne(['code' => $_SESSION['mlanguage']])->id;
            $model->id_user = Yii::$app->user->id;
            $model->created_at = time();
            $model->updated_at = time();
            $model->save();
        }
        return $this->render('index');
    }

    public function actionLogin()
    {
        $this->layout = 'login';

        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new LoginForm();

        $loginError = new CheckLogin();
        $count = $loginError->getCountError();
        if (!$loginError->showError($count)) {
            Yii::$app->getSession()->setFlash('error', \Yii::t('app', 'Bạn đã đăng nhập quá số lần quy định vui lòng đăng nhập lại sau ' . Yii::$app->params['timeOutLogin'] . ' phút'));
            return $this->render('login', [
                'model' => $model,
            ]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $count++;
            $loginError->setCountError($count);

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionSetLanguage($lang = 'vi')
    {
        Yii::$app->language = $lang;
        return $this->goBack();
    }
}
