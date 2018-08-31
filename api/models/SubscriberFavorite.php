<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 25/02/2015
 * Time: 9:03 AM
 */

namespace api\models;

use common\models\ContentAttributeValue;
use Yii;
use yii\helpers\Url;

class SubscriberFavorite extends \common\models\SubscriberFavorite
{
    public function fields()
    {

        $fields = parent::fields();
        unset($fields['site_id']);
        unset($fields['status']);
        unset($fields['created_at']);
        unset($fields['updated_at']);

        $fields['view_count'] = function ($model) {
            /* @var $model ContentViewLog */
            return $model->content ? $model->content->view_count : 0;
        };

        $fields['display_name'] = function ($model) {
            /* @var $model SubscriberFavorite */
            return $model->content ? $model->content->display_name : '';
        };
        $fields['images'] = function ($model) {
            /* @var $model SubscriberFavorite */
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
            /* @var $model SubscriberFavorite */
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
            /* @var $model SubscriberFavorite */
            return $model->content->is_series;
        };
        $fields['episode_count'] = function ($model) {
            /* @var $model SubscriberFavorite */
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

        return $fields;
    }

}