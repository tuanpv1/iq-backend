<?php

namespace api\controllers;

use api\models\PriceLevelCard;
use common\helpers\SmartGateHelper;
use common\models\PriceCard;
use common\models\SmsSupport;
use common\models\Subscriber;
use common\models\SubscriberToken;
use common\models\SubscriberTransaction;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Created by PhpStorm.
 * User: TuanPV
 * Date: 9/20/2017
 * Time: 4:23 PM
 */
class PaymentWebController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
//                IdentifyMsisdn::className(),
                // them header: -H "Authorization: Bearer access_token"
//                HttpBearerAuth::className(),
                // them tham so 'access-token' vao query
//                QueryParamAuth::className(),
            ],
        ];
        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];
        $behaviors['corsFilter'] = ['class' => \yii\filters\Cors::className(),];

        return $behaviors;
    }

    public function actionResponseCharge($merchant_id, $order_id, $created_on, $transaction_id = '',
                                         $result_code, $command, $netAmount = 0, $feeAmount = 0,
                                         $currency_code, $transaction_type, $transaction_status,
                                         $order_info, $checksum)
    {
        $payment_gate = new SmartGateHelper();

        $key = $payment_gate->merchant_id . $order_id . "PAY" . $created_on .
            $result_code . $transaction_type .
            $transaction_status . $payment_gate->secret_key;

        Yii::info($key);
        $token = md5($key);
        Yii::info($token);
        if ($token != $checksum) {
            Yii::error($token);
            throw  new  InternalErrorException(Yii::t('app', 'Lỗi hệ thống: Invalid Checksum'));
        }

        $transaction = SubscriberTransaction::findOne(['order_id' => $order_id, 'status' => SubscriberTransaction::STATUS_PENDING]);

        if (!$transaction) {
            throw  new  BadRequestHttpException(Yii::t('app', "Không tìm thấy giao dịch hoặc giao dịch đã được xử lý. Vui lòng kiểm tra lại tài khoản."));
        }

        $result = $this->getResultByCode($result_code);

        $transaction->status = $result['success'] ? SubscriberTransaction::STATUS_SUCCESS : SubscriberTransaction::STATUS_FAIL;
//        $transaction->cost = $amount;
//        $transaction->balance = $transaction->subscriber->balance + $amount;
        $transaction->updated_at = time();
        $transaction->smartgate_transaction_id = $transaction_id;
        $transaction->smartgate_transaction_timeout = time() + Yii::$app->params['smartgate_timeout'];

        if (!$result['success']) {
            $transaction->error_code = $result_code;
        }

        if ($transaction->save(false)) {
            /** @var Subscriber $subscriber */
            $subscriber = $transaction->subscriber;
            if ($result['success']) {
                $olbBalance = $subscriber->balance;
                $newBalance = $subscriber->balance + $netAmount;
                $subscriber->balance = $newBalance;
                if (!$subscriber->save(false)) {
                    Yii::error($subscriber->getErrors());
                } else {
                    $title = Subscriber::getTitleMessageRecharge();
                    $content = Subscriber::getContentMessageRecharge($subscriber->username, $olbBalance, $netAmount, $newBalance);
                    SmsSupport::addSmsSupportByContent($title, $content, $subscriber);
                    shell_exec("nohup  ./find_campaign_recharge.sh $transaction->cost $subscriber->id > /dev/null 2>&1 &");
                }
            }
        } else {
            Yii::error($transaction->errors);
        }

        if ($result['success']) {
            return ["message" => Yii::t('app', 'Quý khách đã nạp tiền thành công!')];
        } else {
            throw new InternalErrorException(Yii::t('app', 'Giao dịch không thành công, vui lòng thực hiện lại giao dịch.'));
        }
    }

    public function actionCancelCharge($merchant_id, $order_id, $created_on, $transaction_id = '',
                                       $result_code, $command, $netAmount = 0, $feeAmount = 0,
                                       $currency_code, $transaction_type, $transaction_status,
                                       $order_info, $checksum)
    {
//        $payment_gate = new SmartGateHelper();

//
//        $token = md5($payment_gate->merchant_id . $order_id . "PAY" . $created_on .
//            $result_code . $netAmount . $transaction_type .
//            $transaction_status . $payment_gate->secret_key);
//
//        if ($token != $checksum) {
//            return Yii::t('app', 'Lỗi hệ thống: Invalid Checksum');
//        }

        /** @var SubscriberTransaction $transaction */
        $transaction = SubscriberTransaction::findOne(['order_id' => $order_id, 'status' => SubscriberTransaction::STATUS_PENDING]);

        if (!$transaction) {
            throw new BadRequestHttpException(Yii::t('app', "Không tìm thấy giao dịch hoặc giao dịch đã được xử lý. Vui lòng kiểm tra lại tài khoản."));
        }

        $transaction->status = SubscriberTransaction::STATUS_FAIL;
        $transaction->updated_at = time();
        $transaction->error_code = $result_code;
        $transaction->smartgate_transaction_id = $transaction_id;
        $transaction->balance = $transaction->balance - $transaction->cost;

        if ($transaction->save(false)) {
            return ['message' => Yii::t('app', 'Giao dịch không thành công, vui lòng thực hiện lại giao dịch')];
        } else {
            Yii::error($transaction->getErrors());
            throw new InternalErrorException(Yii::t('app', 'Giao dịch không thành công, vui lòng thực hiện lại giao dịch'));
        }
    }

    private function getResultByCode($result_code)
    {
        switch ($result_code) {
            case 0:
                return ['success' => true, 'message' => Yii::t('app', 'Thanh toán thành công')];
            case 1:
                return ['success' => false, 'message' => Yii::t('app', 'Tham số không hợp lệ')];
            case 2:
                return ['success' => false, 'message' => Yii::t('app', 'Chữ ký sai')];
            case 3:
                return ['success' => false, 'message' => Yii::t('app', 'Merchant không đúng')];
            case 4:
                return ['success' => false, 'message' => Yii::t('app', 'Từ chối thanh toán')];
        }
        return ['success' => false, 'message' => Yii::t('app', 'Lỗi không xác định')];
    }

}