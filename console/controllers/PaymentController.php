<?php

/**
 * Swiss army knife to work with user and rbac in command line
 * @author: Nguyen Chi Thuc
 * @email: gthuc.nguyen@gmail.com
 */

namespace console\controllers;

use common\models\SmsSupport;
use common\models\Subscriber;
use common\models\SubscriberTransaction;
use common\helpers\FileUtils;
use SoapClient;
use Yii;
use yii\console\Controller;

/**
 * SecController create user in commandline
 */
class PaymentController extends Controller
{
    public function actionCheckStatusTransaction()
    {
        self::log("---------------Bat dau kiem tra cac transaction pending -------------------");

        /** @var SubscriberTransaction[] $trans */
        $trans = SubscriberTransaction::find()
            ->andWhere(['status' => SubscriberTransaction::STATUS_PENDING])
            ->andWhere(['gateway' => "VTC_PAY"])
            ->all();


        $website_id = Yii::$app->params['payment_vtc_pay']['merchant_id'];
        $app_id = Yii::$app->params['payment_vtc_pay']['app_id'];
        $receive_acc = Yii::$app->params['payment_vtc_pay']['receiver_account'];
        $secret_key_web = Yii::$app->params['payment_vtc_pay']['secret_key'];
        $secret_key_app = Yii::$app->params['payment_vtc_pay']['secret_key_app'];

        $url_check = Yii::$app->params['payment_vtc_pay']['url_check_transaction'];
        $check_transaction_after = Yii::$app->params['payment_vtc_pay']['check_transaction_after'];

        foreach ($trans as $transaction) {
            self::log("---------------------------------------------------------------------------");
            self::log("Dang kiem tra transaction id: $transaction->id ...");
            /** Neu chua den thoi gian kiem tra giao dich thi chay tiep */
            if ($transaction->transaction_time + $check_transaction_after > time()) {
                self::log("Chua den thoi gian kiem tra giao dich: $transaction->id, kiem tra sau");
                continue;
            }

            if ($transaction->channel == SubscriberTransaction::CHANNEL_TYPE_ANDROID) {
                $plaintext = $app_id . "-" . $transaction->order_id . "-" . $receive_acc . "-" . $secret_key_app;
                $sign = strtoupper(hash('sha256', $plaintext));
                $param = ['website_id' => $app_id, 'order_code' => $transaction->order_id, 'receiver_acc' => $receive_acc, 'sign' => $sign];
            } else {
                $plaintext = $website_id . "-" . $transaction->order_id . "-" . $receive_acc . "-" . $secret_key_web;
                $sign = strtoupper(hash('sha256', $plaintext));
                $param = ['website_id' => $website_id, 'order_code' => $transaction->order_id, 'receiver_acc' => $receive_acc, 'sign' => $sign];
            }

            $client = new SoapClient($url_check);
            $res = (array)$client->CheckPartnerTransation($param);

            self::log("Response:" . $res['CheckPartnerTransationResult']);

            $items = explode("|", $res['CheckPartnerTransationResult']);

            $reponsecode = isset($items[0]) ? $items[0] : "";
            $order_code = isset($items[1]) ? $items[1] : "";
            $amount = isset($items[2]) ? $items[2] : "";
            $sign = isset($items[3]) ? $items[3] : "";

            if ($sign != "") {
                if ($transaction->channel == SubscriberTransaction::CHANNEL_TYPE_ANDROID) {
                    $plaintext = $reponsecode . "-" . $order_code . "-" . $amount . "-" . $secret_key_app;
                } else {
                    $plaintext = $reponsecode . "-" . $order_code . "-" . $amount . "-" . $secret_key_web;
                }
                $sign_client = strtoupper(hash('sha256', $plaintext));

                if ($sign_client == $sign) {
                    if ($reponsecode == 1 || $reponsecode == 2) {
                        $transaction->status = SubscriberTransaction::STATUS_SUCCESS;
                        $transaction->error_code = $reponsecode;
                        $transaction->save(false);

                        /** @var Subscriber $subscriber */
                        $subscriber = $transaction->subscriber;

                        $olbBalance = $subscriber->balance;
                        $newBalance = $subscriber->balance + $amount;
                        $subscriber->balance = $newBalance;
                        if (!$subscriber->save(false)) {
                            self::log("Cong tien vao tai khoan that bai");
                        } else {
                            $title = Subscriber::getTitleMessageRecharge();
                            $content = Subscriber::getContentMessageRecharge($subscriber->username, $olbBalance, $amount, $newBalance);
                            SmsSupport::addSmsSupportByContent($title, $content, $subscriber);

                            \Yii::$app->runAction('subscriber/find-campaign-for-recharge', [$transaction->cost, $subscriber->id]);
//                            \console\controllers\SubscriberController::actionFindCampaignForRecharge($transaction->cost, $subscriber->id);
//                            shell_exec("nohup  ./find_campaign_recharge.sh $transaction->cost $subscriber->id > /dev/null 2>&1 &");
                        }
                    } elseif ($reponsecode == 0) {
                        self::log("Giao dich dang khoi tao ben VTC PAY, kiem tra lan sau");
                    } else if ($reponsecode == "-402" || $reponsecode == "-403" || $reponsecode == "-404") {
                        self::log("Sai thong tin kiem tra");
                        continue;
                    } else {
                        $transaction->status = SubscriberTransaction::STATUS_FAIL;
                        $transaction->error_code = $reponsecode;
                        $transaction->save(false);
                    }

                    self::log("Kiem tra transaction (ID: $transaction->id) hoan tat: Error code: $reponsecode");
                } else {
                    self::log("Sai chu ky");
                }
            } else {
                self::log("Khong ton tai chu ky");
            }
            self::log("----------------------------------------------------------------------------------");
        }

    }

    public static function log($message)
    {
        echo date('Y-m-d H:i:s') . ": " . $message . PHP_EOL;
    }
}
