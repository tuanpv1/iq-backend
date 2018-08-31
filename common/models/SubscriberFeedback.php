<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * This is the model class for table "{{%subscriber_feedback}}".
 *
 * @property integer $id
 * @property integer $content_id
 * @property integer $subscriber_id
 * @property string $content
 * @property string $title
 * @property integer $create_date
 * @property integer $status
 * @property string $status_log
 * @property integer $is_responsed
 * @property string $response_date
 * @property integer $response_user_id
 * @property string $response_detail
 * @property integer $site_id
 *
 * @property Subscriber $subscriber
 * @property Content $content1
 * @property ServiceProvider $serviceProvider
 */
class SubscriberFeedback extends \yii\db\ActiveRecord
{
    public $from_date;
    public $to_date;
//    public $sp_id;
//    public $sub_id;
//    public $cont_id;
    /**
     * @inheritdoc
     */
    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 0;
    const STATUS_SPENDING = 1;

    public static function tableName()
    {
        return '{{%subscriber_feedback}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subscriber_id', 'content', 'status', 'site_id'], 'required'],
            [['subscriber_id', 'status', 'is_responsed', 'response_user_id', 'site_id', 'content_id'], 'integer'],
            [['response_date'], 'safe'],
            [['create_date'], 'integer'],
            [['status_log'], 'string'],
            [['content', 'response_detail'], 'string', 'max' => 5000],
            [['title'], 'string', 'max' => 500]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'subscriber_id' => Yii::t('app', 'Subscriber ID'),
            'content' => Yii::t('app', 'Content'),
            'title' => Yii::t('app', 'Title'),
            'create_date' => Yii::t('app', 'Ngày tạo'),
            'status' => Yii::t('app', 'Trạng thái'),
            'status_log' => Yii::t('app', 'Status Log'),
            'is_responsed' => Yii::t('app', 'Is Responsed'),
            'response_date' => Yii::t('app', 'Response Date'),
            'response_user_id' => Yii::t('app', 'Response User ID'),
            'response_detail' => Yii::t('app', 'Response Detail'),
            'site_id' => Yii::t('app', 'Service Provider ID'),
        ];
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
    public function getContent1()
    {
        return $this->hasOne(Content::className(), ['id' => 'content_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceProvider()
    {
        return $this->hasOne(ServiceProvider::className(), ['id' => 'site_id']);
    }

    /**
     * HungNV 14-April
     *
     * @param $subscriber
     * @param $site_id
     * @param null $content_id
     * @param $title
     * @param $content
     * @return bool
     */
    public static function createFeedback($subscriber, $site_id, $content_id = null, $title, $content)
    {
        $feedback = new SubscriberFeedback();
        $feedback->subscriber_id = $subscriber->id;
        $feedback->site_id = $site_id;
        if ($content_id) {
            $feedback->content_id = $content_id;
        }
        $feedback->title = $title;
        $feedback->content = $content;
        $feedback->status = SubscriberFeedback::STATUS_SPENDING;
        $feedback->create_date = time();
        if ($feedback->save()) {
            return true;
        }
        return false;
    }

    /**
     * HungNV
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $subscriber_id = 1;
        $query = SubscriberFeedback::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
            ],
            'pagination' => [
                'defaultPageSize' => 10,
            ]
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        if ($this->site_id) {
            $query->andWhere(['site_id' => $this->site_id]);
        }
        if ($this->content_id) {
            $query->andWhere(['content_id' => $this->content_id]);
        }
        /** using for report statistic */
        if ($this->from_date) {
            $query->andFilterWhere(['>=', 'from_date', strtotime($this->from_date)]);
        }
        if ($this->to_date) {
            $query->andFilterWhere(['<=', 'to_date', strtotime($this->to_date)]);
        }

        return $dataProvider;
    }

    /**
     * HungNV 16 April
     *
     * @param $site_id
     * @param $content_id
     * @param $from_date
     * @param $to_date
     * @return ActiveDataProvider
     */
    public static function getFeedbacks($site_id, $content_id, $from_date = null, $to_date = null)
    {
        $query = new Query();
        $query->select(['subscriber.username', 'subscriber_feedback.*', 'content.display_name'])
            ->from('subscriber_feedback')
            ->innerJoin('subscriber', 'subscriber.id = subscriber_feedback.subscriber_id')
            ->innerJoin('content', 'content.id = subscriber_feedback.content_id')
            ->andWhere(['subscriber_feedback.site_id' => $site_id]);
        if (isset($content_id)) {
            $query->andWhere(['subscriber_feedback.content_id' => $content_id]);
        }
        /** using to statistic feedback on a period */
        if ($from_date && $to_date) {
            $query->andWhere(['>=', 'subscriber_feedback.create_date', strtotime($from_date)])
                ->andWhere(['<=', 'subscriber_feedback.create_date', strtotime($to_date)]);
        }
        $query->andWhere(['subscriber_feedback.status' => SubscriberFeedback::STATUS_ACTIVE]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => SORT_DESC,
            ],
            'pagination' => [
                'defaultPageSize' => 10,
            ]
        ]);
        return $dataProvider;
    }
}
