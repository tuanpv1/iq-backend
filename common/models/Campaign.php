<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "campaign".
 *
 * @property integer $id
 * @property string $name
 * @property string $ascii_name
 * @property string $description
 * @property integer $site_id
 * @property integer $status
 * @property integer $type
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $activated_at
 * @property integer $expired_at
 * @property integer $priority
 * @property integer $number_promotion
 * @property integer $type_subscriber
 * @property integer $updated_at_campaign
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $notification_title
 * @property string $notification_content
 *
 * @property User $createdBy
 * @property User $updatedBy
 * @property Site $site
 * @property CampaignCondition[] $campaignConditions
 * @property CampaignPromotion[] $campaignPromotions
 * @property LogCampaignPromotion[] $logCampaignPromotions
 * @property CampaignGroupSubscriberAsm[] $campaignGroupSubscriberAsms
 */
class Campaign extends \yii\db\ActiveRecord
{

    const TYPE_BOX_SERVICE = 1;
    const TYPE_BOX_CONTENT = 2;
    const TYPE_BOX_CASH = 3;
    const TYPE_CASH_CASH = 4;
    const TYPE_CASH_SERVICE = 5;
    const TYPE_CASH_CONTENT = 6;
    const TYPE_SERVICE_TIME = 7;
    const TYPE_SERVICE_SERVICE = 8;
    const TYPE_SERVICE_CONTENT = 9;
    const TYPE_EVENT = 10;
    const TYPE_REGISTER = 11;
    const TYPE_ACTIVE = 12;

    public $demo_subscribers;
    public $apply_subscribers;

    public static $campaignType = [
        self::TYPE_BOX_SERVICE => 'Mua box khuyến mại gói nội dung',
        self::TYPE_BOX_CONTENT => 'Mua box khuyến mại nội dung lẻ',
        self::TYPE_BOX_CASH => 'Mua box khuyến mại tiền vào tài khoản',
        self::TYPE_CASH_CASH => 'Nạp tiền tặng tiền',
        self::TYPE_CASH_SERVICE => 'Nạp tiền tặng gói',
        self::TYPE_CASH_CONTENT => 'Nạp tiền tặng nội dung lẻ',
        self::TYPE_SERVICE_TIME => 'Mua gói tặng thời gian sử dụng',
        self::TYPE_SERVICE_SERVICE => 'Mua gói tặng gói',
        self::TYPE_SERVICE_CONTENT => 'Mua gói tặng nội dung lẻ',
        self::TYPE_EVENT => 'Khuyến mại theo sự kiện',
        self::TYPE_REGISTER => 'Khuyến mại khi đăng ký tài khoản TVOD',
        self::TYPE_ACTIVE => 'Kích hoạt khuyến mại gói nội dung',
    ];
    /**
     * @inheritdoc
     */

    const STATUS_NOT_ACTIVATED = 11; //Chưa kích hoạt
    const STATUS_ACTIVATED = 12; //Kích hoạt
    const STATUS_RUNNING_TEST = 2; //Đang chạy thử
    const STATUS_ACTIVE = 10; //Đang hoạt động
    const STATUS_INACTIVE = 0; //Ngừng hoạt động
    const STATUS_END = 3; //Kết thúc
    const STATUS_DELETE = 1; // Trạng thái dành riêng cho Xóa không dùng thằng này khi View hay xử lí logic

    public static $campaignStatus = [
        self::STATUS_NOT_ACTIVATED => 'Chưa kích hoạt',
        self::STATUS_ACTIVATED => 'Kích hoạt',
        self::STATUS_RUNNING_TEST => 'Đang chạy thử',
        // self::STATUS_ACTIVE        => 'Đang hoạt động',
        // self::STATUS_INACTIVE      => 'Ngừng hoạt động',
        // self::STATUS_END           => 'Kết thúc',
        //        self::STATUS_DELETE        => 'Trạng thái dành riêng cho Xóa',
    ];

