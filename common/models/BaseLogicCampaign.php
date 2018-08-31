<?php
/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 28-Feb-17
 * Time: 1:48 PM
 */

namespace common\models;


use api\models\CampaignPromotion;
use common\helpers\SysCproviderService;
use Yii;
use yii\helpers\Json;

class BaseLogicCampaign
{
    //kiem tra nguoi dung co duoc khuyen mai voi campaign_id day

    public static function checkSubscriberPromotion($campaign = null, $subscriber = null, $site_id, $mac = null)
    {
        /** @var Campaign $campaign */
        /** @var Subscriber $subscriber */
        /** @var Device $mac */

        //tim chien dich dc khuyen mai cua khach hàng do
        if (!empty($campaign)) {
            if ($campaign->status == Campaign::STATUS_RUNNING_TEST) {
                $groupSub = CampaignGroupSubscriberAsm::find()
                    ->andWhere(['campaign_id' => $campaign->id])
                    ->andWhere(['site_id' => $site_id])
                    ->andWhere(['type' => CampaignGroupSubscriberAsm::TYPE_DEMO])
                    ->all();
            } else {
                $groupSub = CampaignGroupSubscriberAsm::find()
                    ->andWhere(['campaign_id' => $campaign->id])
                    ->andWhere(['site_id' => $site_id])
                    ->andWhere(['type' => CampaignGroupSubscriberAsm::TYPE_REAL])
                    ->all();
            }
            if (!empty($groupSub)) {
                foreach ($groupSub as $group) {
                    /** @var $group CampaignGroupSubscriberAsm */

                    $groupSubcriber = GroupSubscriber::findOne(['id' => $group->group_subscriber_id]);
                    if ($groupSubcriber->type_subsriber == GroupSubscriber::TYPE_USERNAME) {
                        $groupSubUserAsm = GroupSubscriberUserAsm::findOne(['group_subscriber_id' => $group->group_subscriber_id, 'username' => $subscriber->username]);
                    } elseif ($groupSubcriber->type_subsriber == GroupSubscriber::TYPE_MAC) {
                        if (isset($mac)) {
                            $groupSubUserAsm = GroupSubscriberUserAsm::findOne(['group_subscriber_id' => $group->group_subscriber_id, 'mac_address' => $mac->device_id]);
                        }
                    } else {
                        $groupSubUserAsm = null;
                    }
                    if (!empty($groupSubUserAsm)) {
                        return true;
                    }
                }
                return false;
            }
            return false;
        }
        return false;
    }


    //check khach hang da co goi cuoc service_id chua

    public static function checkSubscriberService($service_id = null, $subscriber_id = null, $site_id = null)
    {
        if (!empty($service_id) && !empty($subscriber_id) && !empty($site_id)) {
            $subscriberServiceAsm = SubscriberServiceAsm::findOne(['service_id' => $service_id, 'subscriber_id' => $subscriber_id,
                'site_id' => $site_id, 'status' => SubscriberServiceAsm::STATUS_ACTIVE]);
            if ($subscriberServiceAsm) {
                return true;
            }
            return false;
        }
        return false;
    }

    //check khach hang da mua noi dung le chua

    public static function checkSubscriberContent($content_id = null, $subscriber_id = null, $site_id = null)
    {
        if (!empty($content_id) && !empty($subscriber_id) && !empty($site_id)) {
            $subscriberContentAsm = SubscriberContentAsm::findOne(['content_id' => $content_id, 'site_id' => $site_id,
                'subscriber_id' => $subscriber_id, 'status' => SubscriberContentAsm::STATUS_ACTIVE]);
            if ($subscriberContentAsm) {
                return true;
            }
            return false;
        }
        return false;
    }

    //check dang ky moi thanh cong tai khoan tvod

    public static function checkRegisterNewSubscriberSuccess($device_id = null, $subscriber = null)
    {
        /** @var Subscriber $subscriber */
        if (!empty($subscriber)) {
            $subscriber_activity = SubscriberActivity::find()->andWhere(['subscriber_id' => $subscriber->id, 'type' => SubscriberActivity::ACTION_LOGIN, 'status' => SubscriberActivity::STATUS_SUCCESS])->count();
            if ($subscriber_activity <= 1) {
                return true;
            }
            return false;
        }
        return false;
    }

