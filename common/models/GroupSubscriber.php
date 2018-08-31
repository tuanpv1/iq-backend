<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Html;

/**
 * This is the model class for table "group_subscriber".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $file
 * @property string $StringDS
 * @property integer $site_id
 * @property integer $status
 * @property integer $type_import
 * @property integer $last_import_at
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $subscriber_count
 * @property integer $type_subsriber
 * @property integer $client_type
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property CampaignGroupSubscriberAsm[] $campaignGroupSubscriberAsms
 * @property Site $site
 * @property GroupSubscriberUserAsm[] $groupSubscriberUserAsms
 */
class GroupSubscriber extends \yii\db\ActiveRecord
{
    const TYPE_MAC = 1;
    const TYPE_USERNAME = 2;

    const STATUS_ACTIVE = 10;
    const STATUS_DEACTIVE = 0;
    const STATUS_DELETE = 2;

    const TYPE_IMPORT_CSV = 1;
    const TYPE_IMPORT_TAY = 2;

    const CHANNEL_TYPE_ANDROID = 0b000000001;
    const CHANNEL_TYPE_IOS = 0b000000010;
    const CHANNEL_TYPE_WEBSITE = 0b000000100;
    const CHANNEL_TYPE_SMARTBOX = 0b000001000;

    public $StringDS;
    public $fileDS;
    public $channel_type_android = 0;
    public $channel_type_ios = 0;
    public $channel_type_website = 0;
    public $channel_type_smartbox = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'group_subscriber';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['site_id', 'status', 'type_import', 'type_subsriber', 'file'], 'required'],
            [['site_id', 'status', 'type_import', 'client_type', 'last_import_at', 'created_at', 'updated_at', 'subscriber_count', 'type_subsriber'], 'integer'],
            [['name', 'created_by', 'updated_by', 'file'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 500],
            [['site_id'], 'exist', 'skipOnError' => true, 'targetClass' => Site::className(), 'targetAttribute' => ['site_id' => 'id']],
            [['name'], 'required', 'message' => 'Vui lòng nhập trường thông tin bắt buộc'],
            [['StringDS', 'fileDS', 'channel_type_android', 'channel_type_ios', 'channel_type_website', 'channel_type_smartbox'], 'safe'],
            [['fileDS'], 'file', 'extensions' => 'csv', 'wrongExtension' => 'Danh sách khách hàng phải là file ".csv" '],
            [['client_type'], 'required',
                'message' => 'Vui lòng nhập trường thông tin bắt buộc',
                'when' => function ($model) {
                    return $model->type_subsriber == self::TYPE_USERNAME;
                },
                'whenClient' => "function (attribute, value) {
                    return $('#type_subsriber').val() == '" . self::TYPE_USERNAME . "';
                }"
            ],
            [['fileDS'], 'required',
                'message' => 'Vui lòng nhập trường thông tin bắt buộc',
                'on' => 'create',
                'when' => function ($model) {
                    return $model->type_import == self::TYPE_IMPORT_CSV;
                },
                'whenClient' => "function (attribute, value) {
                    return $('#type_import').val() == '" . self::TYPE_IMPORT_CSV . "';
                }"
            ],
            [['StringDS'], 'required',
                'message' => 'Vui lòng nhập trường thông tin bắt buộc',
                'on' => 'create',
                'when' => function ($model) {
                    return $model->type_import == self::TYPE_IMPORT_TAY;
                },
                'whenClient' => "function (attribute, value) {
                    return $('#type_import').val() == '" . self::TYPE_IMPORT_TAY . "';
                }"
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
            'name' => Yii::t('app', 'Tên Nhóm khách hàng'),
            'description' => Yii::t('app', 'Mô tả'),
            'site_id' => Yii::t('app', 'Site ID'),
            'status' => Yii::t('app', 'Status'),
            'type_import' => Yii::t('app', 'Type Import'),
            'last_import_at' => Yii::t('app', 'Last Import At'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'subscriber_count' => Yii::t('app', 'Subscriber Count'),
            'type_subsriber' => Yii::t('app', 'Loại khách hàng'),
            'file' => Yii::t('app', 'file'),
            'client_type' => Yii::t('app', 'Kênh sử dụng'),
            'channel_type_android' => Yii::t('app', 'Ứng dụng Android'),
            'channel_type_ios' => Yii::t('app', 'Ứng dụng IOS'),
            'channel_type_website' => Yii::t('app', 'Web'),
            'channel_type_smartbox' => Yii::t('app', 'SmartBox'),
            'StringDS' => Yii::t('app', 'Danh sách khách hàng'),
            'fileDS' => Yii::t('app', 'Danh sách khách hàng'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCampaignGroupSubscriberAsms()
    {
        return $this->hasMany(CampaignGroupSubscriberAsm::className(), ['group_subscriber_id' => 'id']);
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
    public function getGroupSubscriberUserAsms()
    {
        return $this->hasMany(GroupSubscriberUserAsm::className(), ['group_subscriber_id' => 'id']);
    }

    /**
     * @return array
     */
    public static function listGroupSubscriberTypes()
    {
        $lst = [
            self::TYPE_MAC => Yii::t('app', 'Smartbox'),
            self::TYPE_USERNAME => Yii::t('app', 'Tài khoản TVOD'),
        ];
        return $lst;
    }

    /**
     * @return int
     */
    public function getGroupSubscriberTypeName()
    {
        $lst = self::listGroupSubscriberTypes();
        if (array_key_exists($this->type_subsriber, $lst)) {
            return $lst[$this->type_subsriber];
        }
        return $this->type_subsriber;
    }

    /**
     * @return array
     */
    public static function listImportTypes()
    {
        $lst = [
            self::TYPE_IMPORT_CSV => Yii::t('app', 'Từ file'),
            self::TYPE_IMPORT_TAY => Yii::t('app', 'Nhập tay')
        ];
        return $lst;
    }

    public function isFeature($mask)
    {
        if (($this->client_type & $mask) > 0) {
            return true;
        }
        return false;
    }

    public static function  setClientType($client_type,$model)
    {
        $model->channel_type_android = 0;
        $model->channel_type_ios = 0;
        $model->channel_type_website = 0;
        $model->channel_type_smartbox = 0;
        foreach($client_type as $key => $value){
            if($value == 1){
                $model->channel_type_android = 1;
            }
            if($value == 2){
                $model->channel_type_ios = 1;
            }
            if($value == 3){
                $model->channel_type_website = 1;
            }
            if($value == 4){
                $model->channel_type_smartbox = 1;
            }
        }
    }

    public function loadClientType()
    {
        $this->client_type = 0;
        if ($this->channel_type_android) {
            $this->client_type = $this->client_type | self::CHANNEL_TYPE_ANDROID;
        }
        if ($this->channel_type_ios) {
            $this->client_type = $this->client_type | self::CHANNEL_TYPE_IOS;
        }
        if ($this->channel_type_website) {
            $this->client_type = $this->client_type | self::CHANNEL_TYPE_WEBSITE;
        }
        if ($this->channel_type_smartbox) {
            $this->client_type = $this->client_type | self::CHANNEL_TYPE_SMARTBOX;
        }
    }


    public function parseClientType()
    {
        $this->channel_type_android = $this->isFeature(self::CHANNEL_TYPE_ANDROID);
        $this->channel_type_ios = $this->isFeature(self::CHANNEL_TYPE_IOS);
        $this->channel_type_website = $this->isFeature(self::CHANNEL_TYPE_WEBSITE);
        $this->channel_type_smartbox = $this->isFeature(self::CHANNEL_TYPE_SMARTBOX);
    }

    public function getClientType($model){
        if($model->channel_type_android == 1){
            $arr[0] = 1;
        }
        if($model->channel_type_ios == 1){
            $arr[1] =2;
        }
        if($model->channel_type_website == 1){
            $arr[2] =3;
        }
        if($model->channel_type_smartbox == 1){
            $arr[3] =4;
        }
        $model->client_type = $arr;
    }

    public function getListClientType()
    {
        $this->parseClientType();
        $value = '';
        if ($this->channel_type_android) $value .= "Ứng dụng Android,";
        if ($this->channel_type_ios) $value .= "Ứng dụng IOS,";
        if ($this->channel_type_website) $value .= "Web,";
        if ($this->channel_type_smartbox) $value .= "Smartbox,";
        return $value;
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

    public function deleteGroupSubscriber($id)
    {
        $check_campaign = CampaignGroupSubscriberAsm::findOne(['group_subscriber_id' => $id]);

        if (!$check_campaign) {
            $model = GroupSubscriber::findOne($id);
            $model->status = GroupSubscriber::STATUS_DELETE;

            if ($model->update(true, ['status'])) {
                $dsCustomer = GroupSubscriberUserAsm::findAll(['group_subscriber_id' => $id]);
                if ($dsCustomer) {
                    foreach ($dsCustomer as $item) {
                        $item->status = GroupSubscriberUserAsm::STATUS_DELETE;
                        $item->save();
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function getSiteName($site_id)
    {
        $site = Site::findOne(['id' => $site_id]);
        return $site->name;
    }

    public function getCampaign($group_subscriber_id, $status)
    {
        $campaign = '';
        $checkCampaignAsm = CampaignGroupSubscriberAsm::findAll(['group_subscriber_id' => $group_subscriber_id]);
        if ($checkCampaignAsm) {
            foreach ($checkCampaignAsm as $item) {
                $checkCampaign = Campaign::findOne(['id' => $item->campaign_id, 'status' => $status]);
                if ($checkCampaign) {
                    $campaign .= Html::a($checkCampaign->name, ['campaign/view', 'id' => $checkCampaign->id]) . '; ';
                }
            }
        }
        return $campaign;

    }
}