    public static function tableName()
    {
        return 'campaign';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'site_id', 'status', 'type', 'activated_at', 'expired_at'], 'required'],
            [['site_id', 'status', 'type', 'created_at', 'updated_at', 'activated_at', 'expired_at', 'updated_at_campaign', 'priority', 'number_promotion', 'type_subscriber'], 'integer'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['site_id'], 'exist', 'skipOnError' => true, 'targetClass' => Site::className(), 'targetAttribute' => ['site_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Tên chiến dịch'),
            'ascii_name' => Yii::t('app', 'Tên chiến dịch'),
            'description' => Yii::t('app', 'Mô tả'),
            'site_id' => Yii::t('app', 'Thị trường (SP)'),
            'status' => Yii::t('app', 'Trạng thái'),
            'type' => Yii::t('app', 'Loại chiến dịch'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'updated_at' => Yii::t('app', 'Ngày cập nhật'),
            'activated_at' => Yii::t('app', 'Thời gian bắt đầu'),
            'expired_at' => Yii::t('app', 'Thời gian kết thúc'),
            'priority' => Yii::t('app', 'Mức ưu tiên'),
            'demo_subscribers' => Yii::t('app', 'Nhóm khách hàng chạy thử'),
            'apply_subscribers' => Yii::t('app', 'Nhóm khách hàng chạy thật '),
            'number_promotion' => Yii::t('app', 'Số lần được hưởng khuyến mại'),
            'type_subscriber' => Yii::t('app', 'Loại khách hàng'),
            'created_by' => Yii::t('app', 'Người tạo'),
            'updated_by' => Yii::t('app', 'Người cập nhật'),
            'notification_title' => Yii::t('app', 'Tiêu đề thông báo'),
            'notification_content' => Yii::t('app', 'Nội dung thông báo'),
            'condition' => Yii::t('app', 'Điều kiện'),
            'promotion' => Yii::t('app', 'Thụ hưởng'),
            'extra' => Yii::t('app', 'Thụ hưởng'),
        ];
    }

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
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
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
    public function getCampaignConditions()
    {
        return $this->hasMany(CampaignCondition::className(), ['campaign_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCampaignPromotions()
    {
        return $this->hasMany(CampaignPromotion::className(), ['campaign_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogCampaignPromotions()
    {
        return $this->hasMany(LogCampaignPromotion::className(), ['campaign_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCampaignGroupSubscriberAsms()
    {
        return $this->hasMany(CampaignGroupSubscriberAsm::className(), ['campaign_id' => 'id']);
    }

    public static function getPriceLevel()
    {
        $price_level_default = [
            10000 => "10.000",
            20000 => "20.000",
            30000 => "30.000",
            50000 => "50.000",
            100000 => "100.000",
            200000 => "200.000",
            500000 => "500.000",
        ];

        return isset(Yii::$app->params['list_price_level']) ? Yii::$app->params['list_price_level'] : $price_level_default;
    }

    public static function getSubscriberType()
    {
        return GroupSubscriber::listGroupSubscriberTypes();
    }

    public function getChannelName()
    {
        $lst = self::getSubscriberType();
        if (array_key_exists($this->type_subscriber, $lst)) {
            return $lst[$this->type_subscriber];
        }
        return $this->type_subscriber;
    }

    public function getTypeName()
    {
        $lst = self::$campaignType;
        if (array_key_exists($this->type, $lst)) {
            return $lst[$this->type];
        }
        return $this->type;
    }

    public function getListStatus()
    {
        return [
            self::STATUS_NOT_ACTIVATED => Yii::t('app', 'Chưa kích hoạt'),
            self::STATUS_ACTIVATED => Yii::t('app', 'Kích hoạt'),
            self::STATUS_RUNNING_TEST => Yii::t('app', 'Đang chạy thử'),
            self::STATUS_ACTIVE => Yii::t('app', 'Đang hoạt động'),
            self::STATUS_INACTIVE => Yii::t('app', 'Ngừng hoạt động'),
            self::STATUS_END => Yii::t('app', 'Kết thúc'),
        ];

    }

    public function getStatusName()
    {
        $lst = self::getListStatus();
        if (array_key_exists($this->status, $lst)) {
            if ($this->isActive()) {
                return $lst[Campaign::STATUS_ACTIVE];
            }
            if ($this->isEnd()) {
                return $lst[Campaign::STATUS_END];
            }
            return $lst[$this->status];
        }
        return $this->status;
    }

    /** Điều kiện kích hoạt */
    public function canActivated()
    {
        if (($this->status == Campaign::STATUS_NOT_ACTIVATED || $this->status == Campaign::STATUS_RUNNING_TEST) && time() < $this->expired_at) {
            return true;
        }
        return false;
    }

    /**  Kiểm tra điều kiện chạy thử */
    public function canRunningTest()
    {
        if ($this->status == Campaign::STATUS_NOT_ACTIVATED && time() < $this->expired_at) {
            return true;
        }
        return false;
    }

    /**  Kiểm tra điều kiện tạm dừng */
    public function canNotActivated()
    {
        if (($this->status == Campaign::STATUS_RUNNING_TEST && time() < $this->expired_at) || $this->isActivated()) {
            return true;
        }
        return false;
    }

    /**  Kiểm tra điều kiện sửa: Chưa kích hoạt, đang chạy thử */
    public function canUpdate()
    {
        if ($this->status == Campaign::STATUS_NOT_ACTIVATED || $this->status == Campaign::STATUS_RUNNING_TEST) {
            return true;
        }
        return false;
    }

    /**  Kiểm tra điều kiện xóa: Kích hoạt và đang hoạt động không được xóa */
    public function canDelete()
    {
        if (($this->status == Campaign::STATUS_ACTIVATED && time() < $this->expired_at) || $this->isActive()) {
            return false;
        }
        return true;
    }

    /** Kiểm tra điều kiện active */
    public function isActive()
    {
        if ($this->status == Campaign::STATUS_ACTIVE) {
            return true;
        }
        /** Chiến dịch đã được kích hoạt có ngày bắt đầu <= thời gian hiện tại<= ngày kết thúc, hệ thống sẽ tự động chuyển sang trạng thái đang hoạt động */
        if ($this->status == Campaign::STATUS_ACTIVATED && $this->activated_at <= time() && time() <= $this->expired_at) {
            return true;
        }
        return false;
    }

    /**  Kiểm tra điều kiện kết thúc */
    public function isEnd()
    {
        if ($this->status == Campaign::STATUS_END) {
            return true;
        }
        /** Khi ngày kết thúc < ngày hiện tại, hệ thống tự động dừng chiến dịch và chuyển sang trạng thái kết thúc */
        if ($this->status == Campaign::STATUS_ACTIVATED && $this->expired_at < time()) {
            return true;
        }
        return false;
    }

    /**  Kiểm tra điều kiện kích hoạt */
    public function isActivated()
    {
        if ($this->status == Campaign::STATUS_ACTIVATED && time() < $this->activated_at) {
            return true;
        }
        return false;
    }

    /**
     * @return null|string
     */
    public function getGroupSubcriber($type = CampaignGroupSubscriberAsm::TYPE_REAL, $gen = true)
    {
        $lst = CampaignGroupSubscriberAsm::findAll(['campaign_id' => $this->id, 'type' => $type]);
        $str = "";
        if (empty($lst)) {
            return null;
        }
        foreach ($lst as $item) {
//            $link = Html::a('<kbd>' . $item->groupSubscriber->name . '</kbd>', ["group-subscriber/view", 'id' => $item->groupSubscriber->id]);
            if (!$gen) {
                $link = $item->groupSubscriber->name;
            } else {
                $link = Html::a($item->groupSubscriber->name, ["group-subscriber/view", 'id' => $item->groupSubscriber->id]);
            }
            $str .= $link . " , ";
        }
        $str = substr($str, 0, -2);
        return $str;
    }

    public function getIdenticalCampaign()
    {
        $lst = [];
        $str = "";
        $items = Campaign::find()
            ->andWhere(['<>', 'status', Campaign::STATUS_DELETE])
            ->andWhere(['<>', 'id', $this->id])
            ->all();
        if (count($items) <= 0) {
            return null;
        }
        foreach ($items as $item) {
            $isCheck = Campaign::checkIdentical($item);
            if (!$isCheck) {
                continue;
            }
//            $link = Html::a('<kbd>' . $item->name . '</kbd>', ["campaign/view", 'id' => $item->id]);
            $link = Html::a($item->name, ["campaign/view", 'id' => $item->id]);
            $str .= $link . " , ";

        }
        $str = substr($str, 0, -2);
        return $str;
    }

    /**
     * @param $campaign Campaign
     * @return bool
     */
    public function checkIdentical($campaign)
    {
        /** Thị trường và loại chiến dịch */
        if ($campaign->site_id == $this->site_id && $campaign->type == $this->type) {
            return true;
        }
        /** Mức ưu tiên */
        if ($campaign->priority == $this->priority) {
            return true;
        }
        /** Nhóm khách hàng */
        if (CampaignGroupSubscriberAsm::checkIdentical($campaign->campaignGroupSubscriberAsms, $this->campaignGroupSubscriberAsms)) {
            return true;
        }
        /** Thị trường và thời gian hoạt động */
        if ($campaign->site_id == $this->site_id && !($campaign->expired_at < $this->activated_at || $campaign->activated_at > $this->expired_at)) {
            return true;
        }
        return false;

    }

    public function getDemoSubscribers()
    {
        $gs = CampaignGroupSubscriberAsm::find()
            ->select(['group_subscriber_id'])
            ->where(['campaign_id' => $this->id])
            ->andWhere(['type' => CampaignGroupSubscriberAsm::TYPE_DEMO])
            ->asArray()
            ->all();

        $gs = array_column($gs, 'group_subscriber_id');
        return $this->demo_subscribers = ArrayHelper::map(GroupSubscriber::find()->where(['IN', 'id', $gs])->all(), 'id', 'name');
    }

    public function getApplySubscribers()
    {
        $gs = CampaignGroupSubscriberAsm::find()
            ->select(['group_subscriber_id'])
            ->where(['campaign_id' => $this->id])
            ->andWhere(['type' => CampaignGroupSubscriberAsm::TYPE_REAL])
            ->asArray()
            ->all();

        $gs = array_column($gs, 'group_subscriber_id');
        return $this->apply_subscribers = ArrayHelper::map(GroupSubscriber::find()->where(['IN', 'id', $gs])->all(), 'id', 'name');
    }

    // public function setDemo_subscribers($value)
    // {
    //     if (!$this->isNewRecord) {
    //         CampaignGroupSubscriberAsm::deleteAll(['campaign_id' => $this->id]);
    //     }

    //     foreach ((array) $value as $sub) {
    //         $cgsa                      = new CampaignGroupSubscriberAsm();
    //         $cgsa->campaign_id         = $this->id;
    //         $cgsa->group_subscriber_id = $sub;
    //         $cgsa->site_id             = $this->site_id;
    //         $cgsa->status              = CampaignGroupSubscriberAsm::STATUS_ACTIVE;
    //         $cgsa->type                = CampaignGroupSubscriberAsm::TYPE_DEMO;
    //         $cgsa->insert();
    //     }

    //     return $value;
    // }

    // public function setApply_subscribers($value)
    // {
    //     foreach ((array) $value as $sub) {
    //         $cgsa                      = new CampaignGroupSubscriberAsm();
    //         $cgsa->campaign_id         = $this->id;
    //         $cgsa->group_subscriber_id = $sub;
    //         $cgsa->site_id             = $this->site_id;
    //         $cgsa->status              = CampaignGroupSubscriberAsm::STATUS_ACTIVE;
    //         $cgsa->type                = CampaignGroupSubscriberAsm::TYPE_REAL;
    //         $cgsa->insert();
    //     }

    //     return true;
    // }
    /**
     * @param $sources CampaignGroupSubscriberAsm[]
     */
    public function copyGroupSubscriberAsms($sources)
    {
        /** $source CampaignGroupSubscriberAsm */
        foreach ($sources as $source) {
            $data = $source->attributes;
            $modelTarget = new CampaignGroupSubscriberAsm();
            $modelTarget->setAttributes($data);
            $modelTarget->campaign_id = $this->id;
            $modelTarget->save();
        }
    }

    /**
     * @param $sources CampaignCondition[]
     */
    public function copyConditions($sources)
    {
        /** $source CampaignCondition */
        foreach ($sources as $source) {
            $data = $source->attributes;
            $modelTarget = new CampaignCondition();
            $modelTarget->setAttributes($data);
            $modelTarget->campaign_id = $this->id;
            $modelTarget->save();
        }
    }

    /**
     * @param $sources
     */
    public function copyPromotions($sources)
    {
        /** $source CampaignPromotion */
        foreach ($sources as $source) {
            $data = $source->attributes;
            $modelTarget = new CampaignPromotion();
            $modelTarget->setAttributes($data);
            $modelTarget->campaign_id = $this->id;
            $modelTarget->save();
        }
    }


    public function condition()
    {
        $output = [
            'service_id' => [],
            'price_level' => [],
            'event_time' => '',
        ];

        if (!$this->id) {
            return $output;
        }

        $conditions = $this->hasMany(CampaignCondition::className(), ['campaign_id' => 'id'])->where(['status' => CampaignCondition::STATUS_ACTIVE])->asArray()->all();

        $service_ids = array_column($conditions, 'service_id');
        $price_levels = array_column($conditions, 'price_level');
        $event_time = array_column($conditions, 'event_time');

        return [
            'service_id' => $service_ids,
            'price_level' => $price_levels,
            'event_time' => count(array_filter($event_time)) > 0 ? $event_time : '',
        ];
    }

    public function promotion()
    {
        $output = [
            'content_id' => '[]',
            'service_id' => [],
            'time_extend_service' => '',
            'price_gift' => '',
        ];

        if (!$this->id) {
            return $output;
        }

        $promotion = $this->hasMany(CampaignPromotion::className(), ['campaign_id' => 'id'])->where(['status' => CampaignPromotion::STATUS_ACTIVE])->asArray()->all();

        if (count($promotion) > 0) {
            $content_id = array_column($promotion, 'content_id');
            $service_id = array_column($promotion, 'service_id');
            $time_extend_service = array_column($promotion, 'time_extend_service')[0];
            $price_gift = array_column($promotion, 'price_gift')[0];
            $price_unit = array_column($promotion, 'price_unit')[0];
            $type = array_column($promotion, 'type')[0];

            return [
                'content_id' => json_encode($this->getContentSelected($content_id)),
                'service_id' => $service_id,
                'time_extend_service' => $time_extend_service,
                'price_gift' => $price_gift,
                'price_unit' => $price_unit,
                'type' => $type,
            ];
        }
    }

    public function getContentSelected($contents)
    {
        return Content::find()->andFilterWhere(['IN', 'id', $contents])->asArray()->all();
    }

    public function getServices()
    {
        return ArrayHelper::map(Service::findAll(['status' => Service::STATUS_ACTIVE]), 'id', 'name');
    }

    public function getConditionDetail($gen = true)
    {
        $msg = "";
        switch ($this->type) {
            case Campaign::TYPE_BOX_SERVICE:
                $msg = Yii::t('app', 'Mua box + đăng nhập trên box lần đầu tiên');
                break;
            case Campaign::TYPE_BOX_CONTENT:
                $msg = Yii::t('app', 'Mua box + đăng nhập trên box lần đầu tiên');
                break;
            case Campaign::TYPE_BOX_CASH:
                $msg = Yii::t('app', 'Mua box + đăng nhập trên box lần đầu tiên');
                break;
            case Campaign::TYPE_CASH_CASH:
                $lst = $this->campaignConditions;
                if (count($lst) <= 0) {
                    return null;
                }
                foreach ($lst as $item) {
                    //Chỉ lấy những thằng status = ACTIVE
                    if ($item->status != CampaignCondition::STATUS_ACTIVE) {
                        continue;
                    }
                    $msg .= $item->price_level . ', ';
                }
                $msg = substr($msg, 0, -2);
                break;
            case Campaign::TYPE_CASH_SERVICE:
                $lst = $this->campaignConditions;
                if (count($lst) <= 0) {
                    return null;
                }
                foreach ($lst as $item) {
                    //Chỉ lấy những thằng status = ACTIVE
                    if ($item->status != CampaignCondition::STATUS_ACTIVE) {
                        continue;
                    }
                    $msg .= $item->price_level . ', ';
                }
                $msg = substr($msg, 0, -2);
                break;
            case Campaign::TYPE_CASH_CONTENT:
                $lst = $this->campaignConditions;
                if (count($lst) <= 0) {
                    return null;
                }
                foreach ($lst as $item) {
                    //Chỉ lấy những thằng status = ACTIVE
                    if ($item->status != CampaignCondition::STATUS_ACTIVE) {
                        continue;
                    }
                    $msg .= $item->price_level . ', ';
                }
                $msg = substr($msg, 0, -2);
                break;
            case Campaign::TYPE_SERVICE_TIME:
                $lst = $this->campaignConditions;
                if (count($lst) <= 0) {
                    return null;
                }
                foreach ($lst as $item) {
                    if (!$item->service) {
                        continue;
                    }
                    //Chỉ lấy những thằng status = ACTIVE
                    if ($item->status != CampaignCondition::STATUS_ACTIVE) {
                        continue;
                    }
                    if (!$gen) {
                        $itemMessage = $item->service->name;
                    } else {
                        $itemMessage = Html::a($item->service->name, ["service/view", 'id' => $item->service->id]);
                    }
                    $msg .= $itemMessage . ', ';
                }
                $msg = substr($msg, 0, -2);
                break;
            case Campaign::TYPE_SERVICE_SERVICE:
                $lst = $this->campaignConditions;
                if (count($lst) <= 0) {
                    return null;
                }
                foreach ($lst as $item) {
                    if (!$item->service) {
                        continue;
                    }
                    //Chỉ lấy những thằng status = ACTIVE
                    if ($item->status != CampaignCondition::STATUS_ACTIVE) {
                        continue;
                    }
                    if (!$gen) {
                        $itemMessage = $item->service->name;
                    } else {
                        $itemMessage = Html::a($item->service->name, ["service/view", 'id' => $item->service->id]);
                    }
                    $msg .= $itemMessage . ', ';
                }
                $msg = substr($msg, 0, -2);
                break;
            case Campaign::TYPE_SERVICE_CONTENT:
                $lst = $this->campaignConditions;
                if (count($lst) <= 0) {
                    return null;
                }
                foreach ($lst as $item) {
                    if (!$item->service) {
                        continue;
                    }
                    //Chỉ lấy những thằng status = ACTIVE
                    if ($item->status != CampaignCondition::STATUS_ACTIVE) {
                        continue;
                    }
                    if (!$gen) {
                        $itemMessage = $item->service->name;
                    } else {
                        $itemMessage = Html::a($item->service->name, ["service/view", 'id' => $item->service->id]);
                    }
                    $msg .= $itemMessage . ', ';
                }
                $msg = substr($msg, 0, -2);
                break;
            case Campaign::TYPE_EVENT:
                $msg = Yii::t('app', 'Khuyến mại theo sự kiện');
                break;
            case Campaign::TYPE_REGISTER:
                $msg = Yii::t('app', 'Đăng ký tài khoản');
                break;
            case  Campaign::TYPE_ACTIVE:
                $msg = Yii::t('app', 'Kích hoạt gói cước khuyến mại');
                break;
        }

        return $msg;
    }

    public function getPromotionDetail($gen = true)
    {
        $lst = $this->campaignPromotions;
        if (count($lst) <= 0) {
            return null;
        }
        $msg = "";
        foreach ($lst as $item) {
            //Chỉ lấy những thằng status = ACTIVE
            if ($item->status != CampaignPromotion::STATUS_ACTIVE) {
                continue;
            }
            switch ($item->type) {
                case CampaignPromotion::TYPE_FREE_SERVICE:
                    if (!$item->service) {
                        break;
                    }
                    if (!$gen) {
                        $itemMessage = $item->service->name;
                    } else {
                        $itemMessage = Html::a($item->service->name, ["service/view", 'id' => $item->service->id]);
                    }
                    $msg .= $itemMessage . ', ';
                    break;
                case CampaignPromotion::TYPE_FREE_COIN:
                    if ($item->price_unit == CampaignPromotion::PRICE_UNIT_PERCENT) {
                        $msg .= $item->price_gift . '%' . ', ';
                        break;
                    }
                    $msg .= $item->price_gift . ' coin , ';
                    break;
                case CampaignPromotion::TYPE_FREE_CONTENT:
                    if (!$item->content) {
                        break;
                    }
                    if (!$gen) {
                        $itemMessage = $item->content->display_name;
                    } else {
                        $itemMessage = Html::a($item->content->display_name, ["content/view", 'id' => $item->content->id]);
                    }
                    $msg .= $itemMessage . ', ';
                    break;
                case CampaignPromotion::TYPE_FREE_TIME:
                    $itemMessage = $item->time_extend_service;
                    $msg .= $itemMessage . ' ngày, ';
                    break;
            }
        }
        $msg = substr($msg, 0, -2);
        return $msg;

    }

    public function getNumberMonthCondition()
    {
        $lst = $this->campaignConditions;
        if (count($lst) <= 0) {
            return null;
        }
        foreach ($lst as $item) {
            //Chỉ lấy những thằng status = ACTIVE
            if ($item->status != CampaignPromotion::STATUS_ACTIVE) {
                continue;
            }
            if ($item->type == CampaignCondition::TYPE_SERVICE) {
                return $item->number_month;
            }
        }

    }

    public function getNumberMonthPromotion()
    {
        $lst = $this->campaignPromotions;
        if (count($lst) <= 0) {
            return null;
        }
        foreach ($lst as $item) {
            //Chỉ lấy những thằng status = ACTIVE
            if ($item->status != CampaignPromotion::STATUS_ACTIVE) {
                continue;
            }
            if ($item->type == CampaignCondition::TYPE_SERVICE) {
                return $item->number_month;
            }
        }

    }

//    public function beforeSave($insert)
//    {
//        if (parent::beforeSave($insert)) {
//            $this->ascii_name = CVietnameseTools::makeSearchableStr($this->name);
//            return true;
//        } else {
//            return false;
//        }
//    }

    public static function getCampaignByCP($cp_id)
    {
        $list = [];
        $campaign1 = CampaignCondition::find()
            ->select('campaign_condition.campaign_id')
            ->innerJoin('service_cp_asm', 'campaign_condition.service_id = service_cp_asm.service_id')
            ->where(['service_cp_asm.cp_id' => $cp_id])
            ->andWhere(['campaign_condition.status' => CampaignCondition::STATUS_ACTIVE])
            ->andWhere(['service_cp_asm.status' => ServiceCpAsm::STATUS_ACTIVE])
            ->distinct()->all();
        foreach ($campaign1 as $item) {
            $list[] = $item->campaign_id;
        }
        $campaign2 = CampaignPromotion::find()
            ->select('campaign_promotion.campaign_id')
            ->innerJoin('service_cp_asm', 'campaign_promotion.service_id = service_cp_asm.service_id')
            ->where(['service_cp_asm.cp_id' => $cp_id])
            ->andWhere(['campaign_promotion.status' => CampaignPromotion::STATUS_ACTIVE])
            ->andWhere(['service_cp_asm.status' => ServiceCpAsm::STATUS_ACTIVE])
            ->distinct()->all();
        foreach ($campaign2 as $item) {
            $list[] = $item->campaign_id;
        }
        $campaign3 = CampaignPromotion::find()
            ->select('campaign_promotion.campaign_id')
            ->innerJoin('content', 'campaign_promotion.content_id = content.id')
            ->where(['content.cp_id' => $cp_id])
            ->andWhere(['campaign_promotion.status' => CampaignPromotion::STATUS_ACTIVE])
            ->andWhere(['content.status' => Content::STATUS_ACTIVE])
            ->distinct()->all();
        foreach ($campaign3 as $item) {
            $list[] = $item->campaign_id;
        }
        return $list;
    }

    public function visibleNumberMonthCondition()
    {
        $array_type = [self::TYPE_SERVICE_SERVICE, self::TYPE_SERVICE_CONTENT, self::TYPE_SERVICE_TIME];
        if(in_array($this->type,$array_type)){
            return true;
        }
        return false;
    }

    public function visibleNumberMonthPromotion()
    {
        $array_type = [self::TYPE_SERVICE_SERVICE, self::TYPE_BOX_SERVICE, self::TYPE_CASH_SERVICE];
        if(in_array($this->type,$array_type)){
            return true;
        }
        return false;
    }
}
