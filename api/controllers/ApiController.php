<?php

namespace api\controllers;

use api\models\HttpBearerAuthExtend;
use common\models\ApiCredential;
use common\models\Languages;
use Yii;
use yii\caching\TagDependency;
use yii\filters\auth\CompositeAuth;
use yii\rest\Controller;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

/**
 * Base controller for API app
 */
class ApiController extends Controller
{
    const HEADER_API_KEY = "TP-Api-Key";
    const HEADER_LANGUAGE = "TP-Language";
    const HEADER_SECRET_KEY = "TP-Secret-Key";
    const HEADER_FINGERPRINT = "TP-Fingerprint";

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public $site;
    public $language;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
//                IdentifyMsisdn::className(),
                // them header: -H "Authorization: Bearer access_token"
                HttpBearerAuthExtend::className(),
                // them tham so 'access-token' vao query
//                QueryParamAuth::className(),
            ],
        ];
        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];
        $behaviors['corsFilter'] = ['class' => \yii\filters\Cors::className(),];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
//        $cache = Yii::$app->cache;

//        CUtils::showLogTime( CUtils::getMilliseconds(), 'Start time: API Controller');
//        $time =  CUtils::getMilliseconds();

////        $language = Yii::$app->request->headers->get(static::HEADER_LANGUAGE);
////
////        if (!array_key_exists($language, Languages::$language)) {
////            throw new UnauthorizedHttpException(Yii::t('app', 'Không hỗ trợ ngôn ngữ : ') . $language);
////        }
////        $this->language = $language;
////        Yii::$app->language = $this->language;
////
////        /** Sửa lại phần beforeAction vì code đang sai */
////        $api_key = Yii::$app->request->headers->get(static::HEADER_API_KEY);
////        if (!$api_key) {
////            throw new UnauthorizedHttpException(Yii::t('app', 'Thiếu API KEY'));
////        }
////
////        $key = Yii::$app->params['key_cache']['ApiKey'] . $api_key;
////        $credential = $cache->get($key);
////        if ($credential === false) {
////            /* @var $credential ApiCredential */
////            $credential = ApiCredential::findCredentialByApiKey($api_key);
////            $cache->set($key, $credential, Yii::$app->params['time_expire_cache'], new TagDependency(['tags' => Yii::$app->params['key_cache']['ApiKey']]));
////        }
////
////
////        if (!$credential) {
////            throw new UnauthorizedHttpException(Yii::t('app', 'Không tồn tại API key'));
////        }
//        /** Set site_id để dùng cho tiện */
//        switch ($credential->type) {
//            case ApiCredential::TYPE_IOS_APPLICATION:
//                break;
//            case ApiCredential::TYPE_WINDOW_PHONE_APPLICATION:
//                $secret_key = Yii::$app->request->headers->get(static::HEADER_SECRET_KEY);
//                if (!$secret_key || ($secret_key != $credential->client_secret)) {
//                    throw new UnauthorizedHttpException(Yii::t('app', 'Không tồn tại API key'));
//                }
//                break;
//            case ApiCredential::TYPE_ANDROID_APPLICATION:
//                $fingerprint = Yii::$app->request->headers->get(static::HEADER_FINGERPRINT);
//                if (!$fingerprint || ($fingerprint != $credential->certificate_fingerprint)) {
//                    throw new UnauthorizedHttpException(Yii::t('app', 'Finger print không hợp lệ hoặc chưa được cấp phép'));
//                }
//                break;
//            default:
//                break;
//        }
//        CUtils::showLogTime( CUtils::getMilliseconds() - $time, 'Execute time 3: API Controller: End');
//        CUtils::showLogTime( CUtils::getMilliseconds());

        // goi cai nay truoc de trigger event EVENT_BEFORE_ACTION
        $res = parent::beforeAction($action);
        return $res;
    }

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
     * replace message
     *
     * @param $message
     * @param $params
     * @return mixed
     */
    public static function replaceParam($message, $params)
    {
        if (is_array($params)) {
            $cnt = count($params);
            for ($i = 1; $i <= $cnt; $i++) {
                $message = str_replace('{' . $i . '}', $params[$i - 1], $message);
            }
        }
        return $message;
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
        return \Yii::$app->request->get($param_name, $default);
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
        return \Yii::$app->request->post($param_name, $default);
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