    //lay tat ca cac chien dich theo ngay kich hoat, do uu tien

    public static function getCampaign($site_id = null, $subscriber = null, $campaign = null, $number_promotion = 0)
    {
        /** @var Subscriber $subscriber */
        /** @var Campaign $campaign */
        if (!empty($subscriber) && !empty($campaign)) {
            $countSubscriberLog = LogCampaignPromotion::find()
                ->andWhere(['site_id' => $site_id])
                ->andWhere(['type' => $campaign->type])
                ->andWhere(['subscriber_name' => $subscriber->username])
                ->andWhere(['type_campaign' => $campaign->status])
                ->andWhere(['campaign_id' => $campaign->id]);
            $count = CampaignPromotion::find()
                ->andWhere(['campaign_id' => $campaign->id])
                ->andWhere(['status' => CampaignPromotion::STATUS_ACTIVE])
                ->count();
            if ($campaign->type == Campaign::TYPE_EVENT) {
                if ($countSubscriberLog->one()) {
                    if ($countSubscriberLog->one()->event_count == 1) {
                        return false;
                    }
                }
            }
            $countLog = $countSubscriberLog->count();
            if ($countLog == 0 && $count != 0) {
                return true;
            } elseif ($count != 0 && $countLog / $count < $number_promotion) {
                return true;
            }
            return false;
        }
        return false;
    }

    public static function getAllCampaign($site_id, $type)
    {
        $campaign = Campaign::find()
            ->andWhere(['status' => Campaign::STATUS_ACTIVATED])
            ->orWhere(['status' => Campaign::STATUS_RUNNING_TEST])
            ->andWhere(['site_id' => $site_id])
            ->andWhere(['IN', 'type', $type])
            ->andWhere('expired_at >= :expired_at', ['expired_at' => time()])
            ->andWhere('activated_at <= :activated_at', ['activated_at' => time()])
            ->orderBy(['activated_at' => SORT_DESC, 'priority' => SORT_DESC])->all();
        return $campaign;
    }

    //kiem tra dieu kien mua goi service_id thi duoc tang goi cuoc

    public static function checkCampaignCondition($campaign, $service, $subscriber, $device = null, $price, $number_month = null)
    {
        /** @var Campaign $campaign */
        /** @var Service $service */
        /** @var Subscriber $subscriber */

        if (!empty($device)) {
            if ($campaign->type == Campaign::TYPE_BOX_SERVICE || $campaign->type == Campaign::TYPE_BOX_CONTENT || $campaign->type == Campaign::TYPE_BOX_CASH) {
                return true;
            } elseif ($campaign->type == Campaign::TYPE_CASH_CASH || $campaign->type == Campaign::TYPE_CASH_CONTENT || $campaign->type == Campaign::TYPE_CASH_SERVICE) {
                return true;
            }
        }
        if (!empty($service)) {
            $campaignCondition = CampaignCondition::find()
                ->andWhere(['service_id' => $service->id])
                ->andWhere(['number_month' => $number_month])
                ->andWhere(['campaign_id' => $campaign->id])
                ->andWhere(['status' => CampaignCondition::STATUS_ACTIVE])
                ->all();
        } else {
            $campaignCondition = CampaignCondition::find()
                ->andWhere(['campaign_id' => $campaign->id])->andWhere(['status' => CampaignCondition::STATUS_ACTIVE])->all();
        }

        if (!empty($price)) {
            $campaignCondition = CampaignCondition::find()
                ->andWhere(['campaign_id' => $campaign->id])
                ->andWhere(['price_level' => $price])
                ->andWhere(['status' => CampaignCondition::STATUS_ACTIVE])
                ->all();
            if (!isset($campaignCondition) && empty($campaignCondition)) {
                Yii::info('khong tim thay dieu kien');
                return false;
            }
        }
        Yii::info('chemgiotype', $campaign->type);
        if ($campaign->type == Campaign::TYPE_REGISTER) {
            Yii::info('chemgio', $campaign->type);
            if (BaseLogicCampaign::checkRegisterNewSubscriberSuccess(null, $subscriber)) {
                return true;
            }
        }
        foreach ($campaignCondition as $item) {
            /** @var $item CampaignCondition */

            if ($campaign->type == Campaign::TYPE_EVENT) {
                if ($item->start_time <= time() && $item->end_time >= time()) {
                    return true;
                }
            } elseif ($campaign->type == Campaign::TYPE_SERVICE_CONTENT || $campaign->type == Campaign::TYPE_SERVICE_SERVICE || $campaign->type == Campaign::TYPE_SERVICE_TIME) {
                if ($item->service_id == $service->id) {
                    return true;
                }
            } elseif ($campaign->type == Campaign::TYPE_BOX_SERVICE || $campaign->type == Campaign::TYPE_BOX_CONTENT || $campaign->type == Campaign::TYPE_BOX_CASH) {
                return true;
            } elseif ($campaign->type == Campaign::TYPE_CASH_CASH || $campaign->type == Campaign::TYPE_CASH_CONTENT || $campaign->type == Campaign::TYPE_CASH_SERVICE) {
                return true;
            }
        }
        return false;
    }

