<?php

namespace api\controllers;

use api\models\PriceLevelCard;
use common\helpers\CUtils;
use common\helpers\SmartGateHelper;
use common\models\PriceCard;
use common\models\SmsSupport;
use common\models\Subscriber;
use common\models\SubscriberTransaction;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Yii;
use yii\web\BadRequestHttpException;

/**
 * Created by PhpStorm.
 * User: TuanPV
 * Date: 9/20/2017
 * Time: 4:23 PM
 */
class PaymentController extends ApiController
{

    const TYPE_TOPUP = 1;
    const TYPE_BUY_PACKAGE = 2;
    const TYPE_EXTEND_PACKAGE = 3;
    const TYPE_BUY_CONTENT = 4;

    const METHOD_ATM = 22;
    const METHOD_VISA = 23;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = [
            'check-mac',
            'topup-tvod-card',
            'topup-voucher-phone',
            'topup-price-level',
            'topup-in-web',
        ];

        return $behaviors;
    }
//    public function behaviors()
//    {
//        $behaviors = parent::behaviors();
//        $behaviors['authenticator']['except'] = [
//            'check-mac',
//            'topup-tvod-card',
//            'topup-voucher-phone',
//            'topup-price-level',
//        ];
//
//        return $behaviors;
//    }
//
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
        ];
    }

    public function actionCheckMac($mac_address)
    {
        return Subscriber::checkMac($mac_address);
    }

    public function actionTopupTvodCard($card_code, $signature, $mac_address)
    {
        $card_serial = '';
//        $c_code = Yii::$app->request->post('card_code');
//        $signature = Yii::$app->request->post('signature');
//        $mac_address = Yii::$app->request->post('mac_address');

        $check = Subscriber::checkMac($mac_address);
        if (!$check['success']) {
            return $check;
        }

        $subscriber = Subscriber::findOne($check['id']);
        $site_id = $subscriber->site_id;

        return Subscriber::topupVoucher($subscriber, $card_code, $site_id, $signature, $card_serial);
    }

    public function actionTopupVoucherPhone($c_serial, $c_code, $signature, $operator, $mac_address)
    {
//        $c_serial = Yii::$app->request->post('card_serial');
//        $c_code = Yii::$app->request->post('card_code');
//        $signature = Yii::$app->request->post('signature');
//        $operator = Yii::$app->request->post('operator');
//        $mac_address = Yii::$app->request->post('mac_address');

        $check = Subscriber::checkMac($mac_address);
        if (!$check['success']) {
            return $check;
        }

        $subscriber = Subscriber::findOne($check['id']);
        $site_id = $subscriber->site_id;

        return Subscriber::topupVoucherPhone($subscriber, $c_serial, $c_code, $site_id, $operator, $signature);
    }

    public function actionTopupPriceLevel()
    {
        $price_level = PriceLevelCard::find()->andWhere(['site_id' => $this->site->id, 'status' => PriceCard::STATUS_ACTIVE])->orderBy(['price' => SORT_ASC])->all();
        return $price_level;
    }

    public function actionTopupBankCard($price, $type, $channel_type)
    {
        /** @var Subscriber $subscriber */
        $subscriber = Yii::$app->user->identity;

        $payment_gate = new  SmartGateHelper();
        $rs = $payment_gate->topup($subscriber, $price, $type, $channel_type, 'VND');
        if (isset($rs['status']) && $rs['status']) {
            return $rs;
        } else {
            if ($rs['error_code'] == SmartGateHelper::ERROR_CODE_INTERNAL_ERROR) {
                throw new InternalErrorException($rs['message']);
            } else {
                throw new BadRequestHttpException($rs['message']);
            }
        }
    }

    public function actionTopupInWeb()
    {
        $price = \Yii::$app->request->get("price", '');
        $type = \Yii::$app->request->get("type", '');
        $channel_type = \Yii::$app->request->get("channel_type", '');
        $subscriber_id = \Yii::$app->request->get("subscriber_id", '');
        $mac_address = \Yii::$app->request->get("mac_address", '');
        $return_url = \Yii::$app->request->get("return_url", '');
        $cancel_url = \Yii::$app->request->get("cancel_url", '');

        /** @var Subscriber $subscriber */
        $subscriber = Subscriber::findOne(['id' => $subscriber_id, 'machine_name' => $mac_address]);
        if (!$subscriber) {
            throw new InternalErrorException(Yii::t('app', 'Yêu cầu không hợp lệ, vui lòng thử lại'));
        }

        $payment_gate = new  SmartGateHelper();
        $payment_gate->cancel_url = $cancel_url;
        $payment_gate->return_url = $return_url;

        $rs = $payment_gate->topup($subscriber, $price, $type, $channel_type, 'VND');
        if (isset($rs['status']) && $rs['status']) {
            return $rs;
        } else {
            if ($rs['error_code'] == SmartGateHelper::ERROR_CODE_INTERNAL_ERROR) {
                throw new InternalErrorException($rs['message']);
            } else {
                throw new BadRequestHttpException($rs['message']);
            }
        }
    }

    public function actionGetOrder($amount, $method, $channel_type = SubscriberTransaction::CHANNEL_TYPE_ANDROID, $type = self::TYPE_TOPUP, $content_id = null, $transaction_time = null, $package_name = null, $gateway = "VTC_PAY")
    {
        $order_id = "" . CUtils::randomNumber(15);

        if ($type == self::TYPE_TOPUP) {
            if ($method == self::METHOD_ATM) {
                $type = SubscriberTransaction::TYPE_TOPUP_ATM;
                $description = "Nạp tiền qua thẻ ATM";
            } else {
                $type = SubscriberTransaction::TYPE_TOPUP_VISA;
                $description = "Nạp tiền qua thẻ quốc tế";
            }
        } else if ($type == self::TYPE_BUY_CONTENT) {
            $type = SubscriberTransaction::TYPE_CONTENT_PURCHASE;
            $description = "Mua nội dung lẻ qua cổng thanh toán";
        } else if ($type == self::TYPE_BUY_PACKAGE) {
            $type = SubscriberTransaction::TYPE_REGISTER;
            $description = "Mua gói cước qua cổng thanh toán";
        } else {
            $type = SubscriberTransaction::TYPE_RENEW;
            $description = "Gia hạn gói cước qua cổng thanh toán";
        }

        /** @var Subscriber $subscriber */
        $subscriber = Yii::$app->user->identity;

        $transaction = $subscriber->newTransaction($type, $channel_type, $description, null, null,
            SubscriberTransaction::STATUS_PENDING, $amount, "VND",
            $subscriber->balance + $amount, '', null, null, null,
            $order_id, $subscriber->balance, $gateway);

        if ($transaction) {
            return ['amount' => $amount, 'order_id' => $order_id];
        } else {
            throw new InternalErrorException(Yii::t('app', "Hệ thống đang bận, vui lòng thử lại sau"));
        }
    }

    public function actionUpdateTransaction($amount, $order_id, $transaction_id, $status_code, $signature, $gateway)
    {
        $key = $amount . $order_id . $transaction_id . $status_code . 'tvod2@vtcpay.ppk';

        $server_signature = md5($key);

        if ($server_signature == $signature) {
            $transaction = SubscriberTransaction::findOne(['order_id' => $order_id, 'status' => SubscriberTransaction::STATUS_PENDING]);

            if (!$transaction) {
                throw  new  BadRequestHttpException(Yii::t('app', "Không tìm thấy giao dịch hoặc giao dịch đã được xử lý. Vui lòng kiểm tra lại tài khoản."));
            }

            $transaction->error_code = $status_code;
            $transaction->smartgate_transaction_id = $transaction_id;

            switch ($status_code) {
                case 1:
                    $transaction->status = SubscriberTransaction::STATUS_SUCCESS;
                    $transaction->save(false);

                    /** @var Subscriber $subscriber */
                    $subscriber = $transaction->subscriber;

                    $olbBalance = $subscriber->balance;
                    $newBalance = $subscriber->balance + $amount;
                    $subscriber->balance = $newBalance;
                    if (!$subscriber->save(false)) {
                        Yii::error($subscriber->getErrors());
                    } else {
                        $title = Subscriber::getTitleMessageRecharge();
                        $content = Subscriber::getContentMessageRecharge($subscriber->username, $olbBalance, $amount, $newBalance);
                        SmsSupport::addSmsSupportByContent($title, $content, $subscriber);
                        shell_exec("nohup  ./find_campaign_recharge.sh $transaction->cost $subscriber->id > /dev/null 2>&1 &");
                    }

                    return ["message" => Yii::t('app', 'Quý khách đã nạp tiền thành công!')];
                case 0:
                    $transaction->status = SubscriberTransaction::STATUS_PENDING;
                    $transaction->save(false);
                    throw new InternalErrorException(Yii::t('app', 'Giao dịch không thành công, vui lòng thực hiện lại giao dịch.'));
                case 7:
                    $transaction->status = SubscriberTransaction::STATUS_PENDING;
                    $transaction->save(false);
                    throw new InternalErrorException(Yii::t('app', 'Giao dịch không thành công, vui lòng thực hiện lại giao dịch.'));
                default:
                    $transaction->status = SubscriberTransaction::STATUS_FAIL;
                    $transaction->save(false);
                    throw new InternalErrorException(Yii::t('app', 'Giao dịch không thành công, vui lòng thực hiện lại giao dịch.'));
            }
        } else {
            throw new BadRequestHttpException(Yii::t('app', "Yêu cầu không hợp lệ, vui lòng thử lại"));
        }
    }
}