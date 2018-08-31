<?php

namespace console\controllers;

use common\helpers\CUtils;
use common\helpers\FileUtils;
use common\helpers\SysCproviderService;
use common\models\BaseLogicCampaign;
use common\models\Campaign;
use common\models\CampaignGroupSubscriberAsm;
use common\models\CampaignPromotion;
use common\models\City;
use common\models\Content;
use common\models\ContentProvider;
use common\models\CpSysnc;
use common\models\Dealer;
use common\models\Device;
use common\models\GroupSubscriberUserAsm;
use common\models\IpAddress;
use common\models\LogCampaignPromotion;
use common\models\Service;
use common\models\ServiceCpAsm;
use common\models\Site;
use common\models\SmsSupport;
use common\models\Subscriber;
use common\models\SubscriberContentAsm;
use common\models\SubscriberServiceAsm;
use common\models\SubscriberTransaction;
use DateTime;
use Yii;
use yii\console\Controller;
use yii\db\Transaction;
use Exception;

class SubscriberController extends Controller
{
    private $targetSubscriber;

    public function actionUpdateSubscriberInitialized($begin = '')
    {
        if ($begin){
            $timeDay = $endPreDay = mktime(0, 0, 0) - 1;
        }else{
            $timeDay = $endPreDay = mktime(0, 0, 0);
        }

        $listSubscriber = Subscriber::find()
            ->innerJoin('view_report_subscriber_initialize', 'view_report_subscriber_initialize.subscriber_id = subscriber.id')
            ->all();
        if (!$listSubscriber) {
            static::log("***** No subscriber to update ****** \n");
        } else {
            static::log("Begin update Subscriber");
            /** @var Subscriber $subscriber */
            foreach ($listSubscriber as $subscriber) {
                $sql = "SELECT * FROM view_report_subscriber_initialize
                                  WHERE subscriber_id = :subscriber_id";
                $connection = Yii::$app->getDb();
                $command1 = $connection->createCommand($sql, [
                    ':subscriber_id' => $subscriber->id]);
                $result = $command1->queryOne();

                $subscriber->initialized_at = $timeDay;
                $subscriber-> service_initialized =$result['service_id'];

                if ($subscriber->save()) {
                    echo 'Update subscriber_id = ' . $subscriber->id . " done\n";
                } else {
                    echo 'Update subscriber_id = ' . $subscriber->id . " fail:" . $subscriber->getErrors() . "\n";
                }
            }
            static::log("****** Update Subscriber done ******");
        }
    }

    public function actionUpdateCity($mac = "")
    {
        try {
            $date = getdate();
            $this_month = mktime(0, 0, 0, $date['mon'], 1);
            if ($mac) {
                $listSubscriber = Subscriber::find()
                    ->Where(['status' => Subscriber::STATUS_ACTIVE])
                    ->andWhere(['is not', 'ip_address', Null])
                    ->andWhere(['like', 'machine_name', $mac])
                    ->all();
            } else {
                $listSubscriber = Subscriber::find()
                    ->Where(['status' => Subscriber::STATUS_ACTIVE])
                    ->andWhere(['is not', 'ip_address', Null])
                    ->all();
            }
            if (!$listSubscriber) {
                echo "***** No subscriber to update ****** \n";
            } else {
                echo 'Begin update City By IP address to Subscriber on ' . date("d-m-Y H:i:s", $this_month) . "\n";
                /** @var Subscriber $subscriber */
                foreach ($listSubscriber as $subscriber) {
                    //kiem tra xem IP_address la IPv4 hay IPv6
                    $kt = strpos($subscriber->ip_address, '.');
                    if ($kt) {
                        // neu la IPv4
                        $ip = CUtils::setIPv4($subscriber->ip_address);
                        /** @var IpAddress $city */
                        $ip= $subscriber->ip_address;
                        $city = IpAddress::find()
                            ->where(['<=', 'ip_start', $ip])
                            ->andWhere(['>=', 'ip_end', $ip])
                            ->andWhere(['type' => IpAddress::TYPE_IPV4])
                            ->one();
                    } else {
                        $ip = CUtils::setIPv6($subscriber->ip_address);
                        /** @var IpAddress $city */
                        $city = IpAddress::find()
                            ->where(['<=', 'ip_start', $ip])
                            ->andWhere(['>=', 'ip_end', $ip])
                            ->andWhere(['type' => IpAddress::TYPE_IPV6])
                            ->one();
                    }
                    if ($city) {
                        $province = City::find()->andWhere(['name' => $city->city])->one();
                        if ($province) {
                            $subscriber->ip_to_location = $province->code;
                            if ($subscriber->ip_location_first == null){
                                $subscriber->ip_location_first = $province->code;
                            }
                            if ($subscriber->save()) {
                                echo 'Update City By IP address ' . $ip . ' to subscriber_id = ' . $subscriber->id . " done\n";
                            } else {
                                echo 'Update City By IP address ' . $ip . ' to subscriber_id = ' . $subscriber->id . " fail:" . $subscriber->getErrors() . "\n";
                            }
                        } else {
                            echo "Cannot detect ip to location - sub $subscriber->id : ip $ip \n";
                        }
                    } else {
                        echo "Cannot detect ip to location - sub $subscriber->id : ip $ip \n";
                    }
                }
                echo "****** Update City By IP address done ******";
            }
        } catch (Exception $e) {
            echo '****** ERROR! Update City By IP address Exception: ' . $e->getMessage() . '******';
        }
    }


