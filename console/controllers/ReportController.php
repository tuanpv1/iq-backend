<?php
/**
 * Created by PhpStorm.
 * User: nhocconsanhdieu
 * Date: 26/5/2015
 * Time: 12:03 PM
 */

namespace console\controllers;

use common\models\Campaign;
use common\models\CampaignPromotion;
use common\models\Category;
use common\models\City;
use common\models\Content;
use common\models\ContentProvider;
use common\models\ContentViewLog;
use common\models\ContentViewReport;
use common\models\Device;
use common\models\LogCampaignPromotion;
use common\models\ReportCampaign;
use common\models\ReportContent;
use common\models\ReportContentHot;
use common\models\ReportRevenue;
use common\models\ReportSmartboxInitialization;
use common\models\ReportSubscriberActivity;
use common\models\ReportSubscriberExpired;
use common\models\ReportSubscriberInitialize;
use common\models\ReportSubscriberNumber;
use common\models\ReportSubscriberService;
use common\models\ReportSubscriberUsing;
use common\models\ReportTopup;
use common\models\Service;
use common\models\ServiceCpAsm;
use common\models\Site;
use common\models\Subscriber;
use common\models\SubscriberServiceAsm;
use common\models\SubscriberTransaction;
use DateTime;
use Exception;
use Yii;
use yii\console\Controller;

class ReportController extends Controller
{
    public function reportSubscriberActivity($beginPreDay, $endPreDay)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            static::log("Thoi gian bat dau: $beginPreDay : Thoi gian ket thuc: $endPreDay ");
            ReportSubscriberActivity::deleteAll(['between', 'report_date', $beginPreDay, $endPreDay]);
            static::log("Deleted report subscriber activity date:" . date("d-m-Y H:i:s", $beginPreDay) . ' timestamp:' . $beginPreDay);
            $sites = Site::findAll(['status' => Site::STATUS_ACTIVE]);

            if (!$sites) {
                $transaction->rollBack();
                static::log('n****** ERROR! Report Subscriber Activity Fail: Site ******');
            }
            /** @var  $site Site */
            foreach ($sites as $site) {
                echo $site->name . "\n";
                $content_types = Content::listTypeBC();
                if (count($content_types) <= 0) {
                    continue;
                }
                foreach ($content_types as $key => $content_type) {
                    echo $content_type . "\n";
                    $via_smb = ContentViewReport::find()
                        ->select('content_view_report.id')
                        ->innerJoin('subscriber', 'content_view_report.subscriber_id = subscriber.id')
                        ->andWhere(['subscriber.type' => Subscriber::TYPE_USER])
                        ->andWhere(['content_view_report.site_id' => $site->id])
                        ->andWhere(['content_view_report.type' => $key])
                        ->andWhere(['content_view_report.status' => ContentViewLog::STATUS_SUCCESS])
                        ->andWhere(['content_view_report.record_type' => ContentViewLog::IS_START])
                        ->andWhere(['content_view_report.channel' => ContentViewLog::CHANNEL_TYPE_ANDROID])
                        ->andWhere('content_view_report.view_date between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                        ->count();
                    $via_android = ContentViewReport::find()
                        ->select('content_view_report.id')
                        ->innerJoin('subscriber', 'content_view_report.subscriber_id = subscriber.id')
                        ->andWhere(['subscriber.type' => Subscriber::TYPE_USER])
                        ->andWhere(['content_view_report.site_id' => $site->id])
                        ->andWhere(['content_view_report.type' => $key])
                        ->andWhere(['content_view_report.status' => ContentViewLog::STATUS_SUCCESS])
                        ->andWhere(['content_view_report.record_type' => ContentViewLog::IS_START])
                        ->andWhere(['content_view_report.channel' => ContentViewLog::CHANNEL_TYPE_ANDROID_MOBILE])
                        ->andWhere('content_view_report.view_date between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                        ->count();
                    $via_ios = ContentViewReport::find()
                        ->select('content_view_report.id')
                        ->innerJoin('subscriber', 'content_view_report.subscriber_id = subscriber.id')
                        ->andWhere(['subscriber.type' => Subscriber::TYPE_USER])
                        ->andWhere(['content_view_report.site_id' => $site->id])
                        ->andWhere(['content_view_report.type' => $key])
                        ->andWhere(['content_view_report.status' => ContentViewLog::STATUS_SUCCESS])
                        ->andWhere(['content_view_report.record_type' => ContentViewLog::IS_START])
                        ->andWhere(['content_view_report.channel' => ContentViewLog::CHANNEL_TYPE_IOS])
                        ->andWhere('content_view_report.view_date between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                        ->count();

                    $via_website = ContentViewReport::find()
                        ->select('content_view_report.id')
                        ->innerJoin('subscriber', 'content_view_report.subscriber_id = subscriber.id')
                        ->andWhere(['subscriber.type' => Subscriber::TYPE_USER])
                        ->andWhere(['content_view_report.site_id' => $site->id])
                        ->andWhere(['content_view_report.type' => $key])
                        ->andWhere(['content_view_report.status' => ContentViewLog::STATUS_SUCCESS])
                        ->andWhere(['content_view_report.record_type' => ContentViewLog::IS_START])
                        ->andWhere(['content_view_report.channel' => ContentViewLog::CHANNEL_TYPE_WEBSITE])
                        ->andWhere('content_view_report.view_date between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                        ->count();
                    $via_site_daily = $via_smb + $via_android + $via_ios + $via_website;
                    $total_via_site = $via_site_daily;
                    $yesterday = $beginPreDay - 86400;
                    for ($i = 0; $i <= 7; $i++) {
                        $yesterday = $yesterday - 86400;
//                        echo date("d-m-Y H:i:s", $yesterday)."\n";
                        $total_via_site_yesterday = ReportSubscriberActivity::findOne(['site_id' => $site->id, 'report_date' => $yesterday, 'content_type' => $key]);
                        if ($total_via_site_yesterday) {
                            $total_via_site = $via_site_daily + $total_via_site_yesterday->total_via_site;
                            break;
                        }
                    }
                    $r = new ReportSubscriberActivity();
                    $r->report_date = $beginPreDay;
                    $r->site_id = $site->id;
                    $r->content_type = $key;
                    $r->via_site_daily = $via_site_daily;
                    $r->total_via_site = $total_via_site;
                    $r->via_smb = $via_smb;
                    $r->via_android = $via_android;
                    $r->via_ios = $via_ios;
                    $r->via_website = $via_website;
                    if (!$r->save()) {
                        echo '****** ERROR! Report Subscriber Activity Fail ******';
                        $transaction->rollBack();
                    }
                }
            }
            $transaction->commit();
            static::log("****** Report Subscriber Activity Done ******");
        } catch (Exception $e) {
            $transaction->rollBack();
            echo '****** ERROR! Report Subscriber Activity Fail Exception ******' . $e->getMessage();
        }
    }

    public function actionSubscriberActivity($start_day = '')
    {
        if ($start_day != '') {
            $beginPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(0, 0)->format('Y-m-d H:i:s'));
            $endPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(23, 59, 59)->format('Y-m-d H:i:s'));
        } else {
            $beginPreDay = strtotime("midnight", time());
            $endPreDay = strtotime("tomorrow", $beginPreDay) - 1;
        }
        $this->reportSubscriberActivity($beginPreDay, $endPreDay);
    }

    public function actionSubscriberActivityYesterday()
    {
        $day = strtotime("midnight", time());
        $from_time = $day - 86400;
        $to_time = $day - 1;
        $this->reportSubscriberActivity($from_time, $to_time);
    }

