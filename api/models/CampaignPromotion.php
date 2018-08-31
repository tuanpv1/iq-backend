<?php
/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 30-Mar-17
 * Time: 5:28 PM
 */

namespace api\models;


use common\models\Service;

class CampaignPromotion extends \common\models\CampaignPromotion
{
    public function fields()
    {
        $fields = parent::fields();
        $fields['content_name'] = function ($model) {
            /** @var CampaignPromotion $model */
            if ($model->content_id) {
               return Content::findOne(['id' => $model->content_id])->display_name;
            }
            return null;
        };
        $fields['service_name'] = function ($model) {
            /** @var CampaignPromotion $model */
            if ($model->service_id) {
                return Service::findOne(['id' => $model->service_id])->display_name;
            }
            return null;
        };
        return $fields;
    }
}