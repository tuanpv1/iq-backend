<?php

/**
 * Swiss army knife to work with user and rbac in command line
 * @author: Nguyen Chi Thuc
 * @email: gthuc.nguyen@gmail.com
 */

namespace console\controllers;

use common\auth\helpers\AuthHelper;
use common\helpers\StringUtils;
use common\models\AuthItem;
use common\models\BaseLogicCampaign;
use common\models\Campaign;
use common\models\Dealer;
use common\models\Device;
use common\models\LogCampaignPromotion;
use common\models\Site;
use common\models\Subscriber;
use common\models\SubscriberActivity;
use common\models\SubscriberDeviceAsm;
use common\models\SubscriberToken;
use common\models\User;
use DateTime;
use ReflectionClass;
use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\StringHelper;
use yii\helpers\VarDumper;
use yii\rbac\DbManager;
use yii\rbac\Item;
use common\models\SubscriberServiceAsm;

/**
 * UserController create user in commandline
 */
class UserController extends Controller
{


    /**
     * Sample: ./yii user/create-subscriber-service "1" "1" "1"
     * @param $service_id
     * @param $subscriber_id
     * @param $site_id
     */
    public function actionCreateSubscriberService($service_id, $subscriber_id, $site_id)
    {
        $sc = new SubscriberServiceAsm();
        $sc->site_id = $site_id;
        if ($site_id == 1) {
            $sc->service_id = $service_id;
            $sc->service_name = "Pk1";
        } elseif ($site_id == 2) {
            $sc->service_id = $service_id;
            $sc->service_name = "Xp2";
        }
        $sc->subscriber_id = 3;
        $sc->msisdn = "0987658888";
        $sc->activated_at = time();
        $sc->expired_at = time();
        if ($sc->save()) {
            echo 'Done !';
        } else {
            var_dump($sc->getFirstErrors());
        }
    }

    /**
     * Sample: ./yii be-user/create-admin-user "thucnc@vivas.vn" "123456"
     * @param $email
     * @param $password
     * @throws Exception
     */
    public function actionCreateAdminUser($email, $password)
    {
        $this->actionCreateUser('admin', $email, $password);
    }


    /**
     * Sample: ./yii be-user/create-dealer-user "huydq" "huydq@vivas.vn" "123456" 1
     * @param $username
     * @param $email
     * @param $password
     * @param $dealer_id
     * @throws Exception
     */
    public function actionCreateDealerUser($user, $email, $password, $dealer_id)
    {
        $sp_user = $this->actionCreateUser($user, $email, $password);
        /**
         * @var $dealer Dealer
         */
        $dealer = Dealer::findOne($dealer_id);
        if (!$dealer) {
            echo "Dealer not available\n";
        }
        $sp_user->site_id = $dealer->site_id;
        $sp_user->dealer_id = $dealer->id;
        $sp_user->type = User::USER_TYPE_DEALER;
        $sp_user->update();
    }

    /**
     * Sample: ./yii be-user/create-sp-user "huydq" "huydq@vivas.vn" "123456" 1
     * @param $username
     * @param $email
     * @param $password
     * @param $sp_id
     * @throws Exception
     */
    public function actionCreateSpUser($user, $email, $password, $sp_id)
    {
        $sp_user = $this->actionCreateUser($user, $email, $password);
        $sp_user->site_id = $sp_id;
        $sp_user->type = User::USER_TYPE_SP;
        $sp_user->update();
        return $sp_user;
    }

    public function actionSetPassword($user, $password)
    {
        $user = User::findByUsername($user);
        if ($user) {
            $user->setPassword($password);
            if ($user->save()) {
                echo 'Password changed!\n';
                return 0;
            } else {
                Yii::error($user->getErrors());
                VarDumper::dump($user->getErrors());
                throw new Exception("Cannot change password!");
            }
        } else {
            echo "User not found!\n";
            return 1;
        }
    }

    /**
     * @param $username
     * @param $email
     * @param $password
     * @param string $full_name
     * @return $user User
     * @throws Exception
     */
    public function actionCreateUser($username, $email, $password, $full_name = "")
    {
        $user = new User();
        $user->username = $username;
        $user->status = User::STATUS_ACTIVE;
//        $user->full_name = $full_name;
        $user->email = $email;
//        $user->type = $type;
        $user->setPassword($password);
        $user->generateAuthKey();

        if ($user->save()) {
            echo 'User created!\n';
            return $user;
        } else {
            Yii::error($user->getErrors());
            VarDumper::dump($user->getErrors());
            throw new Exception("Cannot create User!");
        }
    }

    /**
     * Add permission.
     * Sample: ./yii be-user/add-permission createUser "Create backend user" "be-user/create" UserManager
     * @param $name
     * @param $description
     * @param $route
     * @param null $parent
     */
    public function actionAddPermission($name, $description, $route, $parent = null)
    {
        $this->addAuthItem($name, $description, $route, AuthItem::TYPE_PERMISSION, $parent);

    }