    public function reportContent($beginPreDay, $endPreDay)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {

            ReportContent::deleteAll(['between', 'report_date', $beginPreDay, $endPreDay]);
            static::log("Deleted report content date:" . date("d-m-Y H:i:s", $beginPreDay) . ' timestamp:' . $beginPreDay . "\n");

            $sites = Site::findAll(['status' => Site::STATUS_ACTIVE]);

            if (!$sites) {
                $transaction->rollBack();
                echo 'n****** ERROR! Report Content Fail: Site ******';
            }
            /** @var  $site Site */
            foreach ($sites as $site) {
                echo $site->name . "\n";
                $lstType = Content::listTypeBC();
                if (!$lstType) {
                    continue;
                }
                foreach ($lstType as $key => $value) {
                    $cats = Category::find()
                        ->andWhere(['category.status' => Category::STATUS_ACTIVE])
                        ->andWhere('category.type=:p_type', [':p_type' => $key])
                        ->innerJoin('category_site_asm', 'category.id=category_site_asm.category_id')
                        ->andFilterWhere(['category_site_asm.site_id' => $site->id])
                        ->all();
                    if (!$cats) {
                        continue;
                    }

                    /** @var  $cat Category */
                    foreach ($cats as $cat) {
                        echo $cat->display_name . "\n";
                        $cps = ContentProvider::find()->all();
                        if (!$cps) {
                            continue;
                        }
                        /** @var  $cp ContentProvider */
                        foreach ($cps as $cp) {
                            echo $cp->cp_name . "\n";
                            if ($cp->status == ContentProvider::STATUS_ACTIVE) {
                                $total_content = Content::find()
                                    ->innerJoin('content_site_asm', 'content.id=content_site_asm.content_id')
                                    ->andFilterWhere(['content_site_asm.site_id' => $site->id])
                                    ->innerJoin('content_category_asm', 'content.id=content_category_asm.content_id')
                                    ->andFilterWhere(['content_category_asm.category_id' => $cat->id])
                                    ->andFilterWhere(['content.cp_id' => $cp->id])
                                    ->andFilterWhere(['content.type' => $key])
                                    ->andFilterWhere(['<=', 'content.created_at', $endPreDay])
                                    ->distinct()
                                    ->count();
                                $count_content_upload_daily = Content::find()
                                    ->andWhere('content.created_at between :start_day and :end_day')->addParams([':start_day' => $beginPreDay, ':end_day' => $endPreDay])
                                    ->innerJoin('content_site_asm', 'content.id=content_site_asm.content_id')
                                    ->andFilterWhere(['content_site_asm.site_id' => $site->id])
                                    ->innerJoin('content_category_asm', 'content.id=content_category_asm.content_id')
                                    ->andFilterWhere(['content_category_asm.category_id' => $cat->id])
                                    ->andFilterWhere(['content.cp_id' => $cp->id])
                                    ->andFilterWhere(['content.type' => $key])
                                    ->distinct()
                                    ->count();
                                $total_content_view_dailys = ContentViewReport::find()
                                    ->select('sum(view_count) as view_count')
                                    ->andWhere(['content_view_report.site_id' => $site->id])
                                    ->innerJoin('content_category_asm', 'content_view_report.content_id=content_category_asm.content_id')
                                    ->andFilterWhere(['content_category_asm.category_id' => $cat->id])
                                    ->andWhere('content_view_report.view_date between :start_day and :end_day')->addParams([':start_day' => $beginPreDay, ':end_day' => $endPreDay])
                                    ->andFilterWhere(['content_view_report.cp_id' => $cp->id])
                                    ->andFilterWhere(['content_view_report.type' => $key])
                                    ->one();
                                $total_content_view_daily = $total_content_view_dailys->view_count ? $total_content_view_dailys->view_count : 0;
                                $total_buy_content_daily = SubscriberTransaction::find()
                                    ->select('subscriber_transaction.id')
                                    ->innerJoin('content_category_asm', 'subscriber_transaction.content_id=content_category_asm.content_id')
                                    ->andFilterWhere(['content_category_asm.category_id' => $cat->id])
                                    ->andFilterWhere(['subscriber_transaction.status' => SubscriberTransaction::STATUS_SUCCESS])
                                    ->andFilterWhere(['subscriber_transaction.type' => SubscriberTransaction::TYPE_CONTENT_PURCHASE])
                                    ->andWhere('subscriber_transaction.created_at between :start_day and :end_day')->addParams([':start_day' => $beginPreDay, ':end_day' => $endPreDay])
                                    ->andFilterWhere(['subscriber_transaction.site_id' => $site->id])
                                    ->innerJoin('content', 'subscriber_transaction.content_id=content.id')
                                    ->andFilterWhere(['content.cp_id' => $cp->id])
                                    ->andFilterWhere(['content.type' => $key])
                                    ->distinct()
                                    ->count();

                                /** @var  $r ReportContent */
                                $r = new ReportContent();
                                $r->report_date = $beginPreDay;
                                $r->site_id = $site->id;
                                $r->cp_id = $cp->id;
                                $r->content_type = $key;
                                $r->category_id = $cat->id;
                                $r->total_content = $total_content;
                                $r->count_content_upload_daily = $count_content_upload_daily;
                                $r->total_content_view = $total_content_view_daily;
                                $r->total_content_buy = $total_buy_content_daily;
                                if (!$r->save()) {
//                            var_dump($r->getFirstErrors());exit;
                                    echo '****** ERROR! Report Content Fail ******';
                                    $transaction->rollBack();
                                }
                            } else {
                                /** @var  $r ReportContent */
                                $r = new ReportContent();
                                $r->report_date = $beginPreDay;
                                $r->site_id = $site->id;
                                $r->cp_id = $cp->id;
                                $r->content_type = $key;
                                $r->category_id = $cat->id;
                                $r->total_content = 0;
                                $r->count_content_upload_daily = 0;
                                $r->total_content_view = 0;
                                $r->total_content_buy = 0;
                                if (!$r->save()) {
                                    echo '****** ERROR! Report Content Fail ******';
                                    $transaction->rollBack();
                                }
                            }

                        }
                    }
                }
            }
            $transaction->commit();
            static::log("****** Report Content Done ******");

        } catch (Exception $e) {
            $transaction->rollBack();
            echo '****** ERROR! Report Content Fail Exception: ' . $e->getMessage() . '******';
        }
    }

    public function actionContent($start_day = '')
    {
        if ($start_day != '') {
            $beginPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(0, 0)->format('Y-m-d H:i:s'));
            $endPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(23, 59, 59)->format('Y-m-d H:i:s'));
        } else {
            /** Lấy beginTime và endTime nowday */
            $beginPreDay = mktime(0, 0, 0);
            $endPreDay = mktime(23, 59, 59);
        }
        $this->reportContent($beginPreDay, $endPreDay);
    }

    public function actionContentYesterday()
    {
        $day = strtotime("midnight", time());
        $from_time = $day - 86400;
        $to_time = $day - 1;
        $this->reportContent($from_time, $to_time);
    }

    public function reportContentHot($beginPreDay, $endPreDay)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            ReportContentHot::deleteAll(['between', 'report_date', $beginPreDay, $endPreDay]);
            static::log("Deleted report content hot date:" . date("d-m-Y H:i:s", $beginPreDay) . ' timestamp:' . $beginPreDay);

            $sites = Site::findAll(['status' => Site::STATUS_ACTIVE]);

            if (!$sites) {
                $transaction->rollBack();
                echo 'n****** ERROR! Report Content Hot Fail: Site ******';
            }
            /** @var  $site Site */
            foreach ($sites as $site) {
                echo $site->name . "\n";
                $content_id = ContentViewReport::find()
                    ->select(['content_id', 'sum(view_count) as view_count', 'type', 'cp_id'])
                    ->andWhere(['site_id' => $site->id])
                    ->andWhere('view_date between :start_day and :end_day')->addParams([':start_day' => $beginPreDay, ':end_day' => $endPreDay])
                    ->groupBy('content_id')->all();
                if (!$content_id) {
                    continue;
                }
                foreach ($content_id as $content) {
                    /** @var  $content ContentViewLog */
                    echo $content->content_id . "\n";
//                      echo "<pre>";print_r($content_id);die();
                    $r = new ReportContentHot();
                    $r->report_date = $beginPreDay;
                    $r->site_id = $site->id;
                    $r->content_type = $content->type;
                    $r->content_id = $content->content_id;
                    $r->total_content_view = $content->view_count;
                    $r->cp_id = $content->cp_id;
                    if (!$r->save()) {
                        echo '****** ERROR! Report Content Hot Fail ******';
                        $transaction->rollBack();
                    }
                }

            }
            $transaction->commit();
            static::log('****** Report Content Hot Done ******');

        } catch (Exception $e) {
            $transaction->rollBack();
            echo '****** ERROR! Report Content Hot Fail Exception: ' . $e->getMessage() . '******';
        }
    }

    public function actionContentHot($start_day = '')
    {
        if ($start_day != '') {
            $beginPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(0, 0)->format('Y-m-d H:i:s'));
            $endPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(23, 59, 59)->format('Y-m-d H:i:s'));
        } else {
            /** Lấy beginTime và endTime nowday */
            $beginPreDay = mktime(0, 0, 0);
            $endPreDay = mktime(23, 59, 59);
        }
        $this->reportContentHot($beginPreDay, $endPreDay);
    }

    public function actionContentHotYesterday()
    {
        $day = strtotime("midnight", time());
        $from_time = $day - 86400;
        $to_time = $day - 1;
        $this->reportContentHot($from_time, $to_time);
    }


    public function actionRevenues($start_day = '')
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($start_day != '') {
                $beginPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(0, 0)->format('Y-m-d H:i:s'));
                $endPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(23, 59, 59)->format('Y-m-d H:i:s'));
            } else {
                /** Lấy beginTime và endTime nowday */
                $beginPreDay = mktime(0, 0, 0);
                $endPreDay = mktime(23, 59, 59);
            }
            ReportRevenue::deleteAll(['between', 'report_date', $beginPreDay, $endPreDay]);
            echo "Deleted report revenues date:" . date("d-m-Y H:i:s", $beginPreDay) . ' timestamp:' . $beginPreDay;

            $sites = Site::findAll(['status' => Site::STATUS_ACTIVE]);

            if (!$sites) {
                $transaction->rollBack();
                echo 'n****** ERROR! Report Revenues Fail: Site ******';
            }
            /** @var  $site Site */
            foreach ($sites as $site) {
                $packages = Service::findAll(['status' => Service::STATUS_ACTIVE, 'site_id' => $site->id]);
                if (!$packages) {
                    continue;
                }
                /** Add 1 thằng Service empty vào để chạy vòng for để đảm bảo không bị sót trường hợp mua phim lẻ, lúc này serive_id=null, content_id  */
                $packageEmpty = Service::createServiceEmpty($site->id);
                array_push($packages, $packageEmpty);
                /** @var  $package Service */
                foreach ($packages as $package) {
                    $lstType = SubscriberTransaction::listWhitelistTypes();
                    if (!$lstType) {
                        break;
                    }
                    foreach ($lstType as $key => $value) {
                        if ($package->id != null) {
                            //truong hop gia hạn gói
                            $renew_revenues1 = SubscriberTransaction::find()
                                ->where(['subscriber_transaction.site_id' => $site->id])
                                ->andWhere('subscriber_transaction.transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                ->andWhere(['subscriber_transaction.type' => SubscriberTransaction::TYPE_RENEW])
                                ->andWhere(['subscriber_transaction.status' => SubscriberTransaction::STATUS_SUCCESS])
                                ->andWhere(['subscriber_transaction.service_id' => $package->id])
                                ->andWhere(['subscriber_transaction.white_list' => $key])
                                ->sum('cost');
                            // những thuê bao mua lần 2
                            $renew_revenues2 = SubscriberTransaction::find()
                                ->andWhere(['subscriber_transaction.site_id' => $site->id])
                                ->andWhere('subscriber_transaction.transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                ->andWhere(['subscriber_transaction.type' => SubscriberTransaction::TYPE_REGISTER])
                                ->andWhere(['subscriber_transaction.status' => SubscriberTransaction::STATUS_SUCCESS])
                                ->andWhere(['subscriber_transaction.service_id' => $package->id])
                                ->andWhere(['subscriber_transaction.white_list' => $key])
                                ->andWhere(['subscriber_transaction.is_first_package' => null])
                                ->sum('cost');
                            $renew_revenues = $renew_revenues1 + $renew_revenues2;
                            // trường hợp mua gói
                            $register_revenues = SubscriberTransaction::find()
                                ->andWhere(['subscriber_transaction.site_id' => $site->id])
                                ->andWhere('subscriber_transaction.transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                ->andWhere(['subscriber_transaction.type' => SubscriberTransaction::TYPE_REGISTER])
                                ->andWhere(['subscriber_transaction.status' => SubscriberTransaction::STATUS_SUCCESS])
                                ->andWhere(['subscriber_transaction.service_id' => $package->id])
                                ->andWhere(['subscriber_transaction.white_list' => $key])
                                ->andWhere(['subscriber_transaction.is_first_package' => SubscriberTransaction::IS_FIRST_PACKAGE])
                                ->sum('cost');
                            $total_revenues = $renew_revenues + $register_revenues;
                            $cps = ServiceCpAsm::find()
                                ->andWhere(['service_id' => $package->id])
                                ->all();
                            /** @var  $cp ServiceCpAsm */
                            foreach ($cps as $cp) {
                                if ($cp->status == ServiceCpAsm::STATUS_ACTIVE) {
                                    /** trường hợp gói cước vẫn đang thuộc CP hay CP vẫn active */
                                    /** @var  $rp ReportRevenue */
                                    $rp = new ReportRevenue();
                                    $rp->report_date = $beginPreDay;
                                    $rp->site_id = $site->id;
                                    $rp->cp_id = $cp->cp_id;
                                    $rp->service_id = $package->id;
                                    $rp->white_list = $key;
                                    $rp->total_revenues = $total_revenues ? abs($total_revenues) : 0;
                                    $rp->renew_revenues = $renew_revenues ? abs($renew_revenues) : 0;
                                    $rp->register_revenues = $register_revenues ? abs($register_revenues) : 0;
                                    $rp->content_buy_revenues = 0;
                                    $rp->revenues = $total_revenues ? abs($total_revenues) : 0;
                                    if (!$rp->save()) {
                                        echo '****** ERROR! Report Revenues Daily Fail ******';
                                        $transaction->rollBack();
                                    }
                                } else {
                                    /** trường hợp CP bị inactive nhưng gói cước vẫn còn hoạt động*/
                                    /** @var  $rp ReportRevenue */
                                    $rp = new ReportRevenue();
                                    $rp->report_date = $beginPreDay;
                                    $rp->site_id = $site->id;
                                    $rp->cp_id = $cp->cp_id;
                                    $rp->service_id = $package->id;
                                    $rp->white_list = $key;
                                    $rp->total_revenues = 0;
                                    $rp->renew_revenues = 0;
                                    $rp->register_revenues = 0;
                                    $rp->content_buy_revenues = 0;
                                    $rp->revenues = 0;
                                    if (!$rp->save()) {
                                        echo '****** ERROR! Report Revenues Daily Fail ******';
                                        $transaction->rollBack();
                                    }
                                }
                            }
                        } else {
                            /** truong hop mua le */
                            $cps = ContentProvider::find()
                                ->all();
                            if (!$cps) {
                                break;
                            }
                            /** @var  $cp ContentProvider */
                            foreach ($cps as $cp) {
                                $content_buy_revenues = SubscriberTransaction::find()
                                    ->where(['subscriber_transaction.site_id' => $site->id])
                                    ->andWhere('subscriber_transaction.transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['subscriber_transaction.type' => SubscriberTransaction::TYPE_CONTENT_PURCHASE])
                                    ->andWhere(['subscriber_transaction.status' => SubscriberTransaction::STATUS_SUCCESS])
                                    ->andWhere(['subscriber_transaction.cp_id' => $cp->id])
                                    ->andWhere(['subscriber_transaction.white_list' => $key])
                                    ->sum('cost');
                                /** @var  $rp ReportRevenue */
                                $rp = new ReportRevenue();
                                $rp->report_date = $beginPreDay;
                                $rp->site_id = $site->id;
                                $rp->cp_id = $cp->id;
                                $rp->service_id = $package->id;
                                $rp->white_list = $key;
                                $rp->total_revenues = $content_buy_revenues ? abs($content_buy_revenues) : 0;
                                $rp->renew_revenues = 0;
                                $rp->register_revenues = 0;
                                $rp->revenues = 0;
                                $rp->content_buy_revenues = $content_buy_revenues ? abs($content_buy_revenues) : 0;
                                if (!$rp->save()) {
                                    echo '****** ERROR! Report Revenues Daily Fail ******';
                                    $transaction->rollBack();
                                }
                            }
                        }

                    }
                }
            }
            $transaction->commit();
            echo '****** Report Revenues Done ******';

        } catch (Exception $e) {
            $transaction->rollBack();
            echo '****** ERROR! Report Revenues Fail Exception: ' . $e->getMessage() . '******';
        }
    }

    /*
     * Thong ke doanh thu, so luong nap the
     */
    public function actionTopup($start_day = '')
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            /** Lấy beginTime và endTime nowday */
            if ($start_day != '') {
                $beginPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(0, 0)->format('Y-m-d H:i:s'));
                $endPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(23, 59, 59)->format('Y-m-d H:i:s'));
            } else {
                /** Lấy beginTime và endTime nowday */
                $beginPreDay = mktime(0, 0, 0);
                $endPreDay = mktime(23, 59, 59);
            }
            ReportTopup::deleteAll(['between', 'report_date', $beginPreDay, $endPreDay]);
            static::log("Deleted report topup date:" . date("d-m-Y H:i:s", $beginPreDay) . ' timestamp:' . $beginPreDay);

            $sites = Site::findAll(['status' => Site::STATUS_ACTIVE]);

            if (!$sites) {
                $transaction->rollBack();
                static::log('\n****** ERROR! Report Revenues Fail: Site ******');
            }
            /** @var  $site Site */
            foreach ($sites as $site) {
                $lstType = SubscriberTransaction::listWhitelistTypes();
                if (!$lstType) {
                    break;
                }

                foreach ($lstType as $key => $value) {
                    $lstTypeCard = SubscriberTransaction::listTopupChannelType();
                    if (!$lstTypeCard) {
                        break;
                    }

                    foreach ($lstTypeCard as $channel => $value) {
                        switch ($channel) {
                            case SubscriberTransaction::CHANNEL_TYPE_VOUCHER:
                                $total_subscriber = SubscriberTransaction::find()
                                    ->select('subscriber_id')
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_VOUCHER])
                                    ->andWhere(['white_list' => $key])
                                    ->distinct()
                                    ->count();
                                $total_revenue = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_VOUCHER])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_SUCCESS])
                                    ->sum('cost');
                                $total_revenue_pending = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_VOUCHER])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_PENDING])
                                    ->sum('cost');
                                $total_revenue_error = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_VOUCHER])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_FAIL])
                                    ->sum('cost');
                                break;
                            case SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VIETTEL:
                                $total_subscriber = SubscriberTransaction::find()
                                    ->select('subscriber_id')
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_VOUCHER_PHONE])
                                    ->andWhere(['channel' => SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VIETTEL])
                                    ->andWhere(['white_list' => $key])
                                    ->distinct()
                                    ->count();
                                $total_revenue = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_VOUCHER_PHONE])
                                    ->andWhere(['channel' => SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VIETTEL])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_SUCCESS])
                                    ->sum('cost');
                                $total_revenue_pending = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_VOUCHER_PHONE])
                                    ->andWhere(['channel' => SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VIETTEL])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_PENDING])
                                    ->sum('cost');
                                $total_revenue_error = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_VOUCHER_PHONE])
                                    ->andWhere(['channel' => SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VIETTEL])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_FAIL])
                                    ->sum('cost');
                                break;
                            case SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VINAPHONE:
                                $total_subscriber = SubscriberTransaction::find()
                                    ->select('.subscriber_id')
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_VOUCHER_PHONE])
                                    ->andWhere(['channel' => SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VINAPHONE])
                                    ->andWhere(['white_list' => $key])
                                    ->distinct()
                                    ->count();
                                $total_revenue = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_VOUCHER_PHONE])
                                    ->andWhere(['channel' => SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VINAPHONE])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_SUCCESS])
                                    ->sum('cost');
                                $total_revenue_pending = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_VOUCHER_PHONE])
                                    ->andWhere(['channel' => SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VINAPHONE])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_PENDING])
                                    ->sum('cost');
                                $total_revenue_error = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['.type' => SubscriberTransaction::TYPE_VOUCHER_PHONE])
                                    ->andWhere(['channel' => SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VINAPHONE])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_FAIL])
                                    ->sum('cost');
                                break;
                            case SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_MOBIPHONE:
                                $total_subscriber = SubscriberTransaction::find()
                                    ->select('subscriber_id')
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_VOUCHER_PHONE])
                                    ->andWhere(['channel' => SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_MOBIPHONE])
                                    ->andWhere(['white_list' => $key])
                                    ->distinct()
                                    ->count();
                                $total_revenue = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_VOUCHER_PHONE])
                                    ->andWhere(['channel' => SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_MOBIPHONE])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_SUCCESS])
                                    ->sum('cost');
                                $total_revenue_pending = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_VOUCHER_PHONE])
                                    ->andWhere(['channel' => SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_MOBIPHONE])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_PENDING])
                                    ->sum('cost');
                                $total_revenue_error = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_VOUCHER_PHONE])
                                    ->andWhere(['channel' => SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_MOBIPHONE])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_FAIL])
                                    ->sum('cost');
                                break;
                            case SubscriberTransaction::CHANNEL_TYPE_ATM:
                                $total_subscriber = SubscriberTransaction::find()
                                    ->select('subscriber_id')
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_TOPUP_ATM])
                                    ->andWhere(['white_list' => $key])
                                    ->distinct()
                                    ->count();
                                $total_revenue = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_TOPUP_ATM])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_SUCCESS])
                                    ->sum('cost');
                                $total_revenue_pending = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_TOPUP_ATM])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_PENDING])
                                    ->sum('cost');
                                $total_revenue_error = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_TOPUP_ATM])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_FAIL])
                                    ->sum('cost');
                                break;
                            default:
                                $total_subscriber = SubscriberTransaction::find()
                                    ->select('subscriber_id')
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_TOPUP_VISA])
                                    ->andWhere(['white_list' => $key])
                                    ->distinct()
                                    ->count();
                                $total_revenue = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_TOPUP_VISA])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_SUCCESS])
                                    ->sum('cost');
                                $total_revenue_pending = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_TOPUP_VISA])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_PENDING])
                                    ->sum('cost');
                                $total_revenue_error = SubscriberTransaction::find()
                                    ->where(['site_id' => $site->id])
                                    ->andWhere('transaction_time between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                                    ->andWhere(['type' => SubscriberTransaction::TYPE_TOPUP_VISA])
                                    ->andWhere(['white_list' => $key])
                                    ->andWhere(['status' => SubscriberTransaction::STATUS_FAIL])
                                    ->sum('cost');
                        }

                        $rp = new ReportTopup();
                        $rp->report_date = $beginPreDay;
                        $rp->site_id = $site->id;
                        $rp->channel = $channel;
                        $rp->revenue = $total_revenue;
                        $rp->count = $total_subscriber;
                        $rp->revenue_pending = $total_revenue_pending;
                        $rp->revenue_error = $total_revenue_error;
                        $rp->white_list = $key;
                        if (!$rp->save()) {
                            //var_dump($rp->getFirstErrors());
                            static::log('\n****** ERROR! Report Topup Daily Fail ******');
                            $transaction->rollBack();
                        }
                    }
                }
            }

