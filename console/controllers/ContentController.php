<?php

/**
 * Swiss army knife to work with user and rbac in command line
 * @author: Nguyen Chi Thuc
 * @email: gthuc.nguyen@gmail.com
 */

namespace console\controllers;

use api\helpers\Message;
use common\helpers\CUtils;
use common\helpers\FileUtils;
use common\models\ActorDirector;
use common\models\ApiVersion;
use common\models\Category;
use common\models\Content;
use common\models\ContentCategoryAsm;
use common\models\ContentProfile;
use common\models\ContentSiteAsm;
use common\models\LogSyncContent;
use common\models\ServiceCategoryAsm;
use common\models\Site;
use Yii;
use yii\base\Exception;
use yii\console\Controller;

/**
 * UserController create user in commandline
 */
class ContentController extends Controller
{

    /**
     * @description Gen file karaoke local
     * @param $site_id
     */
    public function actionExportDataToFile($site_id)
    {
        if (!isset($site_id)) {
            $message = '****** SaveData2File ERROR: site_id empty ******';
            echo $message;
            Yii::error($message);
            return false;
        }
        $message = '****** SaveData2File BEGIN ******';
        echo $message;
        Yii::info($message);
        $lst = [];
        $items = Content::find()
            ->joinWith('contentSiteAsms')
            ->andWhere(['content_site_asm.site_id' => $site_id, 'content_site_asm.status' => ContentSiteAsm::STATUS_ACTIVE])
            ->andWhere(['content.type' => Content::TYPE_KARAOKE, 'content.status' => Content::STATUS_ACTIVE])
            ->all();
        /** Nếu không có dữ liệu thì return */
        if (count($items) <= 0) {
            $message = '****** SaveData2File ERROR: ' . Message::getNotDataMessage() . ' ******';
            echo $message;
            Yii::error($message);
            return false;
        }
        /** Get danh sách nội dung đã parse */
        $lst = array();
//        $lst = Content::parseData($items);
        /** @var  $item Content */
        foreach ($items as $item) {
            $group_tmp = $item->getAttributes(['id', 'display_name', 'ascii_name', 'short_description'], ['created_user_id']);
            $tempCat = "";
            $categoryAsms = $item->contentCategoryAsms;
            if (count($categoryAsms) > 0) {
                foreach ($categoryAsms as $asm) {
                    /** @var $asm ContentCategoryAsm */
                    $tempCat .= $asm->category->id . ',';
                }
            }

            /** Cắt xâu */
            if (strlen($tempCat) >= 2) {
                $tempCat = substr($tempCat, 0, -1);
            }
            $group_tmp['categories'] = $tempCat;
            $tempA = "";
            $tempD = "";
            $contentActorDirectorAsms = $item->contentActorDirectorAsms;
            if ($contentActorDirectorAsms) {
                foreach ($contentActorDirectorAsms as $asm) {
                    if ($asm->actorDirector->type == ActorDirector::TYPE_ACTOR) {
                        /** @var $asm ContentCategoryAsm */
                        $tempA .= $asm->actorDirector->id . ',';
                    }
                    if ($asm->actorDirector->type == ActorDirector::TYPE_DIRECTOR) {
                        /** @var $asm ContentCategoryAsm */
                        $tempD .= $asm->actorDirector->id . ',';
                    }
                }
            }
            /** Cắt xâu */
            if (strlen($tempA) >= 2) {
                $tempA = substr($tempA, 0, -1);
            }
            /** Cắt xâu */
            if (strlen($tempD) >= 2) {
                $tempD = substr($tempD, 0, -1);
            }
            $group_tmp['actors'] = $tempA;
            $group_tmp['directors'] = $tempD;

            $strQuality = "";
            $qualities = ContentProfile::find()->andWhere(['content_id' => $item->id, 'type' => ContentProfile::TYPE_CDN])->all();
            if ($qualities) {
                foreach ($qualities as $quality) {
                    $strQuality .= $quality->quality . ',';
                }
            }
            /** Cắt xâu */
            if (strlen($strQuality) >= 2) {
                $strQuality = substr($strQuality, 0, -1);
            }

            $group_tmp['qualities'] = $strQuality;
            $group_tmp['shortname'] = CUtils::parseTitleToKeyword($item->display_name);

            array_push($lst, $group_tmp);
        }

        $res = [
            'success' => true,
            'message' => Message::getSuccessMessage(),
            'totalCount' => count($lst),
            'time_update' => time(),
            "date_expired" => "01/01/2018",
        ];
        $res['items'] = $lst;
        $resJson = json_encode($res);
//        $path = 'backend/web/staticdata/data' . $site_id . '.json';
        $path = "/opt/code/tvod2-backend/" . Yii::getAlias('@staticdata') . DIRECTORY_SEPARATOR . 'data' . $site_id . '.json';
        $save2File = CUtils::writeFile($resJson, $path);
        if ($save2File) {
            $r = ApiVersion::createApiVersion("karaoke", "version karaoke", $site_id, ApiVersion::TYPE_KARAOKE);
            if ($r['success']) {
                $message = '****** SaveData2File SUCCESS: ' . Message::getSuccessMessage() . ' ******';
                echo $message;
                Yii::info($message);
            } else {
                $message = '****** SaveData2File ERROR: khong save duoc apiversion ' . Message::getFailMessage() . ' ******';
                echo $message;
                Yii::error($message);
                return false;
            }

        } else {
            $message = '****** SaveData2File ERROR: không ghi được file ' . Message::getFailMessage() . ' ******';
            echo $message;
            Yii::error($message);
            return false;
        }
        $message = '****** SaveData2File END ******';
        echo $message;
        Yii::info($message);
        return true;
    }