    //buy goi co service_id gift time extend use service_id
    // subscriber: khach hang, service: goi cuoc, type: la loai khuyen mai

    public static function checkCampaign($site_id, $subscriber, $service = null, $type, $device = null, $price = null, $number_month = null)
    {

        /** @var Subscriber $subscriber */
        /** @var Service $service */

        $campaign = BaseLogicCampaign::getAllCampaign($site_id, $type);
        if (!empty($campaign)) {
            foreach ($campaign as $item) {
                /** @var $item Campaign */

                //check so lượt khuyến mãi
                if (BaseLogicCampaign::getCampaign($site_id, $subscriber, $item, $item->number_promotion)) {

                    //check dieu kieu khuyen mai
                    if (BaseLogicCampaign::checkCampaignCondition($item, $service, $subscriber, $device, $price, $number_month)) {

                        //check khách hàng có được hưởng khuyến mãi không
                        if ($item->type == Campaign::TYPE_REGISTER) {
                            return $item;
                        } else if (BaseLogicCampaign::checkSubscriberPromotion($item, $subscriber, $site_id, $device)) {
                            return $item;
                        }
                    }
                }
            }
        }
        return false;
    }

    //mua goi tang thoi gian su dung goi
    public static function buyPackageGiftTimePackage($site_id, $subscriber, $service, $type)
    {
        /** @var Subscriber $subcriber */
        /** @var Service $service */
        /** @var Campaign $campaign */
        $campaign = BaseLogicCampaign::checkCampaign($site_id, $subscriber, $service, $type);
        if ($campaign) {
            if (BaseLogicCampaign::addServiceToSubscriber($subscriber, $campaign, $site_id, $type, null)) {
                return true;
            }
            return false;
        }
        return false;
    }

    public static function BuyBoxPromotion($subscriber, $device = null, $site_id, $type, $service = null, $is_first = false, $number_month = null)
    {
        /** @var Subscriber $subscriber */
        /** @var Device $device */
        if ($subscriber->authen_type == Subscriber::AUTHEN_TYPE_MAC_ADDRESS) {
            return false;
        }
        if ($device && !$is_first) {
            $campaign = BaseLogicCampaign::getAllCampaign($site_id, $type);
            Yii::info('chien dich dang chay mua box khuyen mai');
            Yii::info($campaign);
            if ($campaign && !empty($campaign)) {
                foreach ($campaign as $item) {
                    $tp = BaseLogicCampaign::checkFirstLoginAndRevicedPromotion($subscriber, $device, $item, $site_id, $service, null);
                    if ($tp == true) {
                        return $item;
                    }
                }
            }
            return false;
        } else {
            /** @var Campaign $campaign */
            $campaign = BaseLogicCampaign::checkCampaign($site_id, $subscriber, $service, $type, $device, null, $number_month);
            if ($campaign) {
                if (BaseLogicCampaign::addServiceToSubscriber($subscriber, $campaign, $site_id, $device, null, $service)) {
                    return $campaign;
                }
                return false;
            }
        }
    }

