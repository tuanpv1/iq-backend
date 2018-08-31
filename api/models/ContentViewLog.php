<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 25/02/2015
 * Time: 9:03 AM
 */

namespace api\models;

use common\models\Content;
use common\models\ContentAttributeValue;
use Yii;
use yii\helpers\Url;

class ContentViewLog extends \common\models\ContentViewLog
{
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['user_agent']);
        unset($fields['view_date']);
        unset($fields['msisdn']);
        unset($fields['status']);
        /** Get thông tin metadata */
        $fields['id'] = function ($model) {
            /* @var $model ContentViewLog */
            return $model->content_id;
        };
        $fields['contentAttributeValues'] = function ($model) {
            $lst = [];
            /**@var $model ContentViewLog */
            if ($model->content->type != Content::TYPE_LIVE && $model->content->type != \common\models\Content::TYPE_LIVE_CONTENT) {
                $contentAttributeValues = ContentAttributeValue::find()
                    ->joinWith('contentAttribute')
                    ->andWhere(['content_id' => $model->content->id])->orderBy(['content_attribute.order' => SORT_DESC])->all();
                foreach ($contentAttributeValues as $contentAttributeValue) {
                    /** @var  $contentAttributeValue ContentAttributeValue */
                    $item = [];
                    $item['id'] = $contentAttributeValue->id;
                    $item['content_id'] = $contentAttributeValue->content_id;
                    $item['content_attribute_id'] = $contentAttributeValue->contentAttribute->id;
                    $item['content_attribute_name'] = $contentAttributeValue->contentAttribute->name;
                    $item['value'] = $contentAttributeValue->value;
                    $lst[] = $item;
                }
            }
            return $lst;
        };

        $fields['feature_title'] = function($model){
            /* @var $model ContentViewLog */
            return  $model->content ? $model->content->feature_title : '';
        };
        $fields['view_date'] = function ($model) {
            /* @var $model ContentViewLog */
            return (int)$model->view_date_max;
        };

        /** Get thông tin metadata */
        $fields['view_count'] = function ($model) {
            /* @var $model ContentViewLog */
            return $model->content ? $model->content->view_count : 0;
        };
        $fields['download_count'] = function ($model) {
            /* @var $model ContentViewLog */
            return $model->content ? $model->content->download_count : 0;
        };
        $fields['like_count'] = function ($model) {
            /* @var $model ContentViewLog */
            return $model->content ? $model->content->like_count : 0;
        };
        $fields['dislike_count'] = function ($model) {
            /* @var $model ContentViewLog */
            return $model->content ? $model->content->dislike_count : 0;
        };
        $fields['rating'] = function ($model) {
            /* @var $model ContentViewLog */
            return $model->content ? $model->content->rating : 0;
        };
        $fields['rating_count'] = function ($model) {
            /* @var $model ContentViewLog */
            return $model->content ? $model->content->rating_count : 0;
        };
        $fields['comment_count'] = function ($model) {
            /* @var $model ContentViewLog */
            return $model->content ? $model->content->comment_count : 0;
        };
        $fields['favorite_count'] = function ($model) {
            /* @var $model ContentViewLog */
            return $model->content ? $model->content->favorite_count : 0;
        };
        $fields['display_name'] = function ($model) {
            /* @var $model ContentViewLog */
            return $model->content ? $model->content->display_name : null;
        };
        $fields['ascii_name'] = function ($model) {
            /* @var $model ContentViewLog */
            return $model->content ? $model->content->ascii_name : null;
        };
        $fields['view_time_date'] = function ($model) {
            /* @var $model ContentViewLog */
            return $model->view_date_max ? (int)$model->view_date_max : 0;
        };

        /** get thêm thông tin detail */
        /** Check free hay không */
        $fields['is_free'] = function ($model) {
            /* @var $model Content */
            return $model->content->getIsFree(Yii::$app->params['site_id']);
        };

        /** Nếu là free thì không hiển thị giá */
        $fields['price_coin'] = function ($model) {
            return $model->content->getPriceCoin(Yii::$app->params['site_id']);
        };
        $fields['price_sms'] = function ($model) {
            return $model->content->getPriceSms(Yii::$app->params['site_id']);
        };
        $fields['watching_period'] = function ($model) {
            return $model->content->getWatchingPriod(Yii::$app->params['site_id']);
        };

        $fields['images'] = function ($model) {
            /* @var $model ContentViewLog */
            $link = [];
            if (!$model->content->images) {
                return null;
            }
            $listImages = Content::convertJsonToArray($model->content->images);
            foreach ($listImages as $key => $row) {
                $link[] = [
//                    'link'=>Url::to(\Yii::getAlias('@content_images') . '/' . $row['name'],true),
                    'link' => Url::to(Yii::getAlias('@web') . DIRECTORY_SEPARATOR . Yii::getAlias('@content_images') . DIRECTORY_SEPARATOR . $row['name'], true),
                    'type' => $row['type']
                ];
            }
            return $link;
        };

        $fields['image'] = function ($model) {
            /* @var $model ContentViewLog */
            $link = '';
            if (!$model->content->images) {
                return null;
            }
            $listImages = $listImages = Content::convertJsonToArray($model->content->images);
            foreach ($listImages as $key => $row) {
//                $link = Url::to(\Yii::getAlias('@content_images') . '/' . $row['name'],true);
                $link = Url::to(Yii::getAlias('@web') . DIRECTORY_SEPARATOR . Yii::getAlias('@content_images') . DIRECTORY_SEPARATOR . $row['name'], true);
            }
            return $link;
        };
        $fields['is_series'] = function ($model) {
            /* @var $model ContentViewLog */
            return $model->content->is_series;
        };
        $fields['episode_count'] = function ($model) {
            /* @var $model \common\models\ContentViewLog */
            return $model->content->getEpisodeCount($model->site_id);
        };
        $fields['episode_order'] = function ($model) {
            /* @var $model SubscriberFavorite */
            return $model->content->episode_order;
        };
        $fields['parent_id'] = function ($model) {
            /* @var $model SubscriberFavorite */
            return $model->content->parent_id;
        };

        $fields['contentAttributeValues'] = function ($model) {
            $lst = [];
            foreach ($model->content->contentAttributeValues as $contentAttributeValue) {
                $item = [];
                $item['id'] = $contentAttributeValue->id;
                $item['content_id'] = $contentAttributeValue->content_id;
                $item['content_attribute_id'] = $contentAttributeValue->contentAttribute->id;
                $item['content_attribute_name'] = $contentAttributeValue->contentAttribute->name;
                $item['value'] = $contentAttributeValue->value;
                $lst[] = $item;
            }
            return $lst;
        };

        return $fields;
    }


}