    public function actionAddContentToSite($sites, $cats)
    {
        try {
            $this->writeLog('********** Start add content');

            $sites = explode(",", $sites);
            $cats = explode(",", $cats);

            $contents = ContentCategoryAsm::find()->where(['IN', 'category_id', $cats])->all();

            foreach ($contents as $content) {
                $id = $content->content_id;
                foreach ($sites as $site) {
                    $csa = ContentSiteAsm::findOne(['content_id' => $id, 'site_id' => $site]);
                    if (!$csa) {
                        $content = Content::findOne($id);
                        $status = $content->type == Content::TYPE_LIVE || $content->type == Content::TYPE_NEWS || $content->is_series == Content::IS_SERIES ? ContentSiteAsm::STATUS_ACTIVE : ContentSiteAsm::STATUS_NOT_TRANSFER;

                        $newCsa = new ContentSiteAsm();
                        $newCsa->content_id = $id;
                        $newCsa->site_id = $site;
                        $newCsa->status = $status;
                        if ($newCsa->insert()) {
                            $this->writeLog('Success = ' . $content->display_name);
                        } else {
                            $this->writeLog('Failed = ' . $content->display_name);
                        }
                    }
                }
            }

            $this->writeLog('********** Stop add content');

        } catch (Exception $e) {
            $this->writeLog($e);
        }
    }

    private function writeLog($mes)
    {
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/content-error.log'), date('y-m-d H:i:s') . ' ' . $mes);
    }