    // nap tien khuyen mai
    public static function RechargePromotion($subscriber, $site_id, $type, $price)
    {
        /** @var Subscriber $subcriber */
        /** @var Campaign $campaign */
        $campaign = BaseLogicCampaign::checkCampaign($site_id, $subscriber, null, $type, null, $price);
        if ($campaign) {
            if (BaseLogicCampaign::addServiceToSubscriber($subscriber, $campaign, $site_id, null, $price)) {
                return $campaign;
            }
            return false;
        }
    }

    public static function checkFirstLoginAndRevicedPromotion($subscriber, $device, $campaign, $site_id, $service, $price)
    {
        /** @var Subscriber $subscriber */
        /** @var Device $device */
        /** @var Campaign $campaign */

        $check_first_login = BaseLogicCampaign::checkFirstLogin($subscriber->id, $device->site_id, $device->id, $campaign);
        if ($check_first_login == false) {
            return false;
        }
//        $type = [Campaign::TYPE_BOX_CASH, Campaign::TYPE_BOX_CONTENT, Campaign::TYPE_BOX_SERVICE];
        $check_incentived = LogCampaignPromotion::find()
            ->andWhere(['mac_address' => $device->device_id])
            ->andWhere(['campaign_id' => $campaign->id])
            ->andWhere(['type_campaign' => $campaign->status])
            ->one();
        Yii::info($check_incentived);
        $check_sub = LogCampaignPromotion::find()
            ->andWhere(['subscriber_id' => $subscriber->id])
            ->andWhere(['campaign_id' => $campaign->id])
            ->andWhere(['type_campaign' => $campaign->status])
            ->one();
        Yii::info($check_sub);
        if ($check_sub != null || $check_incentived != null) {
            Yii::info('Da nhan khuyen mai, Khong duoc nhan tiep');
            return false;
        }

        if (BaseLogicCampaign::getCampaign($site_id, $subscriber, $campaign, $campaign->number_promotion)) {

            //check dieu kieu khuyen mai
            if (BaseLogicCampaign::checkCampaignCondition($campaign, $service, $subscriber, $device, $price)) {

                //check khách hàng có được hưởng khuyến mãi không
                if (BaseLogicCampaign::checkSubscriberPromotion($campaign, $subscriber, $site_id, $device)) {
                    if (BaseLogicCampaign::addServiceToSubscriber($subscriber, $campaign, $site_id, $device, null)) {
                        return true;
                    }
                    return false;
                }
            }
        }
    }

    public static function checkFirstLogin($id_sub, $id_site, $id_device, $campaign)
    {
        /** @var Campaign $campaign */
        $check = SubscriberActivity::find()
            ->andWhere(['device_id' => $id_device])
            ->andWhere(['site_id' => $id_site])
            ->andWhere(['action' => SubscriberActivity::ACTION_LOGIN])
            ->andWhere(['status' => SubscriberActivity::STATUS_SUCCESS])
            ->andWhere(['type_subscriber' => Subscriber::AUTHEN_TYPE_ACCOUNT])
            ->andWhere('created_at <= :end', [':end' => $campaign->expired_at])
            ->andWhere('created_at >= :start', [':start' => $campaign->updated_at_campaign])
            ->limit(2)
            ->all();
        $n = count($check);
        Yii::info('So lan dang nhap tren box: ' . $n);
        if ($n > 1) {
            Yii::info('Khong phai lan dau');
            return false;
        }
        if ($check && !empty($check)) {
            foreach ($check as $item) {
                /** @var SubscriberActivity $item */
                if ($item->subscriber_id == $id_sub) {
                    Yii::info($item);
                    Yii::info('sub_id = ' . $id_sub . ' Lan dau dang nhap');
                    return true;
                } else {
                    Yii::info('Khong phai lan dau');
                    return false;
                }
            }
        } else {
            Yii::info('khong tim thay record subscriber_activity nao');
            return true;
        }

    }

