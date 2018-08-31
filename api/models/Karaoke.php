<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 25/02/2015
 * Time: 9:03 AM
 */

namespace api\models;


use common\helpers\CUtils;
use common\models\ActorDirector;
use common\models\ContentCategoryAsm;
use common\models\ContentProfile;
use common\models\ContentSiteAsm;
use Yii;


class Karaoke extends \common\models\Content
{
    public function fields()
    {
//        $fields = parent::fields();
//        unset($fields['display_name']);
//        unset($fields['ascii_name']);
//        unset($fields['short_description']);
//
//        unset($fields['code']);
//        unset($fields['type']);
//        unset($fields['tags']);
//        unset($fields['description']);
//        unset($fields['content']);
//        unset($fields['duration']);
//        unset($fields['urls']);
//        unset($fields['view_count']);
//        unset($fields['download_count']);
//        unset($fields['like_count']);
//        unset($fields['dislike_count']);
//        unset($fields['rating']);
//        unset($fields['rating_count']);
//        unset($fields['comment_count']);
//        unset($fields['favorite_count']);
//        unset($fields['is_catchup']);
//        unset($fields['images']);
//        unset($fields['status']);
//        unset($fields['created_at']);
//        unset($fields['updated_at']);
//        unset($fields['honor']);
//        unset($fields['approved_at']);
//        unset($fields['admin_note']);
//        unset($fields['is_series']);
//        unset($fields['episode_count']);
//        unset($fields['episode_order']);
//        unset($fields['parent_id']);
//        unset($fields['created_user_id']);
//        unset($fields['day_download']);
//        unset($fields['author']);
//        unset($fields['country']);
//        unset($fields['language']);
//        unset($fields['view_date']);
//        unset($fields['catchup_id']);
//        unset($fields['origin_url']);
//        unset($fields['default_site_id']);
//        unset($fields['default_category_id']);
//        unset($fields['en_name']);
//        unset($fields['order']);
//        unset($fields['updated_tvod1']);
//
//        unset($fields['tvod1_id']);
//        unset($fields['version_code']);
//        unset($fields['version']);
//        /** Bỏ 2 trường này thừa thiết kế */
//        unset($fields['actor']);
//        unset($fields['director']);

        $fields = array();
        $fields['id'] = function ($model) {
            return $this->id;
        };

        $fields['type_content'] = function ($model) {
//            $site_id = Yii::$app->params['site_id'];
            $site_id = Yii::$app->requestedAction->controller->site->id;
            $TYPE_CREATED= 1;
            $TYPE_DELETED= 2;
            $TYPE_UPDATED= 3;

            if($this->status != Content::STATUS_ACTIVE){;
                return $TYPE_DELETED;
            }
            foreach ($this->contentSiteAsms as $csa){
                if($csa->site_id == $site_id){
                    if($csa->status != ContentSiteAsm::STATUS_ACTIVE){
                        return $TYPE_DELETED;
                    }
                    if($this->created_at == $this->updated_at){
                        return $TYPE_CREATED;
                    }else{
                        return $TYPE_UPDATED;
                    }
                }
            }
        };

        $fields['display_name'] = function ($model) {
            return $this->display_name;
        };
        $fields['ascii_name'] = function ($model) {
            return $this->ascii_name;
        };
        $fields['short_description'] = function ($model) {
            return $this->short_description;
        };


        /** Lấy thông tin bảng quan hệ */
        $fields['categories'] = function ($model) {
            $tempCat = "";
            $categoryAsms = $this->contentCategoryAsms;
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
            return $tempCat;
        };

        $fields['actors'] = function ($model) {
            $tempA = "";
            $contentActorDirectorAsms = $this->contentActorDirectorAsms;
            if ($contentActorDirectorAsms) {
                foreach ($contentActorDirectorAsms as $asm) {
                    if ($asm->actorDirector->type == ActorDirector::TYPE_ACTOR) {
                        /** @var $asm ContentCategoryAsm */
                        $tempA .= $asm->actorDirector->id . ',';
                    }
                }
            }
            /** Cắt xâu */
            if (strlen($tempA) >= 2) {
                $tempA = substr($tempA, 0, -1);
            }
            return $tempA;
        };

        $fields['directors'] = function ($model) {
            $tempD = "";
            $contentActorDirectorAsms = $this->contentActorDirectorAsms;
            if ($contentActorDirectorAsms) {
                foreach ($contentActorDirectorAsms as $asm) {
                    if ($asm->actorDirector->type == ActorDirector::TYPE_DIRECTOR) {
                        /** @var $asm ContentCategoryAsm */
                        $tempD .= $asm->actorDirector->id . ',';
                    }
                }
            }
            /** Cắt xâu */
            if (strlen($tempD) >= 2) {
                $tempD = substr($tempD, 0, -1);
            }
            return $tempD;
        };

        $fields['qualities'] = function ($model) {
            $strQuality = "";
            $qualities = ContentProfile::find()->andWhere(['content_id' => $this->id, 'type' => ContentProfile::TYPE_CDN])->all();
            if ($qualities) {
                foreach ($qualities as $quality) {
                    $strQuality .= $quality->quality . ',';
                }
            }

            /** Cắt xâu */
            if (strlen($strQuality) >= 2) {
                $strQuality = substr($strQuality, 0, -1);
            }

            return $strQuality;
        };

        $fields['shortname'] = function ($model) {
            return CUtils::parseTitleToKeyword($this->display_name);
        };

        return $fields;
    }




}