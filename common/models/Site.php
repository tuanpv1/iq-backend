<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "{{%service_provider}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $status
 * @property integer $type
 * @property string $website
 * @property double $cp_revernue_percent
 * @property integer $user_admin_id
 * @property string $service_brand_name
 * @property string $service_sms_number
 * @property string $currency
 * @property integer $free_video_count
 * @property integer $free_video_cycle
 * @property integer $default_service_id
 * @property integer $default_price_content_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $primary_streaming_server_id
 *
 *
 * @property Ads[] $ads
 * @property AppAds[] $appAds
 * @property Category[] $categories
 * @property Content[] $contents
 * @property ContentFeedback[] $contentFeedbacks
 * @property ContentKeyword[] $contentKeywords
 * @property Dealer[] $dealers
 * @property ContentSiteAsm[] $contentSiteAsms
 * @property ServiceGroup[] $serviceGroups
 * @property SiteApiCredential[] $siteApiCredentials
 * @property SiteStreamingServerAsm[] $siteStreamingServerAsms
 * @property ContentViewLog[] $contentViewLogs
 * @property ReportMonthlyCpRevenue[] $reportMonthlyCpRevenues
 * @property ReportMonthlyCpRevenueDetail[] $reportMonthlyCpRevenueDetails
 * @property Service[] $services
 * @property SmsMessage[] $smsMessages
 * @property SmsMoSyntax[] $smsMoSyntaxes
 * @property SmsMtTemplateContent[] $smsMtTemplateContents
 * @property Subscriber[] $subscribers
 * @property SubscriberActivity[] $subscriberActivities
 * @property SubscriberContentAsm[] $subscriberContentAsms
 * @property SubscriberFavorite[] $subscriberFavorites
 * @property SubscriberFeedback[] $subscriberFeedbacks
 * @property SubscriberServiceAsm[] $subscriberServiceAsms
 * @property SubscriberTransaction[] $subscriberTransactions
 * @property User[] $users
 * @property UserActivity[] $userActivities
 * @property User $userAdmin
 * @property Service $defaultService
 * @property Pricing $defaultContentPrice
 */
class Site extends \yii\db\ActiveRecord
{
    public $subtitle;

    const SITE_VIETNAM = 5;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 10;
    const STATUS_REMOVE = -1;

    public static function getListStatus()
    {
        return
        $sp_status = [
            self::STATUS_ACTIVE   => Yii::t('app', 'Hoạt động'),
            self::STATUS_INACTIVE => Yii::t('app', 'Tạm ngừng'),
        ];
    }

    public static function getListStatusNameByStatus($status)
    {
        $lst = self::getListStatus();
        if (array_key_exists($status, $lst)) {
            return $lst[$status];
        }
        return $status;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%site}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }

    /**
     * @return Query
     *
     */
    public static function find()
    {
//        # return parent::find()->where(['store_id' => User::getStoreID()]);
        return parent::find()->where('site.status != :status', ['status' => self::STATUS_REMOVE]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'currency', 'status'], 'required'],
            [['created_at', 'updated_at', 'status', 'user_admin_id', 'default_service_id', 'default_price_content_id', 'primary_streaming_server_id'], 'integer'],
            [['description'], 'string'],
            [['cp_revernue_percent'], 'number'],
            [['name'], 'string', 'max' => 200],
            [['service_brand_name', 'service_sms_number'], 'string', 'max' => 126],
            [['currency'], 'string', 'max' => 4],
            [['website'], 'string', 'max' => 255],
            [['name'], 'validateUnique'],
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
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'updated_at' => Yii::t('app', 'Ngày cập nhật'),
            'description' => Yii::t('app', 'Mô tả'),
            'status' => Yii::t('app', 'Trạng thái'),
            'website' => Yii::t('app', 'Website '),
            'cp_revernue_percent' => Yii::t('app', 'Phân chia doanh thu đại lý'),
            'user_admin_id' => Yii::t('app', 'User admin'),
            'currency' => Yii::t('app', 'Đơn vị tiền tệ'),
            'default_price_content_id' => Yii::t('app', 'Giá mặc định'),
            'default_service_id' => Yii::t('app', 'Gói cước mặc định'),
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
        if ($attribute == 'name') {
            $site = Site::find()->andFilterWhere(['name' => $this->name])->andFilterWhere(['not', ['status' => Dealer::STATUS_DELETED]])->andFilterWhere(['not', ['id' => $this->id]])->one();
            if ($site) {
                $this->addError($attribute, Yii::t('app', 'Tên nhà cung cấp dịch vụ đã tồn tại, vui lòng chọn một tên khác'));
            }
        }