    public static function createLog($subscriber, $campaign, $device = null, $site_id, $id_campaignPromotion)
    {
        /** @var Campaign $campaign */
        /** @var Device $device */
        /** @var Subscriber $subscriber */
        $logCampaign = new LogCampaignPromotion();
        if (!empty($device)) {
            $logCampaign->device_id = $device->id;
            $logCampaign->mac_address = $device->device_id;
        }
        if ($campaign->type == Campaign::TYPE_EVENT) {
            $logCampaign->event_count = 1;
        } else {
            $logCampaign->event_count = 0;
        }
        $logCampaign->campaign_id = $campaign->id;
        $logCampaign->campaign_name = $campaign->name;
        $logCampaign->site_id = $site_id;
        $logCampaign->type = $campaign->type;
        $logCampaign->subscriber_id = $subscriber->id;
        $logCampaign->white_list = $subscriber->whitelist;
        $logCampaign->type_campaign = $campaign->status;
        $logCampaign->subscriber_name = $subscriber->username;
        $logCampaign->campaign_promotion_id = $id_campaignPromotion;
        $logCampaign->status = LogCampaignPromotion::STATUS_ACTIVE;
        if ($logCampaign->save()) {
            return true;
        }
        Yii::info(' Khong luu log thanh cong');
        Yii::info($logCampaign->getErrors());
        return false;
    }

