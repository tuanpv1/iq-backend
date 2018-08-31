<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%subscriber_transaction}}".
 *
 * @property integer $id
 * @property integer $subscriber_id
 * @property integer $white_list
 * @property string $card_serial
 * @property string $card_code
 * @property integer $transaction_voucher_id
 * @property string $msisdn
 * @property integer $type
 * @property integer $service_id
 * @property integer $content_id
 * @property integer $transaction_time
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 * @property string $shortcode
 * @property string $description
 * @property double $cost
 * @property double $balance
 * @property string $currency
 * @property double $balance_before_charge
 * @property integer $channel
 * @property string $event_id
 * @property string $error_code
 * @property integer $subscriber_activity_id
 * @property integer $subscriber_service_asm_id
 * @property string $cp_id
 * @property integer $site_id
 * @property integer $dealer_id
 * @property string $vnp_user_ip
 * @property string $vnp_username
 * @property string $application
 * @property integer $content_provider_id
 * @property integer $expired_time
 * @property string $order_id
 * @property string $gateway
 * @property integer $smartgate_transaction_id
 * @property integer $smartgate_transaction_timeout
 * @property integer $number_month
 * @property integer $is_first_package
 *
 * @property SubscriberServiceAsm[] $subscriberServiceAsms
 * @property SubscriberServiceAsm[] $subscriberServiceAsms0
 * @property SubscriberServiceAsm[] $subscriberServiceAsms1
 * @property Service $service
 * @property Subscriber $subscriber
 * @property Content $content
 * @property SubscriberActivity $subscriberActivity
 * @property SubscriberServiceAsm $subscriberServiceAsm
 */
class SubscriberTransaction extends \yii\db\ActiveRecord
{
    const STATUS_SUCCESS = 10;
    const STATUS_FAIL = 0;
    const STATUS_REPAY = 9;
    const STATUS_WARNING = 8;
    const STATUS_PENDING = 1;

    const TYPE_REGISTER = 1; // mua goi
    const TYPE_RENEW = 2;
    const TYPE_DOWNLOAD = 3;
    const TYPE_USER_CANCEL = 4;
    const TYPE_CANCEL = 5;
    const TYPE_RETRY = 6;
    const TYPE_CANCEL_SERVICE_BY_SYSTEM = 7;
    const TYPE_CONTENT_PURCHASE = 8;  // Mua phim le de xem
    const TYPE_CANCEL_BY_API_VNPT = 9;// huy boi api vnpt
    const TYPE_CANCEL_SERVICE_BY_CHANGE_PACKAGE = 10; // Huy do doi goi khac trong cung nhom
    const TYPE_REGISTER_BY_CHANGE_PACKAGE = 11; // Mua goi do doi goi khac trong cung nhom
//    const TYPE_CONTENT_PURCHASE_DOWNLOAD=9; // MUA content de download
    const TYPE_CSKH_SUBSCRIBER_INFO = 12; // Lay thong tin subscriber tu CSKH
    const TYPE_CSKH_GET_ALL_SUBSCRIBER_INFO = 13; // Lay thong tin tat ca cac goi cuoc cua subscriber tren he thong
    const TYPE_CSKH_GET_TRANSACTION_INFO = 14; // Lay thong tin giao dich cua nguoi dung
    const TYPE_CSKH_CHANGE_USER = 15; // Nguoi dung doi so mobile
    const TYPE_VOUCHER = 17; // Nguoi dung nap the tvod2
    const TYPE_VOUCHER_PHONE = 18; // Nguoi dung nap the điện thoại
    const TYPE_TRANFER_MONEY = 19; // chuyen tien
    const TYPE_RECEIVE_MONEY = 20; // nhan tien
    const TYPE_PROMOTION = 21; // tang
    const TYPE_MONEY = 24;
    const TYPE_TOPUP_ATM = 22; // nap tien ngan hang noi dia
    const TYPE_TOPUP_VISA = 23; // nap tien ngan hang nuoc ngoai

//    const CHANNEL_TYPE_WAP = 1;
//    const CHANNEL_TYPE_SMS = 2;
//    const CHANNEL_TYPE_SYSTEM = 3;
//    const CHANNEL_TYPE_ADMIN = 4;
//    const CHANNEL_TYPE_API_VNPT = 5;
//    const CHANNEL_TYPE_ANDROID=6;

