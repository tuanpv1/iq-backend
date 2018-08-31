<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 21/05/2015
 * Time: 9:43 AM
 */

namespace api\controllers;

use api\helpers\APIHelper;
use api\helpers\Message;
use api\helpers\UserHelpers;
use api\models\CampaignPromotion;
use common\helpers\BrandnameVacAccount;
use common\helpers\CommonConst;
use common\helpers\VACHelper;
use common\models\Ads;
use common\models\AdsClickLog;
use common\models\BaseLogicCampaign;
use common\models\Campaign;
use common\models\CampaignCondition;
use common\models\CampaignGroupSubscriberAsm;
use common\models\City;
use common\models\Content;
use common\models\ContentSearch;
use common\models\ContentSiteAsm;
use common\models\ContentViewLog;
use common\models\ContentViewLogSearch;
use common\models\Device;
use common\models\GroupSubscriberUserAsm;
use common\models\LogCampaignPromotion;
use common\models\ManagerDeviceNotification;
use common\models\Notification;
use common\models\Service;
use common\models\SmsSupport;
use common\models\Subscriber;
use common\models\SubscriberActivity;
use common\models\SubscriberDeviceAsm;
use common\models\SubscriberFavorite;
use common\models\SubscriberFeedback;
use common\models\SubscriberServiceAsm;
use common\models\SubscriberServiceAsmSearch;
use common\models\SubscriberToken;
use common\models\SubscriberTransaction;
use common\models\SubscriberTransactionSearch;
use common\models\Voucher;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidValueException;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class SubscriberController extends ApiController
{
    const CHECK_ACCOUNT_MAINTAIN = 1;
    const CHECK_ACCOUNT_LINKED = 2;
    const CHECK_ACCOUNT_FALSE = 3;

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = [
            'feedbacks',
            'register',
            'login',
            'login-sso',
            'login-machine',
            'login-not-vac',
            'list-feedback',
            'get-msisdn',
            'verify-otp-password',
            'send-otp-password',
            'delete-sub',
            'change-pass-voucher',
            'change-mpin-voucher',
            'topup-subscriber',
            'check-account',
            'validate-mac',
        ];

        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'register' => ['GET'],
            'login' => ['GET'],
            'change-password' => ['GET'],
            'edit-profile' => ['GET'],
            'feedback' => ['POST'],
            'list-feedback' => ['GET'],
            'my-favorite' => ['GET'],
            'favorite' => ['GET'],
            'favorites' => ['GET'],
            'change-package' => ['POST'],
            'purchase-service-package' => ['POST'],
            'cancel-service-package' => ['POST'],
            'get-msisdn' => ['GET'],
            'download' => ['POST'],
            'verify-otp-password' => ['POST'],
            'send-otp-password' => ['GET'],
            'purchase-content' => ['GET'],
            'transaction-id' => ['POST'],
            'save-time-view' => ['GET'],
            'last-time-view' => ['GET'],
            'transaction-voucher-phone' => ['POST'],
            'transaction-voucher' => ['POST'],
            'province' => ['GET'],
        ];
    }


    public function actionTest()
    {
        /** Save SubscriberActivity */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        $description = 'cuongvm' . ' login time:' . date('d-m-Y H:i:s', time());
        $s = SubscriberActivity::createSubscriberActivity($subscriber, $description, 7, 5);
        if ($s) {
            echo $s['message'];
        } else {
            echo ' dung: ' . $s['message'];
        }
    }

    /**
     * @description Cho phép đăng ký account theo username, password.
     * @param $username
     * @param $password
     * @param $msisdn
     * @param $city
     * @param $channel
     * @return array
     * @throws ServerErrorHttpException
     */
    public function actionRegister($username, $password, $msisdn, $city, $channel = Subscriber::CHANNEL_TYPE_ANDROID)
    {
        $site_id = $this->site->id;
        if (empty($username)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['Tên đăng nhập']));
        }
        /** Không yêu cầu validate msisdn */
        if (empty($msisdn)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['Số điện thoại']));
        }

        if (empty($password)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['Mật khẩu']));
        }

        if (empty($city)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['Tỉnh/Thành phố']));
        }

        $u = Subscriber::findOne(['username' => $username, 'status' => [Subscriber::STATUS_ACTIVE, Subscriber::STATUS_INACTIVE]]);
        if ($u) {
            throw new InvalidValueException(Message::getExitsUsernameMessage());
        }


        $res = Subscriber::register($username, $password, $msisdn, $city, Subscriber::STATUS_ACTIVE, Subscriber::AUTHEN_TYPE_ACCOUNT, $site_id, $channel, null);

        if ($res['status']) {

            return ['message' => $res['message'],
                'subscriber' => $res['subscriber'],
            ];
        } else {
            throw new ServerErrorHttpException($res['message']);
        }
    }

    public function actionLogin($username, $password, $mac_address, $package_name, $channel = Subscriber::CHANNEL_TYPE_ANDROID, $authen_type = Subscriber::AUTHEN_TYPE_MAC_ADDRESS)
    {
        $site_id = $this->site->id;
        /** validate input */
        if (empty($username)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), [Yii::t('app', 'Tên đăng nhập')]));
        }
        if (empty($mac_address)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), [Yii::t('app', 'Mac')]));
        }
        if (empty($package_name)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), [Yii::t('app', 'package_name')]));
        }

        /** Kiểm tra xem có đúng MAC gửi lên là của VNPT Technology không */
        $device = Device::findByMac($mac_address, $site_id);
        if (!$device) {
            throw new NotFoundHttpException(Message::getDeviceNotExitMessage());
        }

        /**
         * Nếu kiểu authen_type = 1 thì đăng nhập bằng account, ngược lại thì là tài khoản default đăng nhập bằng MAC( không cần đăng ký)
         */
//        if ($site_id == Site::SITE_VIETNAM) {
        if ($authen_type == Subscriber::AUTHEN_TYPE_ACCOUNT) {
            /** Check tài khoản có tồn tại không? */
            /** @var  $subscriber Subscriber */
            $subscriber = Subscriber::findByUsername($username, $site_id, false);
            if (!$subscriber) {
                throw new NotFoundHttpException(Message::getWrongUserOrPassMessage());
            }
            /** Check tài khoản có bị block không? */
            /** Nếu tồn tại mà trạng thái không phải Active thì throw mess, không cho vào */
            if ($subscriber->status != Subscriber::STATUS_ACTIVE) {
                throw new ServerErrorHttpException(Message::getSubscriberInactiveMessage());
            }

            if (empty($password)) {
                throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), [Yii::t('app', 'mật khẩu')]));
            }
            if (!$subscriber->validatePassword($password)) {
                throw new InvalidValueException(Message::getWrongUserOrPassMessage());
            }
        } else {
            /** @var $subscriber Subscriber */
            $subscriber = Subscriber::findByUsername($mac_address, $site_id);
            /** Nếu không tồn tại subscriber thì tạo mới */
            if (!$subscriber) {
                $rs = Subscriber::register($mac_address, $password, null, null, Subscriber::STATUS_ACTIVE, Subscriber::AUTHEN_TYPE_MAC_ADDRESS, $site_id, $channel, $mac_address);
                if (!$rs['status']) {
                    throw new ServerErrorHttpException($rs['message']);
                }
                /** @var  $subscriber Subscriber */
                $subscriber = $rs['subscriber'];
            } else {
                /** Nếu tồn tại mà trạng thái không phải Active thì throw mess, không cho vào */
                if ($subscriber->status != Subscriber::STATUS_ACTIVE) {
                    throw new ServerErrorHttpException(Message::getSubscriberInactiveMessage());
                }
            }
        }

        /** Save SubscriberActivity */
        $description = $subscriber->username . ' login time:' . date('d-m-Y H:i:s', time());
        SubscriberActivity::createSubscriberActivity($subscriber, $description, $channel, $site_id, SubscriberActivity::ACTION_LOGIN);
        /** Gen token */
        $token = SubscriberToken::generateToken($subscriber->id, $channel, $package_name);
        if (!$token) {
            throw new ServerErrorHttpException(Message::getFailMessage());
        }
        /** save last_login */
        $subscriber->last_login_at = time();
        $subscriber->save(false);
