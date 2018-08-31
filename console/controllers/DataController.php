<?php
/**
 * Created by PhpStorm.
 * User: nhocconsanhdieu
 * Date: 30/6/2015
 * Time: 3:37 PM
 */

namespace console\controllers;


use common\helpers\CommonConst;
use common\helpers\CUtils;
use common\helpers\SmartGateHelper;
use common\models\BaseLogicCampaign;
use common\models\Campaign;
use common\models\CampaignPromotion;
use common\models\Device;
use common\models\LogCampaignPromotion;
use common\models\Service;
use common\models\SmsSupport;
use common\models\SmsUserAsm;
use common\models\Subscriber;
use common\models\SubscriberDeviceAsm;
use common\models\SubscriberServiceAsm;
use common\models\SubscriberTransaction;
use Yii;
use yii\console\Controller;

class DataController extends Controller
{


    /**
     * Tạo subscriber demo
     */
    public function actionCreateSubscriberDemo()
    {
        $password = '123456';
        for ($i = 1; $i <= 100; $i++) {
            $u = new Subscriber();
            $randomNumber = CUtils::randomString(7, "987654321");
            $msisdn = '8491' . $randomNumber;
            $u->msisdn = $msisdn;
            $u->site_id = 1;
            $u->username = $msisdn;
            $u->setPassword($password);
            $u->verification_code = $password;
            $u->email = $msisdn . '@gmail.com';
            $u->status = Subscriber::STATUS_ACTIVE;
            $u->sex = (int)CUtils::randomString(1, "01");
            $u->client_type = (int)CUtils::randomString(1, "1234");
            $u->auto_renew = (int)CUtils::randomString(1, "01");
            if ($u->save()) {
                echo "User created!: username:" . $u->msisdn . "\n";
            } else {
                echo "Cannot create User! \n";
            }
        }
    }

    public function actionCreateSubscriberServiceDemo()
    {
        $site_id = 1;
        $uid = (int)rand(8, 108);
        $type = (int)rand(1, 9);
        $channel_type = (int)rand(1, 5);
        $service_id = (int)rand(5, 15);

        /** @var  $subscriber Subscriber */
        $subscriber = Subscriber::find()->andWhere(['id' => $uid])->andWhere(['status' => Subscriber::STATUS_ACTIVE])->one();
        $result = $subscriber->purchaseServicePackage($service_id, $channel_type);
        if ($result['error'] == CommonConst::API_ERROR_NO_ERROR) {
            echo "Success \n";
        } else {
            echo "False \n";
        }
//        $trans = new SubscriberTransaction();
//        $trans->subscriber_id = $uid;
//        $trans->msisdn = $user->msisdn;
//        $trans->type = $type;
    }

    public function actionCreateCampaign()
    {
        for ($i = 51; $i < 100; $i++) {
            $campaign = new Campaign();
            $campaign->site_id = 1;
            $campaign->name = "Chiến dịch " . $i;
            $campaign->status = (int)rand(0, 3);
            $campaign->type = (int)rand(1, 12);
            $campaign->activated_at = (int)rand(1488499200, 1496448000);
            $campaign->expired_at = (int)rand(1499040000, 1501718400);
            $campaign->priority = (int)rand(1, 100);
            $campaign->number_promotion = (int)rand(1, 100);
            $campaign->save(false);
        }
//        $campaign = new Campaign();
//        $campaign->site_id = 1;
//        $campaign->name = "Chiến dịch 4";
//        $campaign->status  = (int)rand(0,3);
//        $campaign->type  = (int)rand(1,11);
//        $campaign->activated_at  = (int)rand(1488499200,1496448000);
//        $campaign->activated_at  = (int)rand(1499040000,1501718400);
//        $campaign->priority  = (int)rand(1,100);
//        $campaign->number_promotion  = (int)rand(1,100);
//        if(!$campaign->save()){
//            var_dump($campaign->firstErrors);
//        }

    }

