<?php
/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 25-Sep-17
 * Time: 11:56 AM
 */

namespace api\models;


use common\models\LogCampaignPromotion;
use Yii;

class Campaign extends  \common\models\Campaign
{
    public function fields() {
        $fields = parent::fields();


        $fields['number_promotion'] = function ($model) {
            /* @var $model Campaign */
            $site_id = $this->site->id;
            $subscriber = Yii::$app->user->identity;
            $countSubscriberLog = LogCampaignPromotion::find()
                ->andWhere(['site_id' => $site_id])
                ->andWhere('type <> :type', ['type' => Campaign::TYPE_ACTIVE])
                ->andWhere(['subscriber_name' => $subscriber->username])
                ->andWhere(['campaign_id' => $model->id])->count();
            return $model->number_promotion - $countSubscriberLog;
        };

        return $fields;
    }
}