    public function actionAddRole($name, $description, $route = null, $parent = null)
    {
        $this->addAuthItem($name, $description, $route, AuthItem::TYPE_ROLE, $parent);
    }

    /**
     * Assign permission/role to user
     * Sample: ./yii be-user/assign admin createUser
     * @param $username
     * @param $auth_item
     */
    public function actionAssign($username, $auth_item)
    {
        /* @var $auth DbManager */
        $auth = Yii::$app->authManager;
        $user = User::findByUsername($username);
        if (!$user) {
            echo "User not found!\n";
            return 1;
        }

        $item = $auth->getPermission($auth_item);
        if (!empty($item)) {
            echo "Permission with name `$auth_item` found\n";
        } else {
            $item = $auth->getRole($auth_item);
            if (!empty($item)) {
                echo "Role with name `$auth_item` found\n";
            } else {
                echo "No auth_item named `$auth_item` found\n";
                return 1;
            }
        }

        if (!$auth->getAssignment($auth_item, $user->id)) {
            $auth->assign($item, $user->id);
            echo "Auth_item `$auth_item` has been assigned to `$username`\n";
        } else {
            echo "Assignment existed!\n";
        }
    }

    private function addAuthItem($name, $description, $route, $type, $parent)
    {
        /* @var $auth DbManager */
        $auth = Yii::$app->authManager;

        $item = $auth->getRole($name);
        $newItem = false;
        if (!empty($item)) {
            echo "Role with name `$name` existed, update it...\n";
        } else {
            $item = $auth->getPermission($name);
            if (!empty($item)) {
                echo "Permission with name `$name` existed, update it...\n";
            } else {
                $newItem = true;
                if ($type == AuthItem::TYPE_ROLE) {
                    $item = $auth->createRole($name);
                } else {
                    $item = $auth->createPermission($name);
                }
            }
        }

        if ($route) {
            $item->data = $route;
        }

        $item->description = $description;
        if (!empty($parent)) {
            /* @var $parentItem Item */
            $parentItem = $auth->getRole($parent);
            if (empty($parentItem)) {
                $parentItem = $auth->getPermission($parent);
            }
            if (empty($parentItem)) {
                echo "Parent item not found\n";
                return 1;
            }

            if ($auth->hasChild($parentItem, $item)) {
                echo "Parent-child asm already exited\n";
            } else {
                $auth->addChild($parentItem, $item);
            }
        }

        if ($newItem) {
            $auth->add($item);
        }
        return 0;
    }

    public function actionListActions($alias = '@app')
    {
        $actionAuth = AuthHelper::listActions(@$alias);
        VarDumper::dump($actionAuth);
    }

    public function actionClearFirstLogin($username, $site_id, $mac)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (empty($mac)) {
                print("****** ERROR! Clear Fail MAC Cannot empty ****** \n");
                exit();
            }
            $mac = strtoupper($mac);
            $type = [Campaign::TYPE_BOX_CASH, Campaign::TYPE_BOX_CONTENT, Campaign::TYPE_BOX_SERVICE];
            $sub = Subscriber::findOne(['username' => $username]);
            if (!isset($sub) && empty($sub)) {
                print("****** ERROR! Clear Fail Cannot find username " . $username . " ****** \n");
                exit();
            }
            print("Xoa first login trong qua trinh dien ra CD cho Subscriber_id:" . $sub->id . " \n");
            $log_promotion = LogCampaignPromotion::find()
                ->andWhere(['subscriber_id' => $sub->id])
                ->andWhere(['IN', 'type', $type])
                ->all();
            if (isset($log_promotion) && !empty($log_promotion)) {
                foreach ($log_promotion as $item) {
                    $item->delete();
                }
            }

