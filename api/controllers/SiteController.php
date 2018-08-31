<?php
namespace api\controllers;

use common\models\Site;
use Yii;
use yii\base\Exception;
use yii\base\UserException;
use yii\web\HttpException;

/**
 * Site controller
 */
class SiteController extends ApiController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors                            = parent::behaviors();
        $behaviors['authenticator']['except'] = [
            'detail',
            'view',
            'error',
            'index',
            'test',
        ];
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [

        ];
    }

    public function actionError()
    {
//        return "ERROR";
        Yii::$app->response->statusCode = 404;

        $res = [
            'name'    => Yii::t('app', "Lỗi tìm kiếm"),
            'message' => Yii::t('app', "Không tìm thấy đường dẫn!"),
            'code'    => 0,
            'status'  => 404,
            'type'    => "yii\base\Exception",
        ];

        if (($exception = Yii::$app->getErrorHandler()->exception) === null) {
            return [];
        }

        if ($exception instanceof HttpException) {
            $code          = $exception->statusCode;
            $res['status'] = $code;
        } else {
            $code = $exception->getCode();
        }
        $res['code'] = 0;

        if ($exception instanceof Exception) {
            $name = $exception->getName();
        } else {
            $name = isset($this->defaultName) ?: Yii::t('app', 'Lỗi');
        }
        if ($code) {
            $name .= " (#$code)";
        }

        $res['name'] = $name;

        if ($exception instanceof UserException) {
            $message = $exception->getMessage();
        } else {
            $message = isset($this->defaultMessage) ?: Yii::t('app', 'Lỗi máy chủ');
        }

        $res['message'] = $message;

        return $res;
    }

    public function actionIndex()
    {
        return Yii::t('app', "TVod Phiên bản 2 API");
    }

    public function actionChildLockGuide()
    {
        return 'https://www.youtube.com/watch?v=yN7mT_CGeEc';
    }

}
