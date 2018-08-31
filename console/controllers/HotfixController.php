<?php
/**
 * Created by PhpStorm.
 * User: nhocconsanhdieu
 * Date: 30/6/2015
 * Time: 3:37 PM
 */

namespace console\controllers;

use api\models\SubscriberServiceAsm;
use api\models\SubscriberTransaction;
use common\helpers\FileUtils;
use common\models\CampaignPromotion;
use common\models\Content;
use common\models\ContentSiteAsm;
use common\models\Service;
use Yii;
use yii\console\Controller;

class HotfixController extends Controller
{

    public function actionFixTongBo()
    {
        $listParents = Content::findAll(['is_series' => Content::IS_SERIES]);

        foreach ($listParents as $k => $content) {
            echo 'ID: ' . $content->id . '. Content: ' . $content->display_name;

            $countEpisode = Content::find()->where(['parent_id' => $content->id, 'status' => Content::STATUS_ACTIVE])->count();

            if ($content->episode_count != $countEpisode) {
                echo '. Update so tap: ' . $content->episode_count . ' ====> ' . $countEpisode;
            } else {
                echo '. So tap: ' . $countEpisode;
            }

            $content->episode_count = $countEpisode;
            $content->save(false);

            echo "\r\n";

        }
    }

    public function actionFixTongBoNew()
    {
        $listParents = Content::findAll(['is_series' => Content::IS_SERIES]);

        foreach ($listParents as $content) {
            echo 'ID: ' . $content->id . '. Content: ' . $content->display_name;

            $listParentSites = ContentSiteAsm::findAll(['content_id' => $content->id]);
            $episodes = Content::find()->where(['parent_id' => $content->id, 'status' => Content::STATUS_ACTIVE])->all();

            foreach ($listParentSites as $csa) {

                echo '. Site: ' . $csa->site_id;

                $countEpisodes = ContentSiteAsm::find()
                    ->where(['IN', 'content_id', array_column($episodes, 'id')])
                    ->andWhere(['site_id' => $csa->site_id])
                    ->andWhere(['status' => ContentSiteAsm::STATUS_ACTIVE])
                    ->count();

                echo '. Tong so tap: ' . $countEpisodes;

                $csa->episode_count = $countEpisodes;
                $csa->save(false);

                echo "\r\n";
            }

        }
    }

    public function actionChangeTransaction()
    {
        $transactions = SubscriberTransaction::find()
            ->andWhere(['IN', 'type', [
                SubscriberTransaction::TYPE_RENEW,
                SubscriberTransaction::TYPE_REGISTER,
                SubscriberTransaction::TYPE_PROMOTION
            ]])
            ->all();
        if (!$transactions) {
            $this->infoLog('Khong tim thay transaction goi cuoc');
        } else {
            foreach ($transactions as $transaction) {
                /** @var  $transaction SubscriberTransaction */
                $this->infoLog('transaction_id: ' . $transaction->id);
                $service = Service::findOne($transaction->service_id);
                $number_month = round($service->period / 30);
                if ($number_month < 0) {
                    $number_month = 1;
                }
                $this->infoLog('So thang se thay: ' . $number_month);
                $transaction->number_month = $number_month;
                $transaction->update();
            }
        }
    }

    public function actionChangeSsa()
    {
        $ssas = SubscriberServiceAsm::find()
            ->andWhere(['status' => SubscriberServiceAsm::STATUS_ACTIVE])
            ->andWhere(['>', 'expired_at', time()])
            ->all();
        if (!$ssas) {
            $this->infoLogSsa('Khong tim thay ssa active');
        } else {
            foreach ($ssas as $ssa) {
                /** @var SubscriberServiceAsm $ssa */
                $this->infoLogSsa('ssa_id = ' . $ssa->id);
                $this->infoLogSsa('Dang tien hanh update cho thue bao id ' . $ssa->subscriber_id . ' goi cuoc id ' . $ssa->service_id);
                $service = Service::findOne($ssa->service_id);
                if (!$service) {
                    $this->infoLogSsa('Khong tim thay goi dc id = ' . $ssa->service_id);
                } else {
                    $number_month = round($service->period / 30);
                    if ($number_month < 0) {
                        $number_month = 1;
                    }
                    $this->infoLogSsa('So thang update = ' . $number_month);
                    $ssa->number_gift_month = $number_month;
                    if (!$ssa->update()) {
                        $this->infoLogSsa('Khong update thanh cong');
                    } else {
                        $this->infoLogSsa('Update thanh cong sub_id ' . $ssa->subscriber_id);
                    }
                }
            }
        }
    }

    public function actionChangeCampaignPromotion()
    {
        $logs = CampaignPromotion::find()
            ->andWhere(['type' => CampaignPromotion::TYPE_FREE_SERVICE])
            ->all();
        if (!$logs) {
            $this->infoCampainPromotion('Khong tim thay log');
        } else {
            /** @var CampaignPromotion $log */
            foreach ($logs as $log) {
                $this->infoCampainPromotion('Update lai so thang tang cho id = ' . $log->id);
                $service = Service::findOne($log->service_id);
                if (!$service) {
                    $this->infoCampainPromotion('Khong tim thay goi cuoc id = ' . $log->service_id);
                } else {
                    $number_month = round($service->period / 30);
                    if ($number_month < 0) {
                        $number_month = 1;
                    }
                    $this->infoCampainPromotion('So thang tang update lai = ' . $number_month);
                    $log->number_month = $number_month;
                    if (!$log->update()) {
                        $this->infoCampainPromotion('Khong update thanh cong');
                    } else {
                        $this->infoCampainPromotion('Cap nhat thanh cong');
                    }
                }
            }
        }
    }

    public function infoLog($txt)
    {
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/info_change_transaction.log'), date('d-m-Y HH:ii:ss ') . $txt);
    }

    public function infoLogSsa($txt)
    {
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/info_change_ssa.log'), date('d-m-Y HH:ii:ss ') . $txt);
    }

    public function infoCampainPromotion($txt)
    {
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/info_change_log_promotion.log'), date('d-m-Y HH:ii:ss ') . $txt);
    }
}
