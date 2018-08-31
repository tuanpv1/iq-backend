<?php
namespace console\controllers;

use common\helpers\CommonUtils;
use common\models\Service;
use common\models\Site;
use common\models\Subscriber;
use common\models\SubscriberServiceAsm;
use common\models\SubscriberTransaction;
use yii\console\Controller;
use yii\helpers\Json;


/**
 * Thuc hien gia han cho cac thue bao den ky
 * @package YiiBoilerplate\Console
 */
class ExtendController extends Controller
{

    public function init()
    {

    }

    public static function log($message)
    {

        echo date('Y-m-d H:i:s') . ": " . $message . PHP_EOL;
    }

    /**
     * Thuc hien gia han thue bao. Thoi gian thuc hien 3h
     * @param int $sp - Service provider id
     * @param int $partition - so thu tu cua phan muon chay trong process hien tai, tinh tu 0 den $partition_count - 1
     * @param int $partition_count - khi muon chia pham vi gia han thanh  nhieu phan de chay nhieu process song song
     */
    public function actionRunExtend($sp, $partition_count = 1, $partition = 0)
    {
        if ($partition_count <= $partition) {
            echo("Invalid params: partition_count should be greater than partition");
            return;
        }

        if ($partition_count > 1) {
            echo("***Important***: You are currently execute 'extend' in multipart ($partition_count parts) mode. Be sure to run all parts from 0 to " . ($partition_count - 1) . "(each with one extend command).");
        }
        /**
         * @var $site Site
         */
        $site = Site::findOne(['id' => $sp]);
        if (!$site) {
            echo("Site not exist");
            return;
        }
        $ssaToExtend = Subscriber::getSubscribersToExtendByPartition($site, $partition_count, $partition);

        $ssaCount = count($ssaToExtend);
        $maxExtendDurationAllow = 55 * 60; // seconds 55p
        static::log("Process extend for service provider " . $site->name);
        static::log("Total subscriber to extend: " . $ssaCount);


        $startExecutionTime = time();
        $ssaIdx = 0;
        foreach ($ssaToExtend as $ssa) {
            $transactionID = 0;
            // Thuc hien gia han cho tung $subscriber mot, moi subscriber co the co nhieu goi cuoc can duoc gia han
            /* @var $ssa SubscriberServiceAsm */

            if (time() - $startExecutionTime >= $maxExtendDurationAllow) {
                static::log("*** WARNING: Extend finished due to time limit exceeded, processed $ssaIdx/$ssaCount, " . ($ssaCount - $ssaIdx) . " remaining!!");
                \Yii::$app->end();
            }

            $subscriber = $ssa->subscriber;
            $ssaIdx++;
            $service = $ssa->service;
            $expired_date = date('d-m-Y H:i:s', $ssa->expired_at);
            static::log("Processing $ssaIdx/$ssaCount:  $subscriber->msisdn, " .
                "service $service->id-$service->name, expired at $expired_date, " .
                "retried $ssa->renew_fail_count time(s), last retried at " . (($ssa->last_renew_fail_at) ? date('d-m-Y H:i:s', $ssa->last_renew_fail_at) : "NA") . "...");


            //TODO duyet lai tat ca cac goi cuoc dang co cua khach hang, de tranh trung lap goi cuoc
//            $usingServicePackages = $subscriber->currentServicePackages;

//            if($ssa->is_active != 1) continue; // no need

            $expireTime = $ssa->expired_at;

            $transType = SubscriberTransaction::TYPE_RENEW;
//            if ($ssa->renew_fail_count == 0) {
//                $transType = SubscriberTransaction::TYPE_RENEW;
            $action = "Gia hạn";
//            } else {
//                $transType = SubscriberTransaction::TYPE_RETRY;
//                $action = "Gia hạn lại";
//            }


            $channel_type = SubscriberTransaction::CHANNEL_TYPE_SYSTEM;

            $tr = $subscriber->newTransaction($transType, $channel_type,
                $action . " gói cước $service->name", $service);
            $transactionID = $tr->id;

            // tien hanh charge tien
            $chargingSuccess = false;
            $price = round($service->pricing->price_coin);

            if ($price <= $subscriber->balance) {
                $newBalance = $subscriber->balance - $price;
                $subscriber->balance = $newBalance;
                $subscriber->update(true, ['balance']);
                $chargingSuccess = true;
            }

            //TODO partner_id?
            $tr->status = $chargingSuccess ? SubscriberTransaction::STATUS_SUCCESS : SubscriberTransaction::STATUS_FAIL;
            $tr->cost = 0;
            $tr->balance = -$price;
            $tr->site_id = $service->site_id;
            $tr->currency = 'coin';
            if (!$tr->update()) {
                static::log("*** ERROR: cannot update transaction: " . Json::encode($tr->getErrors()));
            }

            $ssa->updated_at = time();

            if ($chargingSuccess) { // charging theo $chargeAmount THANH CONG
                // bat ke la renew, retry hay la renew_remaining, khi den day thi la da charge du cuoc cua chu ky,cap nhat chu ky moi
                static::log("CHARGE SUCCESS: " . $action . " goi cuoc $service->name: $price coin");
                // cap nhat lai chu ky moi
                static::renewChargingPeriod($ssa, $service, $transactionID);

//                $subscriber->vasgateSync($transType, $channel_type, $service_package, $tr->cost, $tr->create_date, $ssa->expiry_date);
            } else {
                $beginOfToDay = CommonUtils::getBeginOfDay(time());
                if ($ssa->last_renew_fail_at < $beginOfToDay) {
                    $ssa->today_retry_count = 0;
                }
                $ssa->today_retry_count++;
                $ssa->last_renew_fail_at = time();
                if ($ssa->renew_fail_count == 0 || $ssa->first_renew_fail_at == null) {
                    $ssa->first_renew_fail_at = time();
                }
                $ssa->renew_fail_count++;
                static::log("CHARGE  EXTEND  FAIL: " . $action . " goi cuoc $service->name: $price coin");
                if (!$ssa->update()) {
                    static::log("*** ERROR: cannot update ssa: " . Json::encode($ssa));
                }
                //TODO config so ngay tru cuoc loi lien tuc truoc khi huy vao cho khac
                $maxDaysFailBeforeCancel = $service->max_day_failure_before_cancel;
                static::log("*** Max retry time is: " . $maxDaysFailBeforeCancel);
                if (time() - $ssa->first_renew_fail_at > $maxDaysFailBeforeCancel * 24 * 3600) {
                    // thoi gian gia han loi lien tuc da vuot qua muc cho phep (15 hoac 30 ngay) --> huy goi cuoc
                    $subscriber->cancelServicePackage($service,
                        SubscriberTransaction::CHANNEL_TYPE_SYSTEM,
                        SubscriberTransaction::TYPE_CANCEL);

                    //TODO notification sms mt
                    static::log("*** IMPORTANT: Service package $service->name CANCELED by SYSTEM due to multiple FAIL");
                }
            }
        }
    }

    /**
     * @param $ssa SubscriberServiceAsm
     * @param $service_package Service
     */
    private static function renewChargingPeriod($ssa, $service_package, $transactionID = 0)
    {
        $expireTime = $ssa->expired_at;
        $chargingPeriod = $service_package->period * 24 * 3600;
        $beginTime = ($expireTime > time()) ? $expireTime : time();
        $newExpireTime = $beginTime + $chargingPeriod;

        $ssa->renewed_at = time();
        $ssa->expired_at = $newExpireTime;
        $ssa->first_renew_fail_at = null;
        $ssa->last_renew_fail_at = null;
        $ssa->renew_fail_count = 0;  // reset lai trang thai gia han
        $ssa->today_retry_count = 0;
        if (!$ssa->update()) {
            static::log("*** ERROR: cannot update ssa: " . Json::encode($ssa));
        }
        $expired_date = date('d-m-Y H:i:s', $ssa->expired_at);
        static::log("*** IMPORTANT: Service package $service_package->name EXTENDED TO: $expired_date ");
    }
}
