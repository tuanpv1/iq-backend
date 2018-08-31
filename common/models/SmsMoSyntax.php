<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%sms_mo_syntax}}".
 *
 * @property integer $id
 * @property string $syntax
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $type
 * @property integer $service_id
 * @property integer $site_id
 * @property integer $status
 * @property integer $event
 * @property string $admin_note
 *
 * @property Service $service
 * @property ServiceProvider $serviceProvider
 * @property SmsMtTemplateContent[] $smsMtTemplateContents
 */
class SmsMoSyntax extends \yii\db\ActiveRecord
{

    const STATUS_INACTIVE = 0;
    const STATUS_PENDING = 1;
    const STATUS_PAUSE = 2;
    const STATUS_ACTIVE = 10;

    const SCOPE_SP = 1;
    const SCOPE_ADMIN = 2;

    public static function getMoStatus(){
        return
            $mo_status = [
                self::STATUS_ACTIVE => Yii::t('app','Kích hoạt'),
                self::STATUS_PENDING => Yii::t('app','Chờ duyệt'),
                self::STATUS_PAUSE => Yii::t('app','Tạm dừng'),
                self::STATUS_INACTIVE => Yii::t('app','Loại bỏ'),
            ];
    }

    public static function getMoStatusNameByStatus($status){
        $lst = self::getMoStatus();
        if (array_key_exists($status, $lst)) {
            return $lst[$status];
        }
        return $status;
    }

    const MO_EVENT_REGISTER = 1;
    const MO_EVENT_CANCEL = 2;
    const MO_EVENT_GIFT = 3;
    const MO_EVENT_HELP = 4;
    const MO_EVENT_EXTEND = 5;
    const MO_EVENT_PASSWORD = 6;
    const MO_EVENT_BUY = 7;
    const MO_EVENT_CHARGE_COIN = 8;

    const MO_TYPE_SUBSCRIPTION_BASE = 1;
    const MO_TYPE_GENERAL = 2;

    public static function getMoEvents(){
        return
            $mo_events = [
                self::MO_EVENT_REGISTER => Yii::t('app','Đăng ký'),
                self::MO_EVENT_CANCEL => Yii::t('app','Hủy'),
                self::MO_EVENT_HELP => Yii::t('app','Tra cứu'),
                self::MO_EVENT_PASSWORD => Yii::t('app','Mật khẩu'),
                self::MO_EVENT_CHARGE_COIN => Yii::t('app','Nạp coin'),
            ];
    }

    public static function getMoEventsByStatus($mo){
        $lst = self::getMoStatus();
        if (array_key_exists($mo, $lst)) {
            return $lst[$mo];
        }
        return $mo;
    }