    public function actionForcePromotionBoxService($subscriber_id, $campaign_id)
    {
        /** @var Device $device */
        $subscriber = Subscriber::findOne($subscriber_id);

        if (!$subscriber) {
            echo "Khong ton tai subscriber: $subscriber_id \n";
        } else {

            $campaign = Campaign::findOne($campaign_id);
            if ($campaign) {
                if ($campaign->type == Campaign::TYPE_BOX_SERVICE) {
                    /** @var CampaignPromotion $campaign_promotion */
                    $campaign_promotion = CampaignPromotion::find()->andWhere(['campaign_id' => $campaign_id])->one();
                    if ($campaign_promotion) {

                        $service = Service::findOne(['id' => $campaign_promotion->service_id]);

                        /** @var SubscriberServiceAsm $subscriber_service_asm */
                        $subscriber_service_asm = SubscriberServiceAsm::find()
                            ->andWhere(['subscriber_id' => $subscriber->id])
                            ->andWhere(['service_id' => $campaign_promotion->service_id])
                            ->andWhere(['status' => SubscriberServiceAsm::STATUS_ACTIVE])
                            ->andWhere('expired_at >= :time')->addParams([':time' => time()])
                            ->one();
                        if ($subscriber_service_asm) {
                            echo "Tai khoan da mua goi hien tai truoc do\n";
                        } else {
                            $subscriber_service_asm = SubscriberServiceAsm::createNewMapping($subscriber, $campaign_promotion->service);
                            if ($subscriber_service_asm) {
                                /** Thuc hien ban message khuyen mai va ghi log khuyen mai */

                                //luu log

                                $log = BaseLogicCampaign::createLog($subscriber, $campaign, null, Yii::$app->params['site_id'], $campaign_promotion->id);
                                if ($log) {
                                    echo "Luu log thanh cong";
                                }
                                //send mail
                                $sendmail = SmsSupport::addSmsSupport($campaign, $subscriber, $service);
                                if ($sendmail) {
                                    echo 'Gui mail thanh cong \n';
                                }

                            }
                        }
                    } else {
                        echo "Chien dich khong co goi cuoc duoc tang\n";
                    }
                } else {
                    echo "Chi ap dung khuyen mai mua box tang goi cuoc\n";
                }
            } else {
                echo "Khong ton tai chien dich co id: $campaign_id \n";
            }


        }
    }

    public function actionChangeUser($username_old, $username_new)
    {
        //tai khoan cu
        $subscriber_old = Subscriber::findOne(['username' => $username_old, 'authen_type' => Subscriber::AUTHEN_TYPE_ACCOUNT, 'status' => Subscriber::STATUS_ACTIVE]);
        if (!$subscriber_old) {
            echo "Khong ton tai user cu";
            return;
        }

        //tai khoan moi
        $subscriber_new = Subscriber::findOne(['username' => $username_new, 'authen_type' => Subscriber::AUTHEN_TYPE_ACCOUNT, 'status' => Subscriber::STATUS_ACTIVE]);

        if (!$subscriber_new) {
            echo "khong co user moi";
            return;
        }
        echo "Bat dau cap nhat \n";

        //tim tat ca log campaign cua user cu
        $logCampaignPromotion = LogCampaignPromotion::findAll(['subscriber_name' => $username_old]);
        if ($logCampaignPromotion) {
            foreach ($logCampaignPromotion as $item) {
                /** @var $item LogCampaignPromotion */
                $item->subscriber_name = $username_new;
                $item->subscriber_id = $subscriber_new->id;
                $item->updated_at = time();
                if (!$item->save()) {
                    echo "Cap nhat log loi cua user " . $username_new . " co log id la " . $item->id . " \n";
                }
            }
        }

        //tim tat ca goi cuoc cua user cu
        $subscriberServiceAsm = SubscriberServiceAsm::findAll(['subscriber_id' => $subscriber_old->id]);
        if ($subscriberServiceAsm) {
            foreach ($subscriberServiceAsm as $item) {
                /** @var $item SubscriberServiceAsm */
                $item->subscriber_id = $subscriber_new->id;
                $item->updated_at = time();
                if (!$item->save()) {
                    echo "Cap nhat goi cuoc " . $item->service_id . " cua user " . $subscriber_old->id . " that bai \n";
                }
            }
        }

        //tim tat ca trong bang map thiet bi user cu

        $subscriberDeviceAsm = SubscriberDeviceAsm::findOne(['subscriber_id' => $subscriber_old->id]);
        /** @var $subscriberDeviceAsm SubscriberDeviceAsm */
        if ($subscriberDeviceAsm) {
            $subscriberDeviceAsm->subscriber_id = $subscriber_new->id;
            $subscriberDeviceAsm->status = SubscriberDeviceAsm::STATUS_ACTIVE;
            $subscriberDeviceAsm->save();
        }

        //cap nhat gan lai cho user moi
        $smsUserAsm = SmsUserAsm::findAll(['user_id' => $subscriber_old->id]);
        if ($smsUserAsm) {
            foreach ($smsUserAsm as $item) {
                /** @var $item SmsUserAsm */
                $item->user_id = $subscriber_new->id;
                $item->save();
            }
        }

        echo "Ket thuc cap nhat \n";

    }

    public function actionUpdateStatusTransaction()
    {
        /** @var SubscriberTransaction[] $transactions */
        $transactions = SubscriberTransaction::find()
            ->andWhere(['type' => SubscriberTransaction::TYPE_TOPUP_ATM])
            ->andWhere(['status' => SubscriberTransaction::STATUS_PENDING])
            ->all();


        foreach ($transactions as $trans) {
            $result = false;
            if ($trans->transaction_time + Yii::$app->params['smartgate_timeout'] > time()) {
                //TODO call smartgate
                $smartgate = new SmartGateHelper();
                $result = $smartgate->checkTrans($trans);
                if ($result) {

                }

            }
        }
    }
} 