    const CHANNEL_TYPE_API = 1;
    const CHANNEL_TYPE_SYSTEM = 2;
    const CHANNEL_TYPE_CSKH = 3;
    const CHANNEL_TYPE_SMS = 4;
    const CHANNEL_TYPE_WAP = 5;
    const CHANNEL_TYPE_MOBILEWEB = 6;
    const CHANNEL_TYPE_ANDROID = 7;
    const CHANNEL_TYPE_IOS = 8;
    const CHANNEL_TYPE_VOUCHER = 9;
    const CHANNEL_TYPE_VOUCHER_PHONE_VIETTEL = 10;
    const CHANNEL_TYPE_VOUCHER_PHONE_VINAPHONE = 11;
    const CHANNEL_TYPE_VOUCHER_PHONE_MOBIPHONE = 12;
    const CHANNEL_TYPE_VOUCHER_PARTNER = 14;
    const CHANNEL_TYPE_ANDROID_MOBILE = 13;
    const CHANNEL_TYPE_ATM = 15;
    const CHANNEL_TYPE_VISA = 16;
    const CHANNEL_TYPE_RECHAGRE_FITTER = 19;

    // Type white
    const IS_WHITE_LIST = 1;
    const NOT_WHITE_LIST = 2;

    // Thuê  bao mua gói cước lần đầu phục vụ cho báo cáo doanh thu
    const IS_FIRST_PACKAGE = 1;

    // Định nghĩa mã lỗi VTC_PAY
    const VTC_PAY_TRANSACTION_FAIL = -1;
    const VTC_PAY_CUSTOMER_DESTROY = -9;
    const VTC_PAY_ADMIN_VTC_DESTROY = -3;
    const VTC_PAY_CARD_NOT_CONDITION = -4;
    const VTC_PAY_BALANCE_NOT_CONDITION = -5;
    const VTC_PAY_TRANSACTION_ERROR_IN_VTC_PAY = -6;
    const VTC_PAY_BILLING_INFORMATION = -7;
    const VTC_PAY_EXCEEDING_DAY_TRADING_LIMIT = -8;
    const VTC_PAY_MONNEY_TOO_SMALL = -22;
    const VTC_PAY_PAYMENT_CURENCY_IS_INVALID= -24;
    const VTC_PAY_ACCOUNT_VTC_PAY_NOT_EXIST = -25;
    const VTC_PAY_REQUIRED_PARAMETER_MISSING = -28;
    const VTC_PAY_INVALID_REQUEST = -29;
    const VTC_PAY_REPEAT_TRANSACTION_CODE = -21;
    const VTC_PAY_INVALID_WEBSITEID = -23;
    const VTC_PAY_UNEXPLAINED_ERROR = -99;
    const VTC_PAY_WEBSITEID_INCORRECT = -402;
    const VTC_PAY_DATA_INCRRECT = -403;
    const VTC_PAY_WRONG_SIGNATURE = -404;
    const VTC_PAY_TRANSACTION_EXIST = -620;
    const VTC_PAY_TRANSACTION_CANCELED = -625;
    const VTC_PAY_TRANSACTION_VERFY = -699;


