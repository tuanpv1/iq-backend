<?php
/**
 * Created by PhpStorm.
 * User: cuongvm
 * Date: 28/11/2016
 * Time: 10:44 AM
 */

namespace api\controllers;


use Yii;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\web\Response;

class BaseController extends Controller
{
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];
        $behaviors['corsFilter'] = ['class' => Cors::className(),];

        return $behaviors;
    }
    /**
     * @inheritdoc
     */

    /**
     * @inheritdoc
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
        ];
    }

    /**
     * Checks the privilege of the current user.
     *
     * This method should be overridden to check whether the current user has the privilege
     * to run the specified action against the specified data model.
     * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
     *
     * @param string $action the ID of the action to be executed
     * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
     * @throws ForbiddenHttpException if the user does not have access
     */
    public function checkAccess($action, $model = null, $params = [])
    {
    }

    /**
     * get value of parameter
     *
     * @param $param_name
     * @param null $default
     * @return mixed
     */
    public function getParameter($param_name, $default = null)
    {
        return Yii::$app->request->get($param_name, $default);
    }

    /**
     * get value of parameter
     *
     * @param $param_name
     * @param null $default
     * @return mixed
     */
    public function getParameterPost($param_name, $default = null)
    {
        return Yii::$app->request->post($param_name, $default);
    }
    /**
     * set status code response
     *
     * @param $code
     */
    public function setStatusCode($code)
    {
        Yii::$app->response->setStatusCode($code);
    }


}