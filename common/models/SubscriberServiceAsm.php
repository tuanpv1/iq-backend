<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%subscriber_service_asm}}".
 *
 * @property integer $id
 * @property integer $service_id
 * @property integer $subscriber_id
 * @property integer $site_id
 * @property integer $dealer_id
 * @property string $msisdn
 * @property string $service_name
 * @property string $description
 * @property integer $activated_at
 * @property integer $renewed_at
 * @property integer $expired_at
 * @property integer $last_renew_fail_at
 * @property integer $renew_fail_count
 * @property integer $first_renew_fail_at
 * @property integer $today_retry_count
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $pending_date
 * @property integer $view_count
 * @property integer $download_count
 * @property integer $gift_count
 * @property integer $watching_time
 * @property integer $subscriber2_id
 * @property integer $transaction_id
 * @property integer $cancel_transaction_id
 * @property integer $last_renew_transaction_id
 * @property integer $canceled_at
 * @property integer $auto_renew
 * @property integer $white_list
 * @property integer $number_buy_month
 * @property integer $number_gift_month
 *
 * @property Dealer $dealer
 * @property Service $service
 * @property Subscriber $subscriber
 * @property Site $site
 * @property Subscriber $subscriber2
 * @property SubscriberTransaction $transaction
 * @property SubscriberTransaction $cancelTransaction
 * @property SubscriberTransaction $lastRenewTransaction
 * @property SubscriberTransaction[] $subscriberTransactions
 */
class SubscriberServiceAsm extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 0;
    const STATUS_PENDING = 2;
    const STATUS_RESTORE = 3;

    public $from_date;
    public $to_date;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%subscriber_service_asm}}';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_id', 'subscriber_id', 'site_id', 'activated_at', 'auto_renew'], 'required'],
            [['canceled_at', 'service_id', 'white_list', 'auto_renew', 'subscriber_id', 'site_id', 'dealer_id', 'activated_at', 'renewed_at', 'expired_at', 'last_renew_fail_at', 'renew_fail_count', 'status', 'created_at', 'updated_at', 'pending_date', 'view_count', 'download_count', 'gift_count', 'watching_time', 'subscriber2_id', 'transaction_id', 'cancel_transaction_id', 'last_renew_transaction_id',
                'first_renew_fail_at', 'today_retry_count', 'number_gift_month', 'number_buy_month'], 'integer'],
            [['description', 'from_date', 'to_date'], 'string'],
            [['msisdn'], 'string', 'max' => 20],
            [['service_name'], 'string', 'max' => 45],
            ['white_list', 'default', 'value' => Subscriber::NOT_WHITELIST]
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'service_id' => Yii::t('app', 'Service ID'),
            'subscriber_id' => Yii::t('app', 'Subscriber ID'),
            'site_id' => Yii::t('app', 'Service Provider ID'),
            'dealer_id' => Yii::t('app', 'Dealer ID'),
            'msisdn' => Yii::t('app', 'Msisdn'),
            'service_name' => Yii::t('app', 'Service Name'),
            'description' => Yii::t('app', 'Mô tả'),
            'activated_at' => Yii::t('app', 'Activated At'),
            'renewed_at' => Yii::t('app', 'Renewed At'),
            'expired_at' => Yii::t('app', 'Expired At'),
            'last_renew_fail_at' => Yii::t('app', 'Last Renew Fail At'),
            'renew_fail_count' => Yii::t('app', 'Renew Fail Count'),
            'status' => Yii::t('app', 'Trạng thái'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'updated_at' => Yii::t('app', 'Ngày thay đổi thông tin'),
            'pending_date' => Yii::t('app', 'Pending Date'),
            'view_count' => Yii::t('app', 'Tổng xem'),
            'download_count' => Yii::t('app', 'Tổng lượt tải'),
            'gift_count' => Yii::t('app', 'Gift Count'),
            'watching_time' => Yii::t('app', 'Thời gian xem'),
            'subscriber2_id' => Yii::t('app', 'Subscriber2 ID'),
            'transaction_id' => Yii::t('app', 'Transaction ID'),
            'cancel_transaction_id' => Yii::t('app', 'Cancel Transaction ID'),
            'last_renew_transaction_id' => Yii::t('app', 'Last Renew Transaction ID'),
            'white_list' => Yii::t('app', 'White list'),
            'number_buy_month' => Yii::t('app', 'Đã mua'),
            'number_gift_month' => Yii::t('app', 'Khuyến mãi'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDealer()
    {
        return $this->hasOne(Dealer::className(), ['id' => 'dealer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriber()
    {
        return $this->hasOne(Subscriber::className(), ['id' => 'subscriber_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriber2()
    {
        return $this->hasOne(Subscriber::className(), ['id' => 'subscriber2_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransaction()
    {
        return $this->hasOne(SubscriberTransaction::className(), ['id' => 'transaction_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCancelTransaction()
    {
        return $this->hasOne(SubscriberTransaction::className(), ['id' => 'cancel_transaction_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLastRenewTransaction()
    {
        return $this->hasOne(SubscriberTransaction::className(), ['id' => 'last_renew_transaction_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberTransactions()
    {
        return $this->hasMany(SubscriberTransaction::className(), ['subscriber_service_asm_id' => 'id']);
    }

    /**
     * ******************************** MY FUNCTION ***********************
     */

    /**
     * @return array
     */
    public static function listStatus()
    {
        $lst = [
            self::STATUS_ACTIVE => Yii::t('app', 'Active'),
            self::STATUS_INACTIVE => Yii::t('app', 'Inactive'),
            self::STATUS_PENDING => Yii::t('app', 'Pending'),
            self::STATUS_RESTORE => Yii::t('app', 'Restore'),
        ];
        return $lst;
    }

    /**
     * @return int
     */
    public function getStatusName()
    {
        $lst = self::listStatus();
        if (array_key_exists($this->status, $lst)) {
            return $lst[$this->status];
        }
        return $this->status;
    }

    /**
     * @param $subscriber Subscriber
     * @param $service Service
     * @return SubscriberServiceAsm|null
     */
    public static function createNewMapping($subscriber, $service)
    {
        $mapping = new SubscriberServiceAsm();
        $mapping->subscriber_id = $subscriber->id;
        $mapping->service_id = $service->id;
        $mapping->activated_at = time();
        $mapping->expired_at = time();
        $mapping->created_at = time();
        $mapping->auto_renew = Service::TYPE_AUTO_RENEW;
        $mapping->download_count = ($service->free_download_count) ? $service->free_download_count : 0;
        if ($mapping->save()) {
            return $mapping;
        } else {
            Yii::error($mapping->getErrors());
            return null;
        }
    }

    public static function findSubcriberService($subscriber_id, $service_id)
    {
        return SubscriberServiceAsm::find()
            ->andWhere(['subscriber_id' => $subscriber_id, 'service_id' => $service_id, 'status' => SubscriberServiceAsm::STATUS_ACTIVE])
            ->andWhere(['>=', 'expired_at', time()])
            ->one();
    }

}