//        /** save last_login */
        //        $device = Device::findByMac($mac_address, $site_id);
        //        $device->last_login = time();
        //        $device->save();


        return ['message' => Message::getLoginSuccessMessage(),
            'id' => $subscriber->id,
            'username' => $subscriber->username,
            'full_name' => $subscriber->full_name,
            'city' => $subscriber->city,
            'msisdn' => $subscriber->msisdn,
            'balance' => $subscriber->balance,
            'token' => $token->token,
            'expired_date' => $token->expired_at,
            'authen_type' => $subscriber->authen_type,
            'package_name' => $package_name,
            'channel' => $token->channel,
            'site_id' => $site_id,
        ];
    }

    public function actionLoginSso($access_token, $client_id = '', $fingerprint = '', $package_name = '',
                                   $client_secret = '', $mac_address, $channel = Subscriber::CHANNEL_TYPE_ANDROID)
    {
        $site_id = $this->site->id;
        /** validate input */
        if (empty($access_token)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['access token']));
        }
        if (empty($mac_address)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['Mac']));
        }
        if (empty($package_name)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['package_name']));
        }

        /** Kiểm tra xem có đúng MAC gửi lên là của VNPT Technology không */
        $device = Device::findByMac($mac_address, $site_id);
        if (!$device) {
            throw new NotFoundHttpException(Message::getDeviceNotExitMessage());
        }


        /** Gọi sang VAC get User Info */
        /** @var  $subscriber Subscriber */
        $subscriber = VACHelper::getUserInfo($access_token, $client_id, $fingerprint, $package_name,
            $client_secret, $site_id, $mac_address, $channel);


        Yii::info($subscriber);
        if (!$subscriber) {
            throw new ServerErrorHttpException(Yii::t('app', 'Không thành công, vui lòng thử lại'));
        }

        /** Check tài khoản có bị block không? */
        /** Nếu tồn tại mà trạng thái không phải Active thì throw mess, không cho vào */
        if ($subscriber->status != Subscriber::STATUS_ACTIVE) {
            throw new ServerErrorHttpException(Message::getSubscriberInactiveMessage());
        }
        // kiem tra white list
        if ($subscriber->whitelist != Subscriber::IS_WHITELIST) {
            $check = $subscriber::getDeviceSubscriber($subscriber, $device);
            if (!$check) {
                throw new ServerErrorHttpException(Yii::t('app', 'Tài khoản đăng nhập không được liên kết với thiết bị. Vui lòng liên hệ 19001525 nhánh 3 để được hỗ trợ'));
            }
        }

        /** Save SubscriberActivity */
        $description = $subscriber->username . ' login time:' . date('d-m-Y H:i:s', time());
        SubscriberActivity::createSubscriberActivity($subscriber, $description, $channel, $site_id, SubscriberActivity::ACTION_LOGIN, SubscriberActivity::STATUS_SUCCESS, $device->id, $subscriber->authen_type);
        /** Gen token */
        $token = SubscriberToken::generateToken($subscriber->id, $channel, $package_name);
        if (!$token) {
            throw new ServerErrorHttpException(Message::getFailMessage());
        }

        $type = [Campaign::TYPE_BOX_CASH, Campaign::TYPE_BOX_CONTENT, Campaign::TYPE_BOX_SERVICE];
        $campaign = BaseLogicCampaign::BuyBoxPromotion($subscriber, $device, $site_id, $type, null);
        if (!$campaign) {
            /** @var  $$campaign Campaign */
            $type = [Campaign::TYPE_REGISTER];
            $campaign = BaseLogicCampaign::BuyBoxPromotion($subscriber, null, $site_id, $type, null, true);
            if (!$campaign) {
                Yii::info('campaign', 'Khong co chien dich nao dc ap dung');
            } else {
                if (!empty($campaign->notification_title)) {
                    Yii::info("Id chien dich " . $campaign->id);
                    $sendmail = SmsSupport::addSmsSupport($campaign, $subscriber);
                    if ($sendmail) {
                        Yii::info('sendmail', 'Gui mail thanh cong');
                    }
                }
            }
        } else {
            if (!empty($campaign->notification_title)) {
                Yii::info("Id chien dich " . $campaign->id);
                $sendmail = SmsSupport::addSmsSupport($campaign, $subscriber);
                if ($sendmail) {
                    Yii::info('sendmail', 'Gui mail thanh cong');
                }
            }
        }

//        /** save last_login */
//        $device = Device::findByMac($mac_address, $site_id);
//        $device->last_login = time();
//        $device->save();


        return ['message' => Message::getLoginSuccessMessage(),
            'id' => $subscriber->id,
            'username' => $subscriber->username,
            'full_name' => $subscriber->full_name,
            'city' => $subscriber->city,
            'msisdn' => $subscriber->msisdn,
            'balance' => $subscriber->balance,
            'token' => $token->token,
            'expired_date' => $token->expired_at,
            'authen_type' => $subscriber->authen_type,
            'package_name' => $package_name,
            'channel' => $token->channel,
            'site_id' => $site_id,
            'address' => $subscriber->address,
            'email' => $subscriber->email,
        ];
    }

    //hungnd1_login khong su dung VAC
    public function actionLoginNotVac($mac_address, $channel = Subscriber::CHANNEL_TYPE_ANDROID, $package_name)
    {
        $site_id = $this->site->id;
        /** validate input */

        if (empty($mac_address)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['Mac']));
        }
        if (empty($package_name)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['package_name']));
        }

        /** Kiểm tra xem có đúng MAC gửi lên là của VNPT Technology không */
        $device = Device::findByMac($mac_address, $site_id);
        if (!$device) {
            throw new NotFoundHttpException(Message::getDeviceNotExitMessage());
        }

        $subscriber = Subscriber::find()->andWhere(['machine_name' => $mac_address])
            ->andWhere(['site_id' => $site_id])
            ->orderBy(['authen_type' => SORT_ASC, 'status' => SORT_DESC])
            ->one();

        /** Nếu không tồn tại subscriber thì tạo mới */
        if (!$subscriber) {
            $rs = Subscriber::registerNew($mac_address, "", null, null, Subscriber::STATUS_ACTIVE, Subscriber::AUTHEN_TYPE_ACCOUNT, $site_id, $channel, $mac_address);
            if (!$rs['status']) {
                throw new ServerErrorHttpException($rs['message']);
            }
            /** @var  $subscriber Subscriber */
            $subscriber = $rs['subscriber'];
        } else {
            /** @var  $subscriber Subscriber */
            if ($subscriber->authen_type == Subscriber::AUTHEN_TYPE_MAC_ADDRESS) {
                $subscriber->authen_type = Subscriber::AUTHEN_TYPE_ACCOUNT;
                $subscriber->username = $subscriber->machine_name;
                $subscriber->updated_at = time();
                $subscriber->register_at = time();
                $subscriber->save(false);
            }
            /** Nếu tồn tại mà trạng thái không phải Active thì throw mess, không cho vào */
            if ($subscriber->status != Subscriber::STATUS_ACTIVE) {
                throw new ServerErrorHttpException(Message::getSubscriberInactiveMessage());
            }
        }

        /** Save SubscriberActivity */
        $description = $subscriber->username . ' login time:' . date('d-m-Y H:i:s', time());
        SubscriberActivity::createSubscriberActivity($subscriber, $description, $channel, $site_id, SubscriberActivity::ACTION_LOGIN, SubscriberActivity::STATUS_SUCCESS, $device->id, $subscriber->authen_type);
        /** Gen token */
        $token = SubscriberToken::generateToken($subscriber->id, $channel, $package_name);
        if (!$token) {
            throw new ServerErrorHttpException(Message::getFailMessage());
        }

        /** save last_login */
        $device = Device::findByMac($mac_address, $site_id);
        $device->last_login = time();
        $device->save();

        SubscriberDeviceAsm::createSubscriberDeviceAsm($subscriber->id, $device->id);

        return ['message' => Message::getLoginSuccessMessage(),
            'id' => $subscriber->id,
            'username' => $subscriber->username,
            'full_name' => $subscriber->full_name,
            'city' => $subscriber->getProvinceName(),
            'msisdn' => $subscriber->msisdn,
            'balance' => $subscriber->balance,
            'token' => $token->token,
            'expired_date' => $token->expired_at,
            'authen_type' => $subscriber->authen_type,
            'package_name' => $package_name,
            'channel' => $token->channel,
            'site_id' => $site_id,
            'address' => $subscriber->address,
            'email' => $subscriber->email,
            'machine_name' => $subscriber->machine_name,
            'is_active' => $subscriber->is_active,
            'province_code' => $subscriber->province_code,
            'ip_to_location' => $subscriber->ip_to_location,
        ];
    }

    public function actionLoginMachine($mac_address, $package_name, $channel = Subscriber::CHANNEL_TYPE_ANDROID)
    {
        $site_id = $this->site->id;
        /** Kiểm tra xem có đúng MAC gửi lên là của VNPT Technology không */
        $device = Device::findByMac($mac_address, $site_id);
        if (!$device) {
            throw new NotFoundHttpException(Message::getDeviceNotExitMessage());
        }
        /** @var  $subscriber Subscriber */
//        $subscriber = Subscriber::findByMachine($mac_address, $site_id);
        $subscriber = Subscriber::find()->andWhere(['machine_name' => $mac_address])
            ->andWhere(['site_id' => $site_id])
            ->orderBy(['authen_type' => SORT_ASC, 'status' => SORT_DESC])
            ->one();

        /** Nếu không tồn tại subscriber thì tạo mới */
        if (!$subscriber) {
            $rs = Subscriber::register(null, null, null, null, Subscriber::STATUS_ACTIVE, Subscriber::AUTHEN_TYPE_MAC_ADDRESS, $site_id, $channel, $mac_address);
            if (!$rs['status']) {
                throw new ServerErrorHttpException($rs['message']);
            }
            /** @var  $subscriber Subscriber */
            $subscriber = $rs['subscriber'];
        } else {
            /** Nếu tồn tại mà trạng thái không phải Active thì throw mess, không cho vào */
            if ($subscriber->status != Subscriber::STATUS_ACTIVE) {
                throw new ServerErrorHttpException(Message::getSubscriberInactiveMessage());
            }
        }

        /** Save SubscriberActivity */
        $description = $subscriber->username . ' login time:' . date('d-m-Y H:i:s', time());
        SubscriberActivity::createSubscriberActivity($subscriber, $description, $channel, $site_id, SubscriberActivity::ACTION_LOGIN, SubscriberActivity::STATUS_SUCCESS, $device->id, $subscriber->authen_type);
        /** Gen token */
        $token = SubscriberToken::generateToken($subscriber->id, $channel, $package_name);
        if (!$token) {
            throw new ServerErrorHttpException(Message::getFailMessage());
        }


        return ['message' => Message::getLoginSuccessMessage(),
            'id' => $subscriber->id,
            'username' => $subscriber->username,
            'machine_name' => $subscriber->machine_name,
            'full_name' => $subscriber->full_name,
            'city' => $subscriber->getProvinceName(),
            'msisdn' => $subscriber->msisdn,
            'balance' => $subscriber->balance,
            'token' => $token->token,
            'expired_date' => $token->expired_at,
            'authen_type' => $subscriber->authen_type,
            'package_name' => $package_name,
            'channel' => $token->channel,
            'site_id' => $site_id,
        ];

    }

    /**
     * @return array
     * @throws ServerErrorHttpException
     */
    public function actionLogout()
    {
//        return ["message" => Yii::t('app', 'Không được phép đăng xuất khỏi thiết bị')];
        $site_id = $this->site->id;
        /* @var $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        /** @var  $st SubscriberToken */
        $st = SubscriberToken::findByAccessToken($subscriber->access_token);
        $st->status = SubscriberToken::STATUS_INACTIVE;
        if (!$st->save()) {
            throw new ServerErrorHttpException(Message::getFailMessage());
        }

        ManagerDeviceNotification::removeSubscriber($subscriber->id);
        /** Save SubscriberActivity */
        $description = $subscriber->username . ' logout time:' . date('d-m-Y H:i:s', time());
        SubscriberActivity::createSubscriberActivity($subscriber, $description, $st->channel, $site_id, SubscriberActivity::ACTION_LOGOUT);

        return ["message" => Message::getSuccessMessage()];
    }

    /**
     * @param $new_password
     * @param $old_password
     * @return mixed
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionChangePassword($new_password, $old_password)
    {
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }

        if ($subscriber->status != Subscriber::STATUS_ACTIVE) {
            throw new NotFoundHttpException(Message::getNotFoundUserMessage());
        }
        if (!$subscriber->validatePassword($old_password)) {
            throw new InvalidValueException(Message::getChangeOldPassFailMessage());
        }
        $subscriber->password = $new_password;
        $subscriber->setPassword($new_password);

//        $transaction = Yii::$app->db->beginTransaction();
//        try {
        if (!$subscriber->validate() || !$subscriber->save()) {
            $message = $subscriber->getFirstMessageError();
            throw new InvalidValueException($message);
        }
        /** Xóa tokent khi đổi password */
        /** @var  $st SubscriberToken */
        $st = SubscriberToken::findByAccessToken($subscriber->access_token);
        $st->status = SubscriberToken::STATUS_INACTIVE;
        if (!$st->save()) {
            throw new ServerErrorHttpException(Message::getFailMessage());
        }
        return ['message' => Message::getChangePassSuccessMessage()];