    public function actionAddToSite($sites, $cats)
    {
        try {
            $this->writeLog('********** Start add content');

            $sites = explode(",", $sites);
            $cats = explode(",", $cats);

            $retry = Yii::$app->params['retry'];
            $this->writeLog('****' . $retry);
            // tim kiem tat ca content theo id của category
            $contents = Content::find()->innerJoin('content_category_asm', 'content_category_asm.content_id = content.id')
                ->andWhere(['IN', 'content_category_asm.category_id', $cats])->all();

            if ($contents) {

                foreach ($sites as $site) {

                    foreach ($contents as $content_) {
                        $status_asm = null;

                        $checkContentProfile = ContentProfile::find()
                            ->innerJoin('content', 'content.id = content_profile.content_id')
                            ->innerJoin('content_profile_site_asm', 'content_profile_site_asm.content_profile_id = content_profile.id')
                            ->andWhere(['content.id' => $content_->id])->all();
                        $checkContentSiteAsm = ContentSiteAsm::find()->andWhere(['content_id' => $content_->id])
                            ->andWhere(['site_id' => $site])->one();
                        $content = Content::findOne($content_->id);
                        if (!$checkContentSiteAsm) {
                            $checkContentSiteAsm = new ContentSiteAsm();
                            $checkContentSiteAsm->site_id = $site;
                            $checkContentSiteAsm->content_id = $content_->id;
                            $checkContentSiteAsm->created_at = time();
                            $checkContentSiteAsm->updated_at = time();
                            $status = $content->type == Content::TYPE_LIVE || $content->type == Content::TYPE_NEWS || $content->is_series == Content::IS_SERIES ? ContentSiteAsm::STATUS_ACTIVE : ContentSiteAsm::STATUS_NOT_TRANSFER;
                            $checkContentSiteAsm->status = $status;
                            if (!$checkContentSiteAsm->save(false)) {
                                $status_asm = LogSyncContent::STATUS_GAN_ERROR;
                            };
                        } else {
                            if (!$checkContentProfile && $content->type != Content::TYPE_NEWS && $content->type != Content::TYPE_LIVE && $content->is_series != Content::IS_SERIES) {
                                $checkContentSiteAsm->status = ContentSiteAsm::STATUS_NOT_TRANSFER;
                                $checkContentSiteAsm->updated_at = time();
                                $checkContentSiteAsm->save(false);
                            }
                        }
                    }
                }

                foreach ($sites as $site) {
                    // tim server ip chính
                    $primaryServer = Site::find()->andWhere(['status' => Site::STATUS_ACTIVE])
                        ->andWhere(['id' => $site])->one()->primaryStreamingServer;
                    $primaryServer = $primaryServer == null ? null : $primaryServer->id;
                    $site_ = Site::findOne(['id' => $site]);
                    foreach ($contents as $content_) {

                        $checkContentProfile = ContentProfile::find()
                            ->innerJoin('content', 'content.id = content_profile.content_id')
                            ->innerJoin('content_profile_site_asm', 'content_profile_site_asm.content_profile_id = content_profile.id')
                            ->andWhere(['content.id' => $content_->id])->all();

                        // luu lich su dong bo
                        $this->writeLog('====== start content =====' . $content_->id);

                        $checkContentSiteAsm = ContentSiteAsm::find()->andWhere(['content_id' => $content_->id])
                            ->andWhere(['site_id' => $site])->one();
                        $content = Content::findOne($content_->id);


                        $checkLogSyncContent = LogSyncContent::find()
                            ->andWhere(['site_id' => $site])->andWhere(['content_id' => $content_->id])->one();


                        if ($site == Yii::$app->params['site_id']) {
                            if (!$checkLogSyncContent) {
                                $checkLogSyncContent = new LogSyncContent();
                                $checkLogSyncContent->site_id = $site;
                                $checkLogSyncContent->content_id = $content_->id;
                                $checkLogSyncContent->updated_at = time();

                                if ($checkContentProfile) {
                                    $checkLogSyncContent->content_status = LogSyncContent::CONTENT_STATUS_PROFILE;
                                } else {
                                    $checkLogSyncContent->content_status = LogSyncContent::CONTENT_STATUS_NO_PROFILE;
                                }
                                $checkLogSyncContent->created_at = time();
                                $checkLogSyncContent->sync_status = $checkContentSiteAsm->status;
                                $checkLogSyncContent->retry = 0;
                                $checkLogSyncContent->save(false);
                            } else {
                                if ($checkContentProfile) {
                                    $checkLogSyncContent->sync_status = $checkContentSiteAsm->status;
                                } else {
                                    $checkLogSyncContent->sync_status = ContentSiteAsm::STATUS_NOT_TRANSFER;
                                }
                                $checkLogSyncContent->updated_at = time();
                                $checkLogSyncContent->save(false);
                            }

                        } else {
                            if (!$checkLogSyncContent) {
                                $checkLogSyncContent = new LogSyncContent();
                                $checkLogSyncContent->site_id = $site;
                                $checkLogSyncContent->content_id = $content_->id;

                                if ($checkContentProfile) {
                                    $checkLogSyncContent->content_status = LogSyncContent::CONTENT_STATUS_PROFILE;
                                } else {
                                    $checkLogSyncContent->content_status = LogSyncContent::CONTENT_STATUS_NO_PROFILE;
                                }

                                $checkLogSyncContent->created_at = time();
                                $checkLogSyncContent->updated_at = time();
                                $checkLogSyncContent->sync_status = $checkContentSiteAsm->status;
                                $checkLogSyncContent->retry = 0;
                                $checkLogSyncContent->save(false);
                            }

                            //nếu tồn tại content và có trạng thái khác trạng thái phân phối thành công thì tiếp tục flow

                            if ($checkLogSyncContent) {

                                if ($checkContentSiteAsm->status != ContentSiteAsm::STATUS_ACTIVE || $content->type == Content::TYPE_LIVE || $content->type == Content::TYPE_NEWS) {
                                    if ($checkLogSyncContent->retry < $retry) {
                                        //luu content site asm
                                        if ($primaryServer) {

                                            if ($checkContentProfile) {
                                                //goi phan phoi toi site
                                                $sync = Content::syncContentToSite($site, $content_->id, $site_->primary_streaming_server_id, false, true);
                                                if ($sync['success']) {
                                                    $checkLogSyncContent->sync_status = ContentSiteAsm::STATUS_TRANSFERING;
                                                } else {
                                                    $checkLogSyncContent->sync_status = ContentSiteAsm::STATUS_TRANSFER_ERROR;
                                                }
                                                $checkLogSyncContent->content_status = LogSyncContent::CONTENT_STATUS_PROFILE;
                                            } else {
                                                $checkContentSiteAsm->status = ContentSiteAsm::STATUS_NOT_TRANSFER;
                                                $checkLogSyncContent->content_status = LogSyncContent::CONTENT_STATUS_NO_PROFILE;
                                                $checkLogSyncContent->retry = 0;
                                            }

                                            $checkContentSiteAsm->updated_at = time();
                                            //luu lai thoi gian va trang thai content bang contentSiteAsm
                                            $checkContentSiteAsm->save(false);

                                            $checkLogSyncContent->updated_at = time();

                                            //luu lai thoi gian cap nhật và trạng thái của content khi phân phối vào bảng log phân phối
                                            $checkLogSyncContent->save(false);

                                        } else {
                                            if (!$checkContentProfile) {
                                                $checkLogSyncContent->content_status = LogSyncContent::CONTENT_STATUS_NO_PROFILE;
                                                $checkContentSiteAsm->status = ContentSiteAsm::STATUS_NOT_TRANSFER;
                                            } else {
                                                $checkLogSyncContent->content_status = LogSyncContent::CONTENT_STATUS_PROFILE;
                                                $checkContentSiteAsm->status = ContentSiteAsm::STATUS_TRANSFER_ERROR;
                                            }
                                            $checkContentSiteAsm->updated_at = time();
                                            // lưu lại thời gian cập nhật và trạng thái lỗi vào bảng contentSiteAsm
                                            $checkContentSiteAsm->save(false);
                                            $checkLogSyncContent->sync_status = $checkContentSiteAsm->status;
                                            if ($checkContentSiteAsm->status == ContentSiteAsm::STATUS_TRANSFER_ERROR) {
                                                $checkLogSyncContent->retry = 3;
                                            }
                                            $checkLogSyncContent->updated_at = time();

                                            // lưu lại thời gian cập nhật và trạng thái của content khi phân phối vào bảng log phân phối
                                            $checkLogSyncContent->save(false);

                                        }
                                    }
                                } else {
                                    $checkLogSyncContent_ = LogSyncContent::find()
                                        ->andWhere(['site_id' => $site])->andWhere(['content_id' => $content_->id])->one();
                                    $checkLogSyncContent_->updated_at = time();
                                    $checkLogSyncContent_->save(false);
                                }
                            }
                            $this->writeLog('====== end content =====' . $content_->id);
                        }
                    }
                }
            } else {
                $this->writeLog('********** NOT FOUND CONTENT');
            }

        } catch (Exception $e) {
            $this->writeLog($e);
        }
    }

