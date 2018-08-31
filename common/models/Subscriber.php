<?php

namespace common\models;

use api\helpers\APIHelper;
use api\helpers\Message;
use common\charging\helpers\ChargingGW;
use common\charging\models\ChargingConnection;
use common\charging\models\ChargingResult;
use common\helpers\CommonConst;
use common\helpers\CommonUtils;
use common\helpers\CUtils;
use common\helpers\FileUtils;
use common\helpers\ResMessage;
use common\helpers\SysCproviderService;
use common\helpers\VasProvisioning;
use DateInterval;
use DateTime;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidValueException;
use yii\behaviors\TimestampBehavior;
use yii\console\Exception;
use yii\data\ActiveDataProvider;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\IdentityInterface;
use yii\web\ServerErrorHttpException;

/**
 * This is the model class for table "{{%subscriber}}".
 *
 * @property integer $id
 * @property integer $site_id
 * @property integer $dealer_id
 * @property integer $whitelist
 * @property integer $authen_type
 * @property integer $channel
 * @property string $msisdn
 * @property string $username
 * @property string $machine_name
 * @property integer $balance
 * @property integer $status
 * @property string $email
 * @property string $address
 * @property string $city
 * @property string $full_name
 * @property string $auth_key
 * @property string $password_hash
 * @property integer $last_login_at
 * @property integer $last_login_session
 * @property integer $birthday
 * @property integer $sex
 * @property string $avatar_url
 * @property string $skype_id
 * @property string $google_id
 * @property string $facebook_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $register_at
 * @property integer activated_at
 * @property integer $client_type
 * @property integer $using_promotion
 * @property integer $auto_renew
 * @property integer $verification_code
 * @property string $user_agent
 * @property integer $expired_at
 * @property string $otp_code
 * @property string $ip_address
 * @property string $ip_to_location
 * @property string $province_code
 * @property integer $expired_code_time
 * @property integer $number_otp
 * @property integer $is_active
 * @property integer $type
 * @property integer $initialized_at
 * @property integer $service_initialized
 * @property integer $phone_number
 * @property integer $ip_location_first
 *
 *
 * @property ContentFeedback[] $contentFeedbacks
 * @property ContentKeyword[] $contentKeywords
 * @property ContentViewLog[] $contentViewLogs
 * @property ReportMonthlyCpRevenueDetail[] $reportMonthlyCpRevenueDetails
 * @property SmsMessage[] $smsMessages
 * @property Site $site
 * @property Dealer $dealer
 * @property SubscriberActivity[] $subscriberActivities
 * @property SubscriberContentAsm[] $subscriberContentAsms
 * @property SubscriberContentAsm[] $subscriberContentAsms0
 * @property SubscriberFavorite[] $subscriberFavorites
 * @property SubscriberFeedback[] $subscriberFeedbacks
 * @property SubscriberServiceAsm[] $subscriberServiceAsms
 * @property SubscriberServiceAsm[] $subscriberServiceAsms0
 * @property SubscriberToken[] $subscriberTokens
 * @property SubscriberTransaction[] $subscriberTransactions
 * @property Service[] $services
 * @property Content[] $contents
 */
class Subscriber extends \yii\db\ActiveRecord implements IdentityInterface
{
    // khai bao them de lam cot trong file xuat exel loi
    const EXCEL_ROW1 = 'Tên tài khoản';
    const EXCEL_ROW2 = 'Email';
    const EXCEL_ROW3 = 'Trạng thái';
    // end add
    public $access_token;

    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 1;
    const STATUS_MAINTAIN = 2;
    const STATUS_DELETED = 0;

    const SEX_NAM = 0;
    const SEX_NU = 1;

    const CHANNEL_TYPE_API = 1;
    const CHANNEL_TYPE_SYSTEM = 2;
    const CHANNEL_TYPE_CSKH = 3;
    const CHANNEL_TYPE_SMS = 4;
//    const CHANNEL_TYPE_WAP = 5;
    const CHANNEL_TYPE_MOBILEWEB = 6;
    const CHANNEL_TYPE_ANDROID = 7;
    const CHANNEL_TYPE_IOS = 8;
    const CHANNEL_TYPE_WEBSITE = 9;
    const CHANNEL_TYPE_ANDROID_MOBILE = 10;

    const RENEW_AUTO = 1;
    const RENEW_NOT_AUTO = 0;

    const AUTHEN_TYPE_ACCOUNT = 1; // box đã đk tài khoản
    const AUTHEN_TYPE_MAC_ADDRESS = 2; // box chưa đăng ký tài khoản
//    const AUTHEN_TYPE_MSISDN = 3;

// Định nghĩa thẻ tvod
    const VOUCHER_ACTIVE = 0; // Thẻ hoạt động
    const VOUCHER_USED = -1; // thẻ đã được nạp
    const VOUCHER_INACTIVE = -2; // Thẻ bị tạm dừng
    const VOUCHER_EXPIRE = -3; // Thẻ hết hạn
    const VOUCHER_NOT_ACTIVE = -4; // Thẻ chưa được kích hoạt
    const VOUCHER_NOT_FORMAT = -5; // Mã thẻ sai định dạng
    const VOUCHER_NOT_HAVE = -6; // Không tồn tại thẻ
//    const VOUCHER_CANCEL = 06; // Thẻ bị hủy
    // Mã lỗi hệ thống thẻ tvod
    const VOUCHER_INVALID_LOGIN = 1;
    const VOUCHER_INVALID_SESSION = 2;
    const VOUCHER_PARTNED_LOCKED = 3;
    const VOUCHER_INVALID_PARTNED = 4;
    const VOUCHER_SYSTEM_ERROR = 5;
    const VOUCHER_CANNOT_LOGOUT = 6;
    const VOUCHER_INVALID_USER = 7;
    const VOUCHER_INVALID_MPIN = 8;
    const VOUCHER_INVALID_REQUEST = 9;
    const VOUCHER_INVALID_PASS = 10;
    const VOUCHER_INVALID_ENCRYPT = 11;
    const VOUCHER_INVALID_SINGNATURE = 12;

    // dùngchung
    const VOUCHER_ERROR_WRONG_TIME = 36; // Nạp sai quá số lần quy định CHÚ Ý từ 7 - 40 là dùng chung nên không được trùng với định nghĩa cho nạp thẻ điện thoại
    const VOUCHER_CHANGE_CLIENT = 37; // Dữ liệu bị thay đổi từ client truyền lên
    const VOUCHER_CHANGE_VOUCHER = 38; // Dữ liệu bị thay đổi từ voucher truyền sang
    const VOUCHER_NOT_RESULT = 39; // Không có kết quả
    const VOUCHER_NOT_USER = 40; // Không tồn tại người dùng

    // Định nghĩa cho thẻ nạp điện thoại
    const VOUCHER_PHONE_SUCCESS = 0; // THÀNH CÔNG
    const VOUCHER_PHONE_WRONG_PARAMETERS = 97; // Mã hóa sai các tham số
    const VOUCHER_PHONE_NOT_EXITS_CP = 98; // CP bị khóa hoặc không tồn tại
    const VOUCHER_PHONE_WRONG_FORMAT = 99; // Thiếu tham số hoặc tham số sai format
    const VOUCHER_PHONE_ERROR_CARDCHARGING1 = 100; // Lỗi hệ thống CardCharging
    const VOUCHER_PHONE_NOT_FOUND_CARDCHARGING = 80; // Không tìm thấy module CardCharging
    const VOUCHER_PHONE_NOT_CONNECT_CARDCHARGING = 81; // Không kết nối đến module CardCharging
    const VOUCHER_PHONE_ERROR_CARDCHARGING2 = 82; // Lỗi hệ thống đối tác CardCharging
    const VOUCHER_PHONE_ERROR_MODULE_CARDCHARGING1 = 83; // Lỗi module CardCharging
    const VOUCHER_PHONE_WRONG_10_TIME = 84; // Lỗi giao dịch sai quá 10 lần
    const VOUCHER_PHONE_ERROR_MODULE_CARDCHARGING2 = 89; // Lỗi chung module CardCharging
    const VOUCHER_PHONE_USED = 10; // Lỗi THẺ ĐÃ ĐƯỢC DÙNG
    const VOUCHER_PHONE_BLOCK = 11; // Lỗi THẺ BỊ KHÓA
    const VOUCHER_PHONE_EXPIRE = 12; // Lỗi THẺ HẾT HẠN SỬ DỤNG
    const VOUCHER_PHONE_NOT_ACTIVE = 13; // Thẻ chưa được kích hoạt
    const VOUCHER_PHONE_NOT_HAVE = 14; // Mã thẻ không đúng định dạng
    const VOUCHER_PHONE_ERROR_VMS = 18; // Lỗi hệ thống VMS
    const VOUCHER_PHONE_ERROR_ORTHER = 19; // Lỗi khác
    const VOUCHER_PHONE_ERROR_VNP = 20; // Thẻ không sử dụng được, lỗi chung của VNP
    const VOUCHER_PHONE_ERROR_VTE = 30; // Thẻ không sử dụng được, lỗi chung của Viettel
    const VOUCHER_PHONE_INCORRECT = 4; // Thẻ ko đúng
    // loi tra khi ket noi sang Epay
    const VOUCHER_PHONE_USED_EPAY = 50;


    // topup nap the
    const TOPUP_SUCCESS = 0;
    const TOPUP_EMPTY_USER = 1;
    const TOPUP_EMPTY_COST = 2;
    const TOPUP_EMPTY_PARTNER = 3;
    const TOPUP_EMPTY_SINGNATURE = 4;
    const TOPUP_COST_INCORECT = 5;
    const TOPUP_WRONG_SINGNATURE = 6;
    const TOPUP_WRONG_USER = 7;
    const TOPUP_WRONG_PARTNER = 8;
    const TOPUP_ERROR_SYSTEM = 9;

    const IS_WHITELIST = 1;
    const NOT_WHITELIST = 2;

    const IS_ACTIVE = 1;
    const IS_NOT_ACTIVE = 0;

    // loại thuê bao
    const TYPE_USER = 1; // thuê bao đang được sử dụng
    const TYPE_NSX = 2; // thuê bao test ở nhà máy

    // đánh dấu thuê bao đã được khởi tạo
    const INITIALIZED = 1; // thuê bao đã được khởi tạo
    const NOT_INITIALIZED = 0; // thuê bao chưa được khởi tạo

