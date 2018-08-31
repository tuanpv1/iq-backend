<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 23/05/2015
 * Time: 5:07 PM
 */

namespace api\models;


use api\helpers\UserHelpers;
use common\models\ContentCategoryAsm;
use common\models\Site;
use common\models\Subscriber;
use common\models\SubscriberContentAsm;
use Yii;
use yii\helpers\Url;

class ListContent extends \common\models\Content
{

    public function fields()
    {
        $msisdn = \common\helpers\VNPHelper::getMsisdn(false, true);
        $controller = \Yii::$app->requestedAction->controller;
        /**
         * @var site $sp
         */
        $sp = (isset($controller->site)) ? $controller->site : null;
        \Yii::info($sp);
        $subscriber_id = null;
        if ($msisdn && $sp) {
            $subscriber = Subscriber::findByMsisdn($msisdn, $sp->id);
            if ($subscriber) {
                $subscriber_id = $subscriber->id;
            }
        }
        $fields = parent::fields();
        unset($fields['admin_note']);
        $fields['images'] = function ($model) {
            /* @var $model Content */
            $link = [];
            if (!$model->images) {
                return null;
            }
            $listImages = Content::convertJsonToArray($model->images);
            foreach ($listImages as $key => $row) {
                $link[] = [
//                    'link' => Url::to(\Yii::getAlias('@content_images') . DIRECTORY_SEPARATOR . $row['name'], true),
                    'link' => Url::to( Yii::getAlias('@web').DIRECTORY_SEPARATOR.Yii::getAlias('@content_images') . DIRECTORY_SEPARATOR . $row['name'], true),
                    'type' => $row['type']
                ];
            }
            return $link;
        };
        $fields['is_favorite'] = function ($model) use ($subscriber_id) {
            if ($subscriber_id == null) {
                return false;
            }
            /* @var $model Content */
            $favoriteAsm = $model->subscriberFavorites;
            foreach ($favoriteAsm as $asm) {
                if ($subscriber_id == $asm->subscriber_id) {
                    return true;
                }
            }

            return false;
        };
        $fields['list_category'] = function ($model) {
            /* @var $model \common\models\Content */
            $categoryAsms = $model->contentCategoryAsms;
            $list = [];
            foreach ($categoryAsms as $asm) {
                /** @var $asm ContentCategoryAsm */
                $row = [];
                if ($asm->category->is_content_service == 0) {
                    $row['name'] = $asm->category->display_name;
                    $row['id'] = $asm->category->id;
                    $row['type'] = $asm->category->type;
                    $list[] = $row;
                }
            }
            return $list;
        };

        $fields['is_free'] = function ($model) {
            UserHelpers::manualLogin();
            $subscriber = Yii::$app->user->identity;
            if (!$subscriber) {
                return 0;
            }

            /* @var $model Content */
            /* @var $subscriber Subscriber */

            if($subscriber->checkMyApp($model->id)){
                return 1;
            }else{
                return 0;
            }

        };

        return $fields;
    }
}