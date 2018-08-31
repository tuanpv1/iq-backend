<?php
/**
 * Created by PhpStorm.
 * User: bibon
 * Date: 4/21/2016
 * Time: 9:30 AM
 */

namespace common\helpers;


use common\models\Subscriber;
use common\models\SubscriberTransaction;
use Yii;

class SmartGateHelper
{
    const ERROR_CODE_NONE = 0;
    const ERROR_CODE_BALANCE_TOO_LOW = 1;
    const ERROR_CODE_INTERNAL_ERROR = 2;


    public $merchant_id;
    public $secret_key;
    public $active;
    public $return_url;
    public $cancel_url;
    public $url;
    public $order_type_digital;
    public $check_command;

    public function __construct()
    {
        $this->merchant_id = Yii::$app->params['payment_gate']['merchant_id'];
        $this->secret_key = Yii::$app->params['payment_gate']['secret_key'];
        $this->active = Yii::$app->params['payment_gate']['active'];
        $this->return_url = Yii::$app->params['payment_gate']['return_url'];
        $this->cancel_url = Yii::$app->params['payment_gate']['cancel_url'];
        $this->url = Yii::$app->params['payment_gate']['url'];
        $this->order_type_digital = Yii::$app->params['payment_gate']['order_type_digital'];
        $this->check_command = Yii::$app->params['payment_gate']['command_check'];
    }

    private function call($function, $params, $key, $post = false)
    {
        $ch = new MyCurl();
        $ch->follow_redirects = true;
        $ch->user_agent = "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) coc_coc_browser/50.0.125 Chrome/44.0.2403.125 Safari/537.36";

        $result = null;
        $base_url = $this->url;
        $url = $base_url . $function;

        $query_string = '';
        $i = 0;
        foreach ($params as $param) {
            $query_string .= '&' . $key[$i] . '=' . $param;
            $i++;
        }

        Yii::info('Request params url: ' . $url . $query_string);
        try {
            if ($post) {
                $response = $ch->post($url, $params);
            } else {
                $response = $ch->get($url, $params);
            }
        } catch (\Exception $e) {
            Yii::info($e->getMessage());
            $response = null;
        }
        if ($response) {
            Yii::info('Response status: ' . $response->headers['Status-Code']);
            Yii::info('Response body: ' . $response->body);
        } else {

        }
        return $response;
    }


    /**
     * @param $subscriber Subscriber
     * @param $price
     * @return array
     */
    public function topup($subscriber, $price, $type, $channel_type, $currency_code)
    {
        if ($this->active) {
            $language = 'vn';
            $description = 'Nạp coin';
            $order_id = "" . CUtils::randomNumber(15);
            $transaction = $subscriber->newTransaction($type, $channel_type, $description, null, null,
                SubscriberTransaction::STATUS_PENDING, $price, $currency_code,
                $subscriber->balance + $price, '', null, null, null,
                $order_id, $subscriber->balance);

            if ($transaction) {
                $key = $this->merchant_id . $order_id . "PAY" . $price . $this->return_url . $this->secret_key;

                Yii::info($key);
                $token = md5($key);

                $paymentUrl = $this->url . '?' . http_build_query(['merchant_id' => $this->merchant_id,
                        'command' => "PAY",
                        'order_id' => $order_id,
                        "amount" => $price,
                        "shipping_fee" => 0,
                        "tax_fee" => 0,
                        "currency_code" => $currency_code,
                        "return_url" => $this->return_url,
                        "cancel_url" => $this->cancel_url,
                        "language" => $language,
                        "order_info" => "Nap coin",
                        "order_type" => $this->order_type_digital,
                        "checksum" => $token,
                    ]);
                return ['status' => true, 'redirect_url' => $paymentUrl, 'return_url' => $this->return_url, 'cancel_url' => $this->cancel_url];
            } else {
                return ['status' => false, 'error_code' => self::ERROR_CODE_INTERNAL_ERROR, 'message' => Yii::t('app', 'Hệ thống đang bận. Vui lòng thử lại!')];
            }

        } else {
            return ['status' => false, 'message' => Yii::t('app', 'Chức năng hiện thời chưa hoạt động, xin quý khách vui lòng thực hiện lại sau. Xin cảm ơn!')];
        }
    }

    /**
     * @param $transaction SubscriberTransaction
     * @return array
     */
    public function checkTrans($transaction)
    {
        if ($this->active) {
            $key = $this->merchant_id . $this->check_command . $transaction->order_id . $this->secret_key;

            Yii::info($key);
            $token = md5($key);
            $response = $this->call('/QueryDR', [
                'merchant_id' => $this->merchant_id,
                'command' => $this->check_command,
                'order_id' => $transaction->order_id,
                'checksum' => $token,
            ], [
                'merchant_id',
                'command',
                'order_id',
                'checksum',
            ]);

            var_dump($response);

            return ['status' => false, 'error_code' => self::ERROR_CODE_INTERNAL_ERROR, 'message' => Yii::t('app', 'Hệ thống đang bận. Vui lòng thử lại!')];


        } else {
            return ['status' => false, 'message' => Yii::t('app', 'Chức năng hiện thời chưa hoạt động, xin quý khách vui lòng thực hiện lại sau. Xin cảm ơn!')];
        }
    }

    public function checkResult($transaction)
    {
        $checksum = $this->merchant_id . "QUERYDR" . $transaction->order_id . $transaction->smartgate_transaction_id . $this->secret_key;
        Yii::info($checksum);
        $checksum = md5($checksum);
        $paymentUrl = $this->url . '?' . http_build_query([
                'merchant_id' => $this->merchant_id,
                'command' => "QUERYDR",
                'order_id' => $transaction->order_id, "transaction_id" => $transaction->smartgate_transaction_id,
                "checksum" => $checksum,
            ]);
    }
}