//            $res = VACHelper::changePassword($subscriber,$old_password,$new_password);
//            if ($res['success']) {
//                $transaction->commit();
//                return ['message' => $res['message']];
//            } else {
//                throw new BadRequestHttpException($res['message']);
//            }
//        } catch (\yii\base\Exception $e) {
//            $transaction->rollBack();
//            throw new BadRequestHttpException(Message::getErrorSystemMessage());
//        }

//        $res['message'] = Message::getChangePassSuccessMessage();
//        return [];
    }


    public function actionChangePasswordSso($new_password, $old_password)
    {
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }

        if ($subscriber->status != Subscriber::STATUS_ACTIVE) {
            throw new NotFoundHttpException(Message::getNotFoundUserMessage());
        }
//        if (!$subscriber->validatePassword($old_password)) {
//            throw new InvalidValueException(Message::getChangeOldPassFailMessage());
//        }
        $subscriber->password = $new_password;
        $subscriber->setPassword($new_password);

//        $transaction = Yii::$app->db->beginTransaction();
//        try {
//            if (!$subscriber->validate() || !$subscriber->save()) {
//                $message = $subscriber->getFirstMessageError();
//                throw new InvalidValueException($message);
//            }


        $res = VACHelper::changePassword($subscriber, $old_password, $new_password);
        if ($res['success']) {
//                $transaction->commit();
            /** Xóa tokent khi đổi password */
            /** @var  $st SubscriberToken */
            $st = SubscriberToken::findByAccessToken($subscriber->access_token);
            $st->status = SubscriberToken::STATUS_INACTIVE;
            if (!$st->save()) {
//                    throw new ServerErrorHttpException(Message::getFailMessage());
            }
            return ['message' => $res['message']];
        } else {
            if ($res['error'] == 103) {
                throw new ConflictHttpException($res['message']);
            } else {
                throw new BadRequestHttpException($res['message']);
            }

        }
//        } catch (\yii\base\Exception $e) {
//            $transaction->rollBack();
//            throw new BadRequestHttpException(Message::getErrorSystemMessage());
//        }

//        $res['message'] = Message::getChangePassSuccessMessage();
//        return [];
    }

    /**
     * @return array
     */
    public function actionInfo()
    {
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }

        $rs = \api\models\Subscriber::findOne($subscriber->id);
//        $rs = $subscriber->getAttributes([
//            'id',
//            'username',
//            'machine_name',
//            'full_name',
//            'balance',
//            'msisdn',
//            'city',
//            'province_code',
//            'ip_to_location',
//            'address',
//            'status',
//            'birthday',
//            'sex',
//            'email',
//            'site_id',
//            'created_at',
//            'updated_at'],
//            ['password_hash', 'authen_type']
//        );
        return $rs;
    }

    /**
     * @return mixed
     * @throws ServerErrorHttpException
     */
    public function actionEditProfile()
    {
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        $full_name = $this->getParameter('full_name', '');
        $email = $this->getParameter('email', '');
        $birthday = $this->getParameter('birthday', '');
        $sex = $this->getParameter('sex', 0);
        $msisdn = $this->getParameter('msisdn', '');
        $address = $this->getParameter('address', '');
//        if ($full_name) {
        $subscriber->full_name = $full_name;
//        }
//        if ($address) {
        $subscriber->address = $address;
//        }
//        if ($email) {
        $subscriber->email = $email;
//        }
//        if ($birthday) {
        $subscriber->birthday = $birthday;
//        }
        $subscriber->sex = $sex;
//        if ($msisdn) {
        $subscriber->msisdn = $msisdn;
//        }

        if (!$subscriber->validate() || !$subscriber->save()) {
//            $message = $subscriber->getFirstMessageError();
//            throw new InvalidValueException($message);
            throw new ServerErrorHttpException(Message::getFailMessage());
        }
        $res['message'] = Message::getUpdateProfileMessage();
        return $res;

    }

    public function actionSaveTimeView($content_id, $category_id, $channel, $type = ContentViewLog::TYPE_VIDEO, $record_type = ContentViewLog::IS_STOP, $start_time = 0, $stop_time = 0, $duration = 0, $log_id = null)
    {
        $site_id = $this->site->id;
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        if (!is_numeric($content_id)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['content_id']));
        }
        if (!is_numeric($category_id)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['category_id']));
        }
        if (!is_numeric($channel)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['channel']));
        }
        if (!is_numeric($record_type)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['record_type']));
        }
        /** @var  $content Content */
//        $content = Content::findOne(['id' => $content_id, 'status' => Content::STATUS_ACTIVE]);

        $content = Content::find()
            ->joinWith('contentSiteAsms')
            ->andWhere(['content_site_asm.site_id' => $this->site->id, 'content_site_asm.status' => ContentSiteAsm::STATUS_ACTIVE])
            ->andWhere(['content.id' => $content_id, 'content.status' => Content::STATUS_ACTIVE])
            ->one();
        if (!$content) {
            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
        }
        /** Lưu thời gian của phim */
        if ($duration) {
            $content->duration = $duration;
            $content->save();
        }
        /** Chỉ ghi 1 bản ghi đối với 1 content& 1 channel , 1 type*/
//        if(!$log_id){
        //            $cvl = ContentViewLog::findOne(['subscriber_id'=>$subscriber->id,'content_id'=>$content_id, 'channel'=>$channel,'site_id'=>$site_id,'type' =>$type,'status'=>ContentViewLog::STATUS_SUCCESS]);
        //            $cvl?$log_id = $cvl->id:$log_id=null;
        //        }

        $rs = ContentViewLog::createViewLog($subscriber, $content, $category_id, $type, $record_type, $channel, $site_id, $start_time, $stop_time, $log_id);

        if (!$rs['status']) {
            throw new ServerErrorHttpException($rs['message']);
        }
        return ['message' => $rs['message'], 'log' => $rs['item']];

    }

    public function actionSaveLogAdsClick($ads_id, $channel)
    {
        $site_id = $this->site->id;
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        if (!is_numeric($ads_id)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['ads_id']));
        }
        if (!is_numeric($channel)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['channel']));
        }
        $ads = Ads::find()
            ->where(['id' => $ads_id])
            ->andWhere(['status' => Ads::STATUS_ACTIVE])
            ->andWhere(['site_id' => $this->site->id])
            ->one();
        if (!$ads) {
            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
        }

        $rs = AdsClickLog::createAdsClickLog($subscriber, $ads, $channel, $site_id);

        if (!$rs['status']) {
            throw new ServerErrorHttpException($rs['message']);
        }
//        return ['message' => $rs['message'], 'log' => $rs['item']];

    }

    /**
     * @param $content_id
     * @param $channel
     * @return array|null|\yii\db\ActiveRecord
     * @throws NotFoundHttpException
     */
    public function actionLastTimeView($content_id, $channel)
    {
        $site_id = $this->site->id;
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }

//        $content = Content::findOne(['id' => $content_id, 'status' => Content::STATUS_ACTIVE]);
        $content = Content::find()
            ->joinWith('contentSiteAsms')
            ->andWhere(['content_site_asm.site_id' => $this->site->id, 'content_site_asm.status' => ContentSiteAsm::STATUS_ACTIVE])
            ->andWhere(['content.id' => $content_id, 'content.status' => Content::STATUS_ACTIVE])
            ->one();
        if (!$content) {
            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
        }
        $log = ContentViewLog::find()->where(['subscriber_id' => $subscriber->id, 'content_id' => $content_id, 'channel' => $channel, 'site_id' => $site_id])
            ->orderBy('view_date DESC')->one();
        if (!$log) {
            throw new NotFoundHttpException(Yii::t('app', "Không tìm thấy"));
        }

        return $log;
    }

    /**
     * @param $content_id
     * @param $type
     * @param int $status = 1 add, $status = 0 remove
     * @return array
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionFavorite($content_id, $status, $type)
    {
        $site_id = $this->site->id;
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        /** @var  $content Content */
//        $content = Content::findOne(['id' => $content_id, 'status' => Content::STATUS_ACTIVE]);
        $content = Content::find()
            ->joinWith('contentSiteAsms')
            ->andWhere(['content_site_asm.site_id' => $this->site->id, 'content_site_asm.status' => ContentSiteAsm::STATUS_ACTIVE])
            ->andWhere(['content.id' => $content_id, 'content.status' => Content::STATUS_ACTIVE])
            ->one();
        if (!$content) {
            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
        }

        if (!is_numeric($status)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['status']));
        }
        if (!is_numeric($type)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['type']));
        }
        $rs = SubscriberFavorite::createFavorite($subscriber, $content, $site_id, $status, $type);
        if ($rs) {
            if ($status) {
                return ['message' => Message::getFavoriteSuccessMessage()];
            } else {
                return ['message' => Message::getUnFavoriteSuccessMessage()];
            }
        } else {
            throw new ServerErrorHttpException(Message::getFavoriteFailMessage());
        }
    }

    /**
     * @param $type
     * @return \yii\data\ActiveDataProvider
     */
    public function actionMyFavorite($type)
    {
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        $searchModel = new ContentSearch();

        $param = Yii::$app->request->queryParams;
        $searchModel->site_id = $this->site->id;
        $searchModel->type = $type;
        $searchModel->order = isset($param['order']) ? ($param['order']) : Content::ORDER_NEWEST;
        $searchModel->status = Content::STATUS_ACTIVE;
        $searchModel->subscriber_id = $subscriber->id;
        $searchModel->is_series = isset($param['is_series']) ? ($param['is_series']) : Content::IS_MOVIES;
        $searchModel->is_live = isset($param['is_live']) ? ($param['is_live']) : Content::IS_LIVE;

        if (!$searchModel->validate()) {
            $error = $searchModel->firstErrors;
            $message = "";
            foreach ($error as $key => $value) {
                $message .= $value;
                break;
            }
            throw new InvalidValueException($message);
        }
        $dataProvider = $searchModel->search($param);
        return $dataProvider;

    }

    /**
     * @param  type
     * @return \yii\data\ActiveDataProvider
     */
    public function actionWatchedVideo($type)
    {
        UserHelpers::manualLogin();
        $param = Yii::$app->request->queryParams;
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        if (!is_numeric($type)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['type']));
        }
        $searchModel = new ContentViewLogSearch();
        $searchModel->site_id = $this->site->id;
        $searchModel->type = $type;
        $searchModel->status = Content::STATUS_ACTIVE;
        $searchModel->subscriber_id = $subscriber->id;
        $searchModel->is_series = isset($param['is_series']) ? ($param['is_series']) : Content::IS_MOVIES;
        $searchModel->is_live = isset($param['is_live']) ? ($param['is_live']) : Content::IS_LIVE;

        if (!$searchModel->validate()) {
            $error = $searchModel->firstErrors;
            $message = "";
            foreach ($error as $key => $value) {
                $message .= $value;
                break;
            }
            throw new InvalidValueException($message);
        }
        $dataProvider = $searchModel->getListViewLog($param);
        return $dataProvider;
    }

    public function actionTransaction()
    {
        $site_id = $this->site->id;
        $param = Yii::$app->request->queryParams;
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        $searchModel = new SubscriberTransactionSearch();
        $searchModel->subscriber_id = $subscriber->id;
        $searchModel->site_id = $site_id;

        $dataProvider = $searchModel->search($param);
        return $dataProvider;
    }

    /**
     * HungNV 14 April
     *
     * @return array
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionFeedback()
    {
        /*
         * HungNV
         *
         * feedback to site about Problems or bad Contents
         *  so this not relate to rating, like, etc...
         */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new UnauthorizedHttpException(Message::getAccessDennyMessage());
        }
        $site_id = $this->site->id;
        /* Change to POST later */
        $content_id = $this->getParameterPost('content_id', '');
        $title = $this->getParameterPost('title', '');
        $content = $this->getParameterPost('content', '');
        $res = SubscriberFeedback::createFeedback($subscriber, $site_id, $content_id, $title, $content);
        if ($res) {
            return ['message' => Message::getFeedbackSuccessMessage()];
        } else {
            throw new ServerErrorHttpException(Message::getActionFailMessage());
        }
    }

    /**
     * HungNV 14-April
     *
     * @params content_id, from_date, to_date (statistic report)
     * @return \yii\data\ActiveDataProvider
     */
    public function actionFeedbacks()
    {
        $content_id = $this->getParameter('id');
        $from_date = $this->getParameter('from_date');
        $to_date = $this->getParameter('to_date');
        $res = SubscriberFeedback::getFeedbacks($this->site->id, $content_id, $from_date, $to_date);
        return $res;
    }


    public function actionMyService()
    {
        $site_id = $this->site->id;
        $param = Yii::$app->request->queryParams;
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new UnauthorizedHttpException(Message::getAccessDennyMessage());
        }
        $searchModel = new SubscriberServiceAsmSearch();
        $searchModel->subscriber_id = $subscriber->id;
        $searchModel->status = SubscriberServiceAsm::STATUS_ACTIVE;
        $searchModel->site_id = $site_id;

        $dataProvider = $searchModel->search($param);
        if (!$dataProvider->getModels()) {
            throw new NotFoundHttpException(Message::getNotFoundServiceMessage());
        }
        return $dataProvider;

    }

    public function actionPurchaseContent($content_id, $channel = SubscriberTransaction::CHANNEL_TYPE_ANDROID)
    {
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new UnauthorizedHttpException(Message::getAccessDennyMessage());
        }
        if (!is_numeric($content_id)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['content_id']));
        }