            print("Xoa lan dau dang nhap tren box " . $mac . " \n");
            $device = Device::findOne(['device_id' => $mac, 'status' => Device::STATUS_ACTIVE]);
            if (empty($device)) {
                print("****** ERROR! Khong tim thay mac '.$mac.'****** \n");
            }
            $campaign = Campaign::find()
                ->andWhere(['status' => Campaign::STATUS_ACTIVATED])
                ->orWhere(['status' => Campaign::STATUS_RUNNING_TEST])
                ->andWhere(['site_id' => $site_id])
                ->andWhere(['IN', 'type', $type])
                ->andWhere('expired_at >= :expired_at', ['expired_at' => time()])
                ->orderBy(['activated_at' => SORT_DESC, 'priority' => SORT_DESC])->all();
            if (isset($campaign) && !empty($campaign)) {
                foreach ($campaign as $item) {
                    /** @var Campaign $item */
                    $sub_activity = SubscriberActivity::find()
                        ->andWhere(['device_id' => $device->id])
                        ->andWhere(['site_id' => $site_id])
                        ->andWhere(['action' => SubscriberActivity::ACTION_LOGIN])
                        ->andWhere(['status' => SubscriberActivity::STATUS_SUCCESS])
                        ->andWhere('created_at <= :end', [':end' => $item->expired_at])
                        ->andWhere('created_at >= :start', [':start' => $item->activated_at])
                        ->all();
                    if (isset($sub_activity) && !empty($sub_activity)) {
                        foreach ($sub_activity as $item1) {
                            if ($item1->delete()) {
                                print("****** SUCCESS! Xoa thanh cong SA_ID: '.$item->id.'****** \n");
                            } else {
                                print("****** ERROR! Xoa khong thanh cong item_id '.$item->id.'****** \n");
                            }
                        }
                    }
                }
            }
            $log_promotion = LogCampaignPromotion::find()
                ->andWhere(['mac_address' => $mac])
                ->andWhere(['IN', 'type', $type])
                ->all();
            if (isset($log_promotion) && !empty($log_promotion)) {
                foreach ($log_promotion as $item) {
                    $item->delete();
                }
            }
            $transaction->commit();
            print("****** SUCCESS! Clear success first login " . $username . " ****** \n");
        } catch (Exception $e) {
            $transaction->rollBack();
            echo '****** ERROR! Clear Fail Exception: ' . $e->getMessage() . '****** \n';
        }
    }

    public function actionInsertUser($slg, $site_id, $group_subscriber_id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $array1 = [10, $site_id, $group_subscriber_id, 2];
            $mag1 = $array1;
            for ($i = 1; $i <= $slg; $i++) {
                array_splice($mag1, 4, 0, 'username_' . $i);
                $data[] = $mag1;
                $mag1 = $array1;
            }
            echo "Start insert: " . date("d-m-Y H:i:s", time()) . "\n";
            Yii::$app->db->createCommand()->batchInsert('group_subscriber_user_asm', ['status', 'site_id', 'group_subscriber_id', 'type', 'username'], $data)->execute();
            $transaction->commit();
            echo "Insert success: " . date("d-m-Y H:i:s", time());
        } catch (Exception $e) {
            $transaction->rollBack();
            echo '****** ERROR! Insert Fail Exception: ' . $e->getMessage() . '****** \n';
        }
    }


    public function actionUniqueSub()
    {
        echo "Start \n";
        /** @var Subscriber $sub */
        foreach (Subscriber::find()->each(100) as $sub) {

            echo "Subscriber id $sub->username : $sub->id \n";

            if ($sub->username == "") {
                continue;
            }
            /** @var Subscriber[] $subscribers */
            $subscribers = Subscriber::find()->andFilterWhere(['!=', 'id', $sub->id])
                ->andWhere(['username' => $sub->username])->all();

            /** @var SubscriberToken $subscriber_token */
            $subscriber_token = SubscriberToken::find()
                ->andWhere(['subscriber_id' => $sub->id])
                ->orderBy(['created_at' => SORT_DESC])
                ->one();

            $recent_token = $subscriber_token ? $subscriber_token->created_at : 0;
            $recent_subscriber = $sub;

            foreach ($subscribers as $subscriber) {

                if ($subscriber->username == "") {
                    continue;
                }
                /** @var SubscriberToken $token */
                $token = SubscriberToken::find()
                    ->andWhere(['subscriber_id' => $subscriber->id])
                    ->orderBy(['created_at' => SORT_DESC])
                    ->one();

                $token_time = $token ? $token->created_at : 0;

                if ($recent_token == $token_time) {
                    if ($subscriber->id > $recent_subscriber->id) {
                        $recent_subscriber->username = "bak_" . $recent_subscriber->id . "_" . $recent_subscriber->username;
                        $recent_subscriber->status = Subscriber::STATUS_INACTIVE;
                        $recent_subscriber->save(false);

                        $recent_subscriber = $subscriber;
                    } else {
                        $subscriber->username = "bak_" . $subscriber->id . "_" . $subscriber->username;
                        $subscriber->status = Subscriber::STATUS_INACTIVE;
                        $subscriber->save(false);
                    }
                } else {
                    if ($recent_token > $token_time) {
                        $subscriber->username = "bak_" . $subscriber->id . "_" . $subscriber->username;
                        $subscriber->status = Subscriber::STATUS_INACTIVE;
                        $subscriber->save(false);
                    } else {
                        $recent_subscriber->username = "bak_" . $recent_subscriber->id . "_" . $recent_subscriber->username;
                        $recent_subscriber->status = Subscriber::STATUS_INACTIVE;
                        $recent_subscriber->save(false);

                        $recent_subscriber = $subscriber;
                        $recent_token = $token_time;
                    }
                }
            }
        }

        echo "Finish \n";
    }

}