    public function actionUpdateCityForNewUser($mac = "")
    {
        try {
            $date = getdate();
            $this_month = mktime(0, 0, 0, $date['mon'], 1);
            if ($mac) {
                $listSubscriber = Subscriber::find()
                    ->Where(['status' => Subscriber::STATUS_ACTIVE])
                    ->andWhere(['is not', 'ip_address', null])
                    ->andWhere(['like', 'machine_name', $mac])
                    ->andFilterWhere(['is not', 'ip_to_location', null])
                    ->all();
            } else {
                $listSubscriber = Subscriber::find()
                    ->Where(['status' => Subscriber::STATUS_ACTIVE])
                    ->andWhere(['is not', 'ip_address', null])
                    ->andFilterWhere(['is not', 'ip_to_location', null])
                    ->all();
            }
            if (!$listSubscriber) {
                echo "***** No subscriber to update ****** \n";
            } else {
                echo 'Begin update City By IP address to Subscriber on ' . date("d-m-Y H:i:s", $this_month) . "\n";
                /** @var Subscriber $subscriber */
                foreach ($listSubscriber as $subscriber) {
                    //kiem tra xem IP_address la IPv4 hay IPv6
                    $kt = strpos($subscriber->ip_address, '.');
                    if ($kt) {
                        // neu la IPv4
                        $ip = CUtils::setIPv4($subscriber->ip_address);
                        /** @var IpAddress $city */
                        $city = IpAddress::find()
                            ->where(['<=', 'ip_start', $ip])
                            ->andWhere(['>=', 'ip_end', $ip])
                            ->andWhere(['type' => IpAddress::TYPE_IPV4])
                            ->one();
                    } else {
                        $ip = CUtils::setIPv6($subscriber->ip_address);
                        /** @var IpAddress $city */
                        $city = IpAddress::find()
                            ->where(['<=', 'ip_start', $ip])
                            ->andWhere(['>=', 'ip_end', $ip])
                            ->andWhere(['type' => IpAddress::TYPE_IPV6])
                            ->one();
                    }
                    if ($city) {
                        $province = City::find()->andWhere(['name' => $city->city])->one();
                        if ($province) {
                            $subscriber->ip_to_location = $province->code;
                            if ($subscriber->ip_location_first == null){
                                $subscriber->ip_location_first = $province->code;
                            }
                            if ($subscriber->save()) {
                                echo 'Update City By IP address ' . $ip . ' to subscriber_id = ' . $subscriber->id . " done\n";
                            } else {
                                echo 'Update City By IP address ' . $ip . ' to subscriber_id = ' . $subscriber->id . " fail:" . $subscriber->getErrors() . "\n";
                            }
                        } else {
                            echo "Cannot detect ip to location - sub $subscriber->id : ip $ip ";
                        }
                    } else {
                        echo "Cannot detect ip to location - sub $subscriber->id : ip $ip ";
                    }
                }
                echo "****** Update City By IP address done ******";
            }
        } catch (Exception $e) {
            echo '****** ERROR! Update City By IP address Exception: ' . $e->getMessage() . '******';
        }
    }

