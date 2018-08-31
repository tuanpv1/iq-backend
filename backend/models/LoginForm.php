<?php
namespace backend\models;

use api\helpers\Message;
use common\models\User;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required','message' => '{attribute}'.Yii::t('app','không được phép để trống')],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['username', 'validateUser'],
            ['password', 'validatePassword'],

        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Tên tài khoản'),
            'password' => Yii::t('app', 'Mật khẩu'),
            'rememberMe' => Yii::t('app', 'Nhớ mật khẩu'),
        ];
    }
    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Message::getFailPassMessage());
            }
        }
    }

    public function validateUser($attribute, $params)
    {
        if (!$this->hasErrors()) {
            /** @var  $user User*/
            $user = User::findOne(['username' => $this->username,'type' => User::USER_TYPE_ADMIN]);
            if (!$user) {
                $this->addError($attribute,  Message::getFailPassMessage());
            }
            if($user && $user->status != User::STATUS_ACTIVE) {
                $this->addError($attribute,  Message::getFailUserMessage());
            }
        }

    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
//            $this->_user = User::findSPByUsername($this->username);
            $this->_user = User::findAdminByUsername($this->username);
        }

        return $this->_user;
    }
}