//        }
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $tag = Yii::$app->params['key_cache']['SiteID'] ? Yii::$app->params['key_cache']['SiteID'] : '';

            TagDependency::invalidate(Yii::$app->cache, $tag);
            return true;
        } else {
            return false;
        }
    }

    public static function listSite()
    {
        $objs = Site::find()->andWhere(['status' => Site::STATUS_ACTIVE])->all();
        $lst = [];
        foreach ($objs as $obj) {
            $lst[$obj->id] = $obj->name;
        }
        return $lst;

    }

    public static function getAllSites()
    {
        $data = array();
        $list = Site::findBySql("select s.id, s.name from site s where s.status = " . self::STATUS_ACTIVE)->all();
        foreach ($list as $item) {
            $data[$item['id']] = $item['name'];
        }

        return $data;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContents()
    {
        return $this->hasMany(Content::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentFeedbacks()
    {
        return $this->hasMany(ContentFeedback::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentKeywords()
    {
        return $this->hasMany(ContentKeyword::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDealer()
    {
        return $this->hasMany(Dealer::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentViewLogs()
    {
        return $this->hasMany(ContentViewLog::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReportMonthlyCpRevenues()
    {
        return $this->hasMany(ReportMonthlyCpRevenue::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReportMonthlyCpRevenueDetails()
    {
        return $this->hasMany(ReportMonthlyCpRevenueDetail::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(Service::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscribers()
    {
        return $this->hasMany(Subscriber::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberActivities()
    {
        return $this->hasMany(SubscriberActivity::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberContentAsms()
    {
        return $this->hasMany(SubscriberContentAsm::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberFavorites()
    {
        return $this->hasMany(SubscriberFavorite::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberFeedbacks()
    {
        return $this->hasMany(SubscriberFeedback::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberServiceAsms()
    {
        return $this->hasMany(SubscriberServiceAsm::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberTransactions()
    {
        return $this->hasMany(SubscriberTransaction::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserActivities()
    {
        return $this->hasMany(UserActivity::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserAdmin()
    {
        return $this->hasOne(User::className(), ['id' => 'user_admin_id']);
    }

    public function getDefaultService()
    {
        return $this->hasOne(Service::className(), ['id' => 'default_service_id']);
    }

    public function getDefaultContentPrice()
    {
        return $this->hasOne(Pricing::className(), ['id' => 'default_price_content_id']);
    }

    public function getPrimaryStreamingServer()
    {
        return $this->hasOne(StreamingServer::className(), ['id' => 'primary_streaming_server_id']);
    }

    public static function getSite($id = null)
    {
        $res = Site::find();
        if ($id) {
            $res->andWhere(['id' => $id]);
        }
        $res->andWhere(['status' => Site::STATUS_ACTIVE]);
        $dataProvider = new ActiveDataProvider([
            'query' => $res,
            'sort' => [],
            'pagination' => [
                'defaultPageSize' => 10,
            ],
        ]);
        return $dataProvider;
    }

    public static function getSiteList($id = null, $listFieldSelect = [], $contentId = null)
    {
        if ($id == null) {
            $site = Site::find()->andWhere(['site.status' => Site::STATUS_ACTIVE])
                ->all();

            if ($contentId) {
                $csa = ArrayHelper::map(ContentSiteAsm::findAll(['content_id' => $contentId]), 'site_id', 'subtitle');
            }
        } else {
            $site = Site::findOne(['id' => $id]);
        }
        if (count($listFieldSelect) > 0 && count($listFieldSelect) === 2) {
            $output = [];
            // var_dump($csa);die;
            foreach ($site as $v) {
                if ($contentId && isset($csa[$v->id])) {
                    $output[$v[$listFieldSelect[0]]] = $v[$listFieldSelect[1]] . ' --- Subtitle: ' . Html::a($csa[$v->id], \Yii::getAlias('@subtitle') . '/' . $csa[$v->id]);
                } else {
                    $output[$v[$listFieldSelect[0]]] = $v[$listFieldSelect[1]];
                }
            }
        } else {
            $output = $site;
        }
        return $output;
    }

    public static function getDealers($id)
    {
        return Dealer::find()
            ->select(['id', 'name'])
            ->where(['site_id' => $id, 'status' => Dealer::STATUS_ACTIVE])
            ->all();
    }

}