//                $sql = "SELECT date(from_unixtime(transaction_time)) report_date,site_id, channel, sum(cost) revenue, count(id) cnt, white_list
//                    FROM subscriber_transaction
//                    where status = :status
//                    and transaction_time >= :beginPreDay
//                    and transaction_time <= :endPreDay
//                    and channel in (" . SubscriberTransaction::CHANNEL_TYPE_VOUCHER .
//                    "," . SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VIETTEL .
//                    "," . SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_MOBIPHONE .
//                    "," . SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VINAPHONE .
//                    ")
//                    and type in (" . SubscriberTransaction::TYPE_VOUCHER .
//                    "," . SubscriberTransaction::TYPE_VOUCHER_PHONE .
//                    ")
//                    and white_list = :white_list
//                    group by report_date, site_id, channel";
//
//                $connection = Yii::$app->getDb();
//                $command = $connection->createCommand($sql, [':status' => SubscriberTransaction::STATUS_SUCCESS,
//                    ':beginPreDay' => $beginPreDay,
//                    ':endPreDay' => $endPreDay,
//                    ':white_list' => $key]);
//                $result = $command->queryAll();
//                if ($result) {
//                    foreach ($result as $row) {
//                          $rp = new ReportTopup();
////                        $rp->report_date = strtotime($row['report_date']);
////                        $rp->site_id = $row['site_id'];
////                        $rp->channel = $row['channel'];
////                        $rp->revenue = $row['revenue'];
////                        $rp->count = $row['cnt'];
////                        $rp->white_list = $row['white_list'];
////                        if (!$rp->save()) {
////                            //var_dump($rp->getFirstErrors());
////                            echo '****** ERROR! Report Topup Daily Fail ******';
////                            $transaction->rollBack();
////                        }
//                    }
//                }

            $transaction->commit();
            static::log('\n****** Report Topup Done ******');

        } catch (Exception $e) {
            $transaction->rollBack();
            static::log('\n****** ERROR! Report Topup Fail Exception: ' . $e->getMessage() . '******');
        }
    }

    /**
     * Bao cao thue bao goi cuoc
     */
    public function actionReportServiceSubscriber($start_day = '')
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            static::log("Bao cao bat dau chay \n");

            if ($start_day != '') {
                $to_day = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(0, 0)->format('Y-m-d H:i:s'));
                $end_day = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(23, 59, 59)->format('Y-m-d H:i:s'));
                $to_day_date = DateTime::createFromFormat("dmY", $start_day)->setTime(0, 0)->format('Y-m-d H:i:s');
            } else {
                $to_day = strtotime("midnight", time());
                $end_day = strtotime("tomorrow", $to_day) - 1;
                $to_day_date = (new DateTime('now'))->setTime(0, 0)->format('Y-m-d H:i:s');
            }

            static::log("Thoi gian bat dau: $to_day : Thoi gian ket thuc: $end_day ");
            static::log("Chuyen sang ngay: $to_day_date \n");
            // xoa cot neu da chay truoc do de tranh sinh nhieu truong
            Yii::$app->db->createCommand()->delete('report_subscriber_service', ['report_date' => $to_day_date])->execute();

            $sites = Site::findAll(['status' => Site::STATUS_ACTIVE]); // loc cac site dang hoat dong

            if (!$sites) {
                // khong ton tai site nao hoat dong
                $transaction->rollBack();
                static::log('n****** ERROR! Report Service Subscriber Fail: Site ******');
            }
            /** @var  $site Site */
            foreach ($sites as $site) {
                static::log("****** Site $site->name ****** \n");
                $service = Service::findAll(['status' => [Service::STATUS_ACTIVE, Service::STATUS_PAUSE], 'site_id' => $site->id]); // tim cac goi cuoc
                if (!$service) {
                    continue;
                }
                /** @var  $package Service */
                foreach ($service as $package) {
                    static::log("****** Service $package->name ****** \n");
                    $lstType = SubscriberTransaction::listWhitelistTypes();
                    if (!$lstType) {
                        break;
                    }
                    foreach ($lstType as $key => $value) {
                        $total_register = SubscriberTransaction::find()
                            ->andWhere(['subscriber_transaction.site_id' => $site->id])
                            ->andWhere('subscriber_transaction.transaction_time >= :start')->addParams([':start' => $to_day])
                            ->andWhere('subscriber_transaction.transaction_time <= :end')->addParams([':end' => $end_day])
                            ->andWhere(['subscriber_transaction.type' => SubscriberTransaction::TYPE_REGISTER])
                            ->andWhere(['subscriber_transaction.status' => SubscriberTransaction::STATUS_SUCCESS])
                            ->andWhere(['subscriber_transaction.white_list' => $key])
                            ->andWhere(['subscriber_transaction.service_id' => $package->id])
                            ->count();
                        $total_retry = SubscriberTransaction::find()
                            ->andWhere(['subscriber_transaction.site_id' => $site->id])
                            ->andWhere('subscriber_transaction.transaction_time >= :start')->addParams([':start' => $to_day])
                            ->andWhere('subscriber_transaction.transaction_time <= :end')->addParams([':end' => $end_day])
                            ->andWhere(['subscriber_transaction.type' => SubscriberTransaction::TYPE_RENEW])
                            ->andWhere(['subscriber_transaction.status' => SubscriberTransaction::STATUS_SUCCESS])
                            ->andWhere(['subscriber_transaction.white_list' => $key])
                            ->andWhere(['subscriber_transaction.service_id' => $package->id])
                            ->count();
                        $total_expired = SubscriberServiceAsm::find()
                            ->andWhere(['subscriber_service_asm.site_id' => $site->id])
                            ->andWhere('subscriber_service_asm.expired_at >= :start')->addParams([':start' => $to_day])
                            ->andWhere('subscriber_service_asm.expired_at <= :end')->addParams([':end' => $end_day])
                            ->andWhere(['subscriber_service_asm.status' => SubscriberServiceAsm::STATUS_ACTIVE])
                            ->andWhere(['subscriber_service_asm.white_list' => $key])
                            ->andWhere(['subscriber_service_asm.service_id' => $package->id])
                            ->count();
                        $total_not_expiration = SubscriberServiceAsm::find()
                            ->andWhere(['subscriber_service_asm.site_id' => $site->id])
                            ->andWhere('subscriber_service_asm.expired_at > :end')->addParams([':end' => $end_day])
                            ->andWhere(['subscriber_service_asm.status' => SubscriberServiceAsm::STATUS_ACTIVE])
                            ->andWhere(['subscriber_service_asm.white_list' => $key])
                            ->andWhere(['subscriber_service_asm.service_id' => $package->id])
                            ->count();
                        $cps = ServiceCpAsm::find()
                            ->andWhere(['service_id' => $package->id])
                            ->all();
                        /** @var  $cp ServiceCpAsm */
                        foreach ($cps as $cp) {
                            static::log("****** Content Provider id = $cp->cp_id ****** \n");
                            if ($cp->status == ServiceCpAsm::STATUS_ACTIVE) {
                                /** trường hợp gói cước gắn với CP active */
                                /** @var  $rp ReportSubscriberService */
                                $rp = new ReportSubscriberService();
                                $rp->report_date = $to_day_date;
                                $rp->site_id = $site->id;
                                $rp->cp_id = $cp->cp_id;
                                $rp->white_list = $key;
                                $rp->service_id = $package->id;
                                $rp->subscriber_register = $total_register ? $total_register : 0;
                                $rp->subscriber_retry = $total_retry ? $total_retry : 0;
                                $rp->subscriber_expired = $total_expired ? $total_expired : 0;
                                $rp->subscriber_not_expiration = $total_not_expiration ? $total_not_expiration : 0;

                                if (!$rp->save()) {
                                    static::log("****** ERROR! Report Service Subscriber Fail ****** \n" . $rp->getErrors() . "\n");
                                    $transaction->rollBack();
                                }
                            } else {
                                /** trường hợp CP bị inactive nhưng gói cước vẫn hoạt động */
                                /** @var  $rp ReportSubscriberService */
                                $rp = new ReportSubscriberService();
                                $rp->report_date = $to_day_date;
                                $rp->site_id = $site->id;
                                $rp->cp_id = $cp->cp_id;
                                $rp->white_list = $key;
                                $rp->service_id = $package->id;
                                $rp->subscriber_register = 0;
                                $rp->subscriber_retry = 0;
                                $rp->subscriber_expired = 0;
                                $rp->subscriber_not_expiration = 0;

                                if (!$rp->save()) {
                                    static::log("****** ERROR! Report Service Subscriber Fail ****** \n" . $rp->getErrors() . "\n");
                                    $transaction->rollBack();
                                }
                            }

                        }
                    }
                }
            }
            $transaction->commit();
            static::log('****** Chay bao cao hoan thanh! ******');

        } catch (Exception $e) {
            $transaction->rollBack();
            static::log('****** LOI! Chay bao cao khong thanh cong: ' . $e->getMessage() . '******');
        }
    }

    /**
     * Thong ke doanh thu theo goi dich vu
     * @param string $start_day
     * @throws \yii\db\Exception
     */


    public function reportSubscriberNumber($beginPreDay, $endPreDay)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            static::log("Bao cao so luong thue bao bat dau chay \n");

            static::log("Thoi gian bat dau: $beginPreDay : Thoi gian ket thuc: $endPreDay \n");
            ReportSubscriberNumber::deleteAll(['between', 'report_date', $beginPreDay, $endPreDay]);
            static::log("Deleted report subscriber number from date:" . date("d-m-Y H:i:s", $beginPreDay) . ' timestamp: ' . $beginPreDay);

            $sites = Site::findAll(['status' => Site::STATUS_ACTIVE]);
            if (!$sites) {
                $transaction->rollBack();
                static::log("n****** ERROR! Report Subscriber Number Fail: Site ****** \n");
            }
            /** @var  $site Site */
            foreach ($sites as $site) {
                static::log("****** Site $site->name ****** \n");
//                $cityList = [];
                $cityList = City::findAll(['site_id' => $site->id]);

                $cityEmpty = City::createCityEmpty($site->id);
                array_push($cityList, $cityEmpty);

                if (!empty($cityList)) {

                    foreach ($cityList as $city) {
                        static::log("****** Province $city->ascii_name ****** \n");

                        $total_subscriber = Subscriber::find()
                            ->select('id')
                            ->andWhere(['site_id' => $site->id])
                            ->andWhere(['ip_to_location' => $city->code])
                            ->andWhere(['authen_type' => Subscriber::AUTHEN_TYPE_ACCOUNT])
                            ->andWhere(['type' => Subscriber::TYPE_USER])
                            ->andWhere(['<=', 'register_at', $endPreDay])
                            ->distinct('username')
                            ->count();

                        $subscriber_active = ContentViewReport::find()
                            ->select('content_view_report.subscriber_id')
                            ->innerJoin('subscriber', 'content_view_report.subscriber_id = subscriber.id')
                            ->andWhere(['subscriber.ip_to_location' => $city->code])
                            ->andWhere(['subscriber.type' => Subscriber::TYPE_USER])
                            ->andWhere(['content_view_report.site_id' => $site->id])
                            ->andWhere('content_view_report.view_date between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                            ->distinct('content_view_report.subscriber_id')
                            ->count();

                        $total_subscriber_destroy = Subscriber::find()
                            ->select('id')
                            ->andWhere(['site_id' => $site->id])
                            ->andWhere(['status' => Subscriber::STATUS_INACTIVE])
                            ->andWhere(['authen_type' => Subscriber::AUTHEN_TYPE_ACCOUNT])
                            ->andWhere(['type' => Subscriber::TYPE_USER])
                            ->andWhere(['ip_to_location' => $city->code])
                            ->andWhere(['<=', 'register_at', $endPreDay])
                            ->andWhere(['<=', 'updated_at', $endPreDay])
                            ->distinct('username')
                            ->count();

                        $subscriber_destroy = Subscriber::find()
                            ->select('id')
                            ->andWhere(['site_id' => $site->id])
                            ->andWhere(['ip_to_location' => $city->code])
                            ->andWhere(['status' => Subscriber::STATUS_INACTIVE])
                            ->andWhere('updated_at between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                            ->andWhere(['authen_type' => Subscriber::AUTHEN_TYPE_ACCOUNT])
                            ->andWhere(['type' => Subscriber::TYPE_USER])
                            ->andWhere(['<=', 'register_at', $endPreDay])
                            ->distinct('username')
                            ->count();

                        $subscriber_register_apps = Subscriber::find()
                            ->select('id')
                            ->andWhere(['site_id' => $site->id])
                            ->andWhere(['ip_to_location' => $city->code])
                            ->andWhere('register_at between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                            ->andWhere('channel in (:channelType)')->addParams([':channelType' => Subscriber::CHANNEL_TYPE_ANDROID_MOBILE . "," . Subscriber::CHANNEL_TYPE_IOS])
                            ->andWhere(['authen_type' => Subscriber::AUTHEN_TYPE_ACCOUNT])
                            ->andWhere(['type' => Subscriber::TYPE_USER])
                            ->distinct('username')
                            ->count();

                        $subscriber_register_web = Subscriber::find()
                            ->select('id')
                            ->andWhere(['site_id' => $site->id])
                            ->andWhere(['ip_to_location' => $city->code])
                            ->andWhere('register_at between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                            ->andWhere(['channel' => Subscriber::CHANNEL_TYPE_MOBILEWEB])
                            ->andWhere(['authen_type' => Subscriber::AUTHEN_TYPE_ACCOUNT])
                            ->andWhere(['type' => Subscriber::TYPE_USER])
                            ->distinct('username')
                            ->count();

                        $subscriber_register_smb = Subscriber::find()
                            ->select('id')
                            ->andWhere(['site_id' => $site->id])
                            ->andWhere(['ip_to_location' => $city->code])
                            ->andWhere('register_at between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                            ->andWhere(['channel' => Subscriber::CHANNEL_TYPE_ANDROID])
                            ->andWhere(['authen_type' => Subscriber::AUTHEN_TYPE_ACCOUNT])
                            ->andWhere(['type' => Subscriber::TYPE_USER])
                            ->distinct('username')
                            ->count();;

                        $subscriber_register = $subscriber_register_web + $subscriber_register_apps + $subscriber_register_smb;


                        $rsd = new ReportSubscriberNumber();
                        $rsd->report_date = $beginPreDay;
                        $rsd->site_id = $site->id;
                        $rsd->city = $city->code;
                        $rsd->subscriber_register_smb = $subscriber_register_smb;
                        $rsd->subscriber_register_apps = $subscriber_register_apps;
                        $rsd->subscriber_register_web = $subscriber_register_web;
                        $rsd->total_subscriber = $total_subscriber;
                        $rsd->subscriber_active = $subscriber_active;
                        $rsd->subscriber_register = $subscriber_register;
                        $rsd->total_subscriber_destroy = $total_subscriber_destroy;
                        $rsd->subscriber_destroy = $subscriber_destroy;
                        if (!$rsd->save()) {
                            static::log("****** ERROR! Report Subscriber Number Fail ****** \n" . $rsd->getErrors() . "\n");
                            $transaction->rollBack();
                        }
                    }
                }
            }

            $transaction->commit();
            echo "****** Report Subscriber Number Done ****** \n";
        } catch (\Exception $e) {
            echo "123";
            $transaction->rollBack();
            echo "****** ERROR! Report Subscriber Number Fail Exception ****** \n" . $e->getMessage() . "\n";
        }
    }

    public function actionSubscriberNumber($start_day = '')
    {

        if ($start_day != '') {
            $beginPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(0, 0)->format('Y-m-d H:i:s'));
            $endPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(23, 59, 59)->format('Y-m-d H:i:s'));
        } else {
            $beginPreDay = strtotime("midnight", time());
            $endPreDay = strtotime("tomorrow", $beginPreDay) - 1;
        }
        $this->reportSubscriberNumber($beginPreDay, $endPreDay);
    }

    public function actionSubscriberNumberYesterday()
    {
        $day = strtotime("midnight", time());
        $from_time = $day - 86400;
        $to_time = $day - 1;
        $this->reportSubscriberNumber($from_time, $to_time);
    }


    public function actionReportCampaign($start_day = '')
    {
        // chi tiết áp dụng khuyến mại
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($start_day != '') {
                $beginPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(0, 0)->format('Y-m-d H:i:s'));
                $endPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(23, 59, 59)->format('Y-m-d H:i:s'));
            } else {
                /** Lấy beginTime và endTime nowday */
                $beginPreDay = mktime(0, 0, 0);
                $endPreDay = mktime(23, 59, 59);
            }
            ReportCampaign::deleteAll(['between', 'report_date', $beginPreDay, $endPreDay]);
            echo "Deleted report campaign date:" . date("d-m-Y H:i:s", $beginPreDay) . ' timestamp:' . $beginPreDay;

            $sites = Site::findAll(['status' => Site::STATUS_ACTIVE]);

            if (!$sites) {
                $transaction->rollBack();
                echo 'n****** ERROR! Report Campaign Fail: Site ******';
            }
            /** @var  $site Site */
            foreach ($sites as $site) {
                $campaigns = Campaign::find()
                    ->andWhere(['<>', 'campaign.status', Campaign::STATUS_DELETE])
                    ->andFilterWhere(['campaign.site_id' => $site->id])
                    ->all();
                if (!$campaigns) {
                    continue;
                }

                foreach ($campaigns as $campaign) {
                    /** @var  $campaign Campaign */
                    $lstType = SubscriberTransaction::listWhitelistTypes();
                    if (!$lstType) {
                        break;
                    }

                    foreach ($lstType as $key => $value) {
                        $count = CampaignPromotion::find()
                            ->andWhere(['campaign_id' => $campaign->id])
                            ->andWhere(['status' => CampaignPromotion::STATUS_ACTIVE])
                            ->count();
                        $total_username = LogCampaignPromotion::find()
                            ->andWhere(['site_id' => $site->id])
                            ->andWhere('created_at between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                            ->andWhere(['status' => LogCampaignPromotion::STATUS_ACTIVE])
                            ->andWhere(['campaign_id' => $campaign->id])
                            ->andWhere(['white_list' => $key])
                            ->andWhere(['is not', 'subscriber_name', Null])
                            ->count('id');
                        $total_mac_address = LogCampaignPromotion::find()
                            ->andWhere(['site_id' => $site->id])
                            ->andWhere('created_at between :beginPreDay and :endPreDay')->addParams([':beginPreDay' => $beginPreDay, ':endPreDay' => $endPreDay])
                            ->andWhere(['status' => LogCampaignPromotion::STATUS_ACTIVE])
                            ->andWhere(['campaign_id' => $campaign->id])
                            ->andWhere(['white_list' => $key])
                            ->andWhere(['is not', 'mac_address', Null])
                            ->count('id');
                        /** @var  $rp ReportCampaign */
                        $rp = new ReportCampaign();
                        $rp->report_date = $beginPreDay;
                        $rp->site_id = $site->id;
                        $rp->campaign_id = $campaign->id;
                        $rp->white_list = $key;
                        if ($count > 1) {
                            $rp->total_username = $total_username / $count ? abs($total_username / $count) : 0;
                            $rp->total_mac_address = $total_mac_address / $count ? abs($total_mac_address / $count) : 0;
                        } else {
                            $rp->total_username = $total_username ? abs($total_username) : 0;
                            $rp->total_mac_address = $total_mac_address ? abs($total_mac_address) : 0;
                        }
                        if (!$rp->save()) {
                            echo '****** ERROR! Report Campaign Fail ******';
                            $transaction->rollBack();
                        }
                    }
                }
            }
            $transaction->commit();
            echo '****** Report Campaign Done ******';
        } catch (Exception $e) {
            $transaction->rollBack();
            echo '****** ERROR! Report Campaign Fail Exception: ' . $e->getMessage() . '******';
        }
    }

    public function actionUpdateReportSubscriberNumber($start_day, $end_day)
    {
        for ($i = $start_day; $i < $end_day; $i += 86400) {
            $day = str_replace('-', '', date("d-m-Y", $i));
            $this->actionSubscriberNumber($day);
        }
    }

    public static function log($message)
    {
        echo date('Y-m-d H:i:s') . ": " . $message . PHP_EOL;
    }

    public function actionImportData($start_day = '')
    {
        if ($start_day != '') {
            $beginPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(0, 0)->format('Y-m-d H:i:s'));
            $endPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(23, 59, 59)->format('Y-m-d H:i:s'));
        } else {
            $beginPreDay = strtotime("midnight", time());
            $endPreDay = strtotime("tomorrow", $beginPreDay) - 1;
        }
        $beginPreDay = $beginPreDay - 86400;
        static::log("Import data content view log from $beginPreDay to $endPreDay");

        $connection = Yii::$app->db;
        $connection->createCommand('TRUNCATE TABLE  content_view_report')->execute();
        $connection->createCommand('alter table content_view_report AUTO_INCREMENT = 1')->execute();

        static::log("Truncate data done");
        $inserts = [];
        $countTotal = 0;
        foreach (ContentViewLog::find()
                     ->andFilterWhere(['>=', 'view_date', $beginPreDay])
                     ->andFilterWhere(['<=', 'view_date', $endPreDay])->each(100) as $row) {
            /** @var $row ContentViewLog */
            $countTotal++;
            $inserts[] = [$row->subscriber_id, $row->content_id, $row->category_id, $row->created_at, $row->ip_address,
                $row->status, $row->type, $row->record_type, $row->channel, $row->site_id, $row->started_at,
                $row->stopped_at, $row->view_date, $row->view_count, $row->cp_id, $row->view_time_date];

            if ($countTotal % 1000 == 0) {
                $connection->createCommand()->batchInsert(ContentViewReport::tableName(),
                    ['subscriber_id', 'content_id', 'category_id', 'created_at', 'ip_address', 'status', 'type',
                        'record_type', 'channel', 'site_id', 'started_at', 'stopped_at', 'view_date', 'view_count',
                        'cp_id', 'view_time_date'], $inserts)->execute();
                $inserts = [];
                static::log("Imported $countTotal records");
            }
        }
        $connection->createCommand()->batchInsert(ContentViewReport::tableName(),
            ['subscriber_id', 'content_id', 'category_id', 'created_at', 'ip_address', 'status', 'type',
                'record_type', 'channel', 'site_id', 'started_at', 'stopped_at', 'view_date', 'view_count',
                'cp_id', 'view_time_date'], $inserts)->execute();

        static::log("Imported $countTotal records");
    }

    public function actionReportSmartboxInitialization($start_day = '')
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($start_day != '') {
                $beginPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(0, 0)->format('Y-m-d H:i:s'));
                $endPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(23, 59, 59)->format('Y-m-d H:i:s'));
            } else {
                /** Lấy beginTime và endTime nowday */
                $beginPreDay = mktime(0, 0, 0);
                $endPreDay = mktime(23, 59, 59);
            }
            ReportSmartboxInitialization::deleteAll(['between', 'report_date', $beginPreDay, $endPreDay]);
            echo "Deleted report smartbox initialization date:" . date("d-m-Y H:i:s", $beginPreDay) . ' timestamp:' . $beginPreDay;

            $sites = Site::findAll(['status' => Site::STATUS_ACTIVE]);

            if (!$sites) {
                $transaction->rollBack();
                echo 'n****** ERROR! Report report smartbox initialization Fail: Site ******';
            }
            /** @var  $site Site */
            foreach ($sites as $site) {
                $cityList = City::findAll(['site_id' => $site->id]);

                $cityEmpty = City::createCityEmpty($site->id);
                array_push($cityList, $cityEmpty);

                if (!empty($cityList)) {

                    foreach ($cityList as $city) {
                        echo '****** Province ---- ' . $city->ascii_name . ' ****** \n';
                        foreach (Device::getListAvailableDeviceTypesValue() as $type) {

                            echo '****** Province type device ' . $type . ' ****** \n';
                            $totals = Subscriber::find()
                                ->innerJoin('device', 'device.device_id = subscriber.machine_name')
                                ->andWhere(['between', 'subscriber.register_at', $beginPreDay, $endPreDay])
                                ->andWhere(['subscriber.type' => Subscriber::TYPE_USER])
                                ->andWhere(['subscriber.site_id' => $site->id])
                                ->andWhere(['subscriber.ip_to_location' => $city->code])
                                ->andWhere(['device.device_type' => $type])
                                ->count();

                            if (!$totals) {
                                $totals = 0;
                            }

                            $model = new ReportSmartboxInitialization();
                            $model->report_date = $beginPreDay;
                            $model->site_id = $site->id;
                            $model->city = $city->code;
                            $model->type_model = $type;
                            $model->total = $totals;
                            $model->save();
                        }
                    }
                }
            }
            $transaction->commit();
            echo '****** Report smartbox initialization Done ******';
        } catch (Exception $e) {
            $transaction->rollBack();
            echo '****** ERROR! report smartbox initialization Fail Exception: ' . $e->getMessage() . '******';
        }
    }

    public function actionSubscriberInitialize($begin = '')
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            static::log("Bao cao thue bao khơi tao bat dau chay \n");
            /** Lấy beginTime và endTime nowday */
            $beginPreDay = mktime(0, 0, 0);
            $endPreDay = mktime(23, 59, 59);

            static::log("Thoi gian bat dau: $beginPreDay : Thoi gian ket thuc: $endPreDay \n");
            ReportSubscriberInitialize::deleteAll(['between', 'report_date', $beginPreDay, $endPreDay]);
            static::log("Deleted Report Subscriber Initializer date:" . date("d-m-Y H:i:s", $beginPreDay) . ' timestamp: ' . $beginPreDay);

            $sql = "CREATE OR REPLACE VIEW view_report_subscriber_initialize  AS
                SELECT DISTINCT  subscriber_service_asm.site_id,subscriber_service_asm.subscriber_id, subscriber_service_asm.service_id,subscriber_service_asm.created_at,subscriber.ip_to_location,subscriber.initialized_at,device.device_type
                FROM subscriber_service_asm 
                INNER JOIN subscriber ON subscriber_service_asm.subscriber_id = subscriber.id
                INNER JOIN device ON device.device_id= subscriber.machine_name
                AND subscriber_service_asm.created_at >= :beginPreDay
                AND subscriber_service_asm.created_at < :endPreDay
                AND subscriber.type = :subscriberType
                AND subscriber.initialized_at = :initialize
                OR subscriber.initialized_at >= :beginPreDay
                GROUP BY subscriber_service_asm.subscriber_id
                ORDER BY subscriber_service_asm.created_at";
            $connection = Yii::$app->getDb();
            $connection->createCommand($sql, [
                ':beginPreDay' => $beginPreDay,
                ':endPreDay' => $endPreDay,
                ':subscriberType' => Subscriber::TYPE_USER,
                ':initialize' => Subscriber::NOT_INITIALIZED,
            ])->execute();

            $deviceTypeList = Device::listDeviceTypes();
            if (!$deviceTypeList) {
                $transaction->rollBack();
                static::log("n****** ERROR! Report Subscriber Initializer Fail: Site ****** \n");
            }

            foreach ($deviceTypeList as $key => $value) {
                static::log("****** Device $value ****** \n");

                $sites = Site::findAll(['status' => Site::STATUS_ACTIVE]);
                if (!$sites) {
                    break;
                }
                /** @var  $site Site */
                foreach ($sites as $site) {
                    static::log("****** Site $site->name ****** \n");

                    $service = Service::findAll(['status' => [Service::STATUS_ACTIVE, Service::STATUS_PAUSE], 'site_id' => $site->id]); // tim cac goi cuoc
                    if (!$service) {
                        continue;
                    }
                    /** @var  $package Service */
                    foreach ($service as $package) {
                        static::log("****** Service $package->name ****** \n");

                        $cityList = City::findAll(['site_id' => $site->id]);
                        $cityEmpty = City::createCityEmpty($site->id);
                        array_push($cityList, $cityEmpty);

                        if (!empty($cityList)) {

                            foreach ($cityList as $city) {
                                static::log("****** Province $city->ascii_name ****** \n");

                                $sql1 = "SELECT COUNT(subscriber_id) as total_subscriber 
                                        FROM view_report_subscriber_initialize
                                        WHERE site_id = :site
                                        AND service_id = :service
                                        AND ip_to_location = :city
                                        AND device_type = :deviceType";
                                $connection1 = Yii::$app->getDb();
                                $command1 = $connection1->createCommand($sql1, [
                                    ':site' => $site->id,
                                    ':service' => $package->id,
                                    ':city' => $city->code,
                                    ':deviceType' => $key]);
                                $result = $command1->queryAll();
                                foreach ($result as $row) {
                                    $total_subscriber = $row['total_subscriber'];
                                }

                                $rp = new ReportSubscriberInitialize();
                                $rp->report_date = $beginPreDay;
                                $rp->site_id = $site->id;
                                $rp->device_type = $key;
                                $rp->service_id = $package->id;
                                $rp->ip_to_location = $city->code;
                                $rp->total_subscriber_initialize = $total_subscriber;
                                if (!$rp->save()) {
                                    static::log("****** ERROR! Report Subscriber Initializer Fail ****** \n" . $rp->getErrors() . "\n");
                                    $transaction->rollBack();
                                }
                            }
                        }
                    }
                }
            }
            $transaction->commit();
            static::log("****** Report Subscriber Initialize Done ****** \n");
        } catch (Exception $e) {
            $transaction->rollBack();
            echo '****** ERROR! Report Subscriber Initialize Fail Exception: ' . $e->getMessage() . '******';
        }
    }

    public function actionSubscriberInitializeBegin()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            static::log("Bao cao thue bao khơi tao bat dau chay \n");
            /** Lấy beginTime và endTime nowday */
            $endPreDay = mktime(0, 0, 0);
            $timeDay = $endPreDay - 1;

            static::log("Thoi gian ket thuc: $endPreDay \n");
            ReportSubscriberInitialize::deleteAll(['<', 'report_date', $endPreDay]);
            static::log("Deleted Report Subscriber Initializer before date:" . date("d-m-Y H:i:s", $endPreDay) . ' timestamp: ' . $endPreDay);

            $sql = "CREATE OR REPLACE VIEW view_report_subscriber_initialize  AS
                SELECT DISTINCT  subscriber_service_asm.site_id,subscriber_service_asm.subscriber_id, subscriber_service_asm.service_id,subscriber_service_asm.created_at,subscriber.ip_to_location,subscriber.initialized_at,device.device_type
                FROM subscriber_service_asm 
                INNER JOIN subscriber ON subscriber_service_asm.subscriber_id = subscriber.id
                INNER JOIN device ON device.device_id = subscriber.machine_name
                AND subscriber_service_asm.created_at < :endPreDay
                AND subscriber.type = :subscriberType
                GROUP BY subscriber_service_asm.subscriber_id
                ORDER BY subscriber_service_asm.created_at";
            $connection = Yii::$app->getDb();
            $connection->createCommand($sql, [
                ':endPreDay' => $endPreDay,
                ':subscriberType' => Subscriber::TYPE_USER,
            ])->execute();

            $deviceTypeList = Device::listDeviceTypes();
            if (!$deviceTypeList) {
                $transaction->rollBack();
                static::log("n****** ERROR! Report Subscriber Initializer Fail: Site ****** \n");
            }

            foreach ($deviceTypeList as $key => $value) {
                static::log("****** Device $value ****** \n");

                $sites = Site::findAll(['status' => Site::STATUS_ACTIVE]);
                if (!$sites) {
                    break;
                }
                /** @var  $site Site */
                foreach ($sites as $site) {
                    static::log("****** Site $site->name ****** \n");

                    $service = Service::findAll(['status' => [Service::STATUS_ACTIVE, Service::STATUS_PAUSE], 'site_id' => $site->id]); // tim cac goi cuoc
                    if (!$service) {
                        continue;
                    }
                    /** @var  $package Service */
                    foreach ($service as $package) {
                        static::log("****** Service $package->name ****** \n");

                        $cityList = City::findAll(['site_id' => $site->id]);
                        $cityEmpty = City::createCityEmpty($site->id);
                        array_push($cityList, $cityEmpty);

                        if (!empty($cityList)) {

                            foreach ($cityList as $city) {
                                static::log("****** Province $city->ascii_name ****** \n");

                                $sql1 = "SELECT COUNT(subscriber_id) as total_subscriber 
                                        FROM view_report_subscriber_initialize
                                        WHERE site_id = :site
                                        AND service_id = :service
                                        AND ip_to_location = :city
                                        AND device_type = :deviceType";
                                $connection1 = Yii::$app->getDb();
                                $command1 = $connection1->createCommand($sql1, [
                                    ':site' => $site->id,
                                    ':service' => $package->id,
                                    ':city' => $city->code,
                                    ':deviceType' => $key]);
                                $result = $command1->queryOne();
                                $total_subscriber = $result['total_subscriber'];
                                static::log("$total_subscriber \n");

                                $rp = new ReportSubscriberInitialize();
                                $rp->report_date = $timeDay;
                                $rp->site_id = $site->id;
                                $rp->device_type = $key;
                                $rp->service_id = $package->id;
                                $rp->ip_to_location = $city->code;
                                $rp->total_subscriber_initialize = $total_subscriber;
                                if (!$rp->save()) {
                                    static::log("****** ERROR! Report Subscriber Initializer Fail ****** \n" . $rp->getErrors() . "\n");
                                    $transaction->rollBack();
                                }
                            }
                        }
                    }
                }
            }
            $transaction->commit();
            static::log("****** Report Subscriber Initialize Done ****** \n");
        } catch (Exception $e) {
            $transaction->rollBack();
            echo '****** ERROR! Report Subscriber Initialize Fail Exception: ' . $e->getMessage() . '******';
        }
    }

    public function actionReportSubscriberUsing($start_day = '')
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            /** Lấy beginTime và endTime nowday */
            if ($start_day != '') {
                $beginPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(0, 0)->format('Y-m-d H:i:s'));
                $endPreDay = strtotime(DateTime::createFromFormat("dmY", $start_day)->setTime(23, 59, 59)->format('Y-m-d H:i:s'));
            } else {
                /** Lấy beginTime và endTime nowday */
                $beginPreDay = mktime(0, 0, 0);
                $endPreDay = mktime(23, 59, 59);
            }

            ReportSubscriberUsing::deleteAll(['between', 'report_date', $beginPreDay, $endPreDay]);
            echo "Deleted Report Subscriber Using date:" . date("d-m-Y H:i:s", $beginPreDay) . ' timestamp:' . $beginPreDay;
            $sites = Site::findAll(['status' => Site::STATUS_ACTIVE]);

            if (!$sites) {
                $transaction->rollBack();
                echo 'n****** ERROR! Report report Subscriber Using Fail: Site ******';
            }
            /** @var  $site Site */
            foreach ($sites as $site) {
                $cityList = City::findAll(['site_id' => $site->id]);

                $cityEmpty = City::createCityEmpty($site->id);
                array_push($cityList, $cityEmpty);

                if (!empty($cityList)) {

                    foreach ($cityList as $city) {
                        echo '****** Province ---- ' . $city->ascii_name . ' ****** \n';
                        foreach (Device::getListAvailableDeviceTypesValue() as $type) {
                            echo '****** Province type device ' . $type . ' ****** \n';
                            $services = Service::find()
                                ->andWhere(['service_type' => Service::TYPE_SERVICE_USER])
                                ->all();
                            if (!$services) {
                                echo '****** Notfound service ****** \n';
                            } else {
                                /** @var Service $service */
                                foreach ($services as $service) {
                                    $subscriber_total = Subscriber::find()
                                        ->innerJoin('subscriber_service_asm', 'subscriber_service_asm.subscriber_id = subscriber.id')
                                        ->innerJoin('device', 'device.device_id = subscriber.machine_name')
                                        ->andWhere(['>=', 'subscriber_service_asm.expired_at', $beginPreDay])
                                        ->andWhere(['subscriber_service_asm.status' => SubscriberServiceAsm::STATUS_ACTIVE])
                                        ->andWhere(['subscriber.type' => Subscriber::TYPE_USER])
                                        ->andWhere(['subscriber.site_id' => $site->id])
                                        ->andWhere(['subscriber.ip_to_location' => $city->code])
                                        ->andWhere(['device.device_type' => $type])
                                        ->andWhere(['subscriber_service_asm.service_id' => $service->id])
                                        ->groupBy('subscriber_service_asm.subscriber_id')
                                        ->count();
                                    $service_total = SubscriberServiceAsm::find()
                                        ->innerJoin('subscriber', 'subscriber_service_asm.subscriber_id = subscriber.id')
                                        ->innerJoin('device', 'device.device_id = subscriber.machine_name')
                                        ->andWhere(['subscriber_service_asm.status' => SubscriberServiceAsm::STATUS_ACTIVE])
                                        ->andWhere(['>=', 'subscriber_service_asm.expired_at', $beginPreDay])
                                        ->andWhere(['subscriber.type' => Subscriber::TYPE_USER])
                                        ->andWhere(['subscriber.site_id' => $site->id])
                                        ->andWhere(['subscriber.ip_to_location' => $city->code])
                                        ->andWhere(['device.device_type' => $type])
                                        ->andWhere(['subscriber_service_asm.service_id' => $service->id])
                                        ->count();

                                    if ($service_total == 0 && $subscriber_total == 0) {
                                        continue;
                                    }

                                    $model = new ReportSubscriberUsing();
                                    $model->report_date = $beginPreDay;
                                    $model->site_id = $site->id;
                                    $model->city = $city->code;
                                    $model->type_model = $type;
                                    $model->subscriber_total = $subscriber_total;
                                    $model->service_total = $service_total;
                                    $model->service_id = $service->id;
                                    $model->save();
                                }
                            }
                        }
                    }
                }
            }
            $transaction->commit();
            echo '****** Report Subscriber Using Done ******';
        } catch (Exception $e) {
            $transaction->rollBack();
            echo '****** ERROR! Report Subscriber Using Fail Exception: ' . $e->getMessage() . '******';
        }
    }


    public function actionSubscriberExpired()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            static::log("Bao cao thue bao het han bat dau chay \n");
            /** Lấy beginTime và endTime nowday */
            $beginPreDay = mktime(0, 0, 0);
            $endPreDay = mktime(23, 59, 59);

            static::log("Thoi gian bat dau: $beginPreDay : Thoi gian ket thuc: $endPreDay \n");
            ReportSubscriberExpired::deleteAll(['between', 'report_date', $beginPreDay, $endPreDay]);
            static::log("Deleted Report Subscriber Expired date:" . date("d-m-Y H:i:s", $beginPreDay) . ' timestamp: ' . $beginPreDay);


            $deviceTypeList = Device::listDeviceTypes();
            if (!$deviceTypeList) {
                $transaction->rollBack();
                static::log("n****** ERROR! Report Subscriber Initializer Fail: Site ****** \n");
            }

            foreach ($deviceTypeList as $key => $value) {
                static::log("****** Device $value ****** \n");

                $sites = Site::findAll(['status' => Site::STATUS_ACTIVE]);
                if (!$sites) {
                    break;
                }
                /** @var  $site Site */
                foreach ($sites as $site) {
                    static::log("****** Site $site->name ****** \n");

                    $service = Service::findAll(['status' => [Service::STATUS_ACTIVE, Service::STATUS_PAUSE], 'site_id' => $site->id]); // tim cac goi cuoc
                    $serviceEmpty = Service::createServiceEmpty($site->id);
                    array_push($service, $serviceEmpty);
                    if (!$service) {
                        continue;
                    }
                    /** @var  $package Service */
                    foreach ($service as $package) {
                        static::log("****** Service $package->id ****** \n");

                        $cityList = City::findAll(['site_id' => $site->id]);
                        $cityEmpty = City::createCityEmpty($site->id);
                        array_push($cityList, $cityEmpty);

                        if (!empty($cityList)) {

                            foreach ($cityList as $city) {
                                static::log("****** Province $city->ascii_name ****** \n");

                                if ($package->id != 0) {
                                    $total_subscriber = SubscriberServiceAsm::find()
                                        ->select('subscriber_service_asm.subscriber_id')
                                        ->innerJoin('subscriber', 'subscriber_service_asm.subscriber_id = subscriber.id')
                                        ->innerJoin('device', 'device.device_id = subscriber.machine_name')
                                        ->where(['subscriber_service_asm.site_id' => $site->id])
                                        ->andWhere('subscriber_service_asm.expired_at >= :start')->addParams([':start' => $beginPreDay])
                                        ->andWhere('subscriber_service_asm.expired_at <= :end')->addParams([':end' => $endPreDay])
                                        ->andWhere(['subscriber_service_asm.status' => SubscriberServiceAsm::STATUS_ACTIVE])
                                        ->andWhere(['subscriber_service_asm.service_id' => $package->id])
                                        ->andWhere(['subscriber.ip_to_location' => $city->code])
                                        ->andWhere(['subscriber.type' => Subscriber::TYPE_USER])
                                        ->andWhere(['device.device_type' => $key])
                                        ->distinct()
                                        ->count();

                                    $total_service = SubscriberServiceAsm::find()
                                        ->innerJoin('subscriber', 'subscriber_service_asm.subscriber_id = subscriber.id')
                                        ->innerJoin('device', 'device.device_id = subscriber.machine_name')
                                        ->where(['subscriber_service_asm.site_id' => $site->id])
                                        ->andWhere('subscriber_service_asm.expired_at >= :start')->addParams([':start' => $beginPreDay])
                                        ->andWhere('subscriber_service_asm.expired_at <= :end')->addParams([':end' => $endPreDay])
                                        ->andWhere(['subscriber_service_asm.status' => SubscriberServiceAsm::STATUS_ACTIVE])
                                        ->andWhere(['subscriber_service_asm.service_id' => $package->id])
                                        ->andWhere(['subscriber.ip_to_location' => $city->code])
                                        ->andWhere(['subscriber.type' => Subscriber::TYPE_USER])
                                        ->andWhere(['device.device_type' => $key])
                                        ->count();

                                } else {
                                    // trường hợp không có đk gói cước để tìm đk tất cả của gói cước
                                    $total_subscriber = SubscriberServiceAsm::find()
                                        ->select('subscriber_service_asm.subscriber_id')
                                        ->innerJoin('subscriber', 'subscriber_service_asm.subscriber_id = subscriber.id')
                                        ->innerJoin('device', 'device.device_id = subscriber.machine_name')
                                        ->where(['subscriber_service_asm.site_id' => $site->id])
                                        ->andWhere('subscriber_service_asm.expired_at >= :start')->addParams([':start' => $beginPreDay])
                                        ->andWhere('subscriber_service_asm.expired_at <= :end')->addParams([':end' => $endPreDay])
                                        ->andWhere(['subscriber_service_asm.status' => SubscriberServiceAsm::STATUS_ACTIVE])
                                        ->andWhere(['subscriber.ip_to_location' => $city->code])
                                        ->andWhere(['subscriber.type' => Subscriber::TYPE_USER])
                                        ->andWhere(['device.device_type' => $key])
                                        ->distinct()
                                        ->count();

                                    $total_service = SubscriberServiceAsm::find()
                                        ->innerJoin('subscriber', 'subscriber_service_asm.subscriber_id = subscriber.id')
                                        ->innerJoin('device', 'device.device_id = subscriber.machine_name')
                                        ->where(['subscriber_service_asm.site_id' => $site->id])
                                        ->andWhere('subscriber_service_asm.expired_at >= :start')->addParams([':start' => $beginPreDay])
                                        ->andWhere('subscriber_service_asm.expired_at <= :end')->addParams([':end' => $endPreDay])
                                        ->andWhere(['subscriber_service_asm.status' => SubscriberServiceAsm::STATUS_ACTIVE])
                                        ->andWhere(['subscriber.ip_to_location' => $city->code])
                                        ->andWhere(['subscriber.type' => Subscriber::TYPE_USER])
                                        ->andWhere(['device.device_type' => $key])
                                        ->count();

                                }

                                $rp = new ReportSubscriberExpired();
                                $rp->report_date = $beginPreDay;
                                $rp->site_id = $site->id;
                                $rp->device_type = $key;
                                $rp->service_id = $package->id;
                                $rp->ip_to_location = $city->code;
                                $rp->total_subscriber_expired = $total_subscriber;
                                $rp->total_service_expired = $total_service;
                                if (!$rp->save()) {
                                    static::log("****** ERROR! Report Subscriber Expired Fail ****** \n" . $rp->getErrors() . "\n");
                                    $transaction->rollBack();
                                }
                            }
                        }
                    }
                }
            }
            $transaction->commit();
            static::log("****** Report Subscriber Expired Done ****** \n");
        } catch (Exception $e) {
            $transaction->rollBack();
            echo '****** ERROR! Report Subscriber Expired Fail Exception: ' . $e->getMessage() . '******';
        }
    }
}
