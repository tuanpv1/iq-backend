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
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = [
            'register',
            'login',
            'list-feedback',
            'change-pass-voucher',
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
    public function actionRegister($username, $password, $city, $machine_name, $channel = Subscriber::CHANNEL_TYPE_ANDROID)
    {
        if (empty($username)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['Tên đăng nhập']));
        }

        if (empty($password)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['Mật khẩu']));
        }

        if (empty($city)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['Tỉnh/Thành phố']));
        }

        if (empty($machine_name)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['Tên thiết bị']));
        }

        $u = Subscriber::findOne(['username' => $username, 'status' => [Subscriber::STATUS_ACTIVE, Subscriber::STATUS_INACTIVE]]);
        if ($u) {
            throw new InvalidValueException(Message::getExitsUsernameMessage());
        }


        $res = Subscriber::register($username, $password, $city, Subscriber::STATUS_ACTIVE, $machine_name, $channel, null);

        if ($res['status']) {

            return ['message' => $res['message'],
                'subscriber' => $res['subscriber'],
            ];
        } else {
            throw new ServerErrorHttpException($res['message']);
        }
    }

    public function actionLogin($username, $password, $device_model, $device_id, $channel = Subscriber::CHANNEL_TYPE_ANDROID, $authen_type = Subscriber::AUTHEN_TYPE_MAC_ADDRESS)
    {
        /** validate input */
        if (empty($username)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), [Yii::t('app', 'Tên đăng nhập')]));
        }
        if (empty($device_id)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), [Yii::t('app', 'Device ID')]));
        }
        if (empty($device_model)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), [Yii::t('app', 'Device Model')]));
        }

        $subscriber = Subscriber::findByUsername($username, false);
        if (!$subscriber) {
            throw new NotFoundHttpException(Message::getWrongUserOrPassMessage());
        }
        /** Check tài khoản có bị block không? */
        if ($subscriber->status != Subscriber::STATUS_ACTIVE) {
            throw new ServerErrorHttpException(Message::getSubscriberInactiveMessage());
        }

        if (empty($password)) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), [Yii::t('app', 'mật khẩu')]));
        }
        if (!$subscriber->validatePassword($password)) {
            throw new InvalidValueException(Message::getWrongUserOrPassMessage());
        }

        /** Save SubscriberActivity */
        $description = $subscriber->username . ' login time:' . date('d-m-Y H:i:s', time());
        SubscriberActivity::createSubscriberActivity($subscriber, $description, $channel, SubscriberActivity::ACTION_LOGIN);
        /** Gen token */
        $token = SubscriberToken::generateToken($subscriber->id, $channel);
        if (!$token) {
            throw new ServerErrorHttpException(Message::getFailMessage());
        }
        /** save last_login */
        $subscriber->last_login_at = time();
        $subscriber->save(false);

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
            'channel' => $token->channel,
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
}