    public $from_date;
    public $to_date;
    public $expired_at;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%subscriber_transaction}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subscriber_id', 'status', 'site_id'], 'required'],
            [['subscriber_id', 'type', 'white_list', 'service_id', 'content_id', 'transaction_time', 'created_at',
                'updated_at', 'status', 'channel', 'subscriber_activity_id', 'subscriber_service_asm_id', 'site_id',
                'dealer_id', 'expired_at', 'smartgate_transaction_timeout', 'expired_time', 'number_month', 'is_first_package'], 'integer'],
            [['cost', 'balance', 'balance_before_charge'], 'number'],
            [['msisdn'], 'string', 'max' => 20],
            [['shortcode'], 'string', 'max' => 45],
            [['gateway', 'order_id'], 'string', 'max' => 50],
            [['description', 'application', 'smartgate_transaction_id'], 'string', 'max' => 200],
            [['event_id', 'currency'], 'string', 'max' => 10],
            [['from_date', 'to_date'], 'safe'],
            [['card_serial', 'card_code', 'cp_id'], 'string'],
            ['white_list', 'default', 'value' => Subscriber::NOT_WHITELIST]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'subscriber_id' => Yii::t('app', 'Subscriber ID'),
            'msisdn' => Yii::t('app', 'Msisdn'),
            'type' => Yii::t('app', 'Loại'),
            'service_id' => Yii::t('app', 'Service ID'),
            'content_id' => Yii::t('app', 'Content ID'),
            'transaction_time' => Yii::t('app', 'Thời gian giao dịch'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'updated_at' => Yii::t('app', 'Ngày thay đổi thông tin'),
            'status' => Yii::t('app', 'Trạng thái'),
            'shortcode' => Yii::t('app', 'Shortcode'),
            'description' => Yii::t('app', 'Mô tả'),
            'cost' => Yii::t('app', 'Cost'),
            'channel' => Yii::t('app', 'Kênh giao dịch'),
            'event_id' => Yii::t('app', 'Event ID'),
            'error_code' => Yii::t('app', 'Error Code'),
            'subscriber_activity_id' => Yii::t('app', 'Subscriber Activity ID'),
            'subscriber_service_asm_id' => Yii::t('app', 'Subscriber Service Asm ID'),
            'site_id' => Yii::t('app', 'Service Provider ID'),
            'application' => Yii::t('app', 'Ứng dụng'),
            'voucher_id' => Yii::t('app', 'Mã thẻ nạp'),
            'expired_time' => Yii::t('app', 'Thời gian hết hạn'),
            'card_serial' => Yii::t('app', 'Số serial thẻ'),
            'balance_before_charge' => Yii::t('app', 'Số dư đầu kỳ'),
            'number_month' => Yii::t('app', 'Số tháng mua'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberServiceAsms()
    {
        return $this->hasMany(SubscriberServiceAsm::className(), ['transaction_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberServiceAsms0()
    {
        return $this->hasMany(SubscriberServiceAsm::className(), ['cancel_transaction_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberServiceAsms1()
    {
        return $this->hasMany(SubscriberServiceAsm::className(), ['last_renew_transaction_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriber()
    {
        return $this->hasOne(Subscriber::className(), ['id' => 'subscriber_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->hasOne(Content::className(), ['id' => 'content_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberActivity()
    {
        return $this->hasOne(SubscriberActivity::className(), ['id' => 'subscriber_activity_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberServiceAsm()
    {
        return $this->hasOne(SubscriberServiceAsm::className(), ['id' => 'subscriber_service_asm_id']);
    }


    /**
     * @return array
     */
    public static function listStatus()
    {
        $lst = [
            self::STATUS_SUCCESS => \Yii::t('app', 'Thành công'),
            self::STATUS_FAIL => \Yii::t('app', 'Thất bại'),
            self::STATUS_PENDING => \Yii::t('app', 'Đang xử lý'),
        ];
        return $lst;
    }

    public static function listStatusCr33()
    {
        $lst = [
            self::STATUS_SUCCESS => \Yii::t('app', 'Thành công'),
            self::STATUS_FAIL => \Yii::t('app', 'Thất bại'),
            self::STATUS_REPAY => \Yii::t('app', 'Hoàn tiền'),
            self::STATUS_PENDING => \Yii::t('app', 'Đang xử lý'),
        ];
        return $lst;
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
    public static function listType()
    {
        $lst = [
            self::TYPE_REGISTER => \Yii::t('app', 'Mua gói'),
            self::TYPE_RENEW => \Yii::t('app', 'Gia hạn'),
//            self::TYPE_RETRY => 'Gia hạn (truy thu)',
//            self::TYPE_CANCEL_SERVICE_BY_SYSTEM => 'Hủy bởi hệ thống',
            self::TYPE_DOWNLOAD => \Yii::t('app', 'Tải'),
            self::TYPE_CANCEL => \Yii::t('app', 'Hủy gói'),
            self::TYPE_CONTENT_PURCHASE => \Yii::t('app', 'Mua nội dung lẻ'),
//            self::TYPE_CONTENT_PURCHASE_DOWNLOAD => 'Download',
//            self::TYPE_CHARGE_COIN => \Yii::t('app', 'Nạp coin'),
            self::TYPE_VOUCHER => \Yii::t('app', 'Nạp thẻ TVOD'),
            self::TYPE_VOUCHER_PHONE => \Yii::t('app', 'Nạp thẻ điện thoại'),
            self::TYPE_TRANFER_MONEY => \Yii::t('app', 'Chuyển tiền'),
            self::TYPE_RECEIVE_MONEY => \Yii::t('app', 'Nhận tiền'),
            self::TYPE_PROMOTION => \Yii::t('app', 'Khuyến mãi'),
            self::TYPE_TOPUP_ATM => \Yii::t('app', 'Thẻ ATM'),
            self::TYPE_TOPUP_VISA => \Yii::t('app', 'Thẻ VISA'),
        ];
        return $lst;
    }

    public static function listTypeSP()
    {
        $lst = [
            self::TYPE_MONEY => \Yii::t('app', 'Nạp tiền'),
            self::TYPE_REGISTER => \Yii::t('app', 'Mua gói'),
            self::TYPE_RENEW => Yii::t('app', 'Gia hạn'),
            self::TYPE_CANCEL => Yii::t('app', 'Hủy gói'),
            self::TYPE_CONTENT_PURCHASE => \Yii::t('app', 'Mua nội dung lẻ'),
            self::TYPE_PROMOTION => \Yii::t('app', 'Khuyến mãi'),
        ];
        return $lst;
    }

    /**
     * @return array
     */
    public static function listTypeReport()
    {
        $lst = [
            self::TYPE_REGISTER => \Yii::t('app', 'Đăng ký'),
            self::TYPE_RENEW => \Yii::t('app', 'Gia hạn'),
            self::TYPE_DOWNLOAD => \Yii::t('app', 'Tải'),
            self::TYPE_CONTENT_PURCHASE => \Yii::t('app', 'Mua lẻ'),
//            self::TYPE_CONTENT_PURCHASE_DOWNLOAD => 'Download',
        ];
        return $lst;
    }

    /**
     * @return int
     */
    public function getTypeName()
    {
        $lst = self::listType();
        if (array_key_exists($this->type, $lst)) {
            return $lst[$this->type];
        }
        return $this->type;
    }

    public function getTypeNameSP()
    {
        $lst = self::listTypeSP();
        if (array_key_exists($this->type, $lst)) {
            return $lst[$this->type];
        }
        return $this->type;
    }

    /**
     * @return array
     */
    public static function listChannelType()
    {
        $lst = [
            self::CHANNEL_TYPE_API => \Yii::t('app', 'Api'),
            self::CHANNEL_TYPE_SYSTEM => \Yii::t('app', 'System'),
            self::CHANNEL_TYPE_SMS => \Yii::t('app', 'Sms'),
//            self::CHANNEL_TYPE_WAP => 'Wap',
            self::CHANNEL_TYPE_MOBILEWEB => \Yii::t('app', 'Mobile Web'),
            self::CHANNEL_TYPE_CSKH => \Yii::t('app', 'CSKH'),
            self::CHANNEL_TYPE_ANDROID => \Yii::t('app', 'SmartBox'),
            self::CHANNEL_TYPE_IOS => \Yii::t('app', 'Ios'),
            self::CHANNEL_TYPE_ANDROID_MOBILE => \Yii::t('app', 'Android'),
            self::CHANNEL_TYPE_VOUCHER => \Yii::t('app', 'Thẻ TVOD'),
            self::CHANNEL_TYPE_VOUCHER_PARTNER => \Yii::t('app', 'Đại Lý TVOD'),
            self::CHANNEL_TYPE_VOUCHER_PHONE_VIETTEL => \Yii::t('app', 'Thẻ Viettel'),
            self::CHANNEL_TYPE_VOUCHER_PHONE_MOBIPHONE => \Yii::t('app', 'Thẻ Mobifone'),
            self::CHANNEL_TYPE_VOUCHER_PHONE_VINAPHONE => \Yii::t('app', 'Thẻ Vinaphone'),
        ];
        return $lst;
    }

    public static function listChannelTypeCr33()
    {
        $lst = [
            self::CHANNEL_TYPE_VOUCHER => \Yii::t('app', 'Thẻ TVOD'),
            self::CHANNEL_TYPE_RECHAGRE_FITTER => \Yii::t('app', 'Thẻ Điện thoại'),
//            self::CHANNEL_TYPE_VOUCHER_PHONE_MOBIPHONE => \Yii::t('app', 'Thẻ Mobifone'),
//            self::CHANNEL_TYPE_VOUCHER_PHONE_VINAPHONE => \Yii::t('app', 'Thẻ Vinaphone'),
            self::CHANNEL_TYPE_ATM => \Yii::t('app', 'Thẻ ATM'),
            self::CHANNEL_TYPE_VISA => \Yii::t('app', 'Thẻ TT Quốc tế'),
            self::CHANNEL_TYPE_ANDROID => \Yii::t('app', 'Smart Box'),
            self::CHANNEL_TYPE_IOS => \Yii::t('app', 'iOS Client'),
            self::CHANNEL_TYPE_ANDROID_MOBILE => \Yii::t('app', 'Android Client'),
            self::CHANNEL_TYPE_CSKH => \Yii::t('app', 'CSKH'),
        ];
        return $lst;
    }

    /**
     * @return int
     */
    public function getChannelName()
    {
        $lst = self::listChannelType();
        if (array_key_exists($this->channel, $lst)) {
            return $lst[$this->channel];
        }
        return $this->channel;
    }

    public function get_event()
    {
        switch ($this->type) {
            case self::TYPE_USER_CANCEL:
            case self::TYPE_CANCEL_BY_API_VNPT:
            case self::TYPE_CANCEL_SERVICE_BY_CHANGE_PACKAGE:
            case self::TYPE_CANCEL_SERVICE_BY_SYSTEM:
            case self::TYPE_CANCEL:
                return "UNSUBSCRIBE";
            case self::TYPE_CONTENT_PURCHASE:
            case self::TYPE_REGISTER:
                return "SUBSCRIBE";
            case self::TYPE_RENEW:
                return "RENEW";
        }
        return '';
    }

    /**
     * @return array
     */
    public static function listTopupChannelType()
    {
        $lst = [
            self::CHANNEL_TYPE_VOUCHER => Yii::t('app', 'Thẻ TVOD'),
            self::CHANNEL_TYPE_VOUCHER_PHONE_VIETTEL => Yii::t('app', 'Thẻ Viettel'),
            self::CHANNEL_TYPE_VOUCHER_PHONE_VINAPHONE => Yii::t('app', 'Thẻ Vinaphone'),
            self::CHANNEL_TYPE_VOUCHER_PHONE_MOBIPHONE => Yii::t('app', 'Thẻ Mobifone'),
            self::CHANNEL_TYPE_ATM => Yii::t('app', 'Thẻ ATM'),
            self::CHANNEL_TYPE_VISA => Yii::t('app', 'Thẻ VISA'),
        ];
        return $lst;
    }

    /**
     * @return int
     */
    public function getTopupChannelName()
    {
        $lst = self::listTopupChannelType();
        if (array_key_exists($this->channel, $lst)) {
            return $lst[$this->channel];
        }
        return $this->channel;
    }

    public static function getChanelTypeVoucher($code)
    {
        switch ($code) {
            case 'VMS':
                return self::CHANNEL_TYPE_VOUCHER_PHONE_MOBIPHONE;
            case 'VNP':
                return self::CHANNEL_TYPE_VOUCHER_PHONE_VINAPHONE;
            case 'VTE':
                return self::CHANNEL_TYPE_VOUCHER_PHONE_VIETTEL;
        }
    }


    /**
     * @return array
     */
    public static function listWhitelistTypes()
    {
        $lst = [
            self::NOT_WHITE_LIST => Yii::t('app', 'Bình thường'),
            self::IS_WHITE_LIST => Yii::t('app', 'Whitelist')
        ];
        return $lst;
    }

    public function getViewCost()
    {
        preg_match_all('!\d+!', $this->cost, $cost);
        return $cost[0][0];
    }

    public static function getTypeNameTopup($type)
    {
        $array_type = [
//            SubscriberTransaction::TYPE_CHARGE_COIN,
            SubscriberTransaction::TYPE_VOUCHER_PHONE,
            SubscriberTransaction::TYPE_VOUCHER,
            SubscriberTransaction::TYPE_TOPUP_VISA,
            SubscriberTransaction::TYPE_TOPUP_ATM,
        ];
        if (in_array($type, $array_type)) {
            return Yii::t('app', 'Nạp tiền');
        } else {
            $lst = self::listType();
            if (array_key_exists($type, $lst)) {
                return $lst[$type];
            }
            return $type;
        }
    }

    public static function getChannelNameCr33($channel, $type)
    {
        if ($type == SubscriberTransaction::TYPE_TOPUP_ATM) {
            $channel = SubscriberTransaction::CHANNEL_TYPE_ATM;
        }
        if ($type == SubscriberTransaction::TYPE_TOPUP_VISA) {
            $channel = SubscriberTransaction::CHANNEL_TYPE_VISA;
        }
        $array_channel = [
            SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VINAPHONE,
            SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_MOBIPHONE,
            SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VIETTEL,
        ];
        if (in_array($channel, $array_channel)) {
            return Yii::t('app', 'Thẻ điện thoại');
        } else {
            $lst = self::listChannelTypeCr33();
            if (array_key_exists($channel, $lst)) {
                return $lst[$channel];
            }
            return $channel;
        }
    }

    public function getCostBefore()
    {
        if ($this->type == self::TYPE_REGISTER ||
            $this->type == self::TYPE_CONTENT_PURCHASE ||
            $this->type == self::TYPE_RENEW
        ) {
            return $this->balance + $this->cost;
        }

        if ($this->type == self::TYPE_VOUCHER ||
            $this->type == self::TYPE_VOUCHER_PHONE ||
            $this->type == self::TYPE_TRANFER_MONEY ||
            $this->type == self::TYPE_TOPUP_ATM
        ) {
            return $this->balance - $this->cost;
        }

        return '';
    }

    public static function getNameErrorCode($type, $error_code, $gateway)
    {
        if ($type == self::TYPE_VOUCHER) {
            switch ($error_code) {
                case Subscriber::VOUCHER_USED :
                    return Yii::t('app', 'Thẻ đã sử dụng');
                    break;
                case Subscriber::VOUCHER_INACTIVE :
                    return Yii::t('app', 'Thẻ đã bị khóa');
                    break;
                case Subscriber::VOUCHER_EXPIRE :
                    return Yii::t('app', 'Thẻ đã hết hạn');
                    break;
                case Subscriber::VOUCHER_NOT_ACTIVE :
                    return Yii::t('app', 'Thẻ chưa được kích hoạt');
                    break;
                case Subscriber::VOUCHER_NOT_FORMAT :
                    return Yii::t('app', 'Mã thẻ sai định dạng  ');
                    break;
                case Subscriber::VOUCHER_NOT_HAVE :
                    return Yii::t('app', 'Thẻ không tồn tại');
                    break;
                case Subscriber::VOUCHER_INVALID_LOGIN :
                    return Yii::t('app', 'Chưa đăng nhập vào hệ thống charging');
                    break;
                case Subscriber::VOUCHER_INVALID_SESSION:
                    return Yii::t('app', 'Không tồn tại session');
                    break;
                case Subscriber::VOUCHER_PARTNED_LOCKED :
                    return Yii::t('app', 'Tài khoản partner bị khóa');
                    break;
                case Subscriber::VOUCHER_INVALID_PARTNED :
                    return Yii::t('app', 'Không tồn tại partner');
                    break;
                case Subscriber::VOUCHER_SYSTEM_ERROR :
                    return Yii::t('app', 'Lỗi hệ thống charging');
                    break;
                case Subscriber::VOUCHER_CANNOT_LOGOUT :
                    return Yii::t('app', 'Không đăng xuất thành công charging');
                    break;
                case Subscriber::VOUCHER_INVALID_USER :
                    return Yii::t('app', 'Sai tên đăng nhập hoặc mật khẩu');
                    break;
                case Subscriber::VOUCHER_INVALID_MPIN :
                    return Yii::t('app', 'Không tồn tại mpin');
                    break;
                case Subscriber::VOUCHER_INVALID_REQUEST :
                    return Yii::t('app', 'Request không hợp lệ');
                    break;
                case Subscriber::VOUCHER_INVALID_PASS :
                    return Yii::t('app', 'Password không đúng');
                    break;
                case Subscriber::VOUCHER_INVALID_ENCRYPT :
                    return Yii::t('app', 'Lỗi khi mã hóa/giải mã');
                    break;
                case Subscriber::VOUCHER_INVALID_SINGNATURE :
                    return Yii::t('app', 'Lỗi chữ ký dữ liệu');
                    break;
                case Subscriber::VOUCHER_ERROR_WRONG_TIME :
                    return Yii::t('app', 'Bị khóa tính năng nạp thẻ trong 30 phút do nạp sai vượt quá số lượt quy định');
                    break;
                case Subscriber::VOUCHER_CHANGE_CLIENT:
                    return Yii::t('app','Dữ liệu bị thay đổi từ client truyền lên');
                    break;
                case Subscriber::VOUCHER_CHANGE_VOUCHER:
                    return Yii::t('app','Dữ liệu bị thay đổi từ voucher truyền sang');
                    break;
                case Subscriber::VOUCHER_NOT_RESULT:
                    return Yii::t('app','Không có kết quả');
                    break;
                case Subscriber::VOUCHER_NOT_USER:
                    return Yii::t('app','Không tồn tại người dùng');
                    break;
                default:
                    return Yii::t('app', 'Chưa xác định được lỗi');
            }
        } else {
            if ($type == self::TYPE_VOUCHER_PHONE) {
                switch ($error_code) {
                    case Subscriber::VOUCHER_PHONE_WRONG_PARAMETERS :
                        return Yii::t('app', 'Mã hóa sai các tham số');
                        break;
                    case Subscriber::VOUCHER_PHONE_NOT_EXITS_CP :
                        return Yii::t('app', 'CP bị khóa hoặc không tồn tại');
                        break;
                    case Subscriber::VOUCHER_PHONE_WRONG_FORMAT :
                        return Yii::t('app', 'Thiếu tham số hoặc tham số sai format');
                        break;
                    case Subscriber::VOUCHER_PHONE_ERROR_CARDCHARGING1 :
                        return Yii::t('app', 'Lỗi hệ thống CardCharging');
                        break;
                    case Subscriber::VOUCHER_PHONE_NOT_FOUND_CARDCHARGING :
                        return Yii::t('app', 'Không tìm thấy module CardCharging');
                        break;
                    case Subscriber::VOUCHER_PHONE_NOT_CONNECT_CARDCHARGING :
                        return Yii::t('app', 'Không kết nối đến module CardCharging');
                        break;
                    case Subscriber::VOUCHER_PHONE_ERROR_CARDCHARGING2 :
                        return Yii::t('app', 'Lỗi hệ thống đối tác CardCharging');
                        break;
                    case Subscriber::VOUCHER_PHONE_ERROR_MODULE_CARDCHARGING1 :
                        return Yii::t('app', 'Lỗi module CardCharging');
                        break;
                    case Subscriber::VOUCHER_PHONE_WRONG_10_TIME :
                        return Yii::t('app', 'Lỗi giao dịch sai quá 10 lần');
                        break;
                    case Subscriber::VOUCHER_PHONE_ERROR_MODULE_CARDCHARGING2 :
                        return Yii::t('app', 'Lỗi chung module CardCharging');
                        break;
                    case Subscriber::VOUCHER_PHONE_USED :
                        return Yii::t('app', 'Thẻ đã sử dụng');
                        break;
                    case Subscriber::VOUCHER_PHONE_BLOCK :
                        return Yii::t('app', 'Thẻ đã khóa');
                        break;
                    case Subscriber::VOUCHER_PHONE_EXPIRE :
                        return Yii::t('app', 'Thẻ đã hết hạn sử dụng');
                        break;
                    case Subscriber::VOUCHER_PHONE_NOT_ACTIVE :
                        return Yii::t('app', 'Thẻ chưa được kích hoạt');
                        break;
                    case Subscriber::VOUCHER_PHONE_NOT_HAVE :
                        return Yii::t('app', 'Mã thẻ không đúng định dạng');
                        break;
                    case Subscriber::VOUCHER_PHONE_ERROR_VMS :
                        return Yii::t('app', 'Lỗi hệ thống VMS');
                        break;
                    case Subscriber::VOUCHER_PHONE_ERROR_ORTHER :
                        return Yii::t('app', 'Lỗi khác');
                        break;
                    case Subscriber::VOUCHER_PHONE_ERROR_VNP :
                        return Yii::t('app', 'Thẻ không sử dụng được, lỗi chung của VNP');
                        break;
                    case Subscriber::VOUCHER_PHONE_ERROR_VTE :
                        return Yii::t('app', 'Thẻ không sử dụng được, lỗi chung của Viettel');
                        break;
                    case Subscriber::VOUCHER_PHONE_USED_EPAY :
                        return Yii::t('app', 'Mã thẻ đã được sử dụng');
                        break;
                    case 53 :
                        return Yii::t('app', 'Thẻ không sử dụng được, lỗi chung của Viettel');
                        break;
                    case Subscriber::VOUCHER_PHONE_INCORRECT:
                        return Yii::t('app', 'Thông tin thẻ không hợp lệ');
                        break;
                    case Subscriber::VOUCHER_ERROR_WRONG_TIME :
                        return Yii::t('app', 'Bị khóa tính năng nạp thẻ trong 30 phút do nạp sai vượt quá số lượt quy định');
                        break;
                    case Subscriber::VOUCHER_CHANGE_CLIENT:
                        return Yii::t('app','Dữ liệu bị thay đổi từ client truyền lên');
                        break;
                    case Subscriber::VOUCHER_CHANGE_VOUCHER:
                        return Yii::t('app','Dữ liệu bị thay đổi từ voucher truyền sang');
                        break;
                    case Subscriber::VOUCHER_NOT_RESULT:
                        return Yii::t('app','Không có kết quả');
                        break;
                    case Subscriber::VOUCHER_NOT_USER:
                        return Yii::t('app','Không tồn tại người dùng');
                        break;
                    default:
                        return Yii::t('app', 'Chưa xác định được lỗi');
                }
            } else {
                if ($gateway == 'VTC_PAY') {
                    switch ($error_code) {
                        case self::VTC_PAY_TRANSACTION_FAIL  :
                            return Yii::t('app', 'Giao dịch thất bại');
                            break;
                        case self::VTC_PAY_CUSTOMER_DESTROY  :
                            return Yii::t('app', 'Khách hàng tự hủy giao dịch');
                            break;
                        case self::VTC_PAY_ADMIN_VTC_DESTROY  :
                            return Yii::t('app', 'Quản trị VTC hủy giao dịch');
                            break;
                        case self::VTC_PAY_CARD_NOT_CONDITION  :
                            return Yii::t('app', 'Thẻ/tài khoản không đủ điều kiện giao dịch (Đang bị khóa, chưa đăng ký thanh toán online …)');
                            break;
                        case self::VTC_PAY_BALANCE_NOT_CONDITION  :
                            return Yii::t('app', 'Số dư tài khoản khách hàng (Ví VTC Pay, tài khoản ngân hàng) không đủ để thực hiện giao dịch');
                            break;
                        case self::VTC_PAY_TRANSACTION_ERROR_IN_VTC_PAY  :
                            return Yii::t('app', 'Lỗi giao dịch tại VTC');
                            break;
                        case self::VTC_PAY_BILLING_INFORMATION  :
                            return Yii::t('app', 'Khách hàng nhập sai thông tin thanh toán ( Sai thông tin tài khoản hoặc sai OTP)');
                            break;
                        case self::VTC_PAY_EXCEEDING_DAY_TRADING_LIMIT :
                            return Yii::t('app', 'Quá hạn mức giao dịch trong ngày');
                            break;
                        case self::VTC_PAY_MONNEY_TOO_SMALL  :
                            return Yii::t('app', 'Số tiền thanh toán đơn hàng quá nhỏ');
                            break;
                        case self::VTC_PAY_PAYMENT_CURENCY_IS_INVALID :
                            return Yii::t('app', 'Đơn vị tiền tệ thanh toán đơn hàng không hợp lệ');
                            break;
                        case self::VTC_PAY_ACCOUNT_VTC_PAY_NOT_EXIST  :
                            return Yii::t('app', 'Tài khoản VTC Pay nhận tiền của Merchant không tồn tại.');
                            break;
                        case self::VTC_PAY_REQUIRED_PARAMETER_MISSING :
                            return Yii::t('app', 'Thiếu tham số bắt buộc phải có trong một đơn hàng thanh toán online');
                            break;
                        case self::VTC_PAY_INVALID_REQUEST  :
                            return Yii::t('app', 'Tham số request không hợp lệ');
                            break;
                        case self::VTC_PAY_REPEAT_TRANSACTION_CODE  :
                            return Yii::t('app', 'Trùng mã giao dịch, Có thể do xử lý duplicate không tốt nên mạng chậm hoặc khách hàng nhấn F5 bị, hoặc cơ chế sinh mã GD của đối tác không tốt nên sinh bị trùng, đối tác cần kiểm tra lại để biết kết quả cuối cùng của giao dịch này');
                            break;
                        case self::VTC_PAY_INVALID_WEBSITEID  :
                            return Yii::t('app', 'WebsiteID không tồn tại');
                            break;
                        case self::VTC_PAY_UNEXPLAINED_ERROR  :
                            return Yii::t('app', 'Lỗi chưa rõ nguyên nhân và chưa biết trạng thái giao dịch. Cần kiểm tra để biết giao dịch thành công hay thất bại');
                            break;
                        case self::VTC_PAY_WEBSITEID_INCORRECT :
                            return Yii::t('app', 'WebsiteID không đúng');
                            break;
                        case self::VTC_PAY_DATA_INCRRECT  :
                            return Yii::t('app', 'Dữ liệu truyền không đúng ');
                            break;
                        case self::VTC_PAY_WRONG_SIGNATURE  :
                            return Yii::t('app', 'Sai chữ ký ');
                            break;
                        case self::VTC_PAY_TRANSACTION_EXIST  :
                            return Yii::t('app', 'Giao dịch không tồn tại ');
                            break;
                        case self::VTC_PAY_TRANSACTION_CANCELED  :
                            return Yii::t('app', 'Giao dịch đã bị hủy ');
                            break;
                        case self::VTC_PAY_TRANSACTION_VERFY  :
                            return Yii::t('app', 'Giao dịch cần xác minh lại ');
                            break;
                        default:
                            return Yii::t('app', 'Chưa xác định được lỗi');
                    }
                } else {
                    return Yii::t('app', 'Chưa xác định được lỗi');
                }
            }
        }
    }

}