    public
    function actionSysnContent()
    {
        $content = Content::find()->andWhere(['type' => 0])->all();

        foreach ($content as $item) {
            $category_id = Category::find()->andWhere(['id' => $item->default_category_id])->one();
            $content = Content::findOne($item->id);
            $content->type = $category_id->type;
            if ($content->save(false)) {

            }
        }
    }

    /**
     * @param $min
     * @param $max
     * @description php yii content/fake-view $min $max
     */
    public function actionFakeView($min, $max)
    {
        $success_count = 0;
        $fail_count = 0;
        $i = 0;
        echo "Start update \n";
        $contents = Content::find()->orderBy(['id' => SORT_ASC])->batch(100);
        foreach ($contents as $contentsBatch) {
            foreach ($contentsBatch as $content) {
                $content->view = rand($min, $max);
                $i++;
                if ($content->save()) {
                    $success_count++;
                } else {
                    $fail_count++;
                }
                if ($i % 1000 == 0) {
                    echo "$success_count/$i content(s) updated view count \n";
                }
            }

        }
        echo "End update \n";
    }

    // TuanPV add 02/10/2017 update logic khi bo danh muc ra khoi goi cuoc
    public function actionUpdateAllowContent($category, $service_id)
    {
        $category = json_decode($category);
        $this->infoLog(date('d-m-Y H:i:s') . ' BAT DAU TIM ');
        foreach ($category as $item) {
            $this->infoLog(date('d-m-Y H:i:s') . ' Danh muc co id = ' . $item);
            $contents = Content::find()
                ->innerJoin('content_category_asm', 'content.id = content_category_asm.content_id')
                ->andWhere(['content_category_asm.category_id' => $item])
                ->andWhere(['content.allow_buy_content' => Content::NOT_ALLOW_BUY_CONTENT])
                ->all();
            if ($contents) {
                foreach ($contents as $content) {
                    /** @var  Content $content */
                    $this->infoLog(date('d-m-Y H:i:s') . ' Co noi dung id = ' . $content->id);
                    $this->infoLog(date('d-m-Y H:i:s') . ' Kiem tra noi dung co thuoc gi khac khong ');
                    $check = Content::checkContentInOtherService($content->id, $service_id, true);
                    if(!$check){
                        $content->allow_buy_content = Content::ALLOW_BUY_CONTENT;
                        if (!$content->update()) {
                            $this->infoLog(date('d-m-Y H:i:s') . ' Khong cap nhat duoc noi dung id = ' . $content->id);
                        }
                    }else{
                        $this->infoLog(date('d-m-Y H:i:s') . ' Noi dung da thuoc goi cuoc '.$service_id.' khong chuye');
                    }
                }
            }
            $this->infoLog(date('d-m-Y H:i:s') . ' Khong tim thay noi dung voi danh muc id = ' . $item);
        }
        $this->infoLog(date('d-m-Y H:i:s') . ' KET THUC');
    }

    public static function infoLog($txt)
    {
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/info_update_allow_content.log'), $txt);
    }

}
