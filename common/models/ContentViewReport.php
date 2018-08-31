<?php

namespace common\models;

use api\helpers\Message;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "{{%content_view_log}}".
 *
 * @property integer $id
 * @property integer $subscriber_id
 * @property integer $content_id
 * @property integer $category_id
 * @property string $msisdn
 * @property integer $created_at
 * @property string $ip_address
 * @property integer $status
 * @property integer $type
 * @property integer $record_type
 * @property string $description
 * @property string $user_agent
 * @property integer $channel
 * @property integer $site_id
 * @property integer $started_at
 * @property integer $stopped_at
 * @property integer $view_date
 * @property integer $view_count
 * @property integer $cp_id
 * @property integer $view_time_date
 *
 * @property Category $category
 * @property Content $content
 * @property Site $site
 * @property Subscriber $subscriber
 */
class ContentViewReport extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%content_view_report}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subscriber_id', 'content_id', 'category_id','view_time_date', 'created_at', 'status', 'type', 'record_type', 'channel', 'site_id', 'view_count', 'started_at', 'stopped_at', 'view_date', 'view_date_max'], 'integer'],
            [['content_id', 'site_id'], 'required'],
            [['description'], 'string'],
            [['msisdn'], 'string', 'max' => 20],
            [['ip_address'], 'string', 'max' => 45],
            [['user_agent'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => \Yii::t('app', 'ID'),
            'subscriber_id' => \Yii::t('app', 'Subscriber ID'),
            'content_id'    => \Yii::t('app', 'Content ID'),
            'category_id'   => \Yii::t('app', 'ID danh mục'),
            'msisdn'        => \Yii::t('app', 'Msisdn'),
            'created_at'    => \Yii::t('app', 'Ngày tạo'),
            'ip_address'    => \Yii::t('app', 'Ip Address'),
            'status'        => \Yii::t('app', 'Trạng thái'),
            'type'          => \Yii::t('app', 'Type'),
            'record_type'   => \Yii::t('app', 'Kiểu ghi'),
            'description'   => \Yii::t('app', 'Mô tả'),
            'user_agent'    => \Yii::t('app', 'User Agent'),
            'channel'       => \Yii::t('app', 'Channel'),
            'site_id'       => \Yii::t('app', 'Site ID'),
            'started_at'    => \Yii::t('app', 'Ngày bắt đầu'),
            'stopped_at'    => \Yii::t('app', 'Ngày kết thúc'),
            'view_date'     => \Yii::t('app', 'Ngày xem'),
            'view_count'    => \Yii::t('app', 'Tổng xem'),
            'cp_id'         => Yii::t('app', 'Content Provider ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->hasOne(Content::className(), ['id' => 'content_id']);
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
    public function getSubscriber()
    {
        return $this->hasOne(Subscriber::className(), ['id' => 'subscriber_id']);
    }

}
