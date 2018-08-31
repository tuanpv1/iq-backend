<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "sms_support".
 *
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property string $image
 * @property integer $updated_at
 * @property integer $created_at
 * @property string $description
 * @property string $file_user
 * @property string $file_log
 * @property integer $type
 * @property integer $type_campaign
 * @property integer $status
 *
 *
 */
class SmsSupport extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sms_support';
    }

    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 0;

    const TYPE_CSKH_EXTERNAL = 1;
    const TYPE_CSKH_INTERNAL = 2;
    const TYPE_CAMPAIGN = 3;

    //type_campaign
    const TYPE_BOX_SERVICE = 1;
    const TYPE_BOX_CONTENT = 2;
    const TYPE_BOX_CASH = 3;
    const TYPE_CASH_CASH = 4;
    const TYPE_CASH_SERVICE = 5;
    const TYPE_CASH_CONTENT = 6;
    const TYPE_SERVICE_TIME = 7;
    const TYPE_SERVICE_SERVICE = 8;
    const TYPE_SERVICE_CONTENT = 9;
    const TYPE_EVENT = 10;
    const TYPE_REGISTER = 11;

    const PARAM_USERNAME_RECEIVED = '{{username}}'; // tai khoan dc nhan
    const PARAM_SERVICE_GIFT = '{{service}}'; // goi cuoc dc tang
    const PARAM_TIME_GIFT = '{{time}}'; // thoi gian dc tang or thoi gian ap dung khuyen mai
    const PARAM_CONTENT_GIFT = '{{content}}'; // noi dung dc tang
    const PARAM_COIN_GIFT = '{{coin_gift}}'; // don vi dc tang
    const PARAM_COIN_CURRENT = '{{coin_current}}'; //so du tai khoan
    const PARAM_UNIT_GIFT = '{{unit_gift}}'; // don vi cua gia tri dc tang
    const PARAM_NAME_CAMPAIGN = '{{name_campaign}}'; //teen chuong trinh khuyen mai
    const PARAM_SERVICE_BUY = '{{service_buy}}'; // goi cuoc vua mua
    const PARAM_TIME_BUY = '{{time_buy}}'; // thoi gian mua goi cuoc
    const PARAM_TIME_EXPIRED = '{{time_expired}}';// thoi gian het han cua goi cuoc duoc mua

    public static function addSmsSupportByContent($title, $content, $subscriber)
    {
        /** @var $subscriber Subscriber */
        $smsSupport = new SmsSupport();
        $smsSupport->title = $title;
        $smsSupport->content = $content;
        $smsSupport->created_at = time();
        $smsSupport->updated_at = time();
        $smsSupport->status = SmsSupport::STATUS_ACTIVE;
        $smsSupport->type = SmsSupport::TYPE_CSKH_INTERNAL;
        $smsSupport->save(false);
        $smsSupport->save(false);
        $smsUserAsm = new SmsUserAsm();
        $smsUserAsm->user_id = $subscriber->id;
        $smsUserAsm->is_read = 0;
        $smsUserAsm->date_send = time();
        $smsUserAsm->status = SmsUserAsm::STATUS_ACTIVE;
        $smsUserAsm->sms_support_id = $smsSupport->id;
        $smsUserAsm->save();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'type'], 'required'],
            [['content'], 'string'],
            [['updated_at', 'created_at', 'type', 'status'], 'integer'],
            [['title', 'description', 'image', 'file_user', 'file_log'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => Yii::t('app', 'Tiêu đề'),
            'content' => Yii::t('app', 'Nội dung'),
            'image' => Yii::t('app', 'Ảnh đính kèm'),
            'updated_at' => 'Updated At',
            'created_at' => Yii::t('app', 'Ngày gửi'),
            'description' => 'Description',
            'file_user' => Yii::t('app', 'Danh sách gửi'),
            'file_log' => Yii::t('app', 'Log'),
            'type' => 'Type',
            'status' => 'Status',
        ];
    }

    public function getCustomerFile()
    {
        return Yii::getAlias('@file_customer') . DIRECTORY_SEPARATOR . $this->file_user;
    }


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }

    public static function createSmsSupport($title, $content, $image, $file_user, $file_log, $type = SmsSupport::TYPE_CSKH_EXTERNAL, $status = SmsSupport::STATUS_ACTIVE)
    {
        $smsSupport = new SmsSupport();
        $smsSupport->title = $title;
        $smsSupport->status = $status;
        $smsSupport->content = $content;
        $smsSupport->image = $image;
        $smsSupport->type = $type;
        $smsSupport->file_user = $file_user;
        $smsSupport->file_log = $file_log;
        return $smsSupport->save();
    }

    public static function getContentTemplate($id, $msgParam, $param)
    {
        $smsSupport = SmsSupport::findOne(['id' => $id]);
        if ($smsSupport) {
            $msg = $smsSupport->content;
            foreach ($param as $item) {
                if (isset($msgParam[$item])) {
                    $msg = str_replace($item, $msgParam[$item], $msg);
                }
            }
            return [
                'sms' => $msg
            ];
        }
        return ['sms' => ''];
    }

    public static function addSmsSupport($campaign, $subscriber, $service = null)
    {
        /** @var $campaign Campaign */
        /** @var $subscriber Subscriber */
        $smsSupport = new SmsSupport();
        $smsSupport->title = $campaign->notification_title;
        $smsSupport->content = $campaign->notification_content;
        $smsSupport->created_at = time();
        $smsSupport->updated_at = time();
        $smsSupport->status = SmsSupport::STATUS_ACTIVE;
        $smsSupport->type = SmsSupport::TYPE_CSKH_INTERNAL;
        $smsSupport->save(false);
        $smsSupport->content = SmsSupport::buyBoxGift($campaign, $subscriber, $service, $smsSupport->content);
        $smsSupport->save(false);
        $smsUserAsm = new SmsUserAsm();
        $smsUserAsm->user_id = $subscriber->id;
        $smsUserAsm->is_read = 0;
        $smsUserAsm->date_send = time();
        $smsUserAsm->status = SmsUserAsm::STATUS_ACTIVE;
        $smsUserAsm->sms_support_id = $smsSupport->id;
        $smsUserAsm->save();
        return true;
    }

    public static function buyBoxGift($campaign, $subscriber, $service = null, $content)
    {
        /** @var  $campaign Campaign */
        /** @var  $service Service */
        /** @var  $subscriber Subscriber */
        $promotion = CampaignPromotion::find()
            ->andWhere(['campaign_id' => $campaign->id])
            ->andWhere(['status' => CampaignPromotion::STATUS_ACTIVE])
            ->all();
        $content = str_replace(SmsSupport::PARAM_USERNAME_RECEIVED, $subscriber->username, $content);
        $coin = 0;
        foreach ($promotion as $item) {
            /** @var CampaignPromotion $item */
            if ($campaign->type == Campaign::TYPE_BOX_SERVICE || $campaign->type == Campaign::TYPE_CASH_SERVICE || $campaign->type == Campaign::TYPE_SERVICE_SERVICE || $campaign->type == Campaign::TYPE_ACTIVE) {
                $service_expired_date = date('H:i:s d/m/Y', SubscriberServiceAsm::findOne(['subscriber_id' => $subscriber->id, 'service_id' => $item->service_id, 'status' => SubscriberServiceAsm::STATUS_ACTIVE])->expired_at);
                $service_name = Service::findOne(['id' => $item->service_id])->display_name;
                if ($campaign->type == Campaign::TYPE_CASH_SERVICE) {
                    $time = date('H:i:s d/m/Y', SubscriberTransaction::find()->andWhere(['subscriber_id' => $subscriber->id, 'type' => SubscriberTransaction::TYPE_VOUCHER, 'status' => SubscriberTransaction::STATUS_SUCCESS])->orderBy(['transaction_time' => SORT_DESC])->one()->transaction_time);
                    $content = SmsSupport::str_replace_first(SmsSupport::PARAM_TIME_BUY, $time, $content);
                }
                if ($campaign->type == Campaign::TYPE_SERVICE_SERVICE) {
                    /** @var  $subscriberServiceAsm SubscriberServiceAsm */
                    $subscriberServiceAsm = SubscriberServiceAsm::find()->andWhere(['subscriber_id' => $subscriber->id, 'service_id' => $service->id, 'status' => SubscriberServiceAsm::STATUS_ACTIVE])->orderBy(['updated_at' => SORT_DESC])->one();
                    $time = date('H:i:s d/m/Y', $subscriberServiceAsm->updated_at);
                    $time_expired_at = date('H:i:s d/m/Y', $subscriberServiceAsm->expired_at);
                    $service_gift = $service->display_name;
                    $content = SmsSupport::str_replace_first(SmsSupport::PARAM_TIME_BUY, $time, $content);
                    $content = SmsSupport::str_replace_first(SmsSupport::PARAM_TIME_EXPIRED, $time_expired_at, $content);
                    $content = SmsSupport::str_replace_first(SmsSupport::PARAM_SERVICE_BUY, $service_gift, $content);
                }
                $content = SmsSupport::str_replace_first(SmsSupport::PARAM_SERVICE_GIFT, $service_name, $content);
                $content = SmsSupport::str_replace_first(SmsSupport::PARAM_TIME_GIFT, $service_expired_date, $content);
            } else if ($campaign->type == Campaign::TYPE_BOX_CONTENT || $campaign->type == Campaign::TYPE_CASH_CONTENT || $campaign->type == Campaign::TYPE_SERVICE_CONTENT) {
                $content_expired_date = date('H:i:s d/m/Y', SubscriberContentAsm::findOne(['subscriber_id' => $subscriber->id, 'content_id' => $item->content_id, 'status' => SubscriberContentAsm::STATUS_ACTIVE])->expired_at);
                $content_name = Content::findOne(['id' => $item->content_id])->display_name;
                if ($campaign->type == Campaign::TYPE_CASH_CONTENT) {
                    $time = date('H:i:s d/m/Y', SubscriberTransaction::find()->andWhere(['subscriber_id' => $subscriber->id, 'type' => [SubscriberTransaction::TYPE_VOUCHER, SubscriberTransaction::TYPE_VOUCHER_PHONE], 'status' => SubscriberTransaction::STATUS_SUCCESS])->orderBy(['transaction_time' => SORT_DESC])->one()->transaction_time);
                    $content = SmsSupport::str_replace_first(SmsSupport::PARAM_TIME_BUY, $time, $content);
                }
                if ($campaign->type == Campaign::TYPE_SERVICE_CONTENT) {
                    $time = date('H:i:s d/m/Y', SubscriberServiceAsm::find()->andWhere(['subscriber_id' => $subscriber->id, 'service_id' => $service->id, 'status' => SubscriberServiceAsm::STATUS_ACTIVE])->orderBy(['updated_at' => SORT_DESC])->one()->updated_at);
                    $content = SmsSupport::str_replace_first(SmsSupport::PARAM_TIME_BUY, $time, $content);
                    $content = SmsSupport::str_replace_first(SmsSupport::PARAM_SERVICE_BUY, $service->display_name, $content);
                }
                $content = SmsSupport::str_replace_first(SmsSupport::PARAM_CONTENT_GIFT, $content_name, $content);
                $content = SmsSupport::str_replace_first(SmsSupport::PARAM_TIME_GIFT, $content_expired_date, $content);
            } elseif ($campaign->type == Campaign::TYPE_CASH_CASH) {
                $time = date('H:i:s d/m/Y', SubscriberTransaction::find()->andWhere(['subscriber_id' => $subscriber->id, 'type' => [SubscriberTransaction::TYPE_VOUCHER, SubscriberTransaction::TYPE_VOUCHER_PHONE], 'status' => SubscriberTransaction::STATUS_SUCCESS])->orderBy(['transaction_time' => SORT_DESC])->one()->transaction_time);
                if ($item->price_unit == CampaignPromotion::PRICE_UNIT_ITEM) {
                    $coin_gift = $item->price_gift;
                    $unit_gift = 'VND';
                } else {
                    $coin_gift = $item->price_gift;
                    $unit_gift = ' %';
                }
                $coin_current = $subscriber->balance;
                $content = SmsSupport::str_replace_first(SmsSupport::PARAM_TIME_BUY, $time, $content);
                $content = SmsSupport::str_replace_first(SmsSupport::PARAM_COIN_GIFT, $coin_gift, $content);
                $content = SmsSupport::str_replace_first(SmsSupport::PARAM_UNIT_GIFT, $unit_gift, $content);
                $content = SmsSupport::str_replace_first(SmsSupport::PARAM_COIN_CURRENT, $coin_current, $content);
            } elseif ($campaign->type == Campaign::TYPE_BOX_CASH) {
                $coin = $item->price_gift;
            } elseif ($campaign->type == Campaign::TYPE_SERVICE_TIME) {
                $subscriberServiceAsm = SubscriberServiceAsm::find()->andWhere(['subscriber_id' => $subscriber->id, 'service_id' => $service->id, 'status' => SubscriberServiceAsm::STATUS_ACTIVE])->orderBy(['updated_at' => SORT_DESC])->one();
                $time = date('H:i:s d/m/Y', $subscriberServiceAsm->updated_at);
                $time_expired_at = date('H:i:s d/m/Y', $subscriberServiceAsm->expired_at);
                $service_gift = $service->display_name;
                $content = SmsSupport::str_replace_first(SmsSupport::PARAM_TIME_BUY, $time, $content);
                $content = SmsSupport::str_replace_first(SmsSupport::PARAM_SERVICE_BUY, $service_gift, $content);
                $content = SmsSupport::str_replace_first(SmsSupport::PARAM_SERVICE_BUY, $service_gift, $content);
                $content = SmsSupport::str_replace_first(SmsSupport::PARAM_COIN_GIFT, $item->time_extend_service, $content);
                $content = SmsSupport::str_replace_first(SmsSupport::PARAM_TIME_EXPIRED, $time_expired_at, $content);
            } elseif ($campaign->type == Campaign::TYPE_EVENT || $campaign->type == Campaign::TYPE_REGISTER) {
                $content = SmsSupport::str_replace_first(SmsSupport::PARAM_NAME_CAMPAIGN, $campaign->name, $content);
                if ($item->type == CampaignPromotion::TYPE_FREE_SERVICE) {
                    $service_name = Service::findOne($item->service_id)->display_name;
                    $subscriberServiceAsm = SubscriberServiceAsm::find()->andWhere(['subscriber_id' => $subscriber->id, 'service_id' => $item->service_id, 'status' => SubscriberServiceAsm::STATUS_ACTIVE])->orderBy(['updated_at' => SORT_DESC])->one();
                    $time_expired_at = date('H:i:s d/m/Y', $subscriberServiceAsm->expired_at);
                    $content = SmsSupport::str_replace_first(SmsSupport::PARAM_SERVICE_GIFT, $service_name, $content);
                    $content = SmsSupport::str_replace_first(SmsSupport::PARAM_TIME_GIFT, $time_expired_at, $content);
                } elseif ($item->type == CampaignPromotion::TYPE_FREE_CONTENT) {
                    $content_name = Content::findOne($item->content_id)->display_name;
                    $subscriberContentAsm = SubscriberContentAsm::findOne(['subscriber_id' => $subscriber->id, 'content_id' => $item->content_id, 'status' => SubscriberContentAsm::STATUS_ACTIVE]);
                    $time_expired_at = date('H:i:s d/m/Y', $subscriberContentAsm->expired_at);
                    $content = SmsSupport::str_replace_first(SmsSupport::PARAM_CONTENT_GIFT, $content_name, $content);
                    $content = SmsSupport::str_replace_first(SmsSupport::PARAM_TIME_GIFT, $time_expired_at, $content);
                } elseif ($item->type == CampaignPromotion::TYPE_FREE_COIN) {
                    $content = SmsSupport::str_replace_first(SmsSupport::PARAM_COIN_GIFT, $item->price_gift, $content);
                }
            }
        }
        if ($coin > 0) {
            $content = SmsSupport::str_replace_first(SmsSupport::PARAM_COIN_GIFT, $coin, $content);
        }
        return $content;
    }

    public static function str_replace_first($from, $to, $subject)
    {
        $from = '/' . preg_quote($from, '/') . '/';

        return preg_replace($from, $to, $subject, 1);
    }


}