//        $content = Content::findOne(['id'=>$content_id,'status'=>Content::STATUS_ACTIVE]);
        $content = Content::find()
            ->joinWith('contentSiteAsms')
            ->andWhere(['content_site_asm.site_id' => $this->site->id, 'content_site_asm.status' => ContentSiteAsm::STATUS_ACTIVE])
            ->andWhere(['content.id' => $content_id, 'content.status' => Content::STATUS_ACTIVE])
            ->one();
        /** @var Content $content */
        if (!$content) {
            throw new InvalidValueException(Message::getNotFoundContentMessage());
        }

        if ($content->allow_buy_content == Content::NOT_ALLOW_BUY_CONTENT) {
            throw new InvalidValueException(Yii::t('app', 'Nội dung không được phép mua lẻ!'));
        }

        $res = $subscriber->purchaseContent($this->site, $content, $channel, SubscriberTransaction::TYPE_CONTENT_PURCHASE);

        if ($res['error'] != CommonConst::API_ERROR_NO_ERROR) {
            $this->setStatusCode(500);
            return ['message' => $res['message_web'], 'code' => $res['error']];
        }

        return $res;
    }

    public function actionPurchaseService($service_id, $number_month = null, $channel = SubscriberTransaction::CHANNEL_TYPE_ANDROID)
    {
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new UnauthorizedHttpException(Message::getAccessDennyMessage());
        }
        if (!is_numeric($service_id)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['service_id']));
        }

        if (empty($number_month)) {
            $number_month = !empty(Yii::$app->params['number_month_default']) ? Yii::$app->params['number_month_default'] : 3;
        }

        if (!is_numeric($number_month)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['number_month']));
        }

        if ($number_month <= 0) {
            throw  new InvalidValueException(Yii::t('app', 'Số tháng mua phải lớn hơn 0'));
        }

        $service = Service::findOne(['id' => $service_id, 'status' => Service::STATUS_ACTIVE, 'site_id' => $this->site->id]);
        if (!$service) {
            throw new InvalidValueException(Message::getNotFoundServiceMessage());
        }

        /** Nếu đã mua rồi thì là gia hạn, còn chưa mua thì là đăng ký mới */
        $is_my_package = $subscriber->checkMyService($service->id);
        if (!$is_my_package) {
            $type = SubscriberTransaction::TYPE_REGISTER;
        } else {
            $type = SubscriberTransaction::TYPE_RENEW;
        }


        $res = $subscriber->purchaseServicePackage($channel, $service, $type, true, 0, $number_month);

        Yii::info($res, 'KQ charge');
        if ($res['error'] != CommonConst::API_ERROR_NO_ERROR) {
            $this->setStatusCode(500);
            return ['message' => isset($res['message_web']) ? $res['message_web'] : $res['message'], 'code' => $res['error']];
        }


        $type = [Campaign::TYPE_SERVICE_CONTENT, Campaign::TYPE_SERVICE_TIME, Campaign::TYPE_SERVICE_SERVICE];
        $campaign = BaseLogicCampaign::BuyBoxPromotion($subscriber, null, $this->site->id, $type, $service, false, $number_month);
        if (empty($campaign)) {
            Yii::info('campaign', 'Không tồn tại chiến dịch nào');
        } else {
            shell_exec("nohup  ./send_sms_subscriber.sh $subscriber->id $campaign->id $service_id > /dev/null 3>&1 &");
        }

        return $res;
    }

    /**
     * @return ActiveDataProvider
     */
    public function actionContentLog()
    {
        UserHelpers::manualLogin();
        /** @var $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getNumberOnlyMessage());
        }
        $channel = $this->getParameter('channel', '');
        $content_id = $this->getParameter('content_id', '');
        $view_date = $this->getParameter('view_date', '');
        if ($channel || $content_id) {
            if (!is_numeric($channel) || !is_numeric($content_id)) {
                throw new InvalidValueException(Message::getNumberOnlyMessage());
            }
        }
        $viewLog = ContentViewLog::viewLogSearch($subscriber, $this->site->id, $channel, $content_id, $view_date);
        if (!$viewLog['status']) {
            throw new InvalidCallException($viewLog['message']);
        }
        return $viewLog['items'];
    }

    /**
     * @param $display_id
     * @return mixed
     * @throws ServerErrorHttpException
     */
    public function actionGetUnsupportedQualities($display_id)
    {
        if (empty($display_id)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['display_id']));
        }
        $res = Device::getUnsupportedQualities($display_id);
        if (!$res['success']) {
            throw new ServerErrorHttpException($res['message']);
        }
        return ['qualities' => $res['data']];
    }

    /**
     * @return mixed
     * @throws ServerErrorHttpException
     */
    public function actionSetBalance()
    {
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        $subscriber->balance = 1000000;
        if (!$subscriber->validate() || !$subscriber->save()) {
            throw new ServerErrorHttpException(Message::getFailMessage());
        }
        $res['message'] = Message::getSuccessMessage();
        return $res;
    }

    /**
     * @param $device_id
     * @param $device_type
     * @return mixed
     */
    public function actionAddMAc($device_id, $device_type)
    {
        $site_id = $this->site->id;
        /** @var  $subscriber Subscriber */

        if (empty($device_id)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['mac_address']));
        }
        if (!is_numeric($device_type)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['device_type']));
        }

        Device::createDevice($site_id, $device_id, $device_type);
        $res['message'] = Message::getSuccessMessage();
        return $res;
    }


    public function actionTransactionVoucher()
    {
        $card_serial = '';
        $c_code = Yii::$app->request->post('card_code');
        $signature = Yii::$app->request->post('signature');
        Yii::info('card code send ' . $c_code);
        Yii::info('chu ki ' . $signature);

        // Kiểm tra người dùng
        $subscriber = Yii::$app->user->identity;
        /** @var $subscriber Subscriber */
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        $subscriber = Subscriber::findOne($subscriber->id);
        $site_id = $this->site ? $this->site->id : (int)Yii::getAlias('@default_site_id');

        return Subscriber::topupVoucher($subscriber, $c_code, $site_id, $signature, $card_serial);
    }

    public function actionTransactionVoucherPhone()
    {
        $c_serial = Yii::$app->request->post('card_serial');
        $c_code = Yii::$app->request->post('card_code');
        $signature = Yii::$app->request->post('signature');
        $operator = Yii::$app->request->post('nn');

        Yii::info('Ma serial' . $c_serial);
        Yii::info('Ma code' . $c_code);
        Yii::info('chu ki ' . $signature);
        Yii::info('Ma nha mang ' . $operator);

        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        /** @var Subscriber $subscriber */
        $subscriber = Subscriber::findOne($subscriber->id);
        $site_id = $this->site ? $this->site->id : (int)Yii::getAlias('@default_site_id');
        return Subscriber::topupVoucherPhone($subscriber, $c_serial, $c_code, $site_id, $operator, $signature);
    }

    public function actionChangePhoneNumber()
    {
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }

        $msisdn = $this->getParameter('msisdn', '');

        if ($msisdn) {
            $subscriber->msisdn = $msisdn;
        }

        if (!$subscriber->save()) {
            Yii::info($subscriber->getErrors());
            throw new ServerErrorHttpException(Message::getFailMessage());
        } else {
            return ['message' => Yii::t('app', 'Cập nhật số điện thoại thành công')];
        }

