<?php
namespace backend\models;

use common\helpers\CommonUtils;
use common\helpers\CUtils;
use common\models\Site;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * Login form
 */
class ServiceProviderForm extends Model
{
    public $name;
    public $description;
    public $cp_revernue_percent = 0;
    public $company;
    public $website;
    public $currency;
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
            [['name', 'currency', 'status', 'username', 'email', 'phone_number'], 'required'],
            [['status'], 'integer'],
            [['email'], 'email', 'message' => Yii::t('app','Địa chỉ Email không hợp lệ')],
            [['description'], 'string'],
            [['cp_revernue_percent'], 'number'],
            [['currency'], 'string', 'max' => 4],
            [['name', 'phone_number'], 'string', 'max' => 200],
            [['website'], 'string', 'max' => 255],
            [['confirm_password', 'password'], 'required'],
            [['password'], 'string', 'min' => 8, 'tooShort' => Yii::t('app','Mật khẩu không hợp lệ. Mật khẩu ít nhất 8 ký tự')],
            [
                ['confirm_password'],
                'compare',
                'compareAttribute' => 'password',
                'message' => Yii::t('app','Xác nhận mật khẩu không đúng'),
            ],
            [['username'], 'string', 'max' => 20],
            [['username', 'name'], 'validateUnique', 'skipOnError' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Tên'),
            'description' => Yii::t('app', 'Mô tả'),
            'status' => Yii::t('app', 'Trạng thái'),
            'website' => Yii::t('app', 'Website'),
            'cp_revernue_percent' => Yii::t('app', 'Phân chia doanh thu đại lý'),
            'username' => Yii::t('app', 'Tên tài khoản '),
            'phone_number' => Yii::t('app', 'Số điện thoại'),
            'currency' => Yii::t('app', 'Đơn vị tiền tệ'),
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
                    $this->addError($attribute, Yii::t('app','Tên tài khoản đã tồn tại, vui lòng chọn một tên khác'));
                }
            } else if ($attribute == 'name') {
                $site = Site::findOne(['name' => $this->name, 'status' => [Site::STATUS_ACTIVE, Site::STATUS_INACTIVE]]);
                if ($site) {
                    $this->addError($attribute, Yii::t('app','Tên nhà cung cấp dịch vụ đã tồn tại, vui lòng chọn một tên khác'));
                }
            }
//        }
    }

    public function saveRecord()
    {
        $sp = new Site();
        $sp->name = $this->name;
        $sp->description = $this->description;
        $sp->cp_revernue_percent = $this->cp_revernue_percent;
        $sp->website = $this->website;
        $sp->status = $this->status;
        $sp->currency = $this->currency;
        if (!$sp->save()) {
            $this->addError('name', Yii::t('app','Không thành công. Vui lòng thử lại.'));
            Yii::error($sp->getErrors());
            return;
        }

        $user = new User();
        $user->setScenario('create');
        $user->username = $this->username;
        $user->password = $this->password;
        $user->confirm_password = $this->confirm_password;
        $user->email = $this->email;
        $user->phone_number = $this->phone_number;
        $user->site_id = $sp->id;
        $user->setPassword($user->password);
        $user->type = User::USER_TYPE_SP;
        $user->generateAuthKey();
        if (!$user->save(false)) {
            $this->addError('name', Yii::t('app','Không thành công. Vui lòng thử lại.'));
            Yii::error($user->getErrors());
            $sp->delete();
            return;
        }

        $sp->user_admin_id = $user->id;
        $sp->update(true, ['user_admin_id']);
        return $sp;
    }

}
