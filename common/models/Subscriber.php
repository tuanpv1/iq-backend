<?php

namespace common\models;

use api\helpers\Message;
use api\models\City;
use common\helpers\CommonConst;
use common\helpers\CUtils;
use common\helpers\FileUtils;
use common\helpers\ResMessage;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;

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
 * @property SmsMessage[] $smsMessages
 * @property Site $site
 * @property SubscriberActivity[] $subscriberActivities
 * @property SubscriberToken[] $subscriberTokens
 * @property SubscriberTransaction[] $subscriberTransactions
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
    const CHANNEL_TYPE_ANDROID = 7;
    const CHANNEL_TYPE_IOS = 8;
    const CHANNEL_TYPE_ANDROID_MOBILE = 10;

    const AUTHEN_TYPE_ACCOUNT = 1; // box đã đk tài khoản
    const AUTHEN_TYPE_MAC_ADDRESS = 2; // box chưa đăng ký tài khoản
//    const AUTHEN_TYPE_MSISDN = 3;

    const IS_WHITELIST = 1;
    const NOT_WHITELIST = 2;

    const IS_ACTIVE = 1;
    const IS_NOT_ACTIVE = 0;

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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['authen_type', 'password_hash'], 'required'],
            [['username'], 'required', 'on' => 'create'],
            [['username'], 'unique'],
            [['username', 'msisdn'], 'validateUnique', 'on' => 'create'],
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
            [['msisdn', 'ip_address'], 'string', 'max' => 45],
            [
                'username',
                'match', 'pattern' => '/^(0)\d{9,10}$/',
                'message' => 'Thông tin không hợp lệ, số điện thoại - Định dạng số điện thoại bắt đầu với số 0, ví dụ 0912345678, 012312341234',
                'on' => ['create', 'update'],
            ],
            [['verification_code', 'otp_code', 'auth_key'], 'string', 'max' => 32],
            [['username', 'machine_name', 'email'], 'string', 'max' => 100],
            [['full_name', 'password'], 'string', 'max' => 200],
            [['password_hash', 'address', 'city'], 'string', 'max' => 255],
            [['avatar_url', 'skype_id', 'google_id', 'facebook_id', 'province_code', 'ip_to_location'], 'string', 'max' => 255],
            [['user_agent'], 'string', 'max' => 512],
            ['password', 'string', 'min' => 8, 'tooShort' => Yii::t('app', 'Mật khẩu không hợp lệ. Mật khẩu ít nhất 8 ký tự')],
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
            [['phone_number', 'ip_location_first',], 'string'],
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
            'machine_name' => Yii::t('app', 'Tên SmartPhone'),
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
    public function getSmsMessages()
    {
        return $this->hasMany(SmsMessage::className(), ['subscriber_id' => 'id']);
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
    public static function findByUsername($username, $status = true)
    {
        if (!$status) {
            return Subscriber::findOne(['username' => $username]);
        }
        return Subscriber::findOne(['username' => $username, 'status' => Subscriber::STATUS_ACTIVE]);
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
    public static function register($username, $password, $city = null, $status = Subscriber::STATUS_ACTIVE, $machine_name, $channel = Subscriber::CHANNEL_TYPE_ANDROID, $mac_address = null, $address = '', $email = '', $fullname = '')
    {
        $res = [];
        /** Chuyển sang chữ thường */
        $username = strtolower($username);
        $mac_address = strtolower($mac_address);

        $subscriber = new Subscriber();
        $subscriber->username = $username;
        $subscriber->machine_name = $machine_name;
        $subscriber->status = $status;
        $subscriber->msisdn = $username;
        $subscriber->city = $city;
        $subscriber->email = $email;
        $subscriber->address = $address;
        $subscriber->full_name = $fullname;
        $subscriber->channel = (int)$channel;
        $subscriber->authen_type = self::AUTHEN_TYPE_ACCOUNT;
        $subscriber->password = $password;
        $subscriber->register_at = time();

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
            SubscriberTransaction::CHANNEL_TYPE_SYSTEM => Yii::t('app', 'SYSTEM'),
            SubscriberTransaction::CHANNEL_TYPE_ANDROID => Yii::t('app', 'SmartPhone'),
            SubscriberTransaction::CHANNEL_TYPE_IOS => Yii::t('app', 'IOS'),
        ];
        return $lst;
    }


    /**
     * @return array
     */
    public static function listChannelType()
    {
        $lst = [
            self::CHANNEL_TYPE_SYSTEM => Yii::t('app', 'SYSTEM'),
            self::CHANNEL_TYPE_ANDROID => Yii::t('app', 'SmartPhone'),
            self::CHANNEL_TYPE_IOS => Yii::t('app', 'IOS'),
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
        $content = null,
        $status = SubscriberTransaction::STATUS_FAIL,
        $cost = 0,
        $currency = 'VND',
        $balance = 0,
//        $service_provider = null,
        $error_code = '',
        $card_code = null,
        $card_serial = null,
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
        if ($tr->save()) {
            return $tr;
        } else {
            Yii::error($tr->getErrors());
            return null;
        }

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
    static function writeLog($id)
    {
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/sessionId.log'), $id);
    }

    public
    static function saveOtpSubscriber($subscriber, $otp)
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

    public
    static function changeHeart($subscriber_id, $coin)
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

    public static function getMAC($subscriber_id)
    {
        $channel = Subscriber::findOne($subscriber_id);
        if ($channel) {
            return $channel->machine_name;
        } else {
            return '';
        }
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