    //mua goi
    public static function addServiceToSubscriber($subscriber, $campaign, $site_id, $device = null, $price = null, $service = null)
    {
        /** @var  Campaign $campaign */
        $all_promotion = CampaignPromotion::find()
            ->andWhere(['campaign_id' => $campaign->id])
            ->andWhere(['status' => CampaignPromotion::STATUS_ACTIVE])
            ->orderBy(['honor' => SORT_DESC])
            ->all();
        Yii::info('cac thu huong');
        Yii::info($all_promotion);
        if (empty($all_promotion) && !isset($all_promotion)) {
            return false;
        }
        /** @var Subscriber $subscriber */
        foreach ($all_promotion as $item) {
            /** @var CampaignPromotion $item */
            if ($item->type == CampaignPromotion::TYPE_FREE_SERVICE) {
                $service = Service::findOne(['id' => $item->service_id]);
                $subscriberServiceAsm = SubscriberServiceAsm::findOne(['service_id' => $item->service_id, 'subscriber_id' => $subscriber->id, 'site_id' => $site_id, 'status' => SubscriberServiceAsm::STATUS_ACTIVE]);
                if (empty($subscriberServiceAsm)) {
                    $subscriberServiceAsm = SubscriberServiceAsm::find()
                        ->andWhere(['service_id' => $item->service_id, 'subscriber_id' => $subscriber->id, 'site_id' => $site_id])
                        ->orderBy(['updated_at' => SORT_DESC])
                        ->one();
                }
                if (!$subscriberServiceAsm) {
                    $check_qnet = false;
                    $ssa = BaseLogicCampaign::addSubscriberServiceAsm($service, $subscriber, $site_id, $item->number_month);
                    $tr = $subscriber->newTransaction(
                        SubscriberTransaction::TYPE_PROMOTION,
                        SubscriberTransaction::CHANNEL_TYPE_ANDROID, 'Tặng gói cước', $service, null,
                        SubscriberTransaction::STATUS_SUCCESS,
                        0,
                        'VND',
                        $subscriber->balance,
                        null,
                        null,
                        null,
                        null,
                        null,
                        $subscriber->balance
                    );
                    Yii::info('chua mua goi' . $check_qnet);
                    if ($tr) {
                        $tr->subscriber_service_asm_id = $ssa->id;
                        $serviceCpAsm = ServiceCpAsm::findAll(['service_id' => $service->id]);
                        $serviceCp = '';
                        foreach ($serviceCpAsm as $item_) {
                            $serviceCp .= $item_->cp_id . ',';
                        }
                        $tr->cp_id = rtrim($serviceCp, ',');
                        $tr->number_month = $item->number_month;
                        $tr->update(false);
                        $ssa->transaction_id = $tr->id;
                        $ssa->update(false);
                        SysCproviderService::SysPurchaseService($tr->type, $tr->id, $service, $subscriber, 0, $tr->channel, $ssa, $check_qnet);
                    } else {
                        SysCproviderService::SysPurchaseService(null, null, $service, $subscriber, $price, null, $ssa, $check_qnet);
                    }
                } else {
                    if ($subscriberServiceAsm->status == SubscriberServiceAsm::STATUS_ACTIVE) {
                        $check_qnet = true;
                    } else {
                        $check_qnet = false;
                    }
                    $number_month = $item->number_month;
                    if ($subscriberServiceAsm->expired_at > time() && $subscriberServiceAsm->status == SubscriberServiceAsm::STATUS_ACTIVE) {
                        $subscriberServiceAsm->expired_at = $subscriberServiceAsm->expired_at + $item->number_month * 30 * 86400;
                        if($subscriberServiceAsm->number_gift_month){
                            $number_month = $subscriberServiceAsm->number_gift_month + $number_month;
                        }
                    } else {
                        $subscriberServiceAsm->expired_at = time() + $item->number_month * 30 * 86400;
                        $subscriberServiceAsm->number_buy_month = 0;
                    }
                    $subscriberServiceAsm->updated_at = time();
                    $subscriberServiceAsm->status = SubscriberServiceAsm::STATUS_ACTIVE;
                    $subscriberServiceAsm->number_gift_month = $number_month;
                    $subscriberServiceAsm->save(false);
                    $tr = $subscriber->newTransaction(
                        SubscriberTransaction::TYPE_PROMOTION,
                        SubscriberTransaction::CHANNEL_TYPE_ANDROID, 'Tặng gói cước', $service, null,
                        SubscriberTransaction::STATUS_SUCCESS,
                        0,
                        'VND',
                        $subscriber->balance,
                        null,
                        null,
                        null,
                        null,
                        null,
                        $subscriber->balance
                    );
                    Yii::info('Kiem tra tuanpc' . $check_qnet);
                    if ($tr) {
                        $tr->subscriber_service_asm_id = $subscriberServiceAsm->id;
                        $serviceCpAsm = ServiceCpAsm::findAll(['service_id' => $service->id]);
                        $serviceCp = '';
                        foreach ($serviceCpAsm as $item_) {
                            $serviceCp .= $item_->cp_id . ',';
                        }
                        $tr->cp_id = rtrim($serviceCp, ',');
                        $tr->number_month = $item->number_month;
                        $tr->update(false);
                        $ssa = SubscriberServiceAsm::findOne($subscriberServiceAsm->id);
                        $ssa->transaction_id = $tr->id;
                        $ssa->update(false);
                        SysCproviderService::SysPurchaseService($tr->type, $tr->id, $service, $subscriber, 0, $tr->channel, $subscriberServiceAsm, $check_qnet);
                    } else {
                        SysCproviderService::SysPurchaseService(null, null, $service, $subscriber, $price, null, $subscriberServiceAsm, $check_qnet);
                    }
                }
            } elseif ($item->type == CampaignPromotion::TYPE_FREE_CONTENT) {
                $subscriberContentAsm = SubscriberContentAsm::findOne(['site_id' => $site_id, 'content_id' => $item->content_id, 'subscriber_id' => $subscriber->id, 'status' => SubscriberContentAsm::STATUS_ACTIVE]);
                $content = Content::findOne(['id' => $item->content_id]);
                if (empty($subscriberContentAsm)) {
                    $subscriberContentAsm = SubscriberContentAsm::find()
                        ->andWhere(['site_id' => $site_id, 'content_id' => $item->content_id, 'subscriber_id' => $subscriber->id])
                        ->orderBy(['updated_at' => SORT_DESC])
                        ->one();
                }
                if (!$subscriberContentAsm) {
                    $subscriberContent = BaseLogicCampaign::addSubscriberContentAsm($content, $subscriber, $site_id);
                } else {
                    if ($subscriberContentAsm->expired_at > time() && $subscriberContentAsm->status == SubscriberServiceAsm::STATUS_ACTIVE) {
                        $subscriberContentAsm->expired_at = $subscriberContentAsm->expired_at + $content->getWatchingPriod($site_id) * 3600;
                    } else {
                        $subscriberContentAsm->expired_at = time() + $content->getWatchingPriod($site_id) * 3600;
                    }
                    $subscriberContentAsm->updated_at = time();
                    $subscriberContentAsm->status = SubscriberContentAsm::STATUS_ACTIVE;
                    $subscriberContentAsm->save(false);
                }
            } elseif ($item->type == CampaignPromotion::TYPE_FREE_TIME) {
                $subscriberServiceAsm = SubscriberServiceAsm::findOne(['service_id' => $service->id, 'subscriber_id' => $subscriber->id, 'site_id' => $site_id, 'status' => SubscriberServiceAsm::STATUS_ACTIVE]);
                if ($subscriberServiceAsm->expired_at >= time() && $subscriberServiceAsm->status == SubscriberServiceAsm::STATUS_ACTIVE) {
                    $subscriberServiceAsm->expired_at += $item->time_extend_service * 3600 * 24;
                } else {
                    $subscriberServiceAsm->expired_at = time() + $item->time_extend_service * 3600 * 24 + $service->period * 24 * 60 * 60;
                }
                $subscriberServiceAsm->updated_at = time();
                $subscriberServiceAsm->save(false);
//                $des = Yii::t('app', 'Tặng thời gian sử dụng cố định');
//                $subscriber->newTransaction(SubscriberTransaction::TYPE_GIFT, SubscriberTransaction::CHANNEL_TYPE_ANDROID, $des, null, null, SubscriberTransaction::STATUS_SUCCESS, 0, 'VND', 0);
            } elseif ($item->type == CampaignPromotion::TYPE_GIFT_TIME_SIGNAL) {
                $service = Service::findOne(['id' => $item->service_id]);
                $subscriberServiceAsm = SubscriberServiceAsm::findOne(['service_id' => $item->service_id, 'subscriber_id' => $subscriber->id, 'site_id' => $site_id]);
                $subscriberServiceAsm->expired_at = time() + ($item->time_extend_service * $service->period) * 24 * 60 * 60 / 100;
                $subscriberServiceAsm->updated_at = time();
                $subscriberServiceAsm->save(false);
//                $des = Yii::t('app', 'Tặng thời gian sử dụng theo chu kỳ gói');
//                $subscriber->newTransaction(SubscriberTransaction::TYPE_GIFT, SubscriberTransaction::CHANNEL_TYPE_ANDROID, $des, null, null, SubscriberTransaction::STATUS_SUCCESS, 0, 'VND', 0);
            } elseif ($item->type == CampaignPromotion::TYPE_FREE_COIN) {
                $coin = 0;
                if ($item->price_unit == CampaignPromotion::PRICE_UNIT_ITEM) {
                    $coin = $item->price_gift;
                } elseif ($item->price_unit == CampaignPromotion::PRICE_UNIT_PERCENT) {
                    $coin = ($price * $item->price_gift) / 100;
                }
                $subscriber->balance = $subscriber->balance + $coin;
                $subscriber->updated_at = time();
                $subscriber->save(false);
//                $des = Yii::t('app', 'Tặng coin');
//                $subscriber->newTransaction(SubscriberTransaction::TYPE_GIFT, SubscriberTransaction::CHANNEL_TYPE_ANDROID, $des, null, null, SubscriberTransaction::STATUS_SUCCESS, $coin, 'COIN', $coin);
            }
            if (!BaseLogicCampaign::createLog($subscriber, $campaign, $device, $site_id, $item->id)) {
                Yii::$app->getErrorHandler();
                return false;
            }
        }
        return true;
    }

