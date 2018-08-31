<?php
namespace api\controllers;

use common\models\Site;
use common\models\Subscriber;
use Yii;
use yii\rest\Controller;
use yii\web\Response;

/**
 * Created by PhpStorm.
 * User: TuanPham
 * Date: 3/8/2017
 * Time: 5:15 PM
 */
class TopupController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
//        $behaviors['authenticator'] = [
//            'class' => CompositeAuth::className(),
//            'authMethods' => [
//                IdentifyMsisdn::className(),
//                // them header: -H "Authorization: Bearer access_token"
//                HttpBearerAuth::className(),
//                // them tham so 'access-token' vao query
//                QueryParamAuth::className(),
//            ],
//        ];
        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];
        $behaviors['corsFilter'] = ['class' => \yii\filters\Cors::className(),];

        return $behaviors;
    }

    public function actionTopupSubscriber()
    {
        $name_subscriber = Yii::$app->request->post('name_subscriber');
        $cost = Yii::$app->request->post('cost');
        $partner = Yii::$app->request->post('partner');
        $signature = Yii::$app->request->post('signature');
        Yii::info('Nhan ten user: '.$name_subscriber.' Nhan ten partner: '.$partner.' Nhan ten signature: '.$signature.' Nhan cost: '.$cost);

        $validate = Subscriber::validateTopup($name_subscriber, $cost, $partner, $signature);
        Yii::info('Ma loi validate du lieu: ' . $validate);
        if ($validate != Subscriber::TOPUP_SUCCESS) {
            return ['error_code' => $validate];
        }

        $result = Subscriber::getSubscriberTopup($name_subscriber, $cost);
        if ($result['status'] != Subscriber::TOPUP_SUCCESS) {
            return ['error_code' => $result['status']];
        }
        return ['error_code' => $result['status'], 'trans_id' => $result['transaction_id']];
    }

    public function actionCheckExistUser()
    {
        $username = Yii::$app->request->post('name_subscriber');
        $partner = Yii::$app->request->post('partner');
        $signature = Yii::$app->request->post('signature');
        Yii::info('Nhan ten user: '.$username.' Nhan ten partner: '.$partner.' Nhan ten signature: '.$signature);

        $validate = Subscriber::validateTopup($username, null, $partner, $signature, true);
        Yii::info('Ma loi validate du lieu: ' . $validate);
        if ($validate != Subscriber::TOPUP_SUCCESS) {
            return ['error_code' => $validate];
        }

        $subscriber = Subscriber::findOne(['username' => $username,'status'=>Subscriber::STATUS_ACTIVE]);
        if (!$subscriber) {
            return ['error_code' => Subscriber::TOPUP_WRONG_USER];
        } else {
            return ['error_code' => Subscriber::TOPUP_SUCCESS];
        }
    }


    public function setStatusCode($code)
    {
        Yii::$app->response->setStatusCode($code);
    }

    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
        ];
    }

}