    public function actionAutoDeleteSubscriber()
    {
        self::writeLog("========== Start cronjob Subscriber \r\n");
        $this->targetSubscriber = Subscriber::find();
        $this->scopeLongtimeInactiveSubscribers();
        $targetSubscribers = $this->targetSubscriber->all();

        self::writeArray($targetSubscribers);
        self::writeLog($this->targetSubscriber->createCommand()->rawSql);

        foreach ($targetSubscribers as $sub) {
            $subcriber = Subscriber::findOne($sub['id']);
            $subcriber->username = $sub['username'] . '_deleted_' . date('dmY');
            $subcriber->status = Subscriber::STATUS_DELETED;
            $subcriber->save(false);
        }
    }

    public function scopeLongtimeInactiveSubscribers()
    {
        if ($this->targetSubscriber) {
            $this->targetSubscriber->where(['status' => Subscriber::STATUS_ACTIVE]);
            $this->targetSubscriber->andWhere(['<', 'last_login_at', strtotime('-6 months')]);
        }
    }

    public static function writeLog($txt, $sync = false)
    {
        $txt = date('Y-m-d H:i:s') . ' ' . $txt;
        if ($sync) {
            FileUtils::appendToFile(\Yii::getAlias('@runtime/logs/sync-service.log'), $txt);
        } else {
            FileUtils::appendToFile(\Yii::getAlias('@runtime/logs/subcriber-info.log'), $txt);
        }
    }

    public static function writeArray($array)
    {
        if (count($array) > 0) {
            $out = ["\r\n"];
            foreach ($array as $row) {
                $sub = [
                    '**** ' . $row['id'],
                    '**** ' . '----Site: ' . Site::findOne($row['site_id'])->name,
                    '**** ' . '----Dealer: ' . Dealer::findOne($row['dealer_id'])->name,
                    '**** ' . '----Name: ' . $row['username'],
                ];
                $out[] = join("\r\n", $sub);
            }
            $out[] = "\r\n";
            self::writeLog(join("\r\n", $out));
        } else {
            self::writeLog('No records');
        }
    }

    public function actionCampaignEvent()
    {
        $site_id = Yii::$app->params['site_id'];
        $campaign = SubscriberController::getAllCampaign(Campaign::TYPE_EVENT, $site_id);
        if (!empty($campaign)) {
            foreach ($campaign as $item) {

                /** @var $item Campaign */
                $this->infoLogCampaign('Bat dau kiem tra ap dung chien dich ' . $item->id . ' co ten la ' . $item->name);

                /** @var $item Campaign */
                //tim khach hang thuoc chien dich
                $listSubscriber = SubscriberController::getGroupSubscriber($item->id, $site_id, $item->status);
                if (!$listSubscriber) {
                    return false;
                }
                foreach ($listSubscriber as $subscriber) {
                    /** @var $subscriber  GroupSubscriberUserAsm */
                    $this->infoLogCampaign('Kiem tra dieu kien cua tai khoan co username la ' . $subscriber->username);
                    $subscriber = Subscriber::findOne(['username' => $subscriber->username]);
                    if (BaseLogicCampaign::getCampaign($site_id, $subscriber, $item, $item->number_promotion)) {
                        if (BaseLogicCampaign::addServiceToSubscriber($subscriber, $item, $site_id, null, null)) {
                            $this->infoLogCampaign('Ap dung khuyen mai thanh cong cho tai khoan co username la ' . $subscriber->username);
                            if ($item->notification_title) {
                                $sendmail = SmsSupport::addSmsSupport($item, $subscriber);
                                if ($sendmail) {
                                    $this->infoLogCampaign('Gui mail thanh cong');
                                }
                            }
                        }
                    }
                }
            }
        }
        return false;
    }