    public static function addSubscriberContentAsm($content, $subscriber, $site_id)
    {
        /** @var Content $content */
        /** @var  Subscriber $subscriber */
        $subscriberContentAsm = new SubscriberContentAsm();
        $subscriberContentAsm->content_id = $content->id;
        $subscriberContentAsm->subscriber_id = $subscriber->id;
        $subscriberContentAsm->msisdn = $subscriber->msisdn;
        $subscriberContentAsm->activated_at = time();
        $subscriberContentAsm->expired_at = time() + $content->getWatchingPriod($site_id) * 3600;
        $subscriberContentAsm->status = SubscriberContentAsm::STATUS_ACTIVE;
        $subscriberContentAsm->created_at = time();
        $subscriberContentAsm->updated_at = time();
        $subscriberContentAsm->purchase_type = SubscriberContentAsm::TYPE_PRESENTED;
        $subscriberContentAsm->site_id = $site_id;
        if ($subscriberContentAsm->save()) {
//            $subscriber->newTransaction(SubscriberTransaction::TYPE_GIFT, SubscriberTransaction::CHANNEL_TYPE_ANDROID, Yii::t('app', 'Tặng nội dung lẻ'), null, $content, SubscriberTransaction::STATUS_SUCCESS, 0, 'VND', 0);
            return true;
        }
        return false;
    }

