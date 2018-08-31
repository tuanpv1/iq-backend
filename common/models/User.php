<?php

namespace common\models;

use kartik\helpers\Html;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property integer $id
 * @property string $username
 * @property string $fullname
 * @property string $phone_number
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $access_login_token
 * @property string $email
 * @property integer $role
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $type
 * @property integer $site_id
 * @property integer $dealer_id
 * @property integer $parent_id
 * @property integer $user_ref_id
 * @property integer $cp_id
 * @property integer $is_admin_cp
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthItem[] $itemNames
 * @property ContentLog[] $contentLogs
 * @property Dealer $dealer
 * @property Site $site
 * @property User $parent
 * @property User $userRef
 * @property User[] $users
 * @property UserActivity[] $userActivities
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 1;
    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 0;

    /**
     *  1 - Admin
     * 2 - SP
     * 3 - Dealer
     */
    const USER_TYPE_ADMIN = 1;
    const USER_TYPE_SP = 2;
    const USER_TYPE_CP = 4;
    const USER_TYPE_DEALER = 3;
    const USER_ACCESS_SP = '__user_access_sp';

    const IS_ADMIN_CP = 1;

    public static $user_types = [
        self::USER_TYPE_ADMIN => 'Admin',
        self::USER_TYPE_SP => 'Nhà cung cấp dịch vụ',
        self::USER_TYPE_DEALER => 'Đại lý',
    ];
    /*
     * @var string password for register scenario
     */
    public $password;
    public $confirm_password;
    public $new_password;
    public $old_password;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'auth_key', 'password_hash', 'email', 'status'], 'required'], // bỏ  required phone_number
            [
                [
                    'role',
                    'status',
                    'is_admin_cp',
                    'cp_id',
                    'created_at',
                    'updated_at',
                    'type',
                    'site_id',
                    'parent_id',
                    'user_ref_id'
                ],
                'integer'
            ],
            ['phone_number', 'string', 'max' => 200],
            [['username', 'password_hash', 'password_reset_token', 'email', 'fullname', 'access_login_token'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE,self::STATUS_DELETED]],
            ['email', 'email','message' => Yii::t('app','Email không đúng định dạng')],