    public static $mo_types = [
        self::MO_TYPE_SUBSCRIPTION_BASE => 'Subscription Based',
        self::MO_TYPE_GENERAL => 'General'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sms_mo_syntax}}';
    }

    public static function getServiceList($sp_id)
    {
        return ArrayHelper::map(Service::find()->asArray()->andWhere([
            'site_id' => $sp_id,
            'status' => Service::STATUS_ACTIVE
        ])->all(), 'id', 'name');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['syntax', 'site_id', 'event'], 'required'],
            [['syntax'], 'unique'],
            [['created_at', 'updated_at', 'type', 'service_id', 'site_id', 'status', 'event'], 'integer'],
            [['syntax'], 'string', 'max' => 45],
            ['syntax', 'unique'],
            ['service_id', 'required', 'when' => function ($model) {
                return $model->type == self::MO_TYPE_SUBSCRIPTION_BASE;
            }, 'whenClient' => "function (attribute, value) {

            }"],
            [['description', 'admin_note'], 'string', 'max' => 1024]
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
            'syntax' => Yii::t('app', 'Format'),
            'description' => Yii::t('app', 'Description'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'type' => Yii::t('app', 'Type'),
            'service_id' => Yii::t('app', 'Subscription Plan Name'),
            'site_id' => Yii::t('app', 'Service Provider ID'),
            'status' => Yii::t('app', 'Status'),
        ];
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
    public function getServiceProvider()
    {
        return $this->hasOne(ServiceProvider::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSmsMtTemplateContents()
    {
        return $this->hasMany(SmsMtTemplateContent::className(), ['sms_mo_syntax_id' => 'id']);
    }

    public function getMtProvider()
    {
        $query = SmsMtTemplateContent::find();
        $query->andWhere(['sms_mo_syntax_id' => $this->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    public function getMt($sp_id, $type_mo, $service_id)
    {
        $query = SmsMtTemplateContent::find();
        $query->andWhere(['sms_mo_syntax_id' => '']);
        $query->andWhere(['site_id' => $sp_id]);
        $query->andWhere(['type' => $type_mo]);
        if ($service_id) {
            $query->andWhere(['service_id' => $service_id]);
        }


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    public static function getMoViaService($sp_id, $service_id)
    {
        $result = SmsMoSyntax::find()->select(['id', 'syntax as name',])
            ->andWhere(['site_id' => $sp_id]);
        if ($service_id == null || $service_id == '') {
            $result->andWhere('service_id is null');
        } else {
            $result->andWhere(['service_id' => $service_id]);
        }
        return $result->asArray()->all();
    }

    public function getListStatus($scope = self::SCOPE_SP)
    {
        $status = [];
        foreach (self::getMoStatus() as $id => $des) {
            if ($this->validateMOCycle($id, $scope)) {
                $status[$id] = $des;
            }
        }
        return $status;
    }

    public function isReadOnly($scope = self::SCOPE_SP)
    {
        if ($scope == self::SCOPE_ADMIN) {
            return true;
        }

        if ($this->status >= self::STATUS_PAUSE) {
            return true;
        } else {
            return false;
        }
    }

    public function validateMOCycle($nextStatus, $scope)
    {
        if ($nextStatus == self::STATUS_PENDING) {
            if (($this->status >= self::STATUS_PENDING && $scope == self::SCOPE_ADMIN) ||
                ($this->status != self::STATUS_ACTIVE && $scope == self::SCOPE_SP)
            ) {
                return true;
            } else {
                return false;
            }
        } elseif ($nextStatus == self::STATUS_INACTIVE) {
            /**
             * Change to status INACTIVE (Tu choi) when current status in (PENDING, ACTIVE), only Admin
             */
            if ($this->status == self::STATUS_PENDING && $scope == self::SCOPE_SP) {
                return true;
            } else {
                return false;
            }
        } elseif ($nextStatus == self::STATUS_PAUSE) {
            /**
             * Change to status TEMP (Nhap) when current status in (PENDING), only SP
             */
            if ($this->status == self::STATUS_ACTIVE && $scope == self::SCOPE_SP) {
                return true;
            } else {
                return false;
            }
        } elseif ($nextStatus == self::STATUS_ACTIVE) {
            /**
             * Change to status TEMP (Nhap) when current status in (PENDING), only SP
             */
            if (($this->status == self::STATUS_PENDING && $scope == self::SCOPE_ADMIN) ||
                ($this->status == self::STATUS_PAUSE && $scope == self::SCOPE_SP)
            ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getStatusClassCss()
    {
        switch ($this->status) {
            case self::STATUS_INACTIVE:
                return 'default';
            case self::STATUS_PENDING:
                return 'warning';
            case self::STATUS_PAUSE:
                return "primary";
            case self::STATUS_ACTIVE:
                return "success";
        }
    }

    public static function getServiceByType($sp_id, $type)
    {
        if ($type == self::MO_TYPE_SUBSCRIPTION_BASE) {
            $result = Service::find()->select(['id', 'name',])
                ->andWhere(['site_id' => $sp_id])
                ->andWhere(['status'=>Service::STATUS_ACTIVE])
                ->asArray()
                ->all();
            return $result;
        } else {
            return [];
        }
    }

    public static function getEventByType($type)
    {
        if ($type == self::MO_TYPE_SUBSCRIPTION_BASE) {
            return [
                ['id' => self::MO_EVENT_REGISTER,
                    'name' => 'Đăng ký'],
                ['id' => self::MO_EVENT_CANCEL,
                    'name' => 'Hủy']
            ];
        } else {
            return [
                ['id' => self::MO_EVENT_HELP,
                    'name' => 'Tra cứu'],
                ['id' => self::MO_EVENT_PASSWORD,
                    'name' => 'Mật khẩu']
            ];
        }
    }

    public static function getMoBySyntax($syntax, $site_id)
    {
        Yii::trace("syntax: $syntax | site: $site_id");
        return SmsMoSyntax::findOne(['site_id' => $site_id,
            'syntax' => $syntax, 'status' => self::STATUS_ACTIVE]);
    }
}
