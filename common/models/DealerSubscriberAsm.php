<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "dealer_subscriber_asm".
 *
 * @property integer $id
 * @property integer $dealer_id
 * @property integer $subscriber_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $site_id
 *
 * @property Dealer $dealer
 * @property Subscriber $subscriber
 * @property Site $site
 */
class DealerSubscriberAsm extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dealer_subscriber_asm';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dealer_id', 'subscriber_id', 'site_id'], 'required'],
            [['dealer_id', 'subscriber_id', 'created_at', 'updated_at', 'site_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('app', 'ID'),
            'dealer_id' => \Yii::t('app', 'ID Đại lý'),
            'subscriber_id' => \Yii::t('app', 'ID Thuê bao'),
            'created_at' => \Yii::t('app', 'Ngày tạo'),
            'updated_at' => \Yii::t('app', 'Ngày thay đổi thông tin'),
            'site_id' => \Yii::t('app', 'Site ID'),
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
}