    public function getCampaign($site_id = null, $subscriber = null, $campaign = null, $number_promotion = 0)
    {
        /** @var Subscriber $subscriber */
        /** @var Campaign $campaign */
        if (!empty($subscriber) && !empty($campaign)) {
            $countSubscriberLog = LogCampaignPromotion::find()
                ->andWhere(['site_id' => $site_id])
                ->andWhere(['type' => $campaign->type])
                ->andWhere(['subscriber_name' => $subscriber->username])
                ->andWhere(['campaign_id' => $campaign->id])->count();
            $count = CampaignPromotion::find()
                ->andWhere(['campaign_id' => $campaign->id])
                ->count();
            if ($count != 0 && $countSubscriberLog / $count < $number_promotion) {
                return true;
            }
            return false;
        }
        return false;
    }

    public static function getAllCampaign($type, $site_id)
    {
        $campaign = Campaign::find()
            ->andWhere(['status' => Campaign::STATUS_ACTIVATED])
            ->orWhere(['status' => Campaign::STATUS_RUNNING_TEST])
            ->andWhere(['IN', 'type', $type])
            ->andWhere(['site_id' => $site_id])
            ->andWhere('activated_at <= :activated_at', ['activated_at' => time()])
            ->andWhere('expired_at >= :expired_at', ['expired_at' => time()])
            ->orderBy(['activated_at' => SORT_DESC, 'priority' => SORT_DESC])->all();
        return $campaign;
    }

    public static function getGroupSubscriber($campaign_id, $site_id, $status)
    {
        if ($status == Campaign::STATUS_RUNNING_TEST) {
            $campaignGroupSubscriber = GroupSubscriberUserAsm::find()
                ->innerJoin('campaign_group_subscriber_asm', 'group_subscriber_user_asm.group_subscriber_id = campaign_group_subscriber_asm.group_subscriber_id')
                ->andWhere(['campaign_group_subscriber_asm.campaign_id' => $campaign_id])
                ->andWhere(['campaign_group_subscriber_asm.site_id' => $site_id])
                ->andWhere(['campaign_group_subscriber_asm.status' => CampaignGroupSubscriberAsm::STATUS_ACTIVE])
                ->andWhere(['campaign_group_subscriber_asm.type' => CampaignGroupSubscriberAsm::TYPE_DEMO])
                ->andWhere(['group_subscriber_user_asm.status' => GroupSubscriberUserAsm::STATUS_ACTIVE])
                ->all();
        } else {
            $campaignGroupSubscriber = GroupSubscriberUserAsm::find()
                ->innerJoin('campaign_group_subscriber_asm', 'group_subscriber_user_asm.group_subscriber_id = campaign_group_subscriber_asm.group_subscriber_id')
                ->andWhere(['campaign_group_subscriber_asm.campaign_id' => $campaign_id])
                ->andWhere(['campaign_group_subscriber_asm.site_id' => $site_id])
                ->andWhere(['campaign_group_subscriber_asm.status' => CampaignGroupSubscriberAsm::STATUS_ACTIVE])
                ->andWhere(['campaign_group_subscriber_asm.type' => CampaignGroupSubscriberAsm::TYPE_REAL])
                ->andWhere(['group_subscriber_user_asm.status' => GroupSubscriberUserAsm::STATUS_ACTIVE])
                ->all();
        }
        return $campaignGroupSubscriber;
    }