//        $transaction = Yii::$app->db->beginTransaction();
//        try {
//            if (!$subscriber->save()) {
//                Yii::info($subscriber->getErrors());
//                throw new ServerErrorHttpException(Message::getFailMessage());
//            }
//
//            $res = VACHelper::changePhoneNumber($subscriber, $msisdn);
//            if ($res['success']) {
//                $transaction->commit();
//                return ['message' => $res['message']];
//            } else {
//                throw new BadRequestHttpException($res['message']);
//            }
//        } catch (Exception $e) {
//            $transaction->rollBack();
//            throw new BadRequestHttpException(Message::getErrorSystemMessage());
//        }
    }

    public function actionChangePassVoucher($pass)
    {
        $user_login = Yii::$app->params['user_voucher'];
        $pass_o = Yii::$app->params['pass_voucher'];
        $mpin = Yii::$app->params['mpin_voucher'];
        $log_link = Yii::getAlias('@runtime/logs/sessionId.log');
        if (file_exists($log_link)) {
            $sessionId = Subscriber::ReturnSessionId($log_link);
        } else {
            $sessionId = Subscriber::AddSessionID($user_login, $pass);
        }
        $key_seed = $sessionId;
        $pass_old = self::PhoneEncrypt($pass_o, $key_seed);
        $pass_new = self::PhoneEncrypt($pass, $key_seed);
        $signature1 = md5($user_login . $pass_o . $pass . $mpin);
        $json = "{
                    function:changepassword,
                    username:" . $user_login . ",
                    oldPassword:" . $pass_old . ",
                    newPassword:" . $pass_new . ",
                    signature:" . $signature1 . "
                    }";
        $result = APIHelper::apiQuery('POST', APIHelper::API_CHECK_VOUCHER, $json);
        if (isset($result)) {
            $error_code = Subscriber::GetCodeResult($result);
            if ($error_code == 0) {
                return [
                    'success' => true,
                    'message' => Yii::t('app', 'Đổi mật khẩu thành công! Vui lòng update lại mật khẩu trong file main')
                ];
            } else {
                echo "<pre>";
                print_r($result);
                throw new InvalidValueException(Yii::t('app', 'Đổi mật khẩu ko thành công!'));
            }
        } else {
            throw new InvalidValueException(Yii::t('app', 'Lỗi hệ thống vui lòng thử lại sau!'));
        }
    }

    public function actionChangeMpinVoucher($mpin)
    {
        $user_login = Yii::$app->params['user_voucher'];
        $pass = Yii::$app->params['pass_voucher'];
        $mpin_o = Yii::$app->params['mpin_voucher'];
        $log_link = Yii::getAlias('@runtime/logs/sessionId.log');
        if (file_exists($log_link)) {
            $sessionId = Subscriber::ReturnSessionId($log_link);
        } else {
            $sessionId = Subscriber::AddSessionID($user_login, $pass);
        }
        $key_seed = $sessionId;
        $mpin_old = self::PhoneEncrypt($mpin_o, $key_seed);
        $mpin_new = self::PhoneEncrypt($mpin, $key_seed);
        $signature1 = md5($user_login . $mpin_o . $mpin . $mpin_o);
        $json = "{
                    function:changempin,
                    username:" . $user_login . ",
                    password:" . $pass . ",
                    oldMpin:" . $mpin_old . ",
                    newMpin:" . $mpin_new . ",
                    signature:" . $signature1 . "
                    }";
        $result = APIHelper::apiQuery('POST', APIHelper::API_CHECK_VOUCHER, $json);
        if (isset($result)) {
            $error_code = Subscriber::GetCodeResult($result);
            if ($error_code == 0) {
                return [
                    'success' => true,
                    'message' => Yii::t('app', 'Đổi mpin thành công! Vui lòng update lại mpin trong file main')
                ];
            } else {
                echo "<pre>";
                print_r($result);
                throw new InvalidValueException(Yii::t('app', 'Đổi mpin ko thành công!'));
            }
        } else {
            throw new InvalidValueException(Yii::t('app', 'Lỗi hệ thống vui lòng thử lại sau!'));
        }
    }

    public function actionGetCampaignPromotion($service_id = null, $type = null, $number_month = 1)
    {
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;

        if ($type == Campaign::TYPE_ACTIVE) {
            if ($subscriber->is_active) {
                throw new InvalidValueException(Yii::t('app', 'Quý khách đã kích hoạt khuyến mãi'));
            }
        }
        $site_id = $this->site->id;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        if ($type && !$service_id) {
            $type = [Campaign::TYPE_ACTIVE];
        } elseif (!$type && !$service_id) {
            $type = [Campaign::TYPE_CASH_SERVICE, Campaign::TYPE_CASH_CASH, Campaign::TYPE_CASH_CONTENT];
        } else if ($service_id) {
            $type = [Campaign::TYPE_SERVICE_TIME, Campaign::TYPE_SERVICE_CONTENT, Campaign::TYPE_SERVICE_SERVICE];
            /** @var $service Service */
            $service = Service::findOne(['id' => $service_id]);
            if (!$service) {
                throw new InvalidValueException(Message::getNotFoundServiceMessage());
            }
        }

        $campaign = \api\models\Campaign::find();
        if ($service_id) {
            $campaign->innerJoin('campaign_condition', 'campaign_condition.campaign_id = campaign.id')
                ->andWhere(['campaign_condition.service_id' => $service_id])
                ->andWhere(['campaign_condition.number_month' => $number_month]);
        }
        $campaignPromotion = null;
        $campaignCondition = null;

        $campaign->innerJoin('campaign_group_subscriber_asm', 'campaign_group_subscriber_asm.campaign_id = campaign.id')
            ->innerJoin('group_subscriber_user_asm', 'group_subscriber_user_asm.group_subscriber_id = campaign_group_subscriber_asm.group_subscriber_id')
            ->andWhere(['group_subscriber_user_asm.username' => $subscriber->username])
            ->andWhere(['campaign.status' => Campaign::STATUS_ACTIVATED])
            ->orWhere(['campaign.status' => Campaign::STATUS_RUNNING_TEST])
            ->andWhere(['group_subscriber_user_asm.status' => GroupSubscriberUserAsm::STATUS_ACTIVE])
            ->andWhere(['campaign.site_id' => $site_id])
            ->andWhere(['IN', 'campaign.type', $type])
            ->andWhere('campaign.expired_at >= :expired_at', ['expired_at' => time()])
            ->andWhere('campaign.activated_at <= :activated_at', ['activated_at' => time()])
            ->orderBy(['campaign.activated_at' => SORT_DESC, 'campaign.priority' => SORT_DESC]);
        if ($campaign->all()) {
            foreach ($campaign->all() as $item) {
                /** @var  $item Campaign */
                $countSubscriberLog = LogCampaignPromotion::find()
                    ->andWhere(['site_id' => $site_id])
                    ->andWhere('type <> :type', ['type' => Campaign::TYPE_ACTIVE])
                    ->andWhere(['subscriber_name' => $subscriber->username])
                    ->andWhere(['campaign_id' => $item->id])->count();

                $count = CampaignPromotion::find()
                    ->andWhere(['status' => CampaignPromotion::STATUS_ACTIVE])
                    ->andWhere(['campaign_id' => $item->id])
                    ->count();

                if ($count != 0 && ($countSubscriberLog / $count) < $item->number_promotion) {
                    $campaignPromotion = CampaignPromotion::find()
                        ->andWhere(['status' => CampaignPromotion::STATUS_ACTIVE])
                        ->andWhere(['campaign_id' => $item->id])->all();
                    $campaignCondition = CampaignCondition::find()
                        ->andWhere(['status' => CampaignCondition::STATUS_ACTIVE])
                        ->andWhere(['campaign_id' => $item->id])->all();
                    if ($item->status == Campaign::STATUS_ACTIVATED) {
                        $subscriber_status = GroupSubscriberUserAsm::find()
                            ->innerJoin('group_subscriber', 'group_subscriber.id = group_subscriber_user_asm.group_subscriber_id')
                            ->innerJoin('campaign_group_subscriber_asm', 'campaign_group_subscriber_asm.group_subscriber_id = group_subscriber.id')
                            ->andWhere(['group_subscriber_user_asm.username' => $subscriber->username])
                            ->andWhere(['campaign_group_subscriber_asm.campaign_id' => $item->id])
                            ->andWhere(['group_subscriber_user_asm.status' => GroupSubscriberUserAsm::STATUS_ACTIVE])
                            ->andWhere(['campaign_group_subscriber_asm.status' => CampaignGroupSubscriberAsm::STATUS_ACTIVE])
                            ->andWhere(['campaign_group_subscriber_asm.type' => CampaignGroupSubscriberAsm::TYPE_REAL])->one();
                    } else {
                        $subscriber_status = GroupSubscriberUserAsm::find()
                            ->innerJoin('group_subscriber', 'group_subscriber.id = group_subscriber_user_asm.group_subscriber_id')
                            ->innerJoin('campaign_group_subscriber_asm', 'campaign_group_subscriber_asm.group_subscriber_id = group_subscriber.id')
                            ->andWhere(['group_subscriber_user_asm.username' => $subscriber->username])
                            ->andWhere(['campaign_group_subscriber_asm.campaign_id' => $item->id])
                            ->andWhere(['group_subscriber_user_asm.status' => GroupSubscriberUserAsm::STATUS_ACTIVE])
                            ->andWhere(['campaign_group_subscriber_asm.status' => CampaignGroupSubscriberAsm::STATUS_ACTIVE])
                            ->andWhere(['campaign_group_subscriber_asm.type' => CampaignGroupSubscriberAsm::TYPE_DEMO])->one();
                    }
                    if ($subscriber_status) {
                        return [
                            'campaign' => $item,
                            'promotion' => $campaignPromotion,
                            'condition' => $campaignCondition,
                            'message_campaign' => Notification::findOne(['name' => 'ĐK01'])->content,
                            'message_popup' => Notification::findOne(['name' => 'ĐK02'])->content
                        ];
                    }
                }
            }
        }

        $this->setStatusCode(400);
        return [
            'campaign' => $campaign->one(),
            'promotion' => $campaignPromotion,
            'condition' => $campaignCondition,
            'message_campaign' => Notification::findOne(['name' => 'ĐK01'])->content,
            'message_popup' => Notification::findOne(['name' => 'ĐK02'])->content
        ];
    }

    public function actionGetOneCampaign()
    {
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        $site_id = $this->site->id;
        $type = [Campaign::TYPE_CASH_SERVICE, Campaign::TYPE_CASH_CASH, Campaign::TYPE_CASH_CONTENT, Campaign::TYPE_SERVICE_TIME, Campaign::TYPE_SERVICE_CONTENT, Campaign::TYPE_SERVICE_SERVICE];
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }

        $campaign = \api\models\Campaign::find();
        $campaignPromotion = null;
        $campaignCondition = null;

        $campaign->innerJoin('campaign_group_subscriber_asm', 'campaign_group_subscriber_asm.campaign_id = campaign.id')
            ->innerJoin('group_subscriber_user_asm', 'group_subscriber_user_asm.group_subscriber_id = campaign_group_subscriber_asm.group_subscriber_id')
            ->andWhere(['group_subscriber_user_asm.username' => $subscriber->username])
            ->andWhere(['campaign.status' => Campaign::STATUS_ACTIVATED])
            ->orWhere(['campaign.status' => Campaign::STATUS_RUNNING_TEST])
            ->andWhere(['group_subscriber_user_asm.status' => GroupSubscriberUserAsm::STATUS_ACTIVE])
            ->andWhere(['campaign.site_id' => $site_id])
            ->andWhere(['IN', 'campaign.type', $type])
            ->andWhere('campaign.expired_at >= :expired_at', ['expired_at' => time()])
            ->andWhere('campaign.activated_at <= :activated_at', ['activated_at' => time()])
            ->orderBy(['campaign.activated_at' => SORT_DESC, 'campaign.priority' => SORT_DESC]);
        if ($campaign->all()) {
            foreach ($campaign->all() as $item) {
                /** @var  $item Campaign */
                $countSubscriberLog = LogCampaignPromotion::find()
                    ->andWhere(['site_id' => $site_id])
                    ->andWhere('type <> :type', ['type' => Campaign::TYPE_ACTIVE])
                    ->andWhere(['subscriber_name' => $subscriber->username])
                    ->andWhere(['campaign_id' => $item->id])->count();
                $count = CampaignPromotion::find()
                    ->andWhere(['campaign_id' => $item->id])
                    ->andWhere(['status' => CampaignPromotion::STATUS_ACTIVE])
                    ->count();
                if ($count != 0 && ($countSubscriberLog / $count) < $item->number_promotion) {
                    $campaignPromotion = CampaignPromotion::find()
                        ->andWhere(['status' => CampaignPromotion::STATUS_ACTIVE])
                        ->andWhere(['campaign_id' => $item->id])->all();
                    $campaignCondition = CampaignCondition::find()
                        ->andWhere(['status' => CampaignCondition::STATUS_ACTIVE])
                        ->andWhere(['campaign_id' => $item->id])->all();
                    if ($item->status == Campaign::STATUS_ACTIVATED) {
                        $subscriber_status = GroupSubscriberUserAsm::find()
                            ->innerJoin('group_subscriber', 'group_subscriber.id = group_subscriber_user_asm.group_subscriber_id')
                            ->innerJoin('campaign_group_subscriber_asm', 'campaign_group_subscriber_asm.group_subscriber_id = group_subscriber.id')
                            ->andWhere(['group_subscriber_user_asm.username' => $subscriber->username])
                            ->andWhere(['campaign_group_subscriber_asm.campaign_id' => $item->id])
                            ->andWhere(['group_subscriber_user_asm.status' => GroupSubscriberUserAsm::STATUS_ACTIVE])
                            ->andWhere(['campaign_group_subscriber_asm.status' => CampaignGroupSubscriberAsm::STATUS_ACTIVE])
                            ->andWhere(['campaign_group_subscriber_asm.type' => CampaignGroupSubscriberAsm::TYPE_REAL])->one();
                    } else {
                        $subscriber_status = GroupSubscriberUserAsm::find()
                            ->innerJoin('group_subscriber', 'group_subscriber.id = group_subscriber_user_asm.group_subscriber_id')
                            ->innerJoin('campaign_group_subscriber_asm', 'campaign_group_subscriber_asm.group_subscriber_id = group_subscriber.id')
                            ->andWhere(['group_subscriber_user_asm.username' => $subscriber->username])
                            ->andWhere(['campaign_group_subscriber_asm.campaign_id' => $item->id])
                            ->andWhere(['group_subscriber_user_asm.status' => GroupSubscriberUserAsm::STATUS_ACTIVE])
                            ->andWhere(['campaign_group_subscriber_asm.status' => CampaignGroupSubscriberAsm::STATUS_ACTIVE])
                            ->andWhere(['campaign_group_subscriber_asm.type' => CampaignGroupSubscriberAsm::TYPE_DEMO])->one();
                    }
                    if ($subscriber_status) {
                        return ['campaign' => $item, 'promotion' => $campaignPromotion, 'condition' => $campaignCondition];
                    }
                }
            }
        }

        $this->setStatusCode(400);
        return ['campaign' => $campaign->one(), 'promotion' => $campaignPromotion, 'condition' => $campaignCondition];
    }

    public function actionSendMoneyToFriend()
    {
        $user_friend = Yii::$app->request->post('username');
        $cost = Yii::$app->request->post('cost');
        if (empty($user_friend)) {
            throw new InvalidValueException(Message::getNotFoundUserMessage());
        }
        if (empty($cost)) {
            throw new InvalidValueException(Yii::t('app', 'Số tiền chuyển không được để trống!'));
        }
        if (is_numeric($cost) == false) {
            throw new InvalidValueException(Yii::t('app', 'Số tiền chuyển không hợp lệ!'));
        }
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        $otp = APIHelper::getOTP();
    }

    public function actionSendOtp()
    {
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        $site_id = $this->site->id;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        $otp = APIHelper::getOTP();
        $res = $subscriber->saveSubscriber($subscriber, $otp);
        if ($res) {
            if (BrandnameVacAccount::sendOTP($subscriber, $site_id, $otp)) {
                return [
                    'success' => true,
                    'message' => Yii::t('app', 'Gửi mã OTP thành công')
                ];
            }
        }
        return [
            'success' => false,
            'message' => Yii::t('app', 'Mã OTP sinh bị lỗi')
        ];
    }

    public function actionSendBalanceToFriend($username, $balance, $otp)
    {
        /** @var $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        if (!$username) {
            throw new InvalidValueException(Yii::t('app', 'Tài khoản nhận không được bỏ trống'));
        }
        $subscriberSend = Subscriber::findOne(['username' => $username]);
        if (!$subscriberSend) {
            throw new InvalidValueException(Yii::t('app', 'Tài khoản nhận không tồn tại'));
        }
        if ($subscriberSend->status == Subscriber::STATUS_INACTIVE) {
            throw new InvalidValueException(Yii::t('app', 'Tài khoản nhận đang ở trạng thái tạm dừng'));
        }
        if ($subscriberSend->status == Subscriber::STATUS_DELETED) {
            throw new InvalidValueException(Yii::t('app', 'Tài khoản nhận không tồn tại'));
        }

        if (empty($balance)) {
            throw new InvalidValueException(Yii::t('app', 'Số tiền nạp không được bỏ trống'));
        }
        if (!is_numeric($balance)) {
            throw new InvalidValueException(Yii::t('app', 'Số tiền nạp không đúng! '));
        }

        if ($balance < 0) {
            throw new InvalidValueException(Yii::t('app', 'Số tiền nạp không đúng! '));
        }
        if (empty($otp)) {
            throw new InvalidValueException(Yii::t('app', 'Mã OTP không được bỏ trống'));
        }

        if (!is_numeric($otp)) {
            throw new InvalidValueException(Yii::t('app', 'Mã OTP không đúng!'));
        }

        $checkOtp = Subscriber::findOne(['id' => $subscriber->id]);
        if ($checkOtp->number_otp < 0) {
            throw new  InvalidValueException(Yii::t('app', 'Nhập sai mã OTP 3 lần, Quý Khách vui lòng nhận mã lại mã OTP mới'));
        }
        if ($checkOtp->otp_code != $otp) {
            $checkOtp->number_otp -= 1;
            $checkOtp->save(false);
            throw  new InvalidValueException(Yii::t('app', 'Nhập sai mã OTP, vui lòng kiểm tra và thử lại'));
        }
        if ($checkOtp->expired_code_time <= time()) {
            throw new  InvalidValueException(Yii::t('app', 'Mã OTP hiện tại đã hết hạn. Quý Khách vui lòng nhận mã OTP mới để tiếp tục'));
        }
        if ($subscriber->balance < $balance) {
            throw new  InvalidValueException(Yii::t('app', 'Tài khoản không đủ tiền để nạp'));
        }
        $subscriber->balance -= $balance;
        $subscriber->number_otp = 0;
        $subscriber->save(false);
        $promotion_note = Yii::t('app', "Tài khoản " . $subscriber->username . " chuyển tiền cho tài khoản " . $subscriberSend->username);
        $subscriber->newTransaction(SubscriberTransaction::TYPE_TRANFER_MONEY, SubscriberTransaction::CHANNEL_TYPE_ANDROID, $promotion_note, null, null, SubscriberTransaction::STATUS_SUCCESS, -$balance, 'coin', $subscriber->balance);
        $subscriberSend->balance += $balance;
        $subscriberSend->save(false);
        $promotion_note = Yii::t('app', "Tài khoản " . $subscriberSend->username . " nhận tiền từ tài khoản " . $subscriber->username);
        $subscriberSend->newTransaction(SubscriberTransaction::TYPE_RECEIVE_MONEY, SubscriberTransaction::CHANNEL_TYPE_ANDROID, $promotion_note, null, null, SubscriberTransaction::STATUS_SUCCESS, +$balance, 'coin', $subscriber->balance);
        return [
            'message' => Yii::t('app', 'Chuyển tiền thành công')
        ];

    }

    public function actionCheckAccount($user)
    {
        $subscriber = Subscriber::findOne(['username' => $user]);
        if ($subscriber) {
            if ($subscriber->status == Subscriber::STATUS_MAINTAIN) {
                return ['success' => true, 'error_code' => self::CHECK_ACCOUNT_MAINTAIN];
            } else {
                if ($subscriber->status == Subscriber::STATUS_ACTIVE) {
                    return ['success' => false, 'error_code' => self::CHECK_ACCOUNT_LINKED];
                } else {
                    return ['success' => false, 'error_code' => self::CHECK_ACCOUNT_FALSE];
                }
            }
        } else {
            return ['success' => false, 'error_code' => self::CHECK_ACCOUNT_FALSE];
        }
    }

    // add 11092017 TuanPV
    public function actionActiveSubscriber()
    {
        /** @var Subscriber $subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
//        echo"<pre>";print_r($subscriber);die();
        if ($subscriber->is_active == Subscriber::IS_ACTIVE) {
            throw new InvalidValueException(Yii::t('app', 'Thuê bao đã kích hoạt tài khoản! Vui lòng kiểm tra lại!'));
        }
        $site_id = $this->site ? $this->site->id : (int)Yii::getAlias('@default_site_id');
        $mac = $subscriber->machine_name;
        $device = Device::findByMac($mac, $site_id);

        $campaign = BaseLogicCampaign::getCampaignActiveUser($subscriber, $site_id);
        if (!$campaign) {
            Yii::info("Loi! chua co trong danh sach khach hang");
            throw new InvalidValueException(Yii::t('app', 'Gói cước của Quý khách chưa được kích hoạt thành công do Hệ thống đang bận. Vui lòng thử lại!'));
        }

        if (BaseLogicCampaign::checkRecivedCampaign($subscriber, $campaign)) {
            Yii::info("Loi! Nhan khuyen mai roi");
            throw new InvalidValueException(Yii::t('app', 'Thuê bao đã kích hoạt gói khuyến mãi trước đó. Vui lòng kiểm tra lại!'));
        }

        if (!BaseLogicCampaign::addServiceToSubscriber($subscriber, $campaign, $site_id, $device)) {
            throw new InvalidValueException(Yii::t('app', 'Gói cước của Quý khách chưa được kích hoạt thành công do Hệ thống đang bận. Vui lòng thử lại!'));
        }

        $notification = Notification::findOne(['name' => 'ĐK03']);
        if (!$notification) {
            throw new InvalidValueException(Yii::t('app', 'Gói cước của Quý khách chưa được kích hoạt thành công do Hệ thống đang bận. Vui lòng thử lại!'));
        }
        $message = $notification->content;

        if (!empty($campaign->notification_title)) {
            shell_exec("nohup  ./send_sms_subscriber.sh $subscriber->id $campaign->id > /dev/null 2>&1 &");
        }

        $subscriber->is_active = Subscriber::IS_ACTIVE;
        if (!$subscriber->update()) {
            Yii::info($subscriber->getErrors());
            throw new InvalidValueException(Yii::t('app', 'Gói cước của Quý khách chưa được kích hoạt thành công do Hệ thống đang bận. Vui lòng thử lại!'));
        }

//        $message = SmsSupport::str_replace_first(SmsSupport::PARAM_MONTH, $campaign->, $content);
        return ['success' => true, 'message' => $message]; // TODO: Change the autogenerated stub
    }

    public function actionGetAllCampaign()
    {
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        $site_id = $this->site->id;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        $arr = [];
        $s = [];
        $type = [Campaign::TYPE_CASH_SERVICE, Campaign::TYPE_CASH_CASH, Campaign::TYPE_CASH_CONTENT, Campaign::TYPE_SERVICE_TIME, Campaign::TYPE_SERVICE_CONTENT, Campaign::TYPE_SERVICE_SERVICE, Campaign::TYPE_ACTIVE];
        $campaign = Campaign::find();
        $campaignPromotion = null;
        $campaignCondition = null;

        $campaign->innerJoin('campaign_group_subscriber_asm', 'campaign_group_subscriber_asm.campaign_id = campaign.id')
            ->innerJoin('group_subscriber_user_asm', 'group_subscriber_user_asm.group_subscriber_id = campaign_group_subscriber_asm.group_subscriber_id')
            ->andWhere(['group_subscriber_user_asm.username' => $subscriber->username])
            ->andWhere(['campaign.status' => Campaign::STATUS_ACTIVATED])
            ->orWhere(['campaign.status' => Campaign::STATUS_RUNNING_TEST])
            ->andWhere(['group_subscriber_user_asm.status' => GroupSubscriberUserAsm::STATUS_ACTIVE])
            ->andWhere(['campaign.site_id' => $site_id])
            ->andWhere(['IN', 'campaign.type', $type])
            ->andWhere('campaign.expired_at >= :expired_at', ['expired_at' => time()])
            ->andWhere('campaign.activated_at <= :activated_at', ['activated_at' => time()])
            ->orderBy(['campaign.activated_at' => SORT_DESC, 'campaign.priority' => SORT_DESC]);
        if ($campaign->all()) {
            foreach ($campaign->all() as $item) {
                /** @var  $item Campaign */
                $countSubscriberLog = LogCampaignPromotion::find()
                    ->andWhere(['site_id' => $site_id])
                    ->andWhere(['subscriber_name' => $subscriber->username])
                    ->andWhere(['campaign_id' => $item->id])->count();
                $count = CampaignPromotion::find()
                    ->andWhere(['campaign_id' => $item->id])
                    ->andWhere(['status' => CampaignPromotion::STATUS_ACTIVE])
                    ->count();
                if ($count != 0 && ($countSubscriberLog / $count) < $item->number_promotion) {
                    $campaignPromotion = CampaignPromotion::find()
                        ->andWhere(['status' => CampaignPromotion::STATUS_ACTIVE])
                        ->andWhere(['campaign_id' => $item->id])->all();
                    $campaignCondition = CampaignCondition::find()
                        ->andWhere(['status' => CampaignCondition::STATUS_ACTIVE])
                        ->andWhere(['campaign_id' => $item->id])->all();
                    if ($item->status == Campaign::STATUS_ACTIVATED) {
                        $subscriber_status = GroupSubscriberUserAsm::find()
                            ->innerJoin('group_subscriber', 'group_subscriber.id = group_subscriber_user_asm.group_subscriber_id')
                            ->innerJoin('campaign_group_subscriber_asm', 'campaign_group_subscriber_asm.group_subscriber_id = group_subscriber.id')
                            ->andWhere(['group_subscriber_user_asm.username' => $subscriber->username])
                            ->andWhere(['campaign_group_subscriber_asm.campaign_id' => $item->id])
                            ->andWhere(['group_subscriber_user_asm.status' => GroupSubscriberUserAsm::STATUS_ACTIVE])
                            ->andWhere(['campaign_group_subscriber_asm.status' => CampaignGroupSubscriberAsm::STATUS_ACTIVE])
                            ->andWhere(['campaign_group_subscriber_asm.type' => CampaignGroupSubscriberAsm::TYPE_REAL])->one();
                    } else {
                        $subscriber_status = GroupSubscriberUserAsm::find()
                            ->innerJoin('group_subscriber', 'group_subscriber.id = group_subscriber_user_asm.group_subscriber_id')
                            ->innerJoin('campaign_group_subscriber_asm', 'campaign_group_subscriber_asm.group_subscriber_id = group_subscriber.id')
                            ->andWhere(['group_subscriber_user_asm.username' => $subscriber->username])
                            ->andWhere(['campaign_group_subscriber_asm.campaign_id' => $item->id])
                            ->andWhere(['group_subscriber_user_asm.status' => GroupSubscriberUserAsm::STATUS_ACTIVE])
                            ->andWhere(['campaign_group_subscriber_asm.status' => CampaignGroupSubscriberAsm::STATUS_ACTIVE])
                            ->andWhere(['campaign_group_subscriber_asm.type' => CampaignGroupSubscriberAsm::TYPE_DEMO])->one();
                    }
                    if ($subscriber_status) {
                        $s['campaign'] = $item;
                        $s['promotion'] = $campaignPromotion;
                        $s['condition'] = $campaignCondition;
                        $arr[] = $s;
                    }
                }
            }
        }
        return $arr;
    }

    public function actionSavePayerId($player_id)
    {
        $subscriber = Yii::$app->user->identity;
        $site_id = $this->site->id;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        /** @var Subscriber $subscriber */
        /** @var ManagerDeviceNotification $modelUpdate */

        $modelUpdate = ManagerDeviceNotification::find()
            ->andWhere(['site_id' => $site_id])
            ->andWhere(['player_id' => $player_id])
            ->one();
        if ($modelUpdate) {
            if ($modelUpdate->subscriber_id == $subscriber->id) {
                return false;
            } else {
                $modelUpdate->subscriber_id = $subscriber->id;
                $modelUpdate->update(false);
                return true;
            }
        } else {
            $model = new ManagerDeviceNotification();
            $model->subscriber_id = $subscriber->id;
            $model->site_id = $site_id;
            $model->player_id = $player_id;
            $device = Device::findOne(['device_id' => $subscriber->username]);
            if ($device) {
                $model->device_id = $device->id;
            } else {
                $model->device_id = null;
            }
            $model->status = ManagerDeviceNotification::STATUS_ACTIVE;
            if ($model->save()) {
                return true;
            } else {
                Yii::info($model->getErrors());
                return false;
            }
        }
    }

    public function actionActiveVoucher($code, $channel = Subscriber::CHANNEL_TYPE_ANDROID)
    {
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        $site_id = $this->site->id;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        /** @var $voucher Voucher */
        $voucher = Voucher::findOne(['voucher_code' => $code, 'subscriber_id' => $subscriber->id, 'site_id' => $site_id]);
        if (!$voucher) {
            throw new InvalidValueException(Message::getVoucherFail());
        }
        $voucher->status = Voucher::STATUS_USED_PARTNER;
        $voucher->apply_at = time();
        $voucher->save();

        //tao map goi cuoc voi nguoi dung

        $service = Service::findOne(['id' => $voucher->service_id, 'status' => Service::STATUS_ACTIVE, 'site_id' => $this->site->id]);
        if (!$service) {
            throw new InvalidValueException(Message::getNotFoundServiceMessage());
        }


        $res = $subscriber->addSubscriberServiceAsm($subscriber, $service, $channel);

        if ($res) {
            return ['success' => true, 'message' => Yii::t('app', 'Áp dụng khuyến mãi thành công')];
        }
        return ['success' => false, 'message' => Yii::t('app', 'Áp dụng khuyến mãi thất bại')];
    }

    public function actionProvince()
    {
        $user_ip = Yii::$app->request->getUserIP();

        if ($this->language == 'vi') {
            $provinces = City::find()
                ->select(['name', 'code'])
                ->andWhere(['site_id' => $this->site->id])
                ->orderBy(['ascii_name' => SORT_ASC])
                ->all();
        } else {
            $provinces = City::find()
                ->select(['ascii_name as name', 'code'])
                ->andWhere(['site_id' => $this->site->id])
                ->orderBy(['ascii_name' => SORT_ASC])
                ->all();
        }

        /** @var Subscriber $sub */
        $sub = Yii::$app->user->identity;
        if ($sub->ip_address != $user_ip) {
            $sub->ip_address = $user_ip;
        } else {
            if ($sub->ip_to_location != "") {
                return [
                    'ip_to_location' => $sub->ip_to_location,
                    'province_code' => $sub->province_code,
                    'provinces' => $provinces
                ];
            }
        }

        $location = $sub->getIPToLocation();
        if ($location) {
            /** @var City $province */
            $province = City::find()->andWhere(['name' => $location->city])->one();
            if ($province) {
                $sub->ip_to_location = $province->code;
                if (!$sub->save()) {
                    Yii::error($sub->getErrors());
                }
            }
        }

        return [
            'ip_to_location' => $sub->ip_to_location,
            'province_code' => $sub->province_code,
            'provinces' => $provinces
        ];

    }

    public function actionUpdateProvince()
    {
        $province_code = $this->getParameterPost("province_code", "");
        if ($province_code == "") {
            throw new BadRequestHttpException(Yii::t('app', 'Không tồn tại mã tỉnh/thành phố này'));
        }

        /** @var City $province */
        $province = City::find()->andWhere(['code' => $province_code])->one();
        if (!$province) {
            throw new BadRequestHttpException(Yii::t('app', 'Không tồn tại tỉnh/thành phố này'));
        }

        /** @var Subscriber $subscriber */
        $subscriber = Yii::$app->user->identity;
        $subscriber->city = $province->name;
        $subscriber->province_code = $province_code;
        if ($subscriber->save(false)) {
            return ['message' => Yii::t('app', 'Cập nhật tỉnh/thành phố thành công')];
        } else {
            Yii::error($subscriber->getErrors());
            throw new InternalErrorException(Yii::t('app', 'Không thành công, vui lòng thử lại'));
        }
    }

    public function actionValidateMac($mac_address)
    {
        $site_id = $this->site->id;
        /* Kiểm tra xem có đúng MAC gửi lên là của VNPT Technology không */
        $device = Device::findByMac($mac_address, $site_id);
        if (!$device) {
            throw new NotFoundHttpException(Message::getDeviceNotExitMessage());
        } else {
            return ['message' => "Thiết bị hợp lệ"];
        }
    }

    public function actionGetListMonth()
    {
        if (!empty(Yii::$app->params['list_number_month'])) {
            return array_merge(Yii::$app->params['list_number_month']);
        } else {
            return [Yii::t('app', 'Không có dữ liệu')];
        }
    }
}
