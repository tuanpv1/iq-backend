<?php
/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 14-Jun-17
 * Time: 1:58 PM
 */

namespace backend\models;


use common\models\ContentProvider;
use common\models\User;
use Yii;
use yii\base\Model;

class ContentProviderForm extends Model
{
    public $cp_name;
    public $id;
    public $cp_address;
    public $cp_mst;
    public $fullname;
    public $username;
    public $email;
    public $phone_number;
    public $password;
    public $confirm_password;

    public $status;

    private $_user = false;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cp_name', 'status', 'username', 'email', 'phone_number', 'fullname'], 'required'],
            [['status', 'id'], 'integer'],
            [['email'], 'email', 'message' => Yii::t('app', 'Địa chỉ Email không hợp lệ')],
            [['cp_address', 'cp_mst'], 'string'],
            [['cp_name', 'phone_number'], 'string', 'max' => 200],
            [['confirm_password', 'password'], 'required'],
            [['password'], 'string', 'min' => 8, 'tooShort' => Yii::t('app', 'Mật khẩu không hợp lệ. Mật khẩu ít nhất 8 ký tự')],
            [
                ['confirm_password'],
                'compare',
                'compareAttribute' => 'password',
                'message' => Yii::t('app', 'Xác nhận mật khẩu không khớp!'),
            ],
            [['username'], 'string', 'max' => 20],
            [['username', 'cp_name'], 'validateUnique', 'skipOnError' => false, 'on' => ['create']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'cp_name' => Yii::t('app', 'Tên nhà cung cấp'),
            'cp_address' => Yii::t('app', 'Địa chỉ'),
            'status' => Yii::t('app', 'Trạng thái'),
            'cp_mst' => Yii::t('app', 'Mã số thuế'),
            'username' => Yii::t('app', 'Tên tài khoản '),
            'phone_number' => Yii::t('app', 'SĐT người đại diện'),
            'fullname' => Yii::t('app', 'Họ và tên người đại diện'),
            'email' => Yii::t('app', 'Email người đại diện'),
            'password' => Yii::t('app', 'Mật khẩu'),
            'confirm_password' => Yii::t('app', 'Xác nhận mật khẩu'),
        ];
    }

    /**
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateUnique($attribute, $params)
    {
//        if (!$this->hasErrors()) {
        if ($attribute == 'username') {
            $user = User::findByUsername($this->username);
            if ($user) {
                $this->addError($attribute, Yii::t('app', 'Tên tài khoản đã tồn tại, vui lòng chọn một tên khác'));
            }
        } else if ($attribute == 'cp_name') {
            $site = ContentProvider::findOne(['cp_name' => $this->cp_name, 'status' => [ContentProvider::STATUS_ACTIVE, ContentProvider::STATUS_INACTIVE]]);
            if ($site) {
                $this->addError($attribute, Yii::t('app', 'Tên nhà cung cấp nội dung đã tồn tại, vui lòng chọn một tên khác'));
            }
        }
//        }
    }

    public function saveRecord($id = null, $username = null)
    {
        if ($id) {
            $cp = ContentProvider::findOne(['id' => $id]);
            $user = User::findOne(['username' => $username]);
            if ($user->password_hash != $this->password) {
                $user->password = $this->password;
                $user->setPassword($user->password);
            }
        } else {
            $cp = new ContentProvider();
            $user = new User();
            $user->setScenario('create');
            $cp->created_at = time();
            $user->is_admin_cp = ContentProvider::IS_ADMIN_CP;
            $user->type = User::USER_TYPE_CP;
            $user->password = $this->password;
            $user->setPassword($user->password);
            $user->site_id = Yii::$app->params['site_id'];
            $user->generateAuthKey();
        }
        $cp->cp_name = $this->cp_name;
        $cp->cp_address = $this->cp_address;
        $cp->cp_mst = $this->cp_mst;
        $cp->status = $this->status;
        $cp->updated_at = time();
        if (!$cp->save()) {
            $this->addError('cp_name', Yii::t('app', 'Không thành công. Vui lòng thử lại.'));
            Yii::error($cp->getErrors());
            return;
        }
        $user->username = $this->username;
        $user->email = $this->email;
        $user->cp_id = $cp->id;
        $user->phone_number = $this->phone_number;
        $user->fullname = $this->fullname;
        if (!$user->save(false)) {
            $this->addError('cp_name', Yii::t('app', 'Không thành công. Vui lòng thử lại.'));
            Yii::error($user->getErrors());
            $cp->delete();
            return;
        }
        return $cp;
    }
}