    //mua goi
    public static function addServiceToSubscriber($subscriber, $campaign, $site_id, $device = null, $price = null, $service = null)
    {
        /** @var  Campaign $campaign */
        $all_promotion = CampaignPromotion::find()->andWhere(['campaign_id' => $campaign->id])->orderBy(['honor' => SORT_DESC])->all();
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
                    $ssa = BaseLogicCampaign::addSubscriberServiceAsm($service, $subscriber, $site_id);
                    $tr = $subscriber->newTransaction(
                        SubscriberTransaction::TYPE_PROMOTION,
                        SubscriberTransaction::CHANNEL_TYPE_ANDROID, 'Tặng gói cước', $service, null,
                        SubscriberTransaction::STATUS_SUCCESS,
                        0,
                        'VND',
                        0
                    );
                    if ($tr) {
                        $tr->subscriber_service_asm_id = $ssa->id;
                        $tr->update(false);
                        $serviceCpAsm = ServiceCpAsm::findAll(['service_id' => $service->id]);
                        $serviceCp = '';
                        foreach ($serviceCpAsm as $item_) {
                            $serviceCp .= $item_->cp_id . ',';
                        }
                        $tr->cp_id = rtrim($serviceCp, ',');
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
                    if ($subscriberServiceAsm->expired_at > time() && $subscriberServiceAsm->status == SubscriberServiceAsm::STATUS_ACTIVE) {
                        $subscriberServiceAsm->expired_at = $subscriberServiceAsm->expired_at + $service->period * 24 * 60 * 60;
                    } else {
                        $subscriberServiceAsm->expired_at = time() + $service->period * 24 * 60 * 60;
                    }
                    $subscriberServiceAsm->updated_at = time();
                    $subscriberServiceAsm->status = SubscriberServiceAsm::STATUS_ACTIVE;
                    $subscriberServiceAsm->save(false);
                    $tr = $subscriber->newTransaction(
                        SubscriberTransaction::TYPE_PROMOTION,
                        SubscriberTransaction::CHANNEL_TYPE_ANDROID, 'Tặng gói cước', $service, null,
                        SubscriberTransaction::STATUS_SUCCESS,
                        0,
                        'VND',
                        0
                    );
                    if ($tr) {
                        $tr->subscriber_service_asm_id = $subscriberServiceAsm->id;
                        $serviceCpAsm = ServiceCpAsm::findAll(['service_id' => $service->id]);
                        $serviceCp = '';
                        foreach ($serviceCpAsm as $item_) {
                            $serviceCp .= $item_->cp_id . ',';
                        }
                        $tr->cp_id = rtrim($serviceCp, ',');
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
            return true;
        }
        return false;
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
        $logCampaign->campaign_id = $campaign->id;
        $logCampaign->campaign_name = $campaign->name;
        $logCampaign->site_id = $site_id;
        $logCampaign->type = $campaign->type;
        $logCampaign->subscriber_id = $subscriber->id;
        $logCampaign->subscriber_name = $subscriber->username;
        $logCampaign->campaign_promotion_id = $id_campaignPromotion;
        $logCampaign->status = LogCampaignPromotion::STATUS_ACTIVE;
        if ($logCampaign->save()) {
            return true;
        }
        return false;
    }


    public function actionSendSms($subscriber_id, $campaign_id, $service_id = null)
    {
        sleep(0.5);
        $site_id = Yii::$app->params['site_id'];
        Yii::info('chay tim chien dich');
        $subscriber = Subscriber::findOne($subscriber_id);
        $this->infoLog(date("d-m-Y H:i:sa") . 'BAT DAU GUI SMS ' . $subscriber->username);
        $campaign = Campaign::findOne($campaign_id);
        $service = null;
        if ($service_id) {
            $service = Service::findOne($service_id);
        }
        if (!$campaign) {
            $this->infoLog(date("d-m-Y H:i:sa") . 'KHONG CO CHIEN DICH ');
            Yii::info('chiendich', 'Khong co chien dich nao ca');
        } else {
            $this->infoLog(date("d-m-Y H:i:sa") . 'CHIEN DICH DC AP DUNG ' . $campaign->name . ' id = ' . $campaign->id);
            if (!empty($campaign->notification_title)) {
                $sendmail = SmsSupport::addSmsSupport($campaign, $subscriber, $service);
                if ($sendmail) {
                    $this->infoLog(date("d-m-Y H:i:sa") . 'GUI MAIL THANH CONG');
                    Yii::info('sendmail', 'Gui mail thanh cong');
                }
                $this->infoLog(date("d-m-Y H:i:sa") . 'GUI MAIL THAT BAI');
            }
        }
        $this->infoLog(date("d-m-Y H:i:sa") . 'KET THUC CHIEN DICH ');
    }

    public function actionFindCampaignForRecharge($price, $subscriber_id)
    {
        $site_id = Yii::$app->params['site_id'];
        Yii::info('chay tim chien dich');
        $subscriber = Subscriber::findOne($subscriber_id);
        $this->infoLog(date("d-m-Y H:i:sa") . 'BAT DAU AP DUNG ' . $subscriber->username);
        $arrtype = [Campaign::TYPE_CASH_CASH, Campaign::TYPE_CASH_CONTENT, Campaign::TYPE_CASH_SERVICE];
        Yii::info($price);
        $campaign = BaseLogicCampaign::RechargePromotion($subscriber, $site_id, $arrtype, $price);
        if (!$campaign) {
            $this->infoLog(date("d-m-Y H:i:sa") . 'KHONG CO CHIEN DICH ');
            Yii::info('chiendich', 'Khong co chien dich nao ca');
        } else {
            $this->infoLog(date("d-m-Y H:i:sa") . 'CHIEN DICH DC AP DUNG ' . $campaign->name . ' id = ' . $campaign->id);
            if (!empty($campaign->notification_title)) {
                $sendmail = SmsSupport::addSmsSupport($campaign, $subscriber, $price);
                if ($sendmail) {
                    $this->infoLog(date("d-m-Y H:i:sa") . 'GUI MAIL THANH CONG');
                    Yii::info('sendmail', 'Gui mail thanh cong');
                }
                $this->infoLog(date("d-m-Y H:i:sa") . 'GUI MAIL THAT BAI');
            }
        }
        $this->infoLog(date("d-m-Y H:i:sa") . 'KET THUC CHIEN DICH ');
    }

    public static function errorLog($txt)
    {
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/campaign_error_recharge.log'), $txt);
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/info_campaign_error_recharge.log'), $txt);
    }

    public static function infoLog($txt)
    {
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/info_campaign_error_recharge.log'), $txt);
    }

    public static function infoLogCampaign($txt)
    {
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/info_campaign.log'), $txt);
    }

    public function actionSyncLogToTransaction()
    {
        echo "Bat dau dong bo";
        $type_gift_service = [
            Campaign::TYPE_BOX_SERVICE,
            Campaign::TYPE_CASH_SERVICE,
            Campaign::TYPE_SERVICE_SERVICE
        ];

        $all_log_have_service = LogCampaignPromotion::find()
            ->andWhere(['IN', 'type', $type_gift_service])
            ->andWhere(['status' => LogCampaignPromotion::STATUS_ACTIVE])
            ->all();

        foreach ($all_log_have_service as $log_detail) {
            /** @var LogCampaignPromotion $log_detail */
            $service_id = CampaignPromotion::find()
                ->select('service_id')
                ->andWhere(['id' => $log_detail->campaign_promotion_id])
                ->one();
            $service = Service::findOne($service_id->service_id);
            $subscriber = Subscriber::findOne(['id' => $log_detail->subscriber_id]);
            if ($subscriber) {
                echo "\n Dang dong bo voi " . $subscriber->msisdn . ' co id ' . $subscriber->id . ' Trong KM ' . Campaign::$campaignType[$log_detail->type];
                $ssa = SubscriberServiceAsm::findOne([
                    'service_id' => $service->id,
                    'subscriber_id' => $subscriber->id,
                    'status' => SubscriberServiceAsm::STATUS_ACTIVE
                ]);

                $tr = new SubscriberTransaction();
                $tr->subscriber_id = $subscriber->id;
                $tr->transaction_time = $log_detail->created_at;
                $tr->created_at = $log_detail->created_at;
                $tr->updated_at = $log_detail->updated_at;
                $tr->site_id = $service->site_id;
                $tr->type = SubscriberTransaction::TYPE_PROMOTION;
                $tr->channel = SubscriberTransaction::CHANNEL_TYPE_ANDROID;
                $tr->description = Yii::t('app', 'Tặng gói cước trong chương trình khuyến mãi ' . Campaign::$campaignType[$log_detail->type]);
                $tr->status = SubscriberTransaction::STATUS_SUCCESS;
                $tr->cost = 0;
                $tr->currency = 'COIN';
                $tr->balance = 0;
                $tr->msisdn = $subscriber->msisdn;
                $tr->service_id = $service->id;
                $serviceCpAsm = ServiceCpAsm::findAll(['service_id' => $service->id]);
                if ($ssa) {
                    $tr->subscriber_service_asm_id = $ssa->id;
                }
                $serviceCp = '';
                foreach ($serviceCpAsm as $item) {
                    $serviceCp .= $item->cp_id . ',';
                }
                $tr->cp_id = rtrim($serviceCp, ',');
                if (!$tr->save()) {
                    echo "\n LOI DONG BO";
                    echo "<pre>";
                    print_r($tr->getErrors());
                    die();
                }
            } else {
                echo "\n Thue bao da bi xoa khoi db khong dong bo duoc id_sub: " . $log_detail->subscriber_id;
            }

        }
        echo "\n Dong bo thanh cong";
    }

    public function actionSyncServiceQnet($from_date, $to_date)
    {
        if ($from_date != '' && $to_date != '') {
            echo "\n ============== Bat dau dong bo qnet ==============";
            self::writeLog("============== Bat dau dong bo qnet ============== \r\n", true);
            $from_date = DateTime::createFromFormat("dmY", $from_date)->setTime(0, 0)->format('Y-m-d H:i:s');
            $beginPreDay = strtotime($from_date);
            $to_date = DateTime::createFromFormat("dmY", $to_date)->setTime(23, 59, 59)->format('Y-m-d H:i:s');
            $endPreDay = strtotime($to_date);
        } else {
            echo "Vui long nhap ngay muon chay dong bo";
            self::writeLog("Chua nhap ngay muon chay dong bo \r\n", true);
            die();
        }
        echo "\n Dong bo tu ngay " . $from_date . " - " . $beginPreDay . " den ngay " . $to_date . " - " . $endPreDay;
        self::writeLog("Dong bo tu ngay " . $from_date . " - " . $beginPreDay . " den ngay " . $to_date . " - " . $endPreDay . " \r\n", true);

        $all_cp_need_sync = Yii::$app->params['list_cp'];
        $number_subsriber = 0;
        foreach ($all_cp_need_sync as $cp_id => $cp) {
            $url = $cp['url'];
            /** @var ContentProvider $cp */
            $list_service = ServiceCpAsm::find()
                ->select('service_id')
                ->andWhere(['cp_id' => $cp_id])
                ->andWhere(['status' => ServiceCpAsm::STATUS_ACTIVE])
                ->all();
            if ($list_service) {
                self::writeLog(" Tim thay goi cuoc dc gan cho cp  \r\n", true);
                foreach ($list_service as $service) {
                    /** @var ServiceCpAsm $service */
                    if (in_array($service->service_id, $cp['list_service_id'])) {
                        self::writeLog(" Dung goi cua qnet can dong bo \r\n", true);
                        // tìm các thuê bao đã được đồng bộ để không đồng bộ nữa
                        $all_sub = CpSysnc::find()
                            ->select('subscriber_id')
                            ->andWhere(['status' => CpSysnc::STATUS_SUCCESS])
                            ->all();
                        $all_sub_registed = [];
                        if ($all_sub) {
                            foreach ($all_sub as $sub_sync) {
                                /** @var CpSysnc $sub_sync */
                                $all_sub_registed[] = $sub_sync->subscriber_id;
                            }
                        }
                        // tìm những thuê bao đăng kí gói qnet trước để đồng bộ
                        $ssa = SubscriberServiceAsm::find()
                            ->andWhere(['service_id' => $service->service_id])
                            ->andWhere(['status' => SubscriberServiceAsm::STATUS_ACTIVE])
                            ->andWhere(['>=', 'activated_at', $beginPreDay])
                            ->andWhere(['<=', 'activated_at', $endPreDay])
                            ->andWhere(['NOT IN', 'subscriber_id', $all_sub_registed])
                            ->all();
                        if ($ssa) {
                            /** @var SubscriberServiceAsm $subServiceAsm */
                            foreach ($ssa as $subServiceAsm) {
                                $subscriber = Subscriber::findOne(['id' => $subServiceAsm->subscriber_id]);
                                $tr = SubscriberTransaction::findOne(['subscriber_service_asm_id' => $subServiceAsm->id, 'type' => SubscriberTransaction::TYPE_PROMOTION]);
                                if ($tr) {
                                    self::writeLog(" Dong bo dang ky theo goi cuoc co ID: " . $service->service_id . " \r\n", true);
                                    /** @var SubscriberTransaction $tr */
                                    $ser = Service::findOne(['id' => $service->service_id]);
                                    $ssa = new SubscriberServiceAsm();
                                    $ssa->activated_at = $tr->transaction_time;
                                    $ssa->expired_at = $subServiceAsm->expired_at;
                                    // gọi đồng bộ đăng ký
                                    self::writeLog(" Dong bo voi ID thue bao : " . $subscriber->id . " cua id goi cuoc " . $service->service_id . " \r\n", true);
                                    SysCproviderService::RegisterServiceQnet(
                                        $subscriber,
                                        $ser,
                                        $ssa,
                                        0,
                                        SubscriberTransaction::CHANNEL_TYPE_ANDROID,
                                        $url,
                                        $cp_id,
                                        $tr->id,
                                        SubscriberTransaction::TYPE_PROMOTION
                                    );
                                    $number_subsriber++;
                                } else {
                                    self::writeLog(" Khong dong bo do khong thay subscriber_transaction \r\n", true);
                                }
                            }
                        } else {
                            self::writeLog(" Chua co dang ki lan nao trong subscriber_service_asm \r\n", true);
                        }
                    } else {
                        self::writeLog(" Khong dung goi cuoc can dong bo ser_id " . $service->service_id . " cp_id " . $cp_id . "  \r\n", true);
                    }
                }
            } else {
                self::writeLog(" Khong thay goi cuoc duoc gan cho cp  \r\n", true);
            }
        }
        self::writeLog(" Đong bo hoan tat cho " . $number_subsriber . " thuê bao \r\n", true);
        self::writeLog(" =================== Ket thuc dong bo ============ \r\n", true);
        echo "\n =================== Ket thuc dong bo ============ ";
    }

    public function actionUpdatePassword()
    {
        $subscriber = Subscriber::find()
            ->andWhere('machine_name is not null')
            ->all();
        foreach ($subscriber as $subscri) {
            /** @var $subscri Subscriber */
            $subscri->setPassword($subscri->machine_name . substr($subscri->machine_name, -3));
            $subscri->updated_at = time();
            $subscri->save();
        }
    }

    public function actionUpdateUserByFactoryIp()
    {
        $i = 0;
        $total = 0;
        foreach (Subscriber::find()->each(100) as $row) {

            /** @var $row Subscriber */
            if (in_array($row->ip_address, Yii::$app->params['factory_ip'])) {
                $row->type = Subscriber::TYPE_NSX;
                $row->save(false);

                static::log("Phat hien sub $row->id co ip nha may");
            }
            if ($i % 1000 == 0) {
                static::log("Da kiem tra $i ban ghi, có $total sub nha may");
            }
            $i++;

        }
    }

    public static function log($message)
    {
        echo date('Y-m-d H:i:s') . ": " . $message . PHP_EOL;
    }
}
