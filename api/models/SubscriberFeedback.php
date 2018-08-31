<?php
/**
 * Created by PhpStorm.
 * User: Hoan
 * Date: 7/28/2015
 * Time: 6:05 PM
 */

namespace api\models;


use Yii;
use yii\helpers\Url;

class SubscriberFeedback extends \common\models\SubscriberFeedback
{

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['site_id']);
        unset($fields['status_log']);


        $fields['msisdn'] = function ($model) {
            /* @var $model SubscriberFavorite */
            $msisdn = $model->subscriber->msisdn;
            return substr($msisdn, 0, strlen($msisdn) - 3) . 'xxx';
            //return $msisdn;
        };

        $fields['content_name'] = function ($model) {
            /* @var $model SubscriberFavorite */
            return $model->content1 ? $model->content1->display_name : '';
        };
        $fields['images'] = function ($model) {
            /* @var $model SubscriberFeedback */
            $link = [];
            if (!$model->subscriber->avatar_url) {
                return null;
            }
            $listImages = Content::convertJsonToArray($model->content1->images);
            foreach ($listImages as $key => $row) {
                $link[] = [
//                    'link' => Url::to(\Yii::getAlias('@content_images') . '/' . $row['name'], true),
                    'link' => Url::to( Yii::getAlias('@web').DIRECTORY_SEPARATOR.Yii::getAlias('@content_images') . DIRECTORY_SEPARATOR . $row['name'], true),
                    'type' => $row['type']
                ];
            }
            return $link;
        };
        return $fields;
    }
} 