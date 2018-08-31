<?php

/**
 * Swiss army knife to work with user and rbac in command line
 * @author: Nguyen Chi Thuc
 * @email: gthuc.nguyen@gmail.com
 */

namespace console\controllers;

use common\helpers\FileUtils;
use common\models\Content;
use common\models\ContentProfile;
use common\models\ContentProfileSiteAsm;
use common\models\ContentSiteAsm;
use Yii;
use yii\console\Controller;

/**
 * SecController create user in commandline
 */
class SecController extends Controller
{
    public function actionDistributeCatchup()
    {
        $site_sec_id = 6;
        $last_time_sync = 1526828553;
        $success_count = 0;
        $fail_count = 0;
        $total_count = 0;
        echo("\n");
        static::log("-------------------Start process with site: $site_sec_id :)----------------------");
        //Tim ban ghi dong bo gan nhat
        /** @var ContentSiteAsm $last_content_site_asm */
        $last_content_site_asm = ContentSiteAsm::find()
            ->joinWith('content')
            ->andWhere(['site_id' => $site_sec_id])
            ->andWhere(['content.type' => Content::TYPE_LIVE_CONTENT])
            ->orderBy(['content_site_asm.created_at' => SORT_DESC])
            ->one();

        if ($last_content_site_asm && $last_time_sync < $last_content_site_asm->created_at)
            $last_time_sync = $last_content_site_asm->created_at;

        static::log("Last time sync: " . $last_time_sync . "(" . date('d/m/Y H:i:s', $last_time_sync) . ")");
        $raw = ContentSiteAsm::find()
            ->joinWith('content')
            ->andWhere(['site_id' => Yii::$app->params['site_id']])
            ->andWhere("content_site_asm.created_at > $last_time_sync")->createCommand()->getRawSql();
        static::log("SQL get all available catchup content: " . $raw);

        /** @var ContentSiteAsm[] $content_site_asm */
        $content_site_asm = ContentSiteAsm::find()
            ->joinWith('content')
            ->andWhere(['site_id' => Yii::$app->params['site_id']])
            ->andWhere("content_site_asm.created_at > $last_time_sync")
            ->all();

        foreach ($content_site_asm as $vn_asm) {

            $total_count++;

            $sec_asm = new ContentSiteAsm();
            $sec_asm->content_id = $vn_asm->content_id;
            $sec_asm->site_id = $site_sec_id;
            $sec_asm->status = $vn_asm->status;
            $sec_asm->pricing_id = $vn_asm->pricing_id;
            $sec_asm->subtitle = $vn_asm->subtitle;
            if ($sec_asm->save()) {
                $success_count++;

                /** @var ContentProfile[] $content_profiles */
                $content_profiles = ContentProfile::find()->andWhere(['content_id' => $vn_asm->content_id])->all();
                foreach ($content_profiles as $content_profile) {
                    /** @var ContentProfileSiteAsm[] $profile_vns */
                    $profile_vns = ContentProfileSiteAsm::find()
                        ->andWhere(['site_id' => Yii::$app->params['site_id']])
                        ->andWhere(['content_profile_id' => $content_profile->id])
                        ->all();

                    foreach ($profile_vns as $profile_vn) {
                        $profile_sec = new ContentProfileSiteAsm();
                        $profile_sec->content_profile_id = $profile_vn->content_profile_id;
                        $profile_sec->site_id = $site_sec_id;
                        $profile_sec->url = $profile_vn->url;
                        $profile_sec->status = $profile_vn->status;
                        $profile_sec->save();
                    }
                }

                static::log("Sync success $success_count content: id is $vn_asm->content_id");
            } else {
                $fail_count++;
                static::log("Sync fail $fail_count content id: $vn_asm->content_id");
            }
        }

        static::log("-------------------Total: $total_count, Success: $success_count, Fail: $fail_count----------------------");
        static::log("-------------------Process end, have nice day :)----------------------");
        echo("\n");
    }

    public function actionSyncStatusProfile()
    {
        $site_sec_id = 6;
        $last_time_sync = 1526828553;

        $success_count = 0;
        $fail_count = 0;
        $total_count = 0;

        static::log("-------------------Start process with site: $site_sec_id :)----------------------");

        $sql = "select content_profile_site_asm.* from content_profile_site_asm, content_profile, content
                where content_profile.id = content_profile_site_asm.content_profile_id 
                and content.id = content_profile.content_id
                and content_profile_site_asm.status = 0 
                and content.type = 8 
                and site_id = $site_sec_id limit 1;";


        /** @var ContentProfileSiteAsm $last_sec_site_asm */
        $last_sec_site_asm = ContentProfileSiteAsm::findBySql($sql)->one();
        if ($last_sec_site_asm) {
            $last_time_sync = $last_sec_site_asm->created_at;
            if (time() - $last_time_sync > 7 * 86400) {
                $last_time_sync = time() - 7 * 86400;
            }
        }
        static::log("Last time sync: " . $last_time_sync . "(" . date('d/m/Y H:i:s', $last_time_sync) . ")");

        if ($last_sec_site_asm) {
            $sql_all = "select content_profile_site_asm.* from content_profile_site_asm, content_profile, content
                where content_profile.id = content_profile_site_asm.content_profile_id 
                and content.id = content_profile.content_id
                and content_profile_site_asm.status = 0 
                and content.type = 8 
                and content_profile_site_asm.created_at >= $last_time_sync
                AND content_profile_site_asm.created_at <= NOW()
                and site_id = $site_sec_id;";

            static::log("SQL get All item: $sql_all");

            /** @var ContentProfileSiteAsm[] $profile_site_need_sync */
            $profile_site_need_sync = ContentProfileSiteAsm::findBySql($sql_all)->all();

            foreach ($profile_site_need_sync as $profile_sec) {
                $total_count++;
                $profile_vn = ContentProfileSiteAsm::findOne([
                    'status' => ContentProfileSiteAsm::STATUS_ACTIVE,
                    'content_profile_id' => $profile_sec->content_profile_id,
                    'site_id' => Yii::$app->params['site_id']]);

                if ($profile_vn) {
                    $profile_sec->url = $profile_vn->url;
                    $profile_sec->status = $profile_vn->status;
                    $profile_sec->save();
                    $success_count++;
                    static::log("Sync success $success_count item - content_profile_site_id:  $profile_sec->id");
                } else {
                    $fail_count++;
                    static::log("Sync fail $fail_count item - content_profile_site_id: $profile_sec->id");
                }

            }
        }
    }

    public static function log($message)
    {
        echo date('Y-m-d H:i:s') . ": " . $message . PHP_EOL;
    }

    public static function infoLog($txt)
    {
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/info_update_allow_content.log'), $txt);
    }

}