    /*
     * @var string password for register scenario
     */
    public $password;
    public $confirm_password;
    public $new_password;
    public $old_password;
    public $number_month;
    public $device_type;
    public $serial;
    public $service_name;
    public $total_topup;
    public $transaction_time;
    public $channel;
    public $topup_pending;
    public $error_code;
    public $gateway;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%subscriber}}';
    }

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function getContentMessageRecharge($username, $olbBalance, $price, $newBalance)
    {
        return Yii::t('app', 'Cám ơn Quý khách đã sử dụng dịch vụ TVOD.<br>
                    Tài khoản ' . $username . ' của quý khách đã thực hiện giao dịch nạp tiền thành công.<br>
                    Số dư đầu kỳ: ' . $olbBalance . ' coin. <br>
                    Số coin nạp: ' . $price . ' coin <br>
                    Số dư cuối kỳ: ' . $newBalance . ' coin.<br>
                    Vui lòng gọi 1900.1525 nhánh 3 để biết thêm chi tiết.
                    ');
    }

    public static function getTitleMessageRecharge()
    {
        return Yii::t('app', 'Giao dịch nạp tiền thành công');
    }

    public function addServiceToSubscriber($transaction_type, $channel_type, $smsPrice, $service_package_id, $purchaseService, $service = false, $number_month)
    {
        // them transaction
        $tranDesc = ($transaction_type == SubscriberTransaction::TYPE_REGISTER ? \Yii::t('app', "Mua gói dịch vụ '") : \Yii::t('app', "Gia hạn dịch vụ '")) . $purchaseService->display_name . "'";
        $tr = $this->newTransaction($transaction_type, $channel_type, $tranDesc, $purchaseService);

        // tien hanh charge tien
        $chargingSuccess = false;
        $price = round($channel_type == SubscriberTransaction::CHANNEL_TYPE_SMS ? $purchaseService->pricing->price_sms : $purchaseService->pricing->price_coin);
        // Số tiền thanh toán sẽ bằng đơn giá/tháng nhân số tháng mua
        $price = $price * $number_month;
        $newBalance = $this->balance;
        $oldBalance = $this->balance;
        if ($channel_type == SubscriberTransaction::CHANNEL_TYPE_SMS && $smsPrice >= $price) {
            $chargingSuccess = true;
        } else if ($price <= $this->balance) {
            $newBalance = $this->balance - $price;
            $this->balance = $newBalance;
            $this->update(true, ['balance']);
            $chargingSuccess = true;
        }

        //TODO partner_id?
        $tr->status = $chargingSuccess ? SubscriberTransaction::STATUS_SUCCESS : SubscriberTransaction::STATUS_FAIL;
        $tr->cost = $price;
        $tr->number_month = $number_month;
        $serviceCpAsm = ServiceCpAsm::findAll(['service_id' => $purchaseService->id]);
        $serviceCp = '';
        foreach ($serviceCpAsm as $item) {
            $serviceCp .= $item->cp_id . ',';
        }

        $tr->cp_id = rtrim($serviceCp, ',');
        $tr->balance = $chargingSuccess ? $newBalance : $oldBalance;
        $tr->balance_before_charge = $oldBalance;
        $tr->currency = $channel_type == SubscriberTransaction::CHANNEL_TYPE_SMS ? $purchaseService->site->currency : 'coin';

        if ($chargingSuccess) {
            // charging thanh cong --> tao mapping trong SubscriberServicePackageAsm va tra mt
            $expiryDate = new \DateTime();
            $new_number_month = $number_month;
            if ($transaction_type == SubscriberTransaction::TYPE_RENEW) {
                $ssa = SubscriberServiceAsm::find()
                    ->andWhere(['subscriber_id' => $this->id, 'service_id' => $service_package_id, 'status' => SubscriberServiceAsm::STATUS_ACTIVE])
                    ->orderBy(['expired_at' => SORT_DESC])
                    ->one();
                /** @var $ssa SubscriberServiceAsm */
                $ssa->renewed_at = (new DateTime())->getTimestamp();
                if ($ssa->status == SubscriberServiceAsm::STATUS_ACTIVE && $expiryDate->getTimestamp() < $ssa->expired_at) {
                    $expiryDate = (new DateTime())->setTimestamp($ssa->expired_at);
                }
                if ($ssa->number_buy_month) {
                    $new_number_month = $ssa->number_buy_month + $number_month;
                }
            } else {
                if ($service) {
                    $ssa = SubscriberServiceAsm::find()
                        ->andWhere([
                            'subscriber_id' => $this->id,
                            'service_id' => $service->id,
                            'status' => SubscriberServiceAsm::STATUS_ACTIVE])
                        ->orderBy(['expired_at' => SORT_DESC])
                        ->one();
                    /** @var $ssa SubscriberServiceAsm */
                    $ssa->renewed_at = (new DateTime())->getTimestamp();
                    if ($expiryDate->getTimestamp() < $ssa->expired_at) {
                        $expiryDate = (new DateTime())->setTimestamp($ssa->expired_at);
                    }
                    if ($ssa->number_buy_month) {
                        $new_number_month = $ssa->number_buy_month + $number_month;
                    }
                } else {
                    $ssa = new SubscriberServiceAsm();
                    $ssa->subscriber_id = $this->id;
                    $ssa->msisdn = $this->msisdn;
                    $ssa->service_name = $purchaseService->display_name;
                    $ssa->service_id = $service_package_id;
                    $ssa->white_list = $this->whitelist;
                    $ssa->site_id = $purchaseService->site_id;
                    $ssa->dealer_id = $this->dealer_id;
                    $activationDate = new \DateTime();
                    $ssa->auto_renew = $purchaseService->auto_renew;
                    $ssa->renew_fail_count = 0;
                    $ssa->activated_at = $activationDate->getTimestamp();
                    $tr->is_first_package = SubscriberTransaction::IS_FIRST_PACKAGE;
                }
            }
            $ssa->transaction_id = $tr->id;
            $ssa->number_buy_month = $new_number_month;

            if (isset($purchaseService->period) && $purchaseService->period > 0) {

                $expire_time = $ssa->expired_at > 0 ? $ssa->expired_at + $number_month * 86400 * 30
                    : time() + $number_month * 86400 * 30;
                $ssa->expired_at = $expire_time;
                $tr->expired_time = $expire_time;
            } else {
                // Neu goi cuoc ko co thoi han thi ngay het han de trong
                $ssa->expired_at = null;
                $tr->expired_time = null;
            }

            $ssa->status = SubscriberServiceAsm::STATUS_ACTIVE;

            if (!$ssa->save()) {
                Yii::trace("ERROR: cannot save ssa: " . Json::encode($ssa));
            }

            $tr->subscriber_service_asm_id = $ssa->id;
            $message = $transaction_type == SubscriberTransaction::TYPE_REGISTER ?
                Yii::t('app', 'Đăng kí gói cước thành công') :
                Yii::t('app', 'Gia hạn gói cước thành công');

            $err_code = CommonConst::API_ERROR_NO_ERROR;

        } else {
            $message = Yii::t('app', "Tài khoản không đủ tiền, quý khách vui lòng nạp thêm");
            $err_code = CommonConst::API_ERROR_CHARGING_FAIL;

        }

        if (!$tr->update()) {
            Yii::trace("ERROR: cannot update transaction: " . Json::encode($tr->getErrors()));
//            SysCproviderService::SysPurchaseService($transaction_type,null,$purchaseService,$this,$price,null,$ssa);
        }

        if ($err_code == CommonConst::API_ERROR_NO_ERROR) {
            // Them vào hòm thư  nội bộ 11/12/2017
            if ($transaction_type == SubscriberTransaction::TYPE_REGISTER) {
                $title = Yii::t('app', 'Giao dịch mua gói dịch vụ thành công');
                $content = Yii::t('app', 'Cám ơn Quý khách đã sử dụng dịch vụ TVOD. <br>
                Tài khoản ' . $this->username . ' của quý khách đã thực hiện giao dịch mua gói thành công, 
                Gói ' . $purchaseService->display_name . ' sẽ hết hạn vào ' . date('d-m-Y H:i:s', $ssa->expired_at) . '. 
                Số dư cuối kỳ của tài khoản là: ' . $tr->balance . ' coin. <br>
                Vui lòng gọi 1900.1525 nhánh 3 để biết thêm chi tiết.');
            } else {
                $title = Yii::t('app', 'Gia hạn gói cước thành công');
                $content = Yii::t('app', 'Cám ơn Quý khách đã sử dụng dịch vụ TVOD. <br>
                Tài khoản ' . $this->username . ' của quý khách đã thực hiện gia hạn gói ' . $purchaseService->display_name . ' 
                thành công, Gói ' . $purchaseService->display_name . ' sẽ hết hạn vào ' . date('d-m-Y H:i:s', $ssa->expired_at) . '. 
                Số dư cuối kỳ của tài khoản là: ' . $tr->balance . ' coin. <br>
                Vui lòng gọi 1900.1525 nhánh 3 để biết thêm chi tiết.
                ');
            }

            SmsSupport::addSmsSupportByContent($title, $content, $this);
            Yii::info('bat dau dong bo');
            SysCproviderService::SysPurchaseService($transaction_type, $tr->id, $purchaseService, $this, $price, $channel_type, $ssa);
        }

        return [
            'error' => $err_code,
            'message' => $message,
            'balance' => $this->balance
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['site_id', 'authen_type', 'password_hash'], 'required'], // Bỏ required với msisdn
            [['username'], 'required', 'on' => 'create'], // cuongvm 20170523 Bỏ required với username khi create bằng api bởi machine_name
            [['username'], 'unique'],
            [['username', 'msisdn'], 'validateUnique', 'on' => 'create'], //** Enable cái này nếu cần thiết => $model->setScenario('create'); */
            [
                [
                    'site_id',
                    'dealer_id',
                    'authen_type',
                    'channel',
                    'status',
                    'last_login_at',
                    'last_login_session',
                    'birthday',
                    'sex',
                    'created_at',
                    'updated_at',
                    'client_type',
                    'using_promotion',
                    'auto_renew',
                    'expired_at',
                    'number_otp',
                    'whitelist',
                    'expired_code_time',
                    'register_at',
                    'is_active',
                    'type',
                    'initialized_at',
                    'service_initialized',
                    'topup_pending'
                ],
                'integer',
            ],
            ['whitelist', 'default', 'value' => 2],
            ['whitelist', 'in', 'range' => [self::IS_WHITELIST, self::NOT_WHITELIST]],
//            [
//                'username',
//                'match', 'pattern' => '/^[\*a-zA-Z0-9]{1,20}$/',
//                'message' => Yii::t('app', 'Thông tin không hợp lệ, tên tài khoản - Tối đa 20 ký tự (bao gồm chữ cái và số) không bao gồm ký tự đặc biệt '),
//                'on' => 'create'
//            ],
            [['msisdn', 'ip_address'], 'string', 'max' => 45],
//            [
            //                'msisdn',
            ////                'match', 'pattern' => '/^0[0-9]$/',
            //                'match', 'pattern' => '/^(0)\d{9,10}$/',
            //                'message' => 'Thông tin không hợp lệ, số điện thoại - Định dạng số điện thoại bắt đầu với số 0, ví dụ 0912345678, 012312341234',
            ////                'on' => ['create','update'],
            //            ],
            [['verification_code', 'otp_code', 'auth_key'], 'string', 'max' => 32],
            [['username', 'machine_name', 'email'], 'string', 'max' => 100],
            [['full_name', 'password'], 'string', 'max' => 200],
            [['password_hash', 'address', 'city'], 'string', 'max' => 255],
            [['avatar_url', 'skype_id', 'google_id', 'facebook_id', 'province_code', 'ip_to_location'], 'string', 'max' => 255],
            [['user_agent'], 'string', 'max' => 512],
            //cuongvm
            ['password', 'string', 'min' => 8, 'tooShort' => Yii::t('app', 'Mật khẩu không hợp lệ. Mật khẩu ít nhất 8 ký tự')],
//            [
            //                'password',
            //                'match', 'pattern' => '/^[\a-zA-Z0-9]{8,16}$/',
            //                'message' => 'Thông tin không hợp lệ, mật khẩu bao gồm 8 - 16 chữ cái hoặc số, xin vui lòng nhập lại',
            //            ],
            ['confirm_password', 'string', 'min' => 8, 'tooShort' => Yii::t('app', 'Xác nhận mật khẩu không hợp lệ, ít nhất 8 ký tự')],
            ['new_password', 'string', 'min' => 8, 'tooShort' => Yii::t('app', 'Mật khẩu không hợp lệ, ít nhất 8 ký tự')],
            [['confirm_password', 'password', 'dealer_id'], 'required', 'on' => 'create'],
            [
                ['confirm_password'],
                'compare',
                'compareAttribute' => 'password',
                'message' => Yii::t('app', 'Xác nhận mật khẩu không đúng.'),
                'on' => 'create',
            ],
            [
                ['confirm_password'],
                'compare',
                'compareAttribute' => 'new_password',
                'message' => Yii::t('app', 'Xác nhận mật khẩu chưa đúng.'),
                'on' => 'change-password',
            ],
            [['new_password'], 'required', 'on' => 'change-password'],
            [['old_password', 'new_password', 'confirm_password'], 'required', 'on' => 'change-password'],
            [['email'], 'email', 'message' => Yii::t('app', 'Email không đúng định dạng')],
            [['balance'], 'integer', 'min' => 0],
//            [['full_name','address'], 'safe'],
            [['phone_number', 'ip_location_first',],'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'site_id' => Yii::t('app', 'Nhà cung cấp'),
            'dealer_id' => Yii::t('app', 'Đại lý'),
            'authen_type' => Yii::t('app', 'Loại xác thực'),
            'channel' => Yii::t('app', 'Kênh đăng ký'),
            'msisdn' => Yii::t('app', 'Số điện thoại'),
            'username' => Yii::t('app', 'Tên tài khoản'),
            'machine_name' => Yii::t('app', 'Tên Box'),
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'status' => Yii::t('app', 'Trạng thái'),
            'email' => Yii::t('app', 'Email'),
            'full_name' => Yii::t('app', 'Họ và tên'),
            'password' => Yii::t('app', 'Mật khẩu'),
            'confirm_password' => Yii::t('app', 'Mật khẩu xác nhận'),
            'last_login_at' => Yii::t('app', 'Last Login At'),
            'last_login_session' => Yii::t('app', 'Last Login Session'),
            'birthday' => Yii::t('app', 'Ngày tháng năm sinh'),
            'sex' => Yii::t('app', 'Giới tính'),
            'avatar_url' => Yii::t('app', 'Avatar Url'),
            'skype_id' => Yii::t('app', 'Skype ID'),
            'google_id' => Yii::t('app', 'Google ID'),
            'facebook_id' => Yii::t('app', 'Facebook ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'client_type' => Yii::t('app', 'Client Type'),
            'using_promotion' => Yii::t('app', 'Using Promotion'),
            'auto_renew' => Yii::t('app', 'Auto Renew'),
            'verification_code' => Yii::t('app', 'Verification Code'),
            'user_agent' => Yii::t('app', 'User Agent'),
            'balance' => Yii::t('app', 'Tài khoản ví'),
            'address' => Yii::t('app', 'Địa chỉ'),
            'city' => Yii::t('app', 'Tỉnh/ Thành phố'),
            'whitelist' => Yii::t('app', 'whitelist'),
            'otp_code' => Yii::t('app', 'Opt Code'),
            'ip_address' => Yii::t('app', 'Địa chỉ IP'),
            'phone_number' => Yii::t('app', 'Số điện thoại 2')
        ];
    }

    public function validateUnique($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $subscriber = Subscriber::findOne(['username' => $this->username, 'status' => [Subscriber::STATUS_ACTIVE, Subscriber::STATUS_INACTIVE]]);
            if ($subscriber) {
                $this->addError($attribute, Yii::t('app', 'Tên tài khoản đã tồn tại. Vui lòng chọn tên khác!'));
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentFeedbacks()
    {
        return $this->hasMany(ContentFeedback::className(), ['subscriber_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentKeywords()
    {
        return $this->hasMany(ContentKeyword::className(), ['subscriber_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentViewLogs()
    {
        return $this->hasMany(ContentViewLog::className(), ['subscriber_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReportMonthlyCpRevenueDetails()
    {
        return $this->hasMany(ReportMonthlyCpRevenueDetail::className(), ['subscriber_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSmsMessages()
    {
        return $this->hasMany(SmsMessage::className(), ['subscriber_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDealer()
    {
        return $this->hasOne(Dealer::className(), ['id' => 'dealer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberActivities()
    {
        return $this->hasMany(SubscriberActivity::className(), ['subscriber_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberContentAsms()
    {
        return $this->hasMany(SubscriberContentAsm::className(), ['subscriber_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberContentAsms0()
    {
        return $this->hasMany(SubscriberContentAsm::className(), ['subscriber2_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberFavorites()
    {
        return $this->hasMany(SubscriberFavorite::className(), ['subscriber_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberFeedbacks()
    {
        return $this->hasMany(SubscriberFeedback::className(), ['subscriber_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberServiceAsms()
    {
        return $this->hasMany(SubscriberServiceAsm::className(), ['subscriber_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberServiceAsms0()
    {
        return $this->hasMany(SubscriberServiceAsm::className(), ['subscriber2_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberTokens()
    {
        return $this->hasMany(SubscriberToken::className(), ['subscriber_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberTransactions()
    {
        return $this->hasMany(SubscriberTransaction::className(), ['subscriber_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(Service::className(), ['id' => 'service_id'])
            ->viaTable('subscriber_service_asm', ['subscriber_id' => 'id'], function ($query) {
                $query->onCondition(['status' => SubscriberServiceAsm::STATUS_ACTIVE]);
                $query->onCondition(['>=', 'expired_at', time()]);
                return $query;
            });
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContents()
    {
        return $this->hasMany(Service::className(), ['id' => 'content_id'])
            ->viaTable('subscriber_content_asm', ['subscriber_id' => 'id']);
    }

    /**
     * ******************************** MY FUNCTION ***********************
     */
    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateVerifyCode()
    {
        $this->verification_code = Yii::$app->security->generateRandomString(6);
    }

    public static function findByVerifyToken($verify_code, $username)
    {
        return static::findOne([
            'verification_code' => $verify_code,
            'username' => $username,
            'status' => self::STATUS_INACTIVE,
        ]);
    }

    /**
     * @param $username
     * @param $site_id
     * @param bool|true $status
     * @return null|static
     */
    public static function findByUsername($username, $site_id, $status = true)
    {
        if (!$status) {
            return Subscriber::findOne(['username' => $username, 'site_id' => $site_id]);
        }
        return Subscriber::findOne(['username' => $username, 'site_id' => $site_id, 'status' => Subscriber::STATUS_ACTIVE]);
    }

    /**
     * @param $machine_name
     * @param $site_id
     * @param bool $status
     * @return static
     */
    public static function findByMachine($machine_name, $site_id, $status = true)
    {
        if (!$status) {
            return Subscriber::find()->andWhere(['machine_name' => $machine_name, 'site_id' => $site_id])
//                ->orderBy(['id' => SORT_DESC])
                ->one();
//            return Subscriber::findOne(['machine_name' => $machine_name, 'site_id' => $site_id]);
        }
        return Subscriber::find()->andWhere(['machine_name' => $machine_name, 'site_id' => $site_id, 'status' => Subscriber::STATUS_ACTIVE])
//            ->orderBy(['id' => SORT_DESC])
            ->one();
//        return Subscriber::findOne(['machine_name' => $machine_name, 'site_id' => $site_id, 'status' => Subscriber::STATUS_ACTIVE]);
    }

    /**
     * @param $password
     * @return bool
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * @param $username
     * @param $password
     * @param $authen_type
     * @param $site_id
     * @param null $mac_address
     * @return array
     */
    public static function register($username, $password, $msisdn, $city = null, $status = Subscriber::STATUS_ACTIVE, $authen_type, $site_id, $channel = Subscriber::CHANNEL_TYPE_ANDROID, $mac_address = null, $address = '', $email = '', $fullname = '')
    {
        $res = [];
        /** Chuyển sang chữ thường */
        $username = strtolower($username);
        $mac_address = strtolower($mac_address);

        $subscriber = new Subscriber();
        $subscriber->username = $username;
        $subscriber->machine_name = ($authen_type == Subscriber::AUTHEN_TYPE_MAC_ADDRESS) ? $mac_address : null;
        $subscriber->status = $status;
        $subscriber->msisdn = $msisdn;
        $subscriber->city = $city;
        $subscriber->site_id = $site_id;
        $subscriber->email = $email;
        $subscriber->address = $address;
        $subscriber->full_name = $fullname;
        $subscriber->channel = (int)$channel;
        $subscriber->authen_type = $authen_type;
        $subscriber->password = ($authen_type == Subscriber::AUTHEN_TYPE_MAC_ADDRESS) ? CUtils::randomString(8) : $password;
        if ($authen_type == Subscriber::AUTHEN_TYPE_ACCOUNT) {
            $subscriber->register_at = time();
        }

        if (in_array(Yii::$app->request->getUserIP(), Yii::$app->params['factory_ip'])) {
            $subscriber->type = Subscriber::TYPE_NSX;
        } else {
            $subscriber->type = Subscriber::TYPE_USER;
        }

        $subscriber->setPassword($password);
        $subscriber->generateAuthKey();
        /** Validate và save, nếu có lỗi thì return message_error */
        if (!$subscriber->validate()) {
            $message = $subscriber->getFirstMessageError();
            $res['status'] = false;
            $res['message'] = $message;
            return $res;
        }
        if (!$subscriber->save()) {
            $res['status'] = false;
            $res['message'] = Message::getFailMessage();
            return $res;
        }
        /** TODO tạo bảng quan hệ Subscriber với Device mỗi khi tạo account */
        if ($mac_address) {
            /** @var  $device Device */
            $device = Device::findByMac($mac_address, $site_id);
            if ($device) {
                // if($authen_type == Subscriber::AUTHEN_TYPE_MAC_ADDRESS){
                /** MAC first_login, last_login **/
                $device->first_login = time();
//                    $device->last_login = time();
                $device->save();
                // }

//                SubscriberDeviceAsm::createSubscriberDeviceAsm($subscriber->id, $device->id);
            }
        }

//        $item = $subscriber->getAttributes(['id', 'username','full_name', 'msisdn', 'status', 'site_id', 'created_at', 'updated_at'], ['password_hash', 'authen_type']);
        $res['status'] = true;
        $res['message'] = Message::getRegisterSuccessMessage();
        $res['subscriber'] = $subscriber;
        return $res;
    }

    private function getFirstMessageError()
    {
        $error = $this->firstErrors;
        $message = "";
        foreach ($error as $key => $value) {
            $message .= $value;
            break;
        }
        return $message;
    }

    public function saveProperties($mac_address = null)
    {
        $res = [];
        /** Chuyển sang chữ thường */
        $this->username = strtolower($this->username);
        $mac_address = strtolower($mac_address);
        Yii::info('Trang thai nhan dc: ' . $this->status);
        if ($this->status == null) {
            $this->status = Subscriber::STATUS_ACTIVE;
        }
        if ($this->authen_type == Subscriber::AUTHEN_TYPE_MAC_ADDRESS) {
            $this->password = CUtils::randomString(8);
        }
        if ($this->password) {
            $this->setPassword($this->password);
            $this->generateAuthKey();
        }
        /** Validate và save, nếu có lỗi thì return message_error */
        if (!$this->validate()) {
            $message = $this->getFirstMessageError();
            $res['status'] = false;
            $res['message'] = $message;
            return $res;
        }
        if (!$this->save()) {
            $res['status'] = false;
            $res['message'] = Message::getFailMessage();
            return $res;
        }
        /** TODO tạo bảng quan hệ Subscriber với Device mỗi khi tạo account */
        if ($mac_address) {
            /** @var  $device Device */
            $device = Device::findByMac($mac_address, $this->site_id);
            if ($device) {
                SubscriberDeviceAsm::createSubscriberDeviceAsm($this->id, $device->id);
            }
        }

        /** TODO gán cho subscriber default gói cước mặc định của thị trường */
        if ($this->authen_type == Subscriber::AUTHEN_TYPE_MAC_ADDRESS) {
            /** @var  $site Site */
            $site = Site::findOne($this->site_id);
            /** @var  $service Service */
            $service = $site->defaultService;
            if ($service) {
                $ssa = new SubscriberServiceAsm();
                $ssa->subscriber_id = $this->id;
                $ssa->service_id = $service->id;
                $ssa->service_name = $service->name;
                $ssa->site_id = $this->site_id;
                $ssa->activated_at = time();

                $expiryDate = new DateTime();
                if (isset($service->period) && $service->period > 0) {
                    $expiryDate->add(new DateInterval("P" . $service->period . 'D'));
                }

                /** Nếu charging_period <=0 thì set expired_at =null. Theo yêu cầu BA và có confirm của ViệtNV */
                $ssa->expired_at = $service->period > 0 ? $expiryDate->getTimestamp() : null;
                $ssa->status = SubscriberServiceAsm::STATUS_ACTIVE;
                $ssa->save();
            }
        }

        $res['status'] = true;
        $res['message'] = Message::getRegisterSuccessMessage();
        $res['subscriber'] = $this;
        return $res;
    }

//    public static function register($username, $password, $site_id)
    //    {
    //        $res = [];
    //        if (self::findSubscriberBySP($msisdn, $site_id, false)) {
    //            $res['status'] = false;
    //            $res = ['message' => 'Đã có tài khoản này rồi'];
    //            return $res;
    //        }
    //        $defaultPassword = '123456';
    //        $subscriber = new Subscriber();
    //        $subscriber->username = $msisdn;
    //        $subscriber->msisdn = $msisdn;
    //        $subscriber->setPassword($defaultPassword);
    //        $subscriber->verification_code = $defaultPassword;
    //        $subscriber->site_id = $site_id;
    //        $subscriber->created_at = time();
    //        $subscriber->updated_at = time();
    //        if ($status) {
    //            $subscriber->status = Subscriber::STATUS_ACTIVE;
    //        } else {
    //            $subscriber->status = Subscriber::STATUS_INACTIVE;
    //        }
    //
    //        if ($subscriber->save()) {
    //            $res['status'] = true;
    //            $res['message'] = "Đăng ký thành công";
    //        } else {
    //            $res['status'] = false;
    //            $res['message'] = "Đăng ký thất bại";
    //            $res['err'] = $subscriber->getFirstErrors();
    //        }
    //
    //        return $res;
    //    }

    /**
     * Finds an identity by the given ID.
     * @param string|integer $id the ID to be looked for
     * @return IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        // TODO: Implement findIdentity() method.
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        /* @var SubscriberToken $subscriber_token */
        /* @var Subscriber $subscriber */
        $subscriber_token = SubscriberToken::findByAccessToken($token);

        if ($subscriber_token) {
            $subscriber = $subscriber_token->getSubscriber()->one();
            if ($subscriber) {
                $subscriber->access_token = $token;
            }

            return $subscriber;
        }

        return null;
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|integer an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        // TODO: Implement getId() method.
        return $this->getPrimaryKey();
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        // TODO: Implement getAuthKey() method.
        return $this->auth_key;
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return boolean whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
        return $this->getAuthKey() === $authKey;
    }

    /**
     * @return array
     */
    public static function listStatus()
    {
        $lst = [
            self::STATUS_ACTIVE => Yii::t('app', 'Hoạt động'),
            self::STATUS_INACTIVE => Yii::t('app', 'Tạm khóa'),
            self::STATUS_DELETED => \Yii::t('app', 'Đã xóa'),
            self::STATUS_MAINTAIN => \Yii::t('app', 'Bảo hành'),
        ];
        return $lst;
    }

    public static function listCity($site_id)
    {
        $city = [];
        $listCity = City::find()->andWhere(['site_id' => $site_id])->all();
        foreach ($listCity as $item) {
            /** @var $item City */
            $city[$item->name] = $item->name;
        }
        return $city;
    }

    /**
     * @return int
     */
    public function getStatusName()
    {
        $lst = self::listStatus();
        if (array_key_exists($this->status, $lst)) {
            return $lst[$this->status];
        }
        return $this->status;
    }

    /**
     * @return array
     */
    public static function listClientType()
    {
        $lst = [
            SubscriberTransaction::CHANNEL_TYPE_API => Yii::t('app', 'API'),
            SubscriberTransaction::CHANNEL_TYPE_SYSTEM => Yii::t('app', 'SYSTEM'),
            SubscriberTransaction::CHANNEL_TYPE_CSKH => Yii::t('app', 'CSKH'),
            SubscriberTransaction::CHANNEL_TYPE_SMS => Yii::t('app', 'SMS'),
//            SubscriberTransaction::CHANNEL_TYPE_WAP => 'Wap',
            SubscriberTransaction::CHANNEL_TYPE_MOBILEWEB => Yii::t('app', 'Mobile Web'),
            SubscriberTransaction::CHANNEL_TYPE_ANDROID => Yii::t('app', 'SmartBox'),
            SubscriberTransaction::CHANNEL_TYPE_IOS => Yii::t('app', 'IOS'),
            SubscriberTransaction::CHANNEL_TYPE_ANDROID_MOBILE => Yii::t('app', 'Android')
        ];
        return $lst;
    }


    /**
     * @return array
     */
    public static function listChannelType()
    {
        $lst = [
            self::CHANNEL_TYPE_API => Yii::t('app', 'API'),
            self::CHANNEL_TYPE_SYSTEM => Yii::t('app', 'SYSTEM'),
            self::CHANNEL_TYPE_CSKH => Yii::t('app', 'CSKH'),
            self::CHANNEL_TYPE_SMS => Yii::t('app', 'SMS'),
            self::CHANNEL_TYPE_MOBILEWEB => Yii::t('app', 'Mobile Web'),
            self::CHANNEL_TYPE_ANDROID => Yii::t('app', 'SmartBox'),
            self::CHANNEL_TYPE_IOS => Yii::t('app', 'IOS'),
            self::CHANNEL_TYPE_ANDROID_MOBILE => Yii::t('app', 'Android')
        ];
        return $lst;
    }

    /**
     * @return int
     */
    public function getClientTypeName()
    {
        $lst = self::listClientType();
        if (array_key_exists($this->client_type, $lst)) {
            return $lst[$this->client_type];
        }
        return $this->client_type;
    }

    /**
     * @return array
     */
    public static function listSex()
    {
        $lst = [
            self::SEX_NAM => Yii::t('app', 'Nam'),
            self::SEX_NU => Yii::t('app', 'Nữ'),
        ];
        return $lst;
    }

    /**
     * @return int
     */
    public function getSexName()
    {
        $lst = self::listSex();
        if (array_key_exists($this->sex, $lst)) {
            return $lst[$this->sex];
        }
        return $this->sex;
    }

    public function getDisplayName()
    {
        if ($this->full_name != null && $this->full_name != '') {
            return $this->full_name;
        }

        return $this->username;
    }

    public function getDealerName()
    {
        $dealer = $this->getDealer()->one();
        if ($dealer) {
            return $dealer->name;
        }

        return '';
    }

    /**
     * @param $msisdn
     * @param $site_id
     * @param bool $create
     * @return Subscriber|null|static
     */
    public static function findByMsisdn($msisdn, $site_id, $create = false)
    {
//        $msisdn = CUtils::validateMobile($msisdn);
        $subscriber = Subscriber::findOne([
            'msisdn' => $msisdn,
            'status' => self::STATUS_ACTIVE,
            'site_id' => $site_id,
        ]);
        if (!$create) {
            return $subscriber;
        } else {
            if ($subscriber) {
                return $subscriber;
            } else {
                $subscriber = new Subscriber();

                $subscriber->msisdn = $msisdn;
                $subscriber->username = $msisdn;
                $subscriber->site_id = $site_id;
                $subscriber->status = Subscriber::STATUS_ACTIVE;

                if ($subscriber->save()) {
                    return $subscriber;
                } else {
                    Yii::trace($subscriber->errors);
                }
            }
            return null;
        }
    }

    /**
     * @param $promotion
     * @param $trial
     * @param $bundle
     * @param $service Service
     */
    public function getVpntPurchasePrice($promotion, $trial, $bundle, $service)
    {
        if (Service::freeFirst($service) && $this->isFirstRegister($service)) {
            $result = [
                'period' => $service->period,
                'price' => 0,
                'auto_renew' => 1,
            ];
        } else {
            $result = [
                'period' => $service->period,
                'price' => $service->price,
                'auto_renew' => 1,
            ];
        }

        if ($bundle == 1) {
            $result = [
                'period' => $service->period,
                'price' => 0,
                'auto_renew' => 0,
            ];
            return $result;
        }
        $promotion = strtoupper($promotion);
        if ($promotion != 0) {
            switch ($promotion) {
                case CUtils::endsWith($promotion, 'C'):
                    $result = [
                        'period' => $service->period * (int)$promotion,
                        'price' => 0,
                        'auto_renew' => 1,
                    ];
                    break;
                case CUtils::endsWith($promotion, 'D'):
                    $result = [
                        'period' => (int)$promotion,
                        'price' => 0,
                        'auto_renew' => 1,
                    ];
                    break;
                case CUtils::endsWith($promotion, 'W'):
                    $result = [
                        'period' => 7 * (int)$promotion,
                        'price' => 0,
                        'auto_renew' => 1,
                    ];
                    break;
                case CUtils::endsWith($promotion, 'M'):
                    $result = [
                        'period' => 30 * (int)$promotion,
                        'price' => 0,
                        'auto_renew' => 1,
                    ];
                    break;
            }
            return $result;
        }
        $trial = strtoupper($trial);
        if ($trial != 0) {
            switch ($trial) {
                case CUtils::endsWith($trial, 'C'):
                    $result = [
                        'period' => $service->period * (int)$trial,
                        'price' => 0,
                        'auto_renew' => 1,
                    ];
                    break;
                case CUtils::endsWith($trial, 'D'):
                    $result = [
                        'period' => (int)$trial,
                        'price' => 0,
                        'auto_renew' => 1,
                    ];
                    break;
                case CUtils::endsWith($trial, 'W'):
                    $result = [
                        'period' => 7 * (int)$trial,
                        'price' => 0,
                        'auto_renew' => 1,
                    ];
                    break;
                case CUtils::endsWith($trial, 'M'):
                    $result = [
                        'period' => 30 * (int)$trial,
                        'price' => 0,
                        'auto_renew' => 1,
                    ];
                    break;
            }
            return $result;
        }
        return $result;
    }

    private static function getAutoRenewParams($param)
    {
        return Yii::$app->params['auto_renew'][$param];
    }

    /**
     * Lay ra danh sach cac thue bao va cac goi cuoc den ky gia han
     * TODO: Check thue bao white list hoac thue bao ko gia han o ngoai ham nay
     * @param $sp Site
     * @param $partition_count
     * @param $partition
     * @return array
     */
    public static function getSubscribersToExtendByPartition($sp, $partition_count, $partition)
    {
        // quet nhung thang het han tu thoi diem nay, de tranh trh ngay hom truoc ko gia han het!!
        // TODO: co the gian rong khoang thoi gian nay ra de quet nhung thang ko gia han kip cua ca tuan truoc
        $minExpiredTime = time() - 20 * 24 * 60 * 60;

        $maxExpiredTime = time() + static::getAutoRenewParams('max_time_in_hours') * 60 * 60; // Thoi gian het han lon nhat dc xu ly trong lan nay, (lay nhung sub co goi het han tu now() den thoi diem nay)

//        $lastRetryTime = time() - (24 - static::getAutoRenewParams('max_time_in_hours')) * 3600; // chi xet cac trh gia han loi truoc thoi diem nay, de tranh viec gia han lai trong cung ngay

        //TODO: test
        //        $minExpiredTime = "2014-05-14 00:00:00";
        //        $maxExpiredTime = "2014-05-14 12:00:00";
        //        $lastRetryTime = "2014-05-14 12:00:00";

        echo "\nSelect all subscribers to extend service with expiry_date from " . date('d-m-Y H:i:s',
                $minExpiredTime) . ' to ' . date('d-m-Y H:i:s',
                $maxExpiredTime) . "\n";

        $beginOfToDay = CommonUtils::getBeginOfDay(time());

        $sql = "select ssa.* from `subscriber_service_asm` ssa JOIN `service` s ON ssa.service_id = s.id" .
            " where " . ($partition_count > 1 ? "(ssa.id % $partition_count = $partition) AND " : "") . // xu ly phan theo partition
            " ssa.site_id = " . $sp->id . " AND" .
            " ssa.auto_renew = 1 AND" .
            " ssa.status = 10 AND " . // dieu kien chung
            " (" . // lev 1
            " (ssa.renew_fail_count = 0 and ssa.expired_at >= $minExpiredTime and ssa.expired_at < $maxExpiredTime)" . // truong hop het han goi cuoc
            " OR (ssa.renew_fail_count > 0 AND (last_renew_fail_at < $beginOfToDay OR today_retry_count < s.max_daily_retry))" . // truong hop gia han loi cua cac ngay truoc
            " )" . // lev 1
            " order by ssa.renew_fail_count asc, ssa.expired_at desc";

        echo "\n$sql\n";
        $ssas = SubscriberServiceAsm::findBySql($sql)->all();

        return $ssas;
    }

    /**
     * @param $transType
     * @param $channelType
     * @param $description
     * @param null $service
     * @param null $content
     * @param int $status
     * @param int $cost
     * @param string $currency
     * @param int $balance
     * @param null $service_provider
     * @param string $error_code
     * @param integer $balance_before_charge
     * @param string $$gateway
     * @return SubscriberTransaction
     */
    public function newTransaction(
        $transType,
        $channelType,
        $description,
        $service = null,
        $content = null,
        $status = SubscriberTransaction::STATUS_FAIL,
        $cost = 0,
        $currency = 'VND',
        $balance = 0,
//        $service_provider = null,
        $error_code = '',
        $card_code = null,
        $card_serial = null,
        $voucher_id = null,
        $order_id = '',
        $balance_before_charge = 0,
        $gateway = ''
    )
    {
        $tr = new SubscriberTransaction();
        $tr->subscriber_id = $this->id;
        $tr->site_id = $this->site_id;
        $tr->dealer_id = $this->dealer_id;
        $tr->msisdn = $this->msisdn;
        $tr->white_list = $this->whitelist;
        $tr->type = $transType;
        $tr->channel = $channelType;
        $tr->description = $description;
        $tr->order_id = $order_id;
        /** @var $service Service */
        if ($service) {
            $tr->service_id = $service->id;
            $tr->site_id = $service->site_id;
        }

        /** @var $content Content */
        if ($content) {
            $tr->content_id = $content->id;
        }
        $tr->created_at = time();
        $tr->status = $status;
        $tr->cost = $cost;
        $tr->currency = $currency;
        $tr->balance = $balance;
        $tr->balance_before_charge = $balance_before_charge;
        $tr->transaction_time = time();
        $tr->error_code = $error_code;
        $tr->gateway = $gateway;
        if ($card_code) {
            $tr->card_code = $card_code;
        }
        if ($card_serial) {
            $tr->card_serial = $card_serial;
        }
        if ($voucher_id) {
            $tr->transaction_voucher_id = $voucher_id;
        }
        if ($tr->save()) {
            return $tr;
        } else {
            Yii::error($tr->getErrors());
            return null;
        }

    }

    /**
     * @param $cancelPackage
     * @param int $channel_type
     * @param int $transaction_type
     * @param bool $sendSMS
     * @param null $serviceNumber
     * @return array
     * @throws \Exception
     */
    public
    function cancelServicePackage(
        $cancelPackage,
        $channel_type = SubscriberTransaction::CHANNEL_TYPE_SMS,
        $transaction_type = SubscriberTransaction::TYPE_CANCEL_SERVICE_BY_SYSTEM,
        $sendSMS = false,
        $serviceNumber = null,
        $ssaId = null
    )
    {
        /* @var $cancelPackage Service */
        $service_package_id = $cancelPackage->id;
        $success = false;

        $subscriberServicesAsm = SubscriberServiceAsm::findOne($ssaId);
        $subscriberServicesAsm->status = SubscriberServiceAsm::STATUS_INACTIVE;
        $subscriberServicesAsm->updated_at = time();
        $subscriberServicesAsm->canceled_at = time();
        if ($subscriberServicesAsm->save(true, ['status', 'updated'])) {
            $success = true;
        }

        if (!$success) {
            CUtils::log("ERROR: can not inactivate ssa: ");
        }

        $tranDesc = Yii::t('app', "Hủy gói gói cước '") . $cancelPackage->display_name . "'";
        $tr = $this->newTransaction($transaction_type, $channel_type, $tranDesc, $cancelPackage);
        //TODO goi dong qua qua VMS?
        // them transaction
        $tr->status = $success ? SubscriberTransaction::STATUS_SUCCESS : SubscriberTransaction::STATUS_FAIL; //lay trang thai cua viec cap nhat trang thai inactive o tren
        $tr->cost = 0; // huy ko mat tien
        $tr->error_code = ChargingResult::CHARGING_RESULT_OK;
        $tr->balance = $this->balance;
        $tr->balance_before_charge = $this->balance;

        if (!$tr->update()) {
            CUtils::log("ERROR: cannot save transaction: " . Json::encode($tr));
        }

        if ($success) {

            return array(
                "error" => CommonConst::API_ERROR_NO_ERROR,
                "message" => ResMessage::cancelServiceSuccess($this, $cancelPackage, $sendSMS, $serviceNumber),
            );
        } else {
            //TODO notification service cancel fail
            return array(
                "error" => CommonConst::API_ERROR_SYSTEM_ERROR,
                "message" => ResMessage::cancelFailBySystemError($this, $cancelPackage, $sendSMS, $serviceNumber),
            );
        }
//        }
    }

    /**
     * @param $service Service
     * @return bool
     */
    public
    function isFirstRegister($service, $maxDay = 90)
    {
        //Check goi cuoc trong 1 group da dc dang ky lan nao chua
        $service_related = $service->getPackageOnGroup(false);

        $ssm = SubscriberServiceAsm::find()
            ->andWhere(['subscriber_id' => $this->id])
            ->andWhere(['service_id' => $service->id])
            ->andWhere(['site_id' => $service->site_id])
            ->count();

        $lastRegister = SubscriberServiceAsm::find()
            ->andWhere(['subscriber_id' => $this->id])
            ->andWhere(['service_id' => $service_related])
            ->andWhere(['site_id' => $service->site_id])
            ->count();
        if ($ssm > 0 || $lastRegister > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $service Service
     * @param $channel_type int
     *
     */
    public
    function changeService($service,
                           $channel_type = SubscriberTransaction::CHANNEL_TYPE_SMS, $sendSMS = false, $smsSuccess = true)
    {
        //Kiem tra dieu kien huy goi
        $chargingSuccess1 = false;
        $subscriber_service_asm = SubscriberServiceAsm::find()->andWhere(["subscriber_id" => $this->id, "status" => SubscriberServiceAsm::STATUS_ACTIVE])
            ->andWhere("expired_at > :p_expired_at", [":p_expired_at" => time()])->all();
        if (!$subscriber_service_asm) {
            return array(
                "error" => CommonConst::API_ERROR_NO_SERVICE_PACKAGE,
                "api_message" => Yii::t('app', "Đổi gói thất bại, bạn chưa đăng ký gói nào"),
            );
        }
        /** @var ServiceGroupAsm $service_group_change */
        $service_group_change = ServiceGroupAsm::findOne(["service_id" => $service->id]);
        if (!$service_group_change) {
            return array(
                "error" => CommonConst::API_ERROR_SERVICE_PACKAGE_ALREADY_PURCHASED,
                "api_message" => Yii::t('app', "Đổi gói thất bại, gói bạn định đổi không thuộc nhóm nào"),
            );
        }
        /** @var Service $cancel_service */
//            $cancel_service = $service_group_change->service;
        $cancel_service = null;

        //Kiem tra xem co phan tu nao cung nhom vs goi dinh doi va goi do da dc dang ky.
        /** @var SubscriberServiceAsm $ssa */
        foreach ($subscriber_service_asm as $ssa) {
            if ($ssa->service_id == $service->id) {
                return array(
                    "error" => CommonConst::API_ERROR_SERVICE_PACKAGE_ALREADY_PURCHASED,
                    "api_message" => Yii::t('app', "Đổi gói thất bại, bạn đã đăng ký gói này"),
                );
            }
            /** @var ServiceGroupAsm $subscriber_group_asm */
            $subscriber_group_asm = ServiceGroupAsm::find(["service_group_id" => $service_group_change->service_group_id, "service_id" => $ssa->service_id])->one();
            if (!$subscriber_group_asm) {
                $cancel_service = $subscriber_group_asm->service;
            }
        }

        if ($cancel_service != null) {
            // Tien hanh huy goi
            $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try {
                $cancel_service->status = SubscriberServiceAsm::STATUS_INACTIVE;
                $cancel_service->updated_at = time();
                if ($cancel_service->save()) {
                    $tranDesc = "Hủy gói dịch vụ '" . $cancel_service->name . "'";
                    $tr = $this->newTransaction(SubscriberTransaction::TYPE_CANCEL_SERVICE_BY_CHANGE_PACKAGE, $channel_type, $tranDesc, $cancel_service);
                    $tr->status = SubscriberTransaction::STATUS_SUCCESS;
                    $tr->cost = 0; // huy ko mat tien
                    $tr->error_code = ChargingResult::CHARGING_RESULT_OK;
                    if ($cancel_service->save()) {
                        $charging_connection = new ChargingConnection($cancel_service->site->vivas_gw_host, $cancel_service->site->vivas_gw_port, $cancel_service->site->vivas_gw_username, $cancel_service->site->vivas_gw_password);
                        $chargingRes = ChargingGW::getInstance($charging_connection)->cancelPackage($this->msisdn, $cancel_service, $tr->id, $channel_type);
                        if ($chargingRes->result == ChargingResult::CHARGING_RESULT_OK) {
                            $chargingSuccess1 = true;
                        }
                    } else {
                        return array(
                            "error" => CommonConst::API_ERROR_SYSTEM_ERROR,
                            "api_message" => Yii::t('app', "Hệ thống đang lỗi. Vui lòng thử lại."),
                        );
                    }
                    if ($tr->save() && $chargingSuccess1 == true) {
                        // Tien hanh mua goi
                        $tranDesc = "Mua gói dịch vụ '" . $service->display_name . "'";
                        $tr = $this->newTransaction(SubscriberTransaction::TYPE_REGISTER_BY_CHANGE_PACKAGE, $channel_type, $tranDesc, $service);

                        // tien hanh charge tien
                        $price = 0;
                        $chargingSuccess = false;

                        $originPrice = round($service->price);
                        $promotion = false;
                        $promotionNote = "";
                        //TODO xet khuyen mai o day
                        if (Service::freeFirst($service) && $this->isFirstRegister($service)) {
                            $price = 0;
                            $promotion = true;
                            $promotionNote = Yii::t('app', "Dang ky goi ") . $service->display_name . Yii::t('app', ' lan dau');
                        } else {
                            $price = $originPrice;
                        }

                        //TODO
                        /* @var $chargingRes ChargingResult */
                        $charging_connection = new ChargingConnection($service->site->vivas_gw_host, $service->site->vivas_gw_port, $service->site->vivas_gw_username, $service->site->vivas_gw_password);
                        $chargingRes = ChargingGW::getInstance($charging_connection)->registerPackage($this->msisdn, $price, $service, $tr->id, $channel_type, $promotion, $promotionNote);

                        $chargingTelco = $chargingRes->error;
                        $chargingResult = $chargingRes->result;
                        if ($chargingRes->result == ChargingResult::CHARGING_RESULT_OK) {
                            $chargingSuccess = true;
                        }
                        Yii::trace("LOG : charging_code: " . $chargingRes->error);

                        $tr->error_code = $chargingResult;
                        //TODO partner_id?
                        $tr->status = $chargingSuccess ? SubscriberTransaction::STATUS_SUCCESS : SubscriberTransaction::STATUS_FAIL;
                        $tr->cost = $price;
                        $tr->site_id = $service->site_id;
                        if (!$tr->update()) {
                            Yii::trace("ERROR: cannot update transaction: " . Json::encode($tr->getErrors()));
                        }

                        //TODO Fix charge luon thanh cong. Chay that thi bo di
                        // $chargingSuccess = true;
                        if ($chargingSuccess) {
                            // charging thanh cong --> tao mapping trong SubscriberServicePackageAsm va tra mt
                            $ssa = new SubscriberServiceAsm();
                            $ssa->subscriber_id = $this->id;
                            $ssa->msisdn = $this->msisdn;
                            $ssa->service_name = $service->display_name;
                            $ssa->service_id = $service->id;
                            $ssa->site_id = $service->site_id;
                            $ssa->transaction_id = $tr->id;
                            $ssa->status = SubscriberServiceAsm::STATUS_ACTIVE;
                            $activationDate = new \DateTime();
                            $expiryDate = new \DateTime();
                            $ssa->auto_renew = $service->auto_renew;

                            if (isset($service->charging_period) && $service->charging_period > 0) {
                                $expiryDate->add(new DateInterval("P" . $service->charging_period . 'D'));
                            }
                            $ssa->activated_at = $activationDate->getTimestamp();
                            $ssa->expired_at = $expiryDate->getTimestamp();
                            // them partner_id , link_ads_id

                            $ssa->renew_fail_count = 0;

                            if ($ssa->save()) {
                                $transaction->commit();
                                /**
                                 * So dang trong whitelist thi ko chay sync vasgate
                                 */
                                //TODO dong bo vasgate

//                                $msgParam = [
                                //                                    ResMtParams::PARAM_SERVICE_PRICE => $service->price,
                                //                                    ResMtParams::PARAM_SERVICE_PERIOD => $service->period,
                                //                                    ResMtParams::PARAM_SERVICE_NAME => $service->display_name,
                                //
                                //                                ];
                                $tr->subscriber_service_asm_id = $ssa->id;
                                if ($promotion) {
                                    $message = ResMessage::firstRegisterSuccess($this, $service, date('d-m-Y', $ssa->expired_at), $sendSMS);
                                } else {
                                    $message = ResMessage::registerSuccess($this, $service, date('d-m-Y', $ssa->expired_at), $sendSMS);
                                }
                                $err_code = CommonConst::API_ERROR_NO_ERROR;
                                return array(
                                    "error" => CommonConst::API_ERROR_NO_ERROR,
                                    "api_message" => Yii::t('app', "Đổi gói thành công"),
                                );
                            } else {
                                return array(
                                    "error" => CommonConst::API_ERROR_SYSTEM_ERROR,
                                    "api_message" => Yii::t('app', "Lỗi hệ thống vui lòng thử lại"),
                                );
                            }
                        } else {
                            return array(
                                "error" => CommonConst::API_ERROR_CHARGING_FAIL,
                                "api_message" => Yii::t('app', "Lỗi hệ thống vui lòng thử lại"),
                            );
                        }
                    }
                } else {
                    // Huy goi that bai
                    $tranDesc = Yii::t('app', "Hủy gói dịch vụ '") . $cancel_service->name . "'";
                    $tr = $this->newTransaction(SubscriberTransaction::TYPE_CANCEL_SERVICE_BY_CHANGE_PACKAGE, $channel_type, $tranDesc, $cancel_service);
                    $tr->status = SubscriberTransaction::STATUS_FAIL;
                    $tr->cost = 0; // huy ko mat tien
                    $tr->error_code = ChargingResult::CHARGING_RESULT_OK;
                    $tr->save();
                    $transaction->commit();
                }
            } catch (Exception $e) {
                Yii::trace($e);
                $transaction->rollback();
                return array(
                    "error" => CommonConst::API_ERROR_SYSTEM_ERROR,
                    "api_message" => Yii::t('app', "Lỗi hệ thống, vui lòng thử lại"),
                );
            }

        } else {
            return array(
                "error" => CommonConst::API_ERROR_SERVICE_PACKAGE_ALREADY_PURCHASED,
                "api_message" => Yii::t('app', "Đổi gói thất bại, bạn cần hủy gói trước khi đổi gói"),
            );
        }

    }

    /**
     * @param $purchaseService Service
     * @param int $channel_type
     * @param int $transaction_type
     * @param bool $sendSMS
     * @param int $smsPrice
     * @return array
     * @throws \Exception
     */
    public
    function purchaseServicePackage(
        $channel_type = SubscriberTransaction::CHANNEL_TYPE_SMS,
        $purchaseService,
        $transaction_type = SubscriberTransaction::TYPE_REGISTER,
        $sendSMS = false,
        $smsPrice = 0,
        $number_month
    )
    {

        $groupAsms = $purchaseService->serviceGroupAsms;
        $activeGroup = false;
        foreach ($groupAsms as $groupAsm) {
            if ($groupAsm->serviceGroup->status == ServiceGroup::STATUS_ACTIVE) {
                $activeGroup = true;
                break;
            }
        }
        if (!$activeGroup) {
            return array(
                "error" => CommonConst::API_ERROR_INVALID_SERVICE_PACKAGE,
                "message" => Yii::t('app', "Nhóm gói cước đã tạm dừng")
            );
        }

        $service_package_id = $purchaseService->id;
        $currentPackageAsms = SubscriberServiceAsm::find()
            ->andWhere(['subscriber_id' => $this->id])
            ->andWhere(['status' => SubscriberServiceAsm::STATUS_ACTIVE])
            ->andWhere(['>=', 'expired_at', time()])
            ->all();

        if ($transaction_type == SubscriberTransaction::TYPE_REGISTER && $currentPackageAsms) {

            foreach ($currentPackageAsms as $packageAsm) {
                // kiem tra trang thai cua cac goi da mua
                if ($packageAsm->status != SubscriberServiceAsm::STATUS_ACTIVE) {
                    continue;
                }
                /** @var  $service Service */
                $service = $packageAsm->service;

                /**
                 * Kiem tra co trung voi goi cuoc da mua hay ko
                 */
                if ($packageAsm->service_id == $service_package_id) {
                    // goi cuoc muon mua da duoc dang ky truoc do

//                    $message = ResMessage::registerFailByDuplicate($this, $service, $sendSMS);
                    $message = Yii::t('app', 'Thuê bao hiện đang sử dụng gói cước vui lòng chọn gia hạn gói cước');

                    return array(
                        "error" => CommonConst::API_ERROR_SERVICE_PACKAGE_ALREADY_PURCHASED,
                        "message" => $message,
                    );

                }

                /**
                 * Kiem tra goi cuoc mua co trung voi goi cuoc trong cung group hay ko (group: vtv -> goi ngay,goi tuan,goi thang)
                 * Trong mot group thi chi dc mua 1 goi cuoc trong group do
                 */
                $groups1 = $service->serviceGroupAsms;
                $groups2 = $purchaseService->serviceGroupAsms;

                foreach ($groups1 as $group1) {
                    /** @var $group1 ServiceGroupAsm */
                    foreach ($groups2 as $group2) {
                        /** @var $group2 ServiceGroupAsm */

                        if ($group1->service_group_id == $group2->service_group_id) {
                            /** Truong hop dang ky goi moi cung nhom voi goi cuoc dang dang ky
                             * Huy goi cuoc cu thay bang goi cuoc moi
                             */
//                            $cancelServicePackages[] = $service;
                            // TuanPV bổ xung nếu cùng gói cước thì công thêm chu kì gói mới vào gói cũ thay đổi logic 11/12/2017
                            return $this->addServiceToSubscriber($transaction_type, $channel_type, $smsPrice, $service_package_id, $purchaseService, $service, $number_month);
                        }
                    }
                }
            }
        }
        return $this->addServiceToSubscriber($transaction_type, $channel_type, $smsPrice, $service_package_id, $purchaseService, false, $number_month);
    }

    /**
     * @param $content Content
     * @param int $channel_type
     * @param int $transaction_type
     * @param Site $sp
     * @return array
     * @throws \Exception
     */
    public function purchaseContent(
        $sp,
        $content,
        $channel_type = SubscriberTransaction::CHANNEL_TYPE_SMS,
        $transaction_type = SubscriberTransaction::TYPE_CONTENT_PURCHASE
    )
    {
        $currentPackageAsms = SubscriberServiceAsm::find()->andWhere(["subscriber_id" => $this->id, "status" => SubscriberServiceAsm::STATUS_ACTIVE])
            ->andWhere("expired_at > :p_expired_at", [":p_expired_at" => time()])->all();
        $catIds = [];
        $contentCatAsms = $content->parent ? $content->parent->contentCategoryAsms : $content->contentCategoryAsms;
        foreach ($contentCatAsms as $catAsm) {
            $catIds[] = $catAsm->category_id;
        }

        /* @var $packageAsm SubscriberServiceAsm */
        foreach ($currentPackageAsms as $packageAsm) {
            $cats = $packageAsm->service->serviceCategoryAsms;
            foreach ($cats as $catPurchased) {
                if (in_array($catPurchased->category_id, $catIds)) {
                    return array(
                        "error" => CommonConst::API_ERROR_SERVICE_PACKAGE_ALREADY_PURCHASED,
                        "message_web" => \Yii::t('app', 'Nội dung thuộc gói cước bạn đã mua'),
                        "message" => \Yii::t('app', 'Nội dung thuộc gói cước bạn đã mua'),
                    );
                }
            }
        }
        foreach ($this->subscriberContentAsms as $contentAsm) {
            // cap nhat trang thai neu ban ghi het han
            if ($contentAsm->expired_at < time() && $contentAsm->status == SubscriberContentAsm::STATUS_ACTIVE) {
                $contentAsm->status = SubscriberContentAsm::STATUS_INACTIVE;
                $contentAsm->save(false);
                continue;
            }
            if ($content->id == $contentAsm->content_id && $contentAsm->status == SubscriberContentAsm::STATUS_ACTIVE && $contentAsm->purchase_type == SubscriberContentAsm::TYPE_PURCHASE) {
                return array(
                    "error" => CommonConst::API_ERROR_CONTENT_ALREADY_PURCHASED,
                    "message_web" => \Yii::t('app', 'Bạn đã mua nội dung này'),
                    "message" => \Yii::t('app', 'Bạn đã mua nội dung này'),
                );
            }
        }

        // tim thay goi cuoc can mua them h
        if ($content->getIsFree($sp->id)) {
            // goi cuoc ko app dung gia han thoi gian
            return array(
                "error" => CommonConst::API_ERROR_NOT_FOR_SALE,
                "message_web" => \Yii::t('app', 'Nội dung miễn phí hoặc không được phép mua lẻ'),
                "message" => \Yii::t('app', 'Nội dung miễn phí hoặc không được phép mua lẻ'),
            );
        }

        $tranDesc = "Mua lẻ nội dung: " . $content->display_name;

        /** @var SubscriberTransaction $tr */
        $tr = $this->newTransaction($transaction_type, $channel_type, $tranDesc, null, $content);

        // tien hanh charging
        $chargingSuccess = false;

        $price = $channel_type == SubscriberTransaction::CHANNEL_TYPE_SMS ? round($content->getPriceSms($sp->id)) : round($content->getPriceCoin($sp->id));
        $newBalance = $this->balance;
        $oldBalance = $this->balance;
        if ($this->expired_at && $this->expired_at < time()) {
            return array(
                "error" => CommonConst::API_ERROR_DEVICE_EXPIRED,
                "message_web" => \Yii::t('app', 'Thiết bị đã hết hạn sử dụng'),
                "message" => \Yii::t('app', 'Thiet bi da het han su dung'),
            );
        } else if ($channel_type != SubscriberTransaction::CHANNEL_TYPE_SMS) {
            if ($this->balance >= $price) {
                $newBalance = $this->balance - $price;
                $this->balance = $newBalance;
                if ($this->save(true, ['balance'])) {
                    $chargingSuccess = true;
                }
            }
        } else {
            $chargingSuccess = true;
        }

        // them transaction
        $tr->status = $chargingSuccess ? SubscriberTransaction::STATUS_SUCCESS : SubscriberTransaction::STATUS_FAIL; //lay trang thai cua viec cap nhat trang thai inactive o tren
        $tr->cost = $price;
        $tr->cp_id = $content->cp_id . '';
        $tr->balance = $chargingSuccess ? $newBalance : $oldBalance;
        $tr->balance_before_charge = $oldBalance;


        if ($chargingSuccess) {
            $sca = new SubscriberContentAsm();
            $sca->site_id = $sp->id;
            $sca->content_id = $content->id;
            $sca->subscriber_id = $this->id;
            $activated_at = time();
            $expired_at = $activated_at + $content->getWatchingPriod($sp->id) * 3600;
            $sca->activated_at = time();
            $sca->expired_at = $expired_at;
            $sca->status = SubscriberContentAsm::STATUS_ACTIVE;
            $sca->msisdn = $this->msisdn;
            $sca->purchase_type = SubscriberContentAsm::TYPE_PURCHASE;
            if (!$sca->save()) {
                CUtils::log($sca->getErrors());
                return array(
                    "error" => CommonConst::API_ERROR_SYSTEM_ERROR,
                    "message_web" => \Yii::t('app', 'Lỗi hệ thống'),
                    "message" => \Yii::t('app', 'Loi he thong'),
                );
            }
            $tr->expired_time = $expired_at;
            if ($tr->update()) {
                Yii::trace("ERROR: cannot save transaction: " . Json::encode($tr));
            }
            return array(
                "error" => CommonConst::API_ERROR_NO_ERROR,
                "message_web" => \Yii::t('app', 'Mua nội dung thành công'),
                "message" => \Yii::t('app', 'Mua noi dung thanh cong'),
            );

        } else {
            if (!$tr->update()) {
                Yii::trace("ERROR: cannot save transaction: " . Json::encode($tr));
            }
            return array(
                "error" => CommonConst::API_ERROR_CHARGING_FAIL,
                "message_web" => \Yii::t('app', 'Tài khoản không đủ tiền, quý khách vui lòng nạp thêm'),
                "message" => \Yii::t('app', 'Tài khoản không đủ tiền, quý khách vui lòng nạp thêm'),
            );
        }
    }

    /**
     * @param $content_id
     * @param $subscriber Subscriber
     * @param null $subscriber2_id
     * @param $expired_at
     */
    public
    function createSubscriberContentAsm($content_id, $subscriber, $subscriber2_id = null, $expired_at, $purchase_type = null, $price)
    {
        $searchModel = new SubscriberContentAsm();
        $searchModel->content_id = $content_id;
        $searchModel->subscriber_id = $subscriber->id;
//        $searchModel->site_id = $subscriber->site_id;
        $searchModel->msisdn = $subscriber->msisdn;
        if ($purchase_type) {
            $searchModel->purchase_type = $purchase_type;
        }
        if ($subscriber2_id) {
            $searchModel->subscriber2_id = $subscriber2_id;
        }
        $searchModel->activated_at = time();
        $searchModel->expired_at = time() + ($expired_at * 86400);
        $searchModel->created_at = time();
        $searchModel->status = SubscriberContentAsm::STATUS_ACTIVE;
        $searchModel->save();
        if (!$searchModel->validate() || !$searchModel->save()) {
            $message = $searchModel->getFirstErrors();
            foreach ($message as $error) {
                $firstError = $error;
                break;
            }
            return [
                'status' => false,
                'message' => $firstError,
            ];
        }

        $subscriber->balance = $subscriber->balance - $price;
        if (!$subscriber->update()) {
            throw new InvalidCallException("FAILED");
        }
        $res['status'] = true;
        $res['message'] = Message::getSuccessMessage();
        return $res;
    }

    /**
     * @param $id
     * @param $site_id
     * @throws BadRequestHttpException
     * @throws InternalErrorException
     * @throws ServerErrorHttpException
     * @throws \yii\db\Exception
     */
    public
    function favorite($id, $site_id)
    {
        $subscriber_favorite = SubscriberFavorite::findOne([
            'subscriber_id' => $this->id,
            'content_id' => $id,
            'site_id' => $site_id,
        ]);

        if (!$subscriber_favorite) {
            /* @var Content $content */
            $content = Content::findOne(['id' => $id, 'site_id' => $site_id]);
            $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try {
                if ($content) {
                    $subscriber_favorite = new SubscriberFavorite();
                    $subscriber_favorite->content_id = $id;
                    $subscriber_favorite->subscriber_id = $this->id;
                    $subscriber_favorite->site_id = $site_id;
                    $subscriber_favorite->created_at = time();
                    $subscriber_favorite->updated_at = time();
                    if ($subscriber_favorite->save()) {
                        $content->favorite_count++;
                        if ($content->save()) {
                            $transaction->commit();
                            return Message::getFavoriteSuccessMessage();
                        }
                    }
                } else {
                    throw new InternalErrorException(Message::getActionFailMessage());
                }
            } catch (Exception $e) {
                $transaction->rollback();
                throw new ServerErrorHttpException(Message::getActionFailMessage());
            }
        } else {
            throw new BadRequestHttpException(Message::getFavoriteExitsMessage());
        }
    }

    public
    function unfavorite($id, $site_id)
    {
        $subscriber_favorite = SubscriberFavorite::findOne([
            'subscriber_id' => $this->id,
            'content_id' => $id,
            'site_id' => $site_id,
        ]);

        if ($subscriber_favorite) {
            /* @var Content $content */
            $content = Content::findOne(['id' => $id, 'site_id' => $site_id]);
            $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try {
                if ($content) {
                    if ($subscriber_favorite->delete()) {
                        $content->favorite_count--;
                        if ($content->save()) {
                            $transaction->commit();
                            return Message::getUnFavoriteSuccessMessage();
                        }
                    }
                } else {
                    throw new InternalErrorException(Message::getActionFailMessage());
                }
            } catch (Exception $e) {
                $transaction->rollback();
                throw new ServerErrorHttpException(Message::getActionFailMessage());
            }
        } else {
            throw new BadRequestHttpException(Message::getUnFavoriteExitsMessage());
        }
    }

    public
    function favorites($site_id)
    {
        $query = \api\models\SubscriberFavorite::find()
            ->andWhere(['site_id' => $site_id])
            ->andWhere(['subscriber_id' => $this->id]);

        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'updated_at' => SORT_DESC,
                ],
            ],
            'pagination' => [
                'defaultPageSize' => 10,
            ],
        ]);
        return $provider;
    }

    public
    function comment($title, $content_comment, $content_id, $site_id)
    {
        if ($content_comment == "") {
            throw new BadRequestHttpException(Message::getNoCommentMessage());
        }
        /* @var Content $content */
        $content = Content::findOne(['id' => $content_id]);
        if ($content) {
            $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try {
                $comment = new \common\models\SubscriberFeedback();
                $comment->content = $content_comment;
                $comment->title = $title;
                $comment->create_date = time();
                $comment->subscriber_id = $this->id;
                $comment->site_id = $site_id;
                $comment->content_id = $content_id;
                $comment->status = SubscriberFeedback::STATUS_ACTIVE;
                if ($comment->save()) {
                    $content->comment_count++;
                    if ($content->save()) {
                        $transaction->commit();
                        return Message::getSuccessMessage();
                    }
                }
            } catch (Exception $e) {
                $transaction->rollback();
                throw new ServerErrorHttpException(Message::getActionFailMessage());
            }

        } else {
            throw new InternalErrorException(Message::getActionFailMessage());
        }

    }

    public
    function comments($site_id, $content_id)
    {
        $query = \api\models\SubscriberFeedback::find()
            ->andWhere(['site_id' => $site_id])
            ->andWhere(['content_id' => $content_id]);

        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'create_date' => SORT_DESC,
                ],
            ],
            'pagination' => [
                'defaultPageSize' => 10,
            ],
        ]);
        return $provider;
    }

    public
    function checkMyApp($content_id)
    {
        /** @var  $content Content */
        $content = Content::findOne(["id" => $content_id, "status" => Content::STATUS_ACTIVE]);
        if ($content->type == 2) {
            $content->price = 10;
        }

//        $currentPackageAsms = $this->subscriberServiceAsms;
        $currentPackageAsms = SubscriberServiceAsm::find()->andWhere(["subscriber_id" => $this->id, "status" => SubscriberServiceAsm::STATUS_ACTIVE])->all();
        $catIds = [];
//        $contentCatAsm = $content->parent ? $content->parent->contentCategoryAsms : $content->contentCategoryAsms;
        $contentCatAsm = $content->contentCategoryAsms;

        foreach ($contentCatAsm as $catAsm) {
            $catIds[] = $catAsm->category_id;
        }

        /* @var $packageAsm SubscriberServiceAsm */
        foreach ($currentPackageAsms as $packageAsm) {
            $cats = $packageAsm->service->serviceCategoryAsms;
            Yii::info($packageAsm->service->name);
            foreach ($cats as $catPurchased) {
                if (in_array($catPurchased->category_id, $catIds)) {
                    return true;
                }
            }
        }
        foreach ($this->subscriberContentAsms as $contentAsm) {
            // cap nhat trang thai neu ban ghi het han
            if ($contentAsm->expired_at < time() && $contentAsm->status == SubscriberContentAsm::STATUS_ACTIVE) {
                $contentAsm->status = SubscriberContentAsm::STATUS_INACTIVE;
                $contentAsm->save(false);
                continue;
            }
            if ($content->id == $contentAsm->content_id && $contentAsm->status == SubscriberContentAsm::STATUS_ACTIVE && $contentAsm->purchase_type == SubscriberContentAsm::TYPE_PURCHASE) {
                return true;
            }
        }

        if ($content->is_free || !$content->price) {
            // goi cuoc ko app dung gia han thoi gian
            return true;
        }

        return false;

    }

    public
    static function getMsisdn()
    {
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } elseif (function_exists('http_get_request_headers')) {
            $headers = http_get_request_headers();
        } else {
            foreach ($_SERVER as $name => $value) {
                if (strncmp($name, 'HTTP_', 5) === 0) {
                    $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $headers[$name] = $value;
                }
            }
        }
        $lcHeaders = [];
        foreach ($headers as $name => $value) {
            $lcHeaders[strtolower($name)] = $value;
        }

        $headers = $lcHeaders;
        $clientIp = $_SERVER['REMOTE_ADDR'];
        $msisdn = isset($headers['msisdn']) ? $headers['msisdn'] : "";
        $xIpAddress = isset($headers['x-ipaddress']) ? $headers['x-ipaddress'] : "";
        $xForwardedFor = isset($headers['x-forwarded-for']) ? $headers['x-forwarded-for'] : "";
        $userIp = isset($headers['user-ip']) ? $headers['user-ip'] : "";
        $xWapMsisdn = isset($headers['x-wap-msisdn']) ? $headers['x-wap-msisdn'] : "";

//        $clientIp = "113.186.0.123";
        /*if ($ip_validation) {
        $valid = preg_match('/10\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $clientIp);
        $valid |= preg_match('/113\.185\.\d{1,3}\.\d{1,3}/', $clientIp);
        $valid |= preg_match('/172\.16\.30\.\d{1,3}/', $clientIp);
        if (!$valid) {
        echo "IP invalid";
        return "";
        }
        else {
        echo "IP valid";
        }
        }*/

        if ($msisdn) {
            return $msisdn;
        }

        if ($xWapMsisdn) {
            return $xWapMsisdn;
        }

        return "";
    }

    public
    static function getSubscriberInfo($msisdn)
    {

        $arr = array();
        $i = 0;
        try {
            if (empty($msisdn) || $msisdn == '' || !is_integer($msisdn)) {
                return ['message' => \Yii::t('app', 'Khong ton tai nguoi dung')];
            } else {
                $query = Subscriber::find()
                    ->select('*')
                    ->from('subscriber')
                    ->andWhere(['msisdn' => $msisdn])
                    ->asArray()
                    ->all();
                foreach ($query as $val) {
                    return $val;
                }
            }

        } catch (\yii\db\Exception $ex) {
            return false;
        }
    }

    public
    function convertSubscriberErrorCode($error_code, $service, $sms = false)
    {
        if ($error_code == VasProvisioning::ERROR_NONE
            || $error_code == VasProvisioning::FREE_REGISTER_SUCCESS
            || $error_code == VasProvisioning::ERROR_ALREADY_REGISTER
            || $error_code == VasProvisioning::PRICE_REGISTER_SUCCESS
        ) {
            /** @var SubscriberServiceAsm $user_package_asm */
            $user_package_asm = SubscriberServiceAsm::findOne(['subscriber_id' => $this->id, 'service_id' => $service->id]);
            if ($user_package_asm) {
                return array(
                    "success" => true,
                    "message" => ResMessage::registerSuccess($this, $service, date('d-m-Y', $user_package_asm->expired_at), $sms),
                );
            } else {
                return array(
                    "success" => false,
                    "message" => ResMessage::registerFailBySystemError($this, $service, $sms),
                );
            }
        }

        if ($error_code == VasProvisioning::ERROR_NOT_ENOUGH_MONEY) {
            return array(
                "success" => false,
                "message" => ResMessage::registerFailByMoney($this, $service, $sms),
            );
        }

        return array(
            "success" => false,
            "message" => ResMessage::registerFailBySystemError($this, $service, $sms),
        );
    }

    public
    function getListTransactions($from, $to, $page_size = 10, $page_index = 1)
    {
        $offset = ($page_index - 1) * $page_size;
        if ($offset < 0) {
            $offset = 0;
        }
        $total_pages = 0;
        $total = SubscriberTransaction::find()->andWhere(['>', 'created_at', $from])
            ->andWhere(['<', 'created_at', $to])
            ->andWhere(['subscriber_id' => $this->id, 'site_id' => $this->site_id, 'status' => SubscriberTransaction::STATUS_SUCCESS])
            ->andWhere(['is not', 'service_id', null])
            ->all();
        $total_pages = intval(count($total) / $page_size);
        $transactions = SubscriberTransaction::find()->andWhere(['>', 'created_at', $from])
            ->andWhere(['<', 'created_at', $to])
            ->andWhere(['subscriber_id' => $this->id, 'site_id' => $this->site_id, 'status' => SubscriberTransaction::STATUS_SUCCESS])
            ->andWhere(['is not', 'service_id', null])
            ->orderBy('id desc')
            ->limit($page_size)
            ->offset($offset)->all();
        return [
            'total_pages' => $total_pages,
            'transactions' => $transactions,
        ];
    }

    public
    function cancel()
    {
        foreach ($this->subscriberServiceAsms as $mapping) {
            $mapping->status = SubscriberServiceAsm::STATUS_INACTIVE;
            $mapping->update();
        }
        $this->status = self::STATUS_INACTIVE;

        return $this->update();
    }

    /**
     * Get list active package
     * @param int $package_id
     * @return SubscriberServiceAsm[]|null
     */
    public
    function getActiveServiceAsms($package_id = 0)
    {
        /**
         * @var $service_asms SubscriberServiceAsm[]
         */
        $query = SubscriberServiceAsm::find()->andWhere([
            'subscriber_id' => $this->id,
            'status' => SubscriberServiceAsm::STATUS_ACTIVE,
        ]);
        if ($package_id > 0) {
            $query->andWhere(['service_id' => $package_id]);
        }
        return $query->orderBy(['updated_at' => SORT_DESC])->all();
    }

    /**
     * @param $subscriber_id
     * @param $content_id
     * @return bool
     */
    public
    static function validatePurchasing($subscriber_id, $content_id)
    {
        /** Check xem subscriber đã mua nội dung lẻ này chưa */
        $subscriberContentAsm = SubscriberContentAsm::findOne([
            'subscriber_id' => $subscriber_id,
            'content_id' => $content_id,
            'status' => SubscriberContentAsm::STATUS_ACTIVE,
        ]);
        if ($subscriberContentAsm && $subscriberContentAsm->expired_at >= time()) {
            return true;
        }

        /**Check xem người dùng đã mua gói cước map với nội dung này chưa */
        /** Lấy tất cả gói cước người dùng đã mua */
        $subscriberServiceAsms = SubscriberServiceAsm::findAll(['subscriber_id' => $subscriber_id, 'status' => SubscriberServiceAsm::STATUS_ACTIVE]);
        foreach ($subscriberServiceAsms as $subscriberServiceAsm) {
            /** Nếu gói cước đã hết hạn thì bỏ qua vòng lặp hiện tại*/
            if ($subscriberServiceAsm->expired_at < time()) {
                continue;
            }
            /** Kiểm tra gói cước có được gán nội dung lẻ hay không */
            $contentServiceAsm = ContentServiceAsm::findOne([
                'service_id' => $subscriberServiceAsm->service_id,
                'content_id' => $content_id,
                'status' => ContentServiceAsm::STATUS_ACTIVE
            ]);
            if ($contentServiceAsm) {
                return true;
            }


            /** Kiểm tra xem gói cước người dùng đã mua gắn với category nào */
            $serviceCategoryAsms = ServiceCategoryAsm::findAll(['service_id' => $subscriberServiceAsm->service_id]);
            /** Nếu không gắn với gói cước nào thì bỏ qua vòng lặp hiện tại */
            if (!$serviceCategoryAsms) {
                continue;
            }

            /** Kiểm tra xem category có gắn với nội dung đang xem không*/
            foreach ($serviceCategoryAsms as $serviceCategoryAsm) {
                $contentCategoryAsm = ContentCategoryAsm::findOne(['category_id' => $serviceCategoryAsm->category_id, 'content_id' => $content_id]);
                if ($contentCategoryAsm) {
                    return true;
                }
            }

        }
        return false;

    }

    /**
     * @param $token
     * @return null|static
     */
    public
    static function findCredentialByToken($token)
    {
        return self::findOne(['token' => $token, 'status' => static::STATUS_ACTIVE]);
    }

    /**
     * @param $action
     * @param $channelType
     * @param $description
     * @param null $service Service
     * @param null $content Content
     * @param $status
     * @param int $cost
     * @param string $telco_code
     * @param Site $service_provider
     *
     * @return SubscriberTransaction
     */
    public
    function newActivity(
        $action,
        $channelType,
        $description,
        $status = Sub::STATUS_FAIL,
        $service_provider = null
    )
    {
        $tr = new SubscriberActivity();
        $tr->subscriber_id = $this->id;
        $tr->site_id = $this->site_id;
        $tr->msisdn = $this->msisdn;
        $tr->action = $action;
        $tr->channel = $channelType;
        $tr->description = $description;

        if ($service_provider) {
            $tr->site_id = $service_provider->id;
        }
        $tr->created_at = time();
        $tr->status = $status;
        $tr->created_at = time();
        $tr->save(false);
        return $tr;
    }

    public
    static function chargeCoin($username, $amount, $currency = 'VND', $balance, $channel_type, $msisdn, $site_id, $send_sms = false, $mo = null, $serviceNumber = null)
    {
        if (!$site_id) {
            $subscriber = Subscriber::findOne(['username' => $username, 'status' => Subscriber::STATUS_ACTIVE]);
        } else {
            $subscriber = Subscriber::findOne(['username' => $username, 'status' => Subscriber::STATUS_ACTIVE, 'site_id' => $site_id]);
        }
        if (!$subscriber) {
            return array(
                "success" => false,
                "error" => CommonConst::API_ERROR_INVALID_USERNAME,
                "message" => ResMessage::chargeCoinFailByInvalidUsername($msisdn, $site_id, $send_sms, $serviceNumber),
            );
        }
        if ($mo) {
            $mo->subscriber_id = $subscriber->id;
            $mo->update();
        }
        $site = $subscriber->site;
        $description = \Yii::t('app', 'Nạp coin');
        $subscriber->newTransaction(SubscriberTransaction::TYPE_CHARGE_COIN, $channel_type, $description, null, null, SubscriberTransaction::STATUS_SUCCESS, $amount, $currency, $balance, $site);
        $subscriber->balance = $subscriber->balance + $balance;
        if (!$subscriber->update(true, ['balance'])) {
            Yii::error($subscriber->errors);
            return array(
                "success" => true,
                "error" => CommonConst::API_ERROR_SYSTEM_ERROR,
                "message" => ResMessage::chargeCoinFailBySystemError($subscriber, $send_sms, $serviceNumber),
            );
        }
        return array(
            "success" => true,
            "error" => CommonConst::API_ERROR_NO_ERROR,
            "message" => ResMessage::chargeCoinSuccess($subscriber, $amount, $send_sms, $serviceNumber),
        );
    }

    public
    function checkMyService($service_id)
    {
        $list_my_service = [];
        $listService = SubscriberServiceAsm::find()
            ->andWhere(['subscriber_id' => $this->id])
            ->andWhere(['status' => SubscriberServiceAsm::STATUS_ACTIVE])
            ->andWhere(['>=', 'expired_at', time()])->all();
        foreach ($listService as $row) {
            $list_my_service[] = $row->service_id;
        }

        $is_my_package = in_array($service_id, $list_my_service);
        return $is_my_package;
    }

    public
    static function sendMail($to_mail, $subject, $content, $file_attach)
    {
        $mailer = Yii::$app->mailer;
        $mail = $mailer->compose()
            ->setFrom($mailer->transport->getUsername())
            ->setTo($to_mail)
            ->setSubject($subject)
            ->setHtmlBody($content);
        if (!empty($file_attach)) {
            $mail->attach($file_attach);
        }
        return $mail->send();

    }

    /**
     * @return int
     */
    public static function getChannelName($channel)
    {
        $lst = self::listChannelType();
        if (array_key_exists($channel, $lst)) {
            return $lst[$channel];
        }
        return $channel;
    }


    public
    static function getErrorCode($error_code)
    {
        switch ($error_code) {
            case self::VOUCHER_ACTIVE:
                return Yii::t('app', 'Nạp thẻ thành công');
            case self::VOUCHER_EXPIRE:
                return Yii::t('app', 'Thẻ nạp hết hạn');
            case self::VOUCHER_NOT_ACTIVE:
                return Yii::t('app', 'Thẻ nạp chưa được kích hoạt');
            case self::VOUCHER_NOT_HAVE:
                return Yii::t('app', 'Mã thẻ không hợp lệ, vui lòng nhập lại');
            case self::VOUCHER_USED:
                return Yii::t('app', 'Mã thẻ đã được sử dụng, Vui lòng kiểm tra lại!');
            case self::VOUCHER_INACTIVE:
                return Yii::t('app', 'Thẻ nạp đang bị tạm dừng');
            case self::VOUCHER_NOT_FORMAT:
                return Yii::t('app', 'Mã thẻ không hợp lệ, vui lòng nhập lại');
            case self::VOUCHER_ERROR_WRONG_TIME:
                return Yii::t('app', 'Bạn đang bị khóa tính năng nạp thẻ trong 30 phút do nạp sai vượt quá số lượt quy định. Vui lòng thử lại sau!');
//            case self::VOUCHER_CHANGE_VOUCHER:
//                return Yii::t('app', 'Dữ liệu bị thay đổi khi truyền từ voucher sang');
//            case self::VOUCHER_CHANGE_CLIENT:
//                return Yii::t('app', 'Dữ liệu bị thay đổi khi từ client gửi lên');
//            case self::VOUCHER_NOT_USER:
//                return Yii::t('app', 'Không tồn tại người dùng');
//            case self::VOUCHER_INVALID_LOGIN:
//                return Yii::t('app', 'Chưa đăng nhập vào hệ thống charging');
//            case self::VOUCHER_INVALID_SESSION:
//                return Yii::t('app', 'Không tồn SESSION');
//            case self::VOUCHER_PARTNED_LOCKED:
//                return Yii::t('app', 'Tài khoản partner bị khóa');
//            case self::VOUCHER_INVALID_PARTNED:
//                return Yii::t('app', 'Không tồn tại partner');
//            case self::VOUCHER_SYSTEM_ERROR:
//                return Yii::t('app', 'Lỗi hệ thống charging ');
//            case self::VOUCHER_CANNOT_LOGOUT:
//                return Yii::t('app', 'Không đăng xuất thành công charging ');
//            case self::VOUCHER_INVALID_USER:
//                return Yii::t('app', 'Không tồn tại tài khoản');
//            case self::VOUCHER_INVALID_MPIN:
//                return Yii::t('app', 'Không tồn tại mpin');
//            case self::VOUCHER_INVALID_REQUEST:
//                return Yii::t('app', 'Request không hợp lệ');
//            case self::VOUCHER_INVALID_PASS:
//                return Yii::t('app', 'Password không đúng');
//            case self::VOUCHER_INVALID_ENCRYPT:
//                return Yii::t('app', 'Lỗi khi mã hóa/giải mã');
//            case self::VOUCHER_INVALID_SINGNATURE:
//                return Yii::t('app', 'Lỗi chữ ký dữ liệu');
        }
        return Yii::t('app', 'Nạp thẻ không thành công! vui lòng thử lại');
    }

    public
    static function getErrorCodePhone($error_code)
    {
        switch ($error_code) {
            case self::VOUCHER_PHONE_SUCCESS:
                return Yii::t('app', 'Nạp thẻ thành công');
            case self::VOUCHER_PHONE_EXPIRE:
                return Yii::t('app', 'Thẻ nạp hết hạn');
            case self::VOUCHER_PHONE_NOT_ACTIVE:
                return Yii::t('app', 'Thẻ nạp chưa được kích hoạt');
            case self::VOUCHER_PHONE_NOT_HAVE:
                return Yii::t('app', 'Thông tin thẻ không hợp lệ. Vui lòng kiểm tra lại!');
            case self::VOUCHER_PHONE_INCORRECT:
                return Yii::t('app', 'Thông tin thẻ không hợp lệ. Vui lòng kiểm tra lại!');
            case self::VOUCHER_PHONE_USED:
                return Yii::t('app', 'Mã thẻ đã được sử dụng, Vui lòng kiểm tra lại !');
            case self::VOUCHER_PHONE_USED_EPAY:
                return Yii::t('app', 'Mã thẻ đã được sử dụng, Vui lòng kiểm tra lại !');
            case self::VOUCHER_PHONE_BLOCK:
                return Yii::t('app', 'Thẻ đã bị khóa');
            case self::VOUCHER_PHONE_WRONG_10_TIME:
                return Yii::t('app', 'Bị khóa nạp thẻ sai nhiều lần');
        }
        return Yii::t('app', 'Nạp thẻ không thành công! vui lòng thử lại');
    }

    public
    static function GetDataResult($result)
    {
        $data = [];
        $chuoi = explode(',', $result);
        // giá trị thẻ nạp
        $value = explode(':"', $chuoi[3]);
        $value = explode('"}', $value[1]);
        $value = $value[0];
        $data[0] = $value;
        // mã giao dịch
        $tranID = explode('"', $chuoi[2]);
        $tranID = $tranID[3];
        $data[1] = $tranID;
        return $data;

    }

    public
    static function GetCodeResult($result)
    {
        $chuoi = explode(',', $result);
        $error_code = explode(':"', $chuoi[0]);
        $error_code = $error_code[1];
        return $error_code;

    }

    public
    static function AddSessionID($user_login, $pass)
    {
        $jsonLogin = "{function:login,username:" . $user_login . ",password:" . $pass . "}";
        $result_login = APIHelper::apiQuery('POST', Yii::$app->params['voucher_tvod_link'] . APIHelper::API_CHECK_VOUCHER, $jsonLogin);
        Yii::info($result_login);

        $sessionId = explode(',', $result_login);
        $sessionId = explode(':"', $sessionId[0]);
        $sessionId = str_replace('"', '', $sessionId[1]);
        self::writeLog($sessionId);
        return $sessionId;
    }

    public
    static function writeLog($id)
    {
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/sessionId.log'), $id);
    }

    public
    static function ReturnSessionId($log_link)
    {
        $myfile = fopen($log_link, "r") or die("Unable to open file!");
        $sessionId = fgets($myfile);
        fclose($myfile);
        $sessionId = rtrim($sessionId);
        return $sessionId;
    }

    public
    static function CheckUserVoucher($subscriber, $site_id, $nn = null)
    {
        $time_now = (new DateTime('now'))->format('Y-m-d H:i:s');
        $time_30i = (new DateTime('now'))->modify('-30 minutes')->format('Y-m-d H:i:s');
        $now_integer = strtotime($time_now);
        $integer_30i = strtotime($time_30i);
        if ($nn) {
            $check_user_rechage = SubscriberTransaction::find()
                ->andWhere(['subscriber_id' => $subscriber->id])
                ->andwhere('created_at >= :p_from', [':p_from' => $integer_30i])
                ->andWhere('created_at <= :p_to', [':p_to' => $now_integer])
                ->andWhere(['type' => SubscriberTransaction::TYPE_VOUCHER_PHONE])
                ->andWhere(['channel' => SubscriberTransaction::getChanelTypeVoucher($nn)])
                ->andWhere(['status' => SubscriberTransaction::STATUS_FAIL])
                ->andWhere(['site_id' => $site_id])
                ->all();
        } else {
            $check_user_rechage = SubscriberTransaction::find()
                ->andWhere(['subscriber_id' => $subscriber->id])
                ->andwhere('created_at >= :p_from', [':p_from' => $integer_30i])
                ->andWhere('created_at <= :p_to', [':p_to' => $now_integer])
                ->andWhere(['type' => SubscriberTransaction::TYPE_VOUCHER])
                ->andWhere(['channel' => SubscriberTransaction::CHANNEL_TYPE_VOUCHER])
                ->andWhere(['site_id' => $site_id])
                ->andWhere(['status' => SubscriberTransaction::STATUS_FAIL])
                ->all();
        }
        return $check_user_rechage;
    }

    public
    static function validateTopup($name_subscriber, $cost, $partner, $signature, $type = false)
    {
        if ($type == false) {
            if (empty($cost)) {
                return self::TOPUP_EMPTY_COST;
            }
            $bool = is_numeric($cost);
            if ($bool == false) {
                return self::TOPUP_COST_INCORECT;
            }
        }
        if (empty($name_subscriber)) {
            return self::TOPUP_EMPTY_USER;
        }
        if (empty($partner)) {
            return self::TOPUP_EMPTY_PARTNER;
        }
        if (empty($signature)) {
            return self::TOPUP_EMPTY_SINGNATURE;
        }

        $bool_partner = self::checkPartner($partner);
        if ($bool_partner == false) {
            return self::TOPUP_WRONG_PARTNER;
        }
        $list_partner = Yii::$app->params['partner'];
        $key = $list_partner[$partner]['key'];
        $md5 = md5($name_subscriber . $cost . $partner . $key);
        Yii::info('Ma md5 dung ' . $md5);
//        echo"<pre>";print_r($md5);die();
        if ($md5 != $signature) {
            return self::TOPUP_WRONG_SINGNATURE;
        }
        return self::TOPUP_SUCCESS;
    }

    public
    function checkPartner($partner)
    {
        $list_partner = Yii::$app->params['partner'];
        foreach ($list_partner as $key => $item) {
            if ($key == $partner) {
                return true;
            }
        }
        return false;
    }

    public
    static function getSubscriberTopup($name_subscriber, $cost)
    {
        $subscriber = Subscriber::findOne(['username' => $name_subscriber, 'status' => Subscriber::STATUS_ACTIVE]);
        if (!isset($subscriber)) {
            return ['status' => self::TOPUP_WRONG_USER];
        }
        $old_balance = $subscriber->balance;
        $subscriber->balance = $old_balance + $cost;
        if ($subscriber->save()) {
            $trans = $subscriber->newTransaction(SubscriberTransaction::TYPE_VOUCHER,
                SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PARTNER, Yii::t('app', 'Nạp tiền từ đại lý'),
                null, null, SubscriberTransaction::STATUS_SUCCESS, $cost, 'VND',
                $cost, self::TOPUP_SUCCESS, null, null, null, null, $old_balance);
            if (!$trans) {
                return ['status' => self::TOPUP_ERROR_SYSTEM];
            } else {
                return ['status' => self::TOPUP_SUCCESS, 'transaction_id' => $trans->id];
            }
        } else {
            Yii::error($subscriber->getErrors());
            return ['status' => self::TOPUP_ERROR_SYSTEM];
        }
    }

    public
    static function saveSubscriber($subscriber, $otp)
    {
        /** @var Subscriber $subscriber */
        $subscriber->otp_code = $otp;
        $subscriber->expired_code_time = time() + 3 * 60;
        $subscriber->number_otp = 2;
        $subscriber->save(false);
        return true;
    }

// hàm tạo mã hóa
    public
    static function phoneEncrypt($input, $key_seed)
    {
        $input = trim($input);
        $block = mcrypt_get_block_size('tripledes', 'ecb');
        $len = strlen($input);
        $padding = $block - ($len % $block);
        $input .= str_repeat(chr($padding), $padding);

        // generate a 24 byte key from the md5 of the seed
        $key = substr(md5($key_seed), 0, 24);
        $iv_size = mcrypt_get_iv_size(MCRYPT_TRIPLEDES, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        // encrypt
        $encrypted_data = mcrypt_encrypt(MCRYPT_TRIPLEDES, $key, $input,
            MCRYPT_MODE_ECB, $iv);
        // clean up output and return base64 encoded
        return base64_encode($encrypted_data);
    }

    public
    static function phoneDecrypt($input, $key_seed)
    {
        $input = base64_decode($input);
        $key = substr(md5($key_seed), 0, 24);
        $text = mcrypt_decrypt(MCRYPT_TRIPLEDES, $key, $input, MCRYPT_MODE_ECB, '12345678');

        $block = mcrypt_get_block_size('tripledes', 'ecb');
        $packing = ord($text{strlen($text) - 1});
        if ($packing and ($packing < $block)) {
            for ($P = strlen($text) - 1; $P >= strlen($text) - $packing; $P--) {
                if (ord($text{$P}) != $packing) {
                    $packing = 0;
                }
            }
        }
        $text = substr($text, 0, strlen($text) - $packing);
        return $text;
    }

    public static function callBackToVoucher($subscriber, $card_code, $card_serial, $site_id)
    {
        /** @var Subscriber $subscriber */
        $user_login = Yii::$app->params['user_voucher'];
        $pass = Yii::$app->params['pass_voucher'];
        $mpin = Yii::$app->params['mpin_voucher'];
        $log_link = Yii::getAlias('@runtime/logs/sessionId.log');
        if (file_exists($log_link)) {
            $sessionId = Subscriber::ReturnSessionId($log_link);
        } else {
            $sessionId = Subscriber::AddSessionID($user_login, $pass);
        }
        $key_seed = $sessionId;
        $signature = md5($user_login . $subscriber->username . $card_code . $card_serial . $key_seed . $mpin);
        $json = "{
                    function:cardcharge,
                    username:" . $user_login . ",
                    targetAccount:" . $subscriber->username . ",
                    tranRef:" . $key_seed . ",
                    cardCode:" . self::PhoneEncrypt($card_code, $key_seed) . ",
                    signature:" . $signature . "
                    }";
        $result = APIHelper::apiQuery('POST', Yii::$app->params['voucher_tvod_link'] . APIHelper::API_CHECK_VOUCHER, $json);
        Yii::info($result);
        if (isset($result)) {
            $error_code = Subscriber::GetCodeResult($result);
            if ($error_code == Subscriber::VOUCHER_ACTIVE) {
                $data_explore = Subscriber::GetDataResult($result);
                $price = $data_explore[0];
                $transaction_voucher_id = $data_explore[1];
                $mes = Subscriber::getErrorCode($error_code);
                $balance_tr = $subscriber->balance + $price;
                $stp1 = $subscriber->newTransaction(SubscriberTransaction::TYPE_VOUCHER,
                    SubscriberTransaction::CHANNEL_TYPE_VOUCHER, $mes, null, null,
                    SubscriberTransaction::STATUS_SUCCESS, $price, 'VND',
                    $balance_tr, $error_code, $card_code, $card_serial,
                    $transaction_voucher_id, '', $subscriber->balance);
                if (empty($stp1)) {
                    throw new InvalidValueException(Yii::t('app', 'Đã có lỗi xảy ra, vui lòng thử lại'));
                }
                // tăng tiền
                $subscriber_id = $subscriber->id;
                $title = self::getTitleMessageRecharge();
                $content = self::getContentMessageRecharge($subscriber->username, $stp1->getCostBefore(), $price, $stp1->balance);
                SmsSupport::addSmsSupportByContent($title, $content, $subscriber);
                Subscriber::changeBalance($subscriber_id, $price);
                shell_exec("nohup  ./find_campaign_recharge.sh $price $subscriber_id > /dev/null 2>&1 &");

                Yii::info(Subscriber::getErrorCode($error_code));
                throw new InvalidValueException(Subscriber::getErrorCode($error_code));
            } elseif ($error_code == Subscriber::VOUCHER_INVALID_SESSION || $error_code == Subscriber::VOUCHER_INVALID_REQUEST) {
                // viết hàm logout
                if (file_exists($log_link)) {
                    unlink($log_link);
                }
                self::callBackToVoucher($subscriber, $card_code, $card_serial, $site_id);
            } else {
                $mes = Subscriber::getErrorCode($error_code);
                $stp1 = $subscriber->newTransaction(SubscriberTransaction::TYPE_VOUCHER,
                    SubscriberTransaction::CHANNEL_TYPE_VOUCHER, $mes,
                    null, null, SubscriberTransaction::STATUS_FAIL,
                    0, 'VND', $subscriber->balance, $error_code, $card_code,
                    $card_serial, null, '', $subscriber->balance);
                if (empty($stp1)) {
                    throw new InvalidValueException(Yii::t('app', 'Đã có lỗi xảy ra, vui lòng thử lại'));
                }
                if (
                    $error_code == Subscriber::VOUCHER_INACTIVE ||
                    $error_code == Subscriber::VOUCHER_NOT_FORMAT ||
                    $error_code == Subscriber::VOUCHER_NOT_HAVE ||
                    $error_code == Subscriber::VOUCHER_USED ||
                    $error_code == Subscriber::VOUCHER_EXPIRE ||
                    $error_code == Subscriber::VOUCHER_NOT_ACTIVE
                ) {
                    throw new InvalidValueException(Subscriber::getErrorCode($error_code));
                } else {
                    throw new InvalidValueException(Yii::t('app', 'Nạp thẻ không thành công! vui lòng thử lại'));
                }
            }
        } else {
            $mes = Yii::t('app', 'Nạp thẻ không thành công! vui lòng thử lại');
            $stp1 = $subscriber->newTransaction(SubscriberTransaction::TYPE_VOUCHER,
                SubscriberTransaction::CHANNEL_TYPE_VOUCHER, $mes, null,
                null, SubscriberTransaction::STATUS_FAIL, 0, 'VND',
                $subscriber->balance, Subscriber::VOUCHER_NOT_RESULT, $card_code,
                $card_serial, null, '', $subscriber->balance);
            if (empty($stp1)) {
                throw new InvalidValueException(Yii::t('app', 'Đã có lỗi xảy ra, vui lòng thử lại'));
            }
            throw new InvalidValueException($mes);
        }
    }

    public static function phoneDecryptTP($c_code)
    {
        $st = substr($c_code, 1, -1);
        return $st;
    }


    public
    static function getDeviceSubscriber($subscriber, $device)
    {
        // tìm xem thiết bị được gán first login chưa mà ở status hoạt động
        $asm = SubscriberDeviceAsm::find()
            ->andWhere(['device_id' => $device->id])
            ->andWhere(['status' => SubscriberDeviceAsm::STATUS_ACTIVE])
//            ->andWhere(['first_login' => SubscriberDeviceAsm::IS_FIRST_LOGIN])
            ->one();
        /** @var $asm SubscriberDeviceAsm */
        if ($asm) {
            if ($asm->subscriber_id != $subscriber->id) {
                return false;
            } else {
                return true;
            }
        } else {
            // nếu chưa thì tìm thằng thuê bao đó được gán cho thiết bị nào chưa
            $check_sub = SubscriberDeviceAsm::find()
                ->andWhere(['subscriber_id' => $subscriber->id])
                ->andWhere(['status' => SubscriberDeviceAsm::STATUS_ACTIVE])
//                ->andWhere(['first_login' => SubscriberDeviceAsm::IS_FIRST_LOGIN])
                ->one();
            if ($check_sub) {
                Yii::info(' Da duoc gan cho mac khac');
                return false;
            }
            $asm = SubscriberDeviceAsm::find()
                ->andWhere(['device_id' => $device->id])
                ->andWhere(['subscriber_id' => $subscriber->id])
                ->one();
            if ($asm) {
                $asm->status = SubscriberDeviceAsm::STATUS_ACTIVE;
                if (!$asm->update()) {
                    Yii::info($asm->getErrors());
                    return false;
                }
                return true;
            } else {
                $insert_sda = new SubscriberDeviceAsm();
                $insert_sda->subscriber_id = $subscriber->id;
                $insert_sda->device_id = $device->id;
                $insert_sda->status = SubscriberDeviceAsm::STATUS_ACTIVE;
//                $insert_sda->first_login = SubscriberDeviceAsm::IS_FIRST_LOGIN;
                if (!$insert_sda->save()) {
                    Yii::info($asm->getErrors());
                    return false;
                }
                return true;
            }
        }
    }

    public
    static function changeBalance($subscriber_id, $coin)
    {
        $model = Subscriber::findOne($subscriber_id);
        $old_balance = $model->balance;
        $new_balance = (int)$coin + $old_balance;
        $model->balance = (int)$new_balance;
        if (!$model->update()) {
            Yii::info($model->getErrors());
        }
        return true;
    }

    public
    static function getClientTypeNameChannel($subscriber_id)
    {
        $channel = Subscriber::findOne($subscriber_id);
        if ($channel) {
            $lst = self::listClientType();
            if (array_key_exists($channel->channel, $lst)) {
                return $lst[$channel->channel];
            }
            return $channel->channel;
        } else {
            return '';
        }
    }

    public static function getExpiredAtWithSubscriberTransaction($subscriber_id, $service_id, $content_id, $subscriber_service_asm_id, $created_at)
    {
        if ($subscriber_service_asm_id) {
            $service = SubscriberServiceAsm::findOne($subscriber_service_asm_id);
            if ($service) {
                return date('d-m-Y H:i:s', $service->expired_at);
            }
        } else {
            if ($content_id) {
                $content = SubscriberContentAsm::findOne(['subscriber_id' => $subscriber_id, 'content_id' => $content_id, 'status' => SubscriberContentAsm::STATUS_ACTIVE]);
                if ($content) {
                    return date('d-m-Y H:i:s', $content->expired_at);
                }
            } else {
                $subscriber = SubscriberServiceAsm::findOne(['subscriber_id' => $subscriber_id, 'service_id' => $service_id, 'status' => SubscriberServiceAsm::STATUS_ACTIVE]);
                if ($subscriber) {
                    return date('d-m-Y H:i:s', $subscriber->expired_at);
                }
            }
        }
        return '';
    }

    public static function getMAC($subscriber_id)
    {
        $channel = Subscriber::findOne($subscriber_id);
        if ($channel) {
            return $channel->machine_name;
        } else {
            return '';
        }
    }


    public
    static function topupVoucher($subscriber, $c_code, $site_id, $signature, $card_serial)
    {
        /** @var $subscriber Subscriber */
        if (empty($c_code)) {
            throw new InvalidValueException(Yii::t('app', 'Mã thẻ không được để trống !'));
        }
        $key_seed_de = Yii::$app->params['key_seed_voucher'];
        $card_code = Subscriber::phoneDecryptTP($c_code);
        Yii::info('Card code da loc ' . $card_code);
        // kiểm tra trong vòng 30 phút
        $check_user_rechage = Subscriber::checkUserVoucher($subscriber, $site_id);
        $number_rechage = count($check_user_rechage);
        if ($number_rechage >= 5) {
            $mes = Subscriber::getErrorCode(Subscriber::VOUCHER_ERROR_WRONG_TIME);
            $stp1 = $subscriber->newTransaction(SubscriberTransaction::TYPE_VOUCHER, SubscriberTransaction::CHANNEL_TYPE_VOUCHER, $mes, null, null, SubscriberTransaction::STATUS_FAIL, 0, 'VND', $subscriber->balance, Subscriber::VOUCHER_ERROR_WRONG_TIME, $card_code, $card_serial, null, '', $subscriber->balance);
            if (empty($stp1)) {
                throw new InvalidValueException(Yii::t('app', 'Đã có lỗi xảy ra, vui lòng thử lại'));
            }
            throw new InvalidValueException(Yii::t('app', 'Bạn đang bị khóa tính năng nạp thẻ trong 30 phút do nạp sai vượt quá số lượt quy định. Vui lòng thử lại sau!'));
        }

        $boo = is_numeric($card_code);
        Yii::info('Kiem tra co chu trong chuoi ko ' . $boo);
        if ($boo == false) {
            $mes = Subscriber::getErrorCode(Subscriber::VOUCHER_NOT_FORMAT);
            $stp1 = $subscriber->newTransaction(SubscriberTransaction::TYPE_VOUCHER, SubscriberTransaction::CHANNEL_TYPE_VOUCHER, $mes, null, null, SubscriberTransaction::STATUS_FAIL, 0, 'VND', $subscriber->balance, Subscriber::VOUCHER_NOT_FORMAT, $card_code, $card_serial, null, '', $subscriber->balance);
            if (empty($stp1)) {
                throw new InvalidValueException(Yii::t('app', 'Đã có lỗi xảy ra, vui lòng thử lại'));
            }
            throw new InvalidValueException($mes);
        }
        $total_md5 = md5($key_seed_de . $card_code);
        Yii::info($total_md5);
        if ($total_md5 != $signature) {
            $mes = Yii::t('app', 'Nạp thẻ không thành công! vui lòng thử lại');
            $stp1 = $subscriber->newTransaction(SubscriberTransaction::TYPE_VOUCHER, SubscriberTransaction::CHANNEL_TYPE_VOUCHER, $mes, null, null, SubscriberTransaction::STATUS_FAIL, 0, 'VND', $subscriber->balance, Subscriber::VOUCHER_CHANGE_CLIENT, $card_code, $card_serial,null, '', $subscriber->balance);
            if (empty($stp1)) {
                throw new InvalidValueException(Yii::t('app', 'Đã có lỗi xảy ra, vui lòng thử lại'));
            }
            throw new InvalidValueException($mes);
        }
        $user_login = Yii::$app->params['user_voucher'];
        $pass = Yii::$app->params['pass_voucher'];
        $mpin = Yii::$app->params['mpin_voucher'];
        $log_link = Yii::getAlias('@runtime/logs/sessionId.log');
        if (file_exists($log_link)) {
            $sessionId = Subscriber::ReturnSessionId($log_link);
        } else {
            $sessionId = Subscriber::AddSessionID($user_login, $pass);
        }
        $username = $subscriber->username;
        $key_seed = $sessionId;
        $signature1 = md5($user_login . $username . $card_code . $card_serial . $key_seed . $mpin);
        $json = "{
                    function:cardcharge,
                    username:" . $user_login . ",
                    targetAccount:" . $username . ",
                    tranRef:" . $key_seed . ",
                    cardCode:" . Subscriber::phoneEncrypt($card_code, $key_seed) . ",
                    signature:" . $signature1 . "
                    }";
        $result = APIHelper::apiQuery('POST', Yii::$app->params['voucher_tvod_link'] . APIHelper::API_CHECK_VOUCHER, $json);
        Yii::info($result);
        if (isset($result)) {
            $error_code = Subscriber::GetCodeResult($result);
            if ($error_code == Subscriber::VOUCHER_ACTIVE) {
                $data_explore = Subscriber::GetDataResult($result);
                $price = $data_explore[0];
                $transaction_voucher_id = $data_explore[1];
                $mes = Subscriber::getErrorCode($error_code);
                $stp1 = $subscriber->newTransaction(SubscriberTransaction::TYPE_VOUCHER, SubscriberTransaction::CHANNEL_TYPE_VOUCHER, $mes, null, null, SubscriberTransaction::STATUS_SUCCESS, $price, 'VND', $subscriber->balance + $price, $error_code, $card_code, $card_serial, $transaction_voucher_id, '', $subscriber->balance);
                if (empty($stp1)) {
                    throw new InvalidValueException(Yii::t('app', 'Đã có lỗi xảy ra, vui lòng thử lại'));
                }
                // tăng tiền
                $subscriber_id = $subscriber->id;
                Subscriber::changeBalance($subscriber_id, $price);
                // Them vao hòm thư 11/12/2017
                $title = self::getTitleMessageRecharge();
                $content = self::getContentMessageRecharge($subscriber->username, $stp1->getCostBefore(), $price, $stp1->balance);
                SmsSupport::addSmsSupportByContent($title, $content, $subscriber);
                shell_exec("nohup  ./find_campaign_recharge.sh $price $subscriber_id > /dev/null 2>&1 &");
                throw new InvalidValueException(Subscriber::getErrorCode($error_code));
            } elseif ($error_code == Subscriber::VOUCHER_INVALID_SESSION || $error_code == Subscriber::VOUCHER_INVALID_REQUEST) {
                // viết hàm logout
                if (file_exists($log_link)) {
                    unlink($log_link);
                }
                Subscriber::callBackToVoucher($subscriber, $card_code, $card_serial, $site_id);

            } else {
                $mes = Subscriber::getErrorCode($error_code);
                $stp1 = $subscriber->newTransaction(SubscriberTransaction::TYPE_VOUCHER,
                    SubscriberTransaction::CHANNEL_TYPE_VOUCHER,
                    $mes, null, null,
                    SubscriberTransaction::STATUS_FAIL, 0, 'VND',
                    $subscriber->balance, $error_code, $card_code, $card_serial,
                    null, '', $subscriber->balance);
                if (empty($stp1)) {
                    throw new InvalidValueException(Yii::t('app', 'Đã có lỗi xảy ra, vui lòng thử lại'));
                }
                if (
                    $error_code == Subscriber::VOUCHER_INACTIVE ||
                    $error_code == Subscriber::VOUCHER_NOT_FORMAT ||
                    $error_code == Subscriber::VOUCHER_NOT_HAVE ||
                    $error_code == Subscriber::VOUCHER_USED ||
                    $error_code == Subscriber::VOUCHER_EXPIRE ||
                    $error_code == Subscriber::VOUCHER_NOT_ACTIVE
                ) {
                    throw new InvalidValueException(Subscriber::getErrorCode($error_code));
                } else {
                    throw new InvalidValueException(Yii::t('app', 'Nạp thẻ không thành công! vui lòng thử lại'));
                }
            }
        } else {
            $mes = Yii::t('app', 'Nạp thẻ không thành công! vui lòng thử lại');
            $stp1 = $subscriber->newTransaction(SubscriberTransaction::TYPE_VOUCHER,
                SubscriberTransaction::CHANNEL_TYPE_VOUCHER, $mes, null,
                null, SubscriberTransaction::STATUS_FAIL, 0, 'VND',
                $subscriber->balance, Subscriber::VOUCHER_NOT_RESULT,
                $card_code, $card_serial, null, '', $subscriber->balance);
            if (empty($stp1)) {
                throw new InvalidValueException(Yii::t('app', 'Đã có lỗi xảy ra, vui lòng thử lại'));
            }
            throw new InvalidValueException($mes);
        }
    }

    public
    static function checkMac($mac_address)
    {
        if (!$mac_address) {
            Subscriber::setStatusCode(201);
            return ['success' => false, 'message' => Yii::t('app', 'Địa chỉ MAC không được để trống!')];
        }
        $subscriber = Subscriber::findOne(['machine_name' => $mac_address, 'status' => Subscriber::STATUS_ACTIVE, 'authen_type' => Subscriber::AUTHEN_TYPE_ACCOUNT]);
        if (!$subscriber) {
            Subscriber::setStatusCode(201);
            return ['success' => false, 'message' => Yii::t('app', 'Địa chỉ MAC không đúng, quý khách vui lòng nhập lại !')];
        }
        return [
            'success' => true,
            'username' => $subscriber->username,
            'birthday' => $subscriber->birthday,
            'fullname' => $subscriber->full_name,
            'address' => $subscriber->address,
            'phone' => $subscriber->msisdn,
            'email' => $subscriber->email,
            'id' => $subscriber->id,
            'mac_address' => $subscriber->machine_name,
            'register_at' => $subscriber->created_at,
            'balance' => $subscriber->balance,
        ];
    }

    public
    static function topupVoucherPhone($subscriber, $c_serial, $c_code, $site_id, $operator, $signature)
    {
        if (empty($c_serial)) {
            throw new InvalidValueException(Yii::t('app', 'Serial không được để trống !'));
        }
        if (empty($c_code)) {
            throw new InvalidValueException(Yii::t('app', 'Mã thẻ không được để trống !'));
        }
        if (empty($operator)) {
            throw new InvalidValueException(Yii::t('app', 'Nhà mạng không được để trống ! '));
        }
        /** @var $subscriber Subscriber */
        $msisdn = $subscriber->msisdn;
        $username = $subscriber->username;
        $email = $subscriber->email;
        $key_seed = Yii::$app->params['key_seed_voucher_phone'];
        $card_serial = Subscriber::phoneDecryptTP($c_serial);
        $card_code = Subscriber::phoneDecryptTP($c_code);

        $boo = is_numeric($card_code);
        $boo1 = is_numeric($card_serial);
        Yii::info('Kiem tra co chu trong chuoi ko ' . $boo . 'serial ' . $boo1);
        $check_user_rechage = Subscriber::CheckUserVoucher($subscriber, $site_id, $operator);
        $number_rechage = count($check_user_rechage);
        if ($number_rechage >= 5) {
            $mes = Yii::t('app', 'Bạn đang bị khóa tính năng nạp thẻ trong 30 phút do nạp sai vượt quá số lượt quy định. Vui lòng thử lại sau!');
            $error_code = Subscriber::VOUCHER_ERROR_WRONG_TIME;
            $stp1 = $subscriber->newTransaction(SubscriberTransaction::TYPE_VOUCHER_PHONE,
                SubscriberTransaction::getChanelTypeVoucher($operator), $mes,
                null, null, SubscriberTransaction::STATUS_FAIL, 0, 'VND',
                $subscriber->balance, $error_code, $card_code, $card_serial, null, null, $subscriber->balance
            );
            if (empty($stp1)) {
                throw new InvalidValueException(Yii::t('app', 'Đã có lỗi xảy ra, vui lòng thử lại'));
            }
            throw new InvalidValueException($mes);
        }
        if ($boo == false || $boo1 == false) {
            $mes = Yii::t('app', 'Thông tin thẻ không hợp lệ. Vui lòng kiểm tra lại!');
            $error_code = Subscriber::VOUCHER_PHONE_NOT_HAVE;
            $stp1 = $subscriber->newTransaction(SubscriberTransaction::TYPE_VOUCHER_PHONE,
                SubscriberTransaction::getChanelTypeVoucher($operator), $mes,
                null, null, SubscriberTransaction::STATUS_FAIL, 0, 'VND',
                $subscriber->balance, $error_code, $card_code, $card_serial, null, null, $subscriber->balance);
            if (empty($stp1)) {
                throw new InvalidValueException(Yii::t('app', 'Đã có lỗi xảy ra, vui lòng thử lại'));
            }
            throw new InvalidValueException($mes);
        }
        $total_md5 = md5($card_serial . $card_code . $operator);
        Yii::info($total_md5);
        if (empty($msisdn)) {
            $msisdn = '0000000000';
        }
        if (empty($email)) {
            $email = 'null_email@admin.com';
        }
//        echo"<pre>";print_r($card_serial.' '.$card_code.' '.$total_md5);die();
        if ($total_md5 != $signature) {
            $mes = Yii::t('app', 'Nạp thẻ không thành công! vui lòng thử lại');
            $stp1 = $subscriber->newTransaction(SubscriberTransaction::TYPE_VOUCHER_PHONE,
                SubscriberTransaction::getChanelTypeVoucher($operator), $mes, null,
                null, SubscriberTransaction::STATUS_FAIL, 0, 'VND',
                $subscriber->balance, Subscriber::VOUCHER_CHANGE_CLIENT, $card_code,
                $card_serial, null, null, $subscriber->balance);
            if (empty($stp1)) {
                throw new InvalidValueException(Yii::t('app', 'Đã có lỗi xảy ra, vui lòng thử lại'));
            }
            throw new InvalidValueException($mes);
        }
        $cpid = Yii::$app->params['cpid_phone'];
        $op = $operator;
        $sn = Subscriber::phoneEncrypt($card_serial, $key_seed);
        $pin = Subscriber::phoneEncrypt($card_code, $key_seed);
        $userdata = $username . ';' . $msisdn . ';' . $email;
        $url = Yii::$app->params['voucher_phone_link'] . APIHelper::API_CHECK_VOUCHER_PHONE;
        $result = APIHelper::CallAPI('POST', $url,
            [
                'cpid' => $cpid,
                'op' => $op,
                'sn' => $sn,
                'pin' => $pin,
                'userdata' => $userdata,
            ]
        );
        Yii::info($result);
        if (isset($result) && !empty($result)) {
            $rs = explode(';', $result);
            $error_code = $rs[0];
            $price = $rs[2];
            $transaction_voucher_id = $rs[1];
            $message1 = $rs[3];
            Yii::info($result);
            yii::info($error_code);
            if ($error_code == 0) {
                $mes = Subscriber::getErrorCodePhone($error_code);
                $stp1 = $subscriber->newTransaction(SubscriberTransaction::TYPE_VOUCHER_PHONE,
                    SubscriberTransaction::getChanelTypeVoucher($operator), $mes, null, null,
                    SubscriberTransaction::STATUS_SUCCESS, $price, 'VND',
                    $subscriber->balance + $price, $error_code, $card_code, $card_serial,
                    $transaction_voucher_id, null, $subscriber->balance);
                if (empty($stp1)) {
                    throw new InvalidValueException(Yii::t('app', 'Đã có lỗi xảy ra, vui lòng thử lại'));
                }
                $subscriber_id = $subscriber->id;
                Subscriber::changeBalance($subscriber_id, $price);
                shell_exec("nohup  ./find_campaign_recharge.sh $price $subscriber_id > /dev/null 2>&1 &");
                $title = self::getTitleMessageRecharge();
                $content = self::getContentMessageRecharge($subscriber->username, $stp1->getCostBefore(), $price, $stp1->balance);
                SmsSupport::addSmsSupportByContent($title, $content, $subscriber);
                throw new InvalidValueException(Subscriber::getErrorCode($error_code));
            } else {
                $mes = Subscriber::getErrorCodePhone($error_code);
                $stp1 = $subscriber->newTransaction(SubscriberTransaction::TYPE_VOUCHER_PHONE,
                    SubscriberTransaction::getChanelTypeVoucher($operator), $mes, null, null,
                    SubscriberTransaction::STATUS_FAIL, $price, 'VND', $subscriber->balance + $price,
                    $error_code, $card_code, $card_serial, $transaction_voucher_id, null, $subscriber->balance);
                if (empty($stp1)) {
                    throw new InvalidValueException(Yii::t('app', 'Đã có lỗi xảy ra, vui lòng thử lại'));
                }
                throw new InvalidValueException($mes);
            }
        } else {
            $mes = Yii::t('app', 'Nạp thẻ không thành công! vui lòng thử lại');
            $stp1 = $subscriber->newTransaction(SubscriberTransaction::TYPE_VOUCHER_PHONE,
                SubscriberTransaction::getChanelTypeVoucher($operator), $mes, null,
                null, SubscriberTransaction::STATUS_FAIL, 0, 'VND',
                $subscriber->balance, Subscriber::VOUCHER_NOT_RESULT, $card_code, $card_serial,
                null, null, $subscriber->balance);
            if (empty($stp1)) {
                throw new InvalidValueException(Yii::t('app', 'Đã có lỗi xảy ra, vui lòng thử lại'));
            }
            throw new InvalidValueException($mes);
        }
    }


//hungnd1 them ham them moi tai khoan
    public
    static function addNewSubscriber($mac_address, $site_id, $channel)
    {
        /** @var Subscriber $subscriber */
        $subscriber = Subscriber::find()
            ->andWhere(['username' => $mac_address])
            ->andWhere(['site_id' => $site_id])
            ->andWhere(['status' => Subscriber::STATUS_ACTIVE])
            ->orderBy(['authen_type' => SORT_ASC, 'status' => SORT_DESC])
            ->one();
        if (!$subscriber) {
            $subscriber = new Subscriber();
            $subscriber->site_id = $site_id;
            $subscriber->channel = $channel;
            $subscriber->username = $mac_address;
            $subscriber->machine_name = $mac_address;
            $subscriber->authen_type = Subscriber::AUTHEN_TYPE_ACCOUNT;
            $subscriber->status = Subscriber::STATUS_ACTIVE;
            $subscriber->register_at = time();
            $subscriber->save(false);
            return $subscriber;
        }
        return null;
    }

    public static function registerNew($username, $password = "123456", $msisdn, $city = null, $status = Subscriber::STATUS_ACTIVE, $authen_type, $site_id, $channel = Subscriber::CHANNEL_TYPE_ANDROID, $mac_address = null, $address = '', $email = '', $fullname = '')
    {
        $res = [];
        /** Chuyển sang chữ thường */
        $username = strtolower($username);
        $mac_address = strtolower($mac_address);

        $subscriber = new Subscriber();
        $subscriber->username = $username;
        $subscriber->machine_name = $mac_address;
        $subscriber->status = $status;
        $subscriber->msisdn = $msisdn;
        $subscriber->city = $city;
        $subscriber->site_id = $site_id;
        $subscriber->email = $email;
        $subscriber->address = $address;
        $subscriber->full_name = $fullname;
        $subscriber->channel = (int)$channel;
        $subscriber->is_active = Subscriber::IS_NOT_ACTIVE;
        $subscriber->authen_type = $authen_type;
        $subscriber->password = ($authen_type == Subscriber::AUTHEN_TYPE_MAC_ADDRESS) ? CUtils::randomString(8) : CUtils::randomString(8);
        if ($authen_type == Subscriber::AUTHEN_TYPE_ACCOUNT) {
            $subscriber->register_at = time();
        }

        if (in_array(Yii::$app->request->getUserIP(), Yii::$app->params['factory_ip'])) {
            $subscriber->type = Subscriber::TYPE_NSX;
        } else {
            $subscriber->type = Subscriber::TYPE_USER;
        }

        $subscriber->setPassword($password);
        $subscriber->generateAuthKey();
        /** Validate và save, nếu có lỗi thì return message_error */
        if (!$subscriber->validate()) {
            $message = $subscriber->getFirstMessageError();
            $res['status'] = false;
            $res['message'] = $message;
            return $res;
        }
        if (!$subscriber->save()) {
            $res['status'] = false;
            $res['message'] = Message::getFailMessage();
            return $res;
        }
        /** TODO tạo bảng quan hệ Subscriber với Device mỗi khi tạo account */
        if ($mac_address) {
            /** @var  $device Device */
            $device = Device::findByMac($mac_address, $site_id);
            if ($device) {
                // if($authen_type == Subscriber::AUTHEN_TYPE_MAC_ADDRESS){
                /** MAC first_login, last_login **/
                $device->first_login = time();
//                    $device->last_login = time();
                $device->save();
                // }

//                SubscriberDeviceAsm::createSubscriberDeviceAsm($subscriber->id, $device->id);
            }
        }

        /** cuongvm 20170523 Bỏ logic này vì không dùng nữa, đã check với HoanPD */
        /** TODO gán cho subscriber default gói cước mặc định của thị trường */
//        if ($authen_type == Subscriber::AUTHEN_TYPE_MAC_ADDRESS) {
//            /** @var  $site Site */
//            $site = Site::findOne($site_id);
//            /** @var  $service Service */
//            $service = $site->defaultService;
//            if ($service) {
//                $ssa = new SubscriberServiceAsm();
//                $ssa->subscriber_id = $subscriber->id;
//                $ssa->service_id = $service->id;
//                $ssa->service_name = $service->name;
//                $ssa->site_id = $site_id;
//                $ssa->activated_at = time();
//
//                $expiryDate = new DateTime();
//                if (isset($service->period) && $service->period > 0) {
//                    $expiryDate->add(new DateInterval("P" . $service->period . 'D'));
//                }
//
//                /** Nếu charging_period <=0 thì set expired_at =null. Theo yêu cầu BA và có confirm của ViệtNV */
//                $ssa->expired_at = $service->period > 0 ? $expiryDate->getTimestamp() : null;
//                $ssa->status = SubscriberServiceAsm::STATUS_ACTIVE;
//                $ssa->save();
//            }
//        }

//        $item = $subscriber->getAttributes(['id', 'username','full_name', 'msisdn', 'status', 'site_id', 'created_at', 'updated_at'], ['password_hash', 'authen_type']);
        $res['status'] = true;
        $res['message'] = Message::getRegisterSuccessMessage();
        $res['subscriber'] = $subscriber;
        return $res;
    }

    public function setStatusCode($code)
    {
        Yii::$app->response->setStatusCode($code);
    }

    public function getIPToLocation()
    {
        $kt = strpos($this->ip_address, '.');
        if ($kt) {
            // neu la IPv4
            $ip = CUtils::setIPv4($this->ip_address);
            /** @var IpAddress $city */
            $city = IpAddress::find()
                ->where(['<=', 'ip_start', $ip])
                ->andWhere(['>=', 'ip_end', $ip])
                ->andWhere(['type' => IpAddress::TYPE_IPV4])
                ->one();
        } else {
            $ip = CUtils::setIPv6($this->ip_address);
            /** @var IpAddress $city */
            $city = IpAddress::find()
                ->where(['<=', 'ip_start', $ip])
                ->andWhere(['>=', 'ip_end', $ip])
                ->andWhere(['type' => IpAddress::TYPE_IPV6])
                ->one();
        }

        return $city;
    }

    public function getProvinceIPName()
    {
        $city = City::findOne(['code' => $this->ip_to_location]);
        if ($city) {
            return $city->name;
        }

        return "";

    }

    public function getProvinceName()
    {
        $city = City::findOne(['code' => $this->province_code]);
        $lang = Yii::$app->language;


        if ($city) {
            if ($lang == "vi") {
                return $city->name;
            } else {
                return $city->ascii_name;
            }
        }

        return "";

    }

    public static function getListMonth()
    {
        $list_month_default = [
            1 => "1 tháng",
            2 => "2 tháng",
            3 => "3 tháng",
            4 => "4 tháng",
            5 => "5 tháng",
            6 => "6 tháng",
            7 => "7 tháng",
            8 => "8 tháng",
            9 => "9 tháng",
            10 => "10 tháng",
            11 => "11 tháng",
            12 => "12 tháng",
        ];

        return isset(Yii::$app->params['list_number_month']) ? Yii::$app->params['list_number_month'] : $list_month_default;
    }

    public function getCityName($codeCity)
    {
        $city = City::findOne(['code' => $codeCity]);
        $lang = Yii::$app->language;

        if ($city) {
            if ($lang == "vi") {
                return $city->name;
            } else {
                return $city->ascii_name;
            }
        }

        return "";

    }
}