    public static function addSubscriberServiceAsm($service, $subscriber, $site_id, $number_month)
    {
        /** @var  Subscriber $subscriber */
        /** @var  Service $service */
        $ssa = new SubscriberServiceAsm();
        $ssa->subscriber_id = $subscriber->id;
        $ssa->msisdn = $subscriber->msisdn;
        $ssa->service_name = $service->display_name;
        $ssa->service_id = $service->id;
        $ssa->site_id = $site_id;
        $ssa->dealer_id = $subscriber->dealer_id;
        $activationDate = new \DateTime();
        $expiryDate = new \DateTime();
        $ssa->auto_renew = $service->auto_renew;
        $ssa->renew_fail_count = 0;
        $ssa->activated_at = $activationDate->getTimestamp();
//        if (isset($service->period) && $service->period > 0) {
//            $expiryDate->add(new DateInterval("P" . $service->period . 'D'));
//            $ssa->expired_at = $expiryDate->getTimestamp();
//        } else {
//            $ssa->expired_at = null;
//        }
        $ssa->expired_at = time() + 30 * 86400 * $number_month;
        $ssa->number_gift_month = $number_month;
        $ssa->status = SubscriberServiceAsm::STATUS_ACTIVE;

        if (!$ssa->save()) {
            Yii::trace("ERROR: cannot save ssa: " . Json::encode($ssa));
        }
        return $ssa;
    }

    public static function getCampaignActiveUser($subscriber, $site_id, $content_id = null)
    {
        $campaign = Campaign::find()
            ->innerJoin('campaign_group_subscriber_asm', 'campaign_group_subscriber_asm.campaign_id = campaign.id')
            ->innerJoin('group_subscriber_user_asm', 'group_subscriber_user_asm.group_subscriber_id = campaign_group_subscriber_asm.group_subscriber_id');
        $campaign->andWhere(['group_subscriber_user_asm.username' => $subscriber->username])
            ->andWhere(['campaign.status' => Campaign::STATUS_ACTIVATED])
            ->orWhere(['campaign.status' => Campaign::STATUS_RUNNING_TEST])
            ->andWhere(['group_subscriber_user_asm.status' => GroupSubscriberUserAsm::STATUS_ACTIVE])
            ->andWhere(['campaign.site_id' => $site_id])
            ->andWhere(['campaign.type' => Campaign::TYPE_ACTIVE])
            ->andWhere('campaign.expired_at >= :expired_at', ['expired_at' => time()])
            ->andWhere('campaign.activated_at <= :activated_at', ['activated_at' => time()])
            ->orderBy(['campaign.activated_at' => SORT_DESC, 'campaign.priority' => SORT_DESC]);
        if ($content_id) {
            foreach ($campaign->all() as $item) {
                /** @var $item Campaign */
                $service = Service::find()
                    ->innerJoin('campaign_promotion', 'campaign_promotion.service_id = service.id')
                    ->innerJoin('service_category_asm', 'service_category_asm.service_id = service.id')
                    ->innerJoin('content_category_asm', 'content_category_asm.category_id = service_category_asm.category_id')
                    ->andWhere(['content_category_asm.content_id' => $content_id])
                    ->andWhere(['service.status' => Service::STATUS_ACTIVE])
                    ->andWhere(['campaign_promotion.status' => CampaignPromotion::STATUS_ACTIVE])
                    ->andWhere(['campaign_promotion.campaign_id' => $item->id])->one();
                if ($service) {
                    return true;
                }
                return false;
            }
        }
        return $campaign->one();
    }

    public static function checkRecivedCampaign($subscriber, $campaign)
    {
        $check = LogCampaignPromotion::findOne(['subscriber_id' => $subscriber->id, 'campaign_id' => $campaign->id, 'status' => LogCampaignPromotion::STATUS_ACTIVE]);
        if ($check) {
            return true;
        } else {
            return false;
        }

    }
}