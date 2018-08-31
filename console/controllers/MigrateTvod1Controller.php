<?php
/**
 * Created by PhpStorm.
 * User: bibon
 * Date: 7/25/2016
 * Time: 8:25 AM
 */

namespace console\controllers;


use common\helpers\FileUtils;
use common\models\Site;
use common\models\SiteApiCredential;
use common\models\User;
use Yii;
use yii\console\Controller;

class MigrateTvod1Controller extends Controller
{
    public function actionRun()
    {
        MigrateTvod1Controller::errorLog("============= Bat dau dong bo: " . date("Y-m-d H:i:s", time()) . " ===============\n
        ========================================================");
        $this->createDefaultAdmin();
        $this->createDefaultSite();
        Yii::$app->runAction('migrate-categories/run');
        Yii::$app->runAction('migrate-content/run');
        Yii::$app->runAction('migrate-catchup/run');
        MigrateTvod1Controller::errorLog("========================================================\n
        ===== Ket thuc dong bo: " . date("Y-m-d H:i:s", time()) . " ========");
    }

    private function createDefaultAdmin()
    {
        $admin = User::findOne(['status' => User::STATUS_ACTIVE, 'type' => User::USER_TYPE_ADMIN]);
        if (!$admin) {
            $admin = Yii::$app->runAction('user/create-admin-user', array('admin@tvod.vn', "12345678"));
        }
        return $admin;
    }

    private function createDefaultSite()
    {
        $site = Site::findOne(['status' => Site::STATUS_ACTIVE]);
        if (!$site) {
            $site = new Site();
            $site->name = 'Việt Nam';
            $site->description = "Nhà cung cấp mặc định";
            $site->status = Site::STATUS_ACTIVE;
            $site->service_sms_number = '8x85';
            $site->created_at = time();
            $site->updated_at = time();
            $site->save(false);
            $sp = Yii::$app->runAction('user/create-sp-user', array('spvn1', 'spvn1@tvod.vn', '12345678', $site->id));
            $site->user_admin_id = $sp->id;
            $site->save(false);

            //create api key
            $apiKey = new SiteApiCredential();
            $apiKey->site_id = $site->id;
            $apiKey->client_name = 'Android';
            $apiKey->type = SiteApiCredential::TYPE_ANDROID_APPLICATION;
            $apiKey->client_api_key = 'oor2i3obujgy8eyaa854ar8r0gspmic0';
            $apiKey->client_secret = 'xut5n878hdwfhlfikacksb2jhkyut1kg';
            $apiKey->description = 'Key dành cho Android';
            $apiKey->status = SiteApiCredential::STATUS_ACTIVE;
            $apiKey->package_name = 'TBD';
            $apiKey->certificate_fingerprint = 'TBD';
            $apiKey->created_at = time();
            $apiKey->updated_at = time();
            $apiKey->save();

            $apiKey = new SiteApiCredential();
            $apiKey->site_id = $site->id;
            $apiKey->client_name = 'Web';
            $apiKey->type = SiteApiCredential::TYPE_WEB_APPLICATION;
            $apiKey->client_api_key = '19f7qpvuvl39szgnzc4wtmuzdzpqiej9';
            $apiKey->client_secret = 'eo6u3sfp38omyuzoxuag7v6aklrsq0su';
            $apiKey->description = 'Key dành cho Web';
            $apiKey->status = SiteApiCredential::STATUS_ACTIVE;
            $apiKey->created_at = time();
            $apiKey->updated_at = time();
            $apiKey->save();
        }
        return $site;
    }

    public static function errorLog($txt) {
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/error.log'), $txt);
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/info.log'), $txt);
    }

    public static function infoLog($txt) {
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/info.log'), $txt);
    }
}