<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;

/**
 * This is the model class for table "{{%service_provider_api_credential}}".
 *
 * @property integer $id
 * @property integer $site_id
 * @property string $client_name
 * @property integer $type
 * @property string $client_api_key
 * @property string $client_secret
 * @property string $description
 * @property integer $status
 * @property string $package_name
 * @property string $certificate_fingerprint
 * @property string $bundle_id
 * @property string $appstore_id
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Site $site
 */
class SiteApiCredential extends \yii\db\ActiveRecord
{

    /**
     * can co api key va secret key
     */
    const TYPE_WEB_APPLICATION = 0;

    /**
     * can co api key, package name va fingerprint
     */
    const TYPE_ANDROID_APPLICATION = 1;

    /**
     * can co api key, secret key, bundle id va appstore id
     */
    const TYPE_IOS_APPLICATION = 2;
    const TYPE_WINDOW_PHONE_APPLICATION = 3;

    const STATUS_INACTIVE= 0;
    const STATUS_ACTIVE = 10;
    const STATUS_REMOVE = -1;

    public static $api_key_types = [
        self::TYPE_WEB_APPLICATION => "Web",
        self::TYPE_ANDROID_APPLICATION => "Android",
        self::TYPE_IOS_APPLICATION => "IOS",
    ];
    public static function getListType(){
        return
            $credential_status = [
                self::TYPE_WEB_APPLICATION => Yii::t('app','Web'),
                self::TYPE_ANDROID_APPLICATION => Yii::t('app','Android'),
                self::TYPE_IOS_APPLICATION => Yii::t('app','IOS'),
            ];
    }

    public static function getListStatus(){
        return
            $credential_status = [
                self::STATUS_INACTIVE => Yii::t('app','Tạm dừng'),
                self::STATUS_ACTIVE => Yii::t('app','Hoạt động'),
            ];
    }

    public static function getListStatusNameByStatus($status){
        $lst = self::getListStatus();
        if (array_key_exists($status, $lst)) {
            return $lst[$status];
        }
        return $status;
    }


    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $tag = Yii::$app->params['key_cache']['ApiKey'] ? Yii::$app->params['key_cache']['ApiKey'] : '';

        TagDependency::invalidate(Yii::$app->cache, $tag);

        return true;
    }

    /**
     * @inheritdoc
     */


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }

    public static function tableName()
    {
        return '{{%site_api_credential}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $webapp = function ($model) {
            return $model->type == self::TYPE_WEB_APPLICATION;
        };
        $android = function ($model) {
            return $model->type == self::TYPE_ANDROID_APPLICATION;
        };
        $ios = function ($model) {
            return $model->type == self::TYPE_IOS_APPLICATION;
        };
        return [
            [['site_id', 'client_name', 'client_api_key'], 'required'],
            [['client_secret'], 'required', 'when' => $webapp],
            [['package_name', 'certificate_fingerprint',], 'required', 'when' => $android],
            [['bundle_id', 'appstore_id', 'client_secret'], 'required', 'when' => $ios],
            [['site_id', 'type', 'status', 'created_at', 'updated_at'], 'integer'],
            [['client_name', 'package_name', 'bundle_id', 'appstore_id'], 'string', 'max' => 200],
            [['client_api_key', 'client_secret'], 'string', 'max' => 128],
            [['description', 'certificate_fingerprint'], 'string', 'max' => 1024]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'site_id' => Yii::t('app', 'Service Provider ID'),
            'client_name' => Yii::t('app', 'Client Name'),
            'type' => Yii::t('app', 'Type'),
            'client_api_key' => Yii::t('app', 'Client Api Key'),
            'client_secret' => Yii::t('app', 'Client Secret'),
            'description' => Yii::t('app', 'Description'),
            'status' => Yii::t('app', 'Status'),
            'package_name' => Yii::t('app', 'Package Name'),
            'certificate_fingerprint' => Yii::t('app', 'Certificate Fingerprint'),
            'bundle_id' => Yii::t('app', 'Bundle ID'),
            'appstore_id' => Yii::t('app', 'Appstore ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Udpated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }

    public static function findCredentialByApiKey($apiKey) {
        return self::findOne(['client_api_key' => $apiKey, 'status' => static::STATUS_ACTIVE]);
    }
}
