<?php
/**
 * Created by PhpStorm.
 * User: bibon
 * Date: 4/6/2016
 * Time: 5:11 PM
 */

namespace api\controllers;


use common\helpers\CUtils;
use common\models\Subscriber;
use common\models\SubscriberTransaction;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class PaymentVtcPayController extends Controller
{
    private function isActive()
    {
        return !!Yii::$app->params['payment_vtc_pay']['active'];
    }

    private function getPaymentVtcPayUrl()
    {
        return Yii::$app->params['payment_vtc_pay']['url'];
    }

    private function getTvod2ApiBaseURL()
    {
        return Yii::$app->params['payment_vtc_pay']['tvod2_api_base_url'];
    }

    private function getReturnUrl()
    {
        return self::getTvod2ApiBaseURL() . 'payment-vtc-pay/response-charge';
    }

    private function getSecretKey()
    {
        return Yii::$app->params['payment_vtc_pay']['secret_key'];
    }

    private function getMerchantId()
    {
        return Yii::$app->params['payment_vtc_pay']['merchant_id'];
    }

    private function getReceiveAccount()
    {
        return Yii::$app->params['payment_vtc_pay']['receiver_account'];
    }

    public function actionChargeCoin($username, $amount = 0, $currency_code = 'VND', $channel_type, $paymentType, $type, $url_return)
    {

        if ($this->isActive()) {
            if (empty($amount) || $amount <= 0) return $this->responseError(Yii::t('app', 'Số tiền trong tài khoản đã hết hoặc không đủ để thực hiện giao dịch'));
            if (empty($currency_code)) return $this->responseError(Yii::t('app', 'Số tiền không đủ'));

            // Lấy các tham số tạo chữ kí
            $receiveAccount = self::getReceiveAccount();
            $returnUrlFromVtc = self::getReturnUrl();
            $websiteId = self::getMerchantId();
            $secretKey = self::getSecretKey();

            $subscriber = Subscriber::findOne(['username' => $username, 'status' => Subscriber::STATUS_ACTIVE]);
            if (!$subscriber) {
                return Yii::t('app', 'Thuê bao không tồn tại hoặc chưa được kích hoạt');
            }

            // tạo order
            $order_id = "" . CUtils::randomNumber(15);
            $description = 'Nạp coin';
            // Tạo transaction
            $transaction = $subscriber->newTransaction($type, $channel_type, $description, null, null, SubscriberTransaction::STATUS_PENDING, $amount, $currency_code, 0,'',null,null,null,$order_id);

            if(!$transaction){
                return ['status' => false, 'message' => Yii::t('app', 'Chức năng hiện thời gián đoạn, xin quý khách vui lòng thực hiện lại sau. Xin cảm ơn!')];
            }
            // tạo chuỗi sign
            Yii::trace($url_return);
            $plaintext =
                $amount . "|"
                . $currency_code . "|"
                . $paymentType . "|"
                . $receiveAccount . "|"
                . $order_id . "|"
                . $url_return . "|"
                . $websiteId . "|"
                . $secretKey;
            Yii::info($plaintext);

            $signature = strtoupper(hash('sha256', $plaintext));
            Yii::info('chu ky: ' . $signature);

            $paymentUrl = self::getPaymentVtcPayUrl() . '?' . http_build_query([
                    'website_id' => $websiteId,
                    'currency' => $currency_code,
                    'reference_number' => $order_id,
                    'amount' => $amount,
                    'receiver_account' => $receiveAccount,
                    'url_return' => $url_return,
                    'payment_type' => $paymentType,
                    'signature' => $signature,
                ]);
            Yii::info($paymentUrl);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => true, 'redirect_url' => $paymentUrl, 'return_url' => $returnUrlFromVtc];
        } else {
            return ['status' => false, 'message' => Yii::t('app', 'Chức năng hiện thời chưa hoạt động, xin quý khách vui lòng thực hiện lại sau. Xin cảm ơn!')];
        }
    }

    public function actionResponseCharge($amount, $message, $payment_type, $reference_number, $status, $trans_ref_no, $website_id, $signature)
    {
        $secretKey = self::getSecretKey();
        $plaintext = $amount . '|' . $message . '|' . $payment_type . '|' . $reference_number . '|' . $status . '|' . $trans_ref_no . '|' . $website_id . '|' . $secretKey;
        $sign = strtoupper(hash('sha256', $plaintext));

        if ($sign !== $signature) {
            return $this->responseError(Yii::t('app', 'Lỗi hệ thống: Invalid Signature'));
        }

        $transaction = SubscriberTransaction::findOne(['order_id' => $reference_number, 'status' => SubscriberTransaction::STATUS_PENDING]);

        if (!$transaction) {
            return $this->responseError(Yii::t('app', 'Không tìm thấy giao dịch hoặc giao dịch đã được xử lý. Vui lòng kiểm tra lại tài khoản hoặc liên hệ <hotline>.'));
        }

        $result = $this->getResultByCode($status);

        $subscriber = $transaction->subscriber;

        $transaction->status = $result['success'] ? SubscriberTransaction::STATUS_SUCCESS : SubscriberTransaction::STATUS_FAIL;
        $transaction->cost = $amount;
        $transaction->smartgate_transaction_id = $trans_ref_no;
        $transaction->updated_at = time();
        $transaction->error_code = $status;

        if ($transaction->save()) {
            if ($result['success']) {
                $subscriber->balance = $subscriber->balance + $amount;
                if (!$subscriber->update(false)) {
                    Yii::error($subscriber->errors);
                }
                $transaction->balance = $subscriber->balance;
                $transaction->update();
            }
        } else {
            Yii::error($transaction->errors);
        }

        if ($result['success']) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['success' => true, 'message' => Yii::t('app', 'Quý khách đã nạp tiền thành công vào ví điện tử với mệnh giá ' . $amount . ', số dư sau khi nạp là: ' . $subscriber->balance . ' coin. Cảm ơn quý khách đã sử dụng dịch vụ TVOD!')];
        } else {
            return $this->responseError('Giao dịch không thành công, vui lòng thực hiện lại giao dịch. Tài khoản khách hàng không bị trừ tiền.');
        }
    }

    private function getResultByCode($result_code)
    {
        switch ($result_code) {
            case 0:
                return ['success' => false, 'message' => Yii::t('app', 'Giao dịch ở trạng thái khởi tạo')];
            case 1:
                return ['success' => true, 'message' => Yii::t('app', 'Thanh toán thành công')];
            case 7:
                return ['success' => false, 'message' => Yii::t('app', 'Đã trừ tiền nhưng tài khoản merchant chưa nhận được tiền chờ quản trị VTC duyệt')];
            case -1:
                return ['success' => false, 'message' => Yii::t('app', 'Giao dịch thất bại')];
            case -9:
                return ['success' => false, 'message' => Yii::t('app', 'Khách hàng tự hủy giao dịch')];
            case -3:
                return ['success' => false, 'message' => Yii::t('app', 'Quản trị VTC hủy giao dịch')];
            case -4:
                return ['success' => false, 'message' => Yii::t('app', 'Thẻ không đủ điều kiện')];
            case -5:
                return ['success' => false, 'message' => Yii::t('app', 'Số dư không đủ')];
            case -6:
                return ['success' => false, 'message' => Yii::t('app', 'Lỗi giao dịch tại VTC')];
            case -7:
                return ['success' => false, 'message' => Yii::t('app', 'Khách hàng nhập sai thông tin thanh toán')];
            case -8:
                return ['success' => false, 'message' => Yii::t('app', 'Quá hạn mức giao dịch trong ngày')];
        }
        return ['success' => false, 'message' => Yii::t('app', 'Lỗi không xác định')];
    }

    private function responseError($string)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'success' => false,
            'message' => $string,
        ];
    }
}