//            ['email', 'unique'],
//            ['username', 'unique','message' => 'Tên tài khoản đã tồn tại trong hệ thống'],
            ['username', 'validateUnique','on' => 'create'],
            //cuongvm
            [['old_password'],'required','on'=>'change-password','message' => Yii::t('app','{attribute} không được phép để trống')],
            ['password', 'string', 'min' => '8', 'max' => '30', 'tooShort' => Yii::t('app','{attribute} không hợp lệ. {attribute} ít nhất 8 ký tự'), 'tooLong' =>Yii::t('app','{attribute} không hợp lệ. {attribute} ít nhất 8-30 ký tự')],
            ['old_password', 'string', 'min' => '8', 'max' => '30', 'tooShort' => Yii::t('app','{attribute} không hợp lệ. {attribute} ít nhất 8 ký tự'), 'tooLong' =>Yii::t('app','{attribute} không hợp lệ. {attribute} ít nhất 8-30 ký tự')],
            ['confirm_password', 'string', 'min' => '8', 'max' => '30', 'tooShort' => Yii::t('app','{attribute} không hợp lệ. {attribute} ít nhất 8 ký tự'), 'tooLong' =>Yii::t('app','{attribute} không hợp lệ. {attribute} ít nhất 8-30 ký tự')],
            ['new_password', 'string', 'min' => '8', 'max' => '30', 'tooShort' => Yii::t('app','{attribute} không hợp lệ. {attribute} ít nhất 8 ký tự'), 'tooLong' =>Yii::t('app','{attribute} không hợp lệ. {attribute} ít nhất 8-30 ký tự')],
            [['confirm_password', 'password'], 'required', 'on' => 'create','message' => Yii::t('app','{attribute} không được phép để trống')],
            [
                ['confirm_password'],
                'compare',
                'compareAttribute' => 'password',
                'message' => Yii::t('app','Xác nhận mật khẩu không đúng.'),
                'on' => 'create'
            ],
            [
                ['confirm_password'],
                'compare',
                'compareAttribute' => 'new_password',
                'message' => Yii::t('app','Xác nhận mật khẩu không đúng.'),
                'on' => 'change-password'
            ],
            [
                ['confirm_password'],
                'compare',
                'compareAttribute' => 'new_password',
                'message' => Yii::t('app','Xác nhận mật khẩu không đúng.'),
                'on' => 'reset-password'
            ],
            [['new_password', 'confirm_password'], 'required', 'on' => 'change-password','message' => Yii::t('app','{attribute} không được phép để trống')],
            [['new_password', 'confirm_password'], 'required', 'on' => 'reset-password','message' => Yii::t('app','{attribute} không được phép để trống')],
        ];
    }

    public function validateUnique($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = User::findUser($this->username);
            if($user){
                $this->addError($attribute, Yii::t('app','Tên tài khoản đã tồn tại trong hệ thống'));
            }
        }
    }

    public function validator_password($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!$this->validatePassword($this->old_password)) {
                $this->addError('old_password',Yii::t('app', 'Mật khẩu hiện tại không chính xác'));
            }
        }
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
            'id' => Yii::t('app', 'Số thứ tự'),
            'username' => Yii::t('app', 'Tên tài khoản'),
            'auth_key' => Yii::t('app', 'Auth Key'),
            'password_hash' => Yii::t('app', 'Password Hash'),
            'password_reset_token' => Yii::t('app', 'Password Reset Token'),
            'email' => Yii::t('app', 'Email'),
            'role' => Yii::t('app', 'Role'),
            'status' => Yii::t('app', 'Trạng thái'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'updated_at' => Yii::t('app', 'Ngày thay đổi thông tin'),
            'type' => Yii::t('app', 'Loại người dùng'),
            'site_id' => Yii::t('app', 'Service Provider ID'),
            'dealer_id' => Yii::t('app', 'Dealer ID'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'fullname' => Yii::t('app', 'Họ và tên'),
            'phone_number' => Yii::t('app', 'Số điện thoại'),
            'password' => Yii::t('app', 'Mật khẩu'),
            'confirm_password' => Yii::t('app', ' Xác nhận mật khẩu'),
            'new_password' => Yii::t('app', 'Mật khẩu mới'),
            'old_password' => Yii::t('app', 'Mật khẩu cũ'),
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemNames()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'item_name'])->viaTable('{{%auth_assignment}}',
            ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(User::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRef(){
        return $this->hasOne(User::className(), ['id' => 'user_ref_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['parent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserActivities()
    {
        return $this->hasMany(UserActivity::className(), ['user_id' => 'id']);
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
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }


    /**
     * @description : Dùng để validate unique
     * @param $username
     * @return null|static
     */
    public static function findUser($username)
    {
        return static::findOne(['username' => $username, 'status' => [self::STATUS_ACTIVE,self::STATUS_INACTIVE]]);
    }
    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findAdminByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE, 'type' => self::USER_TYPE_ADMIN]);
    }

    public static function findSPByUsername($username)
    {
        $sp = static::find()
            ->where(['username' => $username, 'status' => self::STATUS_ACTIVE])
            ->andWhere(['type'=>self::USER_TYPE_SP])->one();
        if (!$sp) {
            $sp = static::find()
                ->where(['username' => $username, 'status' => self::STATUS_INACTIVE])
                ->andWhere(['type'=>self::USER_TYPE_SP])->one();
        }
        return $sp;
    }
    public static function findCPByUsername($username)
    {
        return static::find()
            ->where(['username' => $username, 'status' => self::STATUS_ACTIVE])
            ->andWhere(['type'=>self::USER_TYPE_DEALER])->one();
    }


    public static function findCProviderByUsername($username)
    {
        return static::find()
            ->where(['username' => $username, 'status' => self::STATUS_ACTIVE])
            ->andWhere(['type'=>self::USER_TYPE_CP])->one();
    }

    public static function findCProviderByUsernameNoStatus($username)
    {
        return static::find()
            ->where(['username' => $username])
            ->andWhere(['type'=>self::USER_TYPE_CP])->one();
    }
    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return User|null
     */
    public static function findByAccessLoginToken($token)
    {
        if (!static::isAccessLoginTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'access_login_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int)end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * Finds out if password reset token is valid
     * @param $token Token to login as user
     * @return bool
     */
    private static function isAccessLoginTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = 10;//Validate on 10s
        $parts = explode('_', $token);
        $timestamp = (int)end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|integer an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
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
        return $this->auth_key;
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
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
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

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function generateAccessLoginToken(){
        $this->access_login_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }


    public function isServiceProvider()
    {
        if ($this->type == self::USER_TYPE_SP && $this->site_id != null) {
            return true;
        }

        return false;
    }

    public function isContentProvider()
    {
        if ($this->type == self::USER_TYPE_CP) {
            return true;
        }

        return false;
    }

    public function isDealer()
    {
        if ($this->type == self::USER_TYPE_DEALER && $this->dealer_id != null) {
            return true;
        }

        return false;
    }

    /**
     * ******************************** MY FUNCTION ***********************
     */

    /**
     * @return ActiveDataProvider
     */
    public function getAuthItemProvider($acc_type = null)
    {
        if($acc_type){
            return new ActiveDataProvider([
                'query' => $this->getAuthItems()->andWhere(['acc_type' => $acc_type])
            ]);
        }else{
            return new ActiveDataProvider([
                'query' => $this->getAuthItems()
            ]);
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItems()
    {
//        return $this->hasMany(AuthItem::className(), ['name' => 'item_name'])->viaTable('v2_auth_assignment', ['user_id' => "$this->id"]);
        return AuthItem::find()->andWhere(['name' => AuthAssignment::find()->select(['item_name'])->andWhere(['user_id' => $this->id])]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMissingRoles($acc_type = AuthItem::ACC_TYPE_BACKEND)
    {
        $roles = AuthItem::find()->andWhere(['type' => AuthItem::TYPE_ROLE, 'acc_type' => $acc_type])
            ->andWhere('name not in (select item_name from auth_assignment where user_id = :id)', [':id' => $this->id]);

        return $roles->all();
    }

    /**
     * @return string
     */
    public function getRolesName()
    {
        $str = "";
        $roles = $this->getAuthItems()->all();
        $action = 'rbac-backend/update-role';
        foreach ($roles as $role) {
            $res = Html::a($role['description'], [$action, 'name' => $role['name']]);
//            $res = $role['description'];
            $res .= " [" . sizeof($role['children']) . "]";
            $str = $str . $res . '  ,';
        }
        return $str;
    }

    /**
     * @return array
     */
    public static function listStatus()
    {
        $lst = [
            self::STATUS_ACTIVE => \Yii::t('app', 'Kích hoạt'),
            self::STATUS_INACTIVE => \Yii::t('app', 'Tạm dừng'),
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

    public function getSite()
    {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentLogs()
    {
        return $this->hasMany(ContentLog::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDealer()
    {
        return $this->hasOne(Dealer::className(), ['id' => 'dealer_id']);
    }

    /**
     * @return array
     */
    public static function listType()
    {
        $lst = [
            self::USER_TYPE_ADMIN => \Yii::t('app', 'Quản trị backend'),
            self::USER_TYPE_SP => \Yii::t('app', 'Nhà cung cấp dịch vụ'),
            self::USER_TYPE_DEALER => \Yii::t('app', 'Đại lý'),
            self::USER_TYPE_CP => Yii::t('app','Nhà cung cấp nội dung')
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

    /**
     * @param $model User
     * @return bool
     */
    public function validateChildUser($model)
    {
        if ($this->id != $model->parent_id ||
            $this->site_id != $model->site_id ||
            $this->dealer_id != $model->dealer_id ||
            $this->type != $model->type)
        {
            return false;
        } else {
            return true;
        }
    }

    public function haveAccessSP()
    {
        if($this->type == User::USER_TYPE_SP){
            return true;
        }
        $roles = $this->getAuthItemProvider(AuthItem::ACC_TYPE_SP);
        if($roles->count > 0){
            return true;
        }
        return false;
    }

    public static function getUsernameById($user_id) {
        $user = User::findOne($user_id);
        if ($user) {
            return $user->username;
        }
    }
}
