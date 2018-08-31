<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use api\controllers\ApiController;

/**
 * This is the model class for table "{{%content_feedback}}".
 *
 * @property integer $id
 * @property integer $rating
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $content_id
 * @property integer $subscriber_id
 * @property string $title
 * @property string $content
 * @property integer $status
 * @property integer $like
 * @property integer $site_id
 * @property string $admin_note
 *
 * @property Subscriber $subscriber
 * @property Content $content0
 * @property Site $site
 */
class ContentFeedback extends \yii\db\ActiveRecord
{
    const CM_STATUS_PENDING = 0;
    const CM_STATUS_APPROVED = 1;
    const CM_STATUS_REJECTED = 2;

    public static function getListStatus(){
        $listStatus = [
            self::CM_STATUS_PENDING => \Yii::t('app', 'Chờ duyệt'),
            self::CM_STATUS_APPROVED => \Yii::t('app', 'Đã duyệt'),
            self::CM_STATUS_REJECTED => \Yii::t('app', 'Từ chối')
        ];
        return $listStatus;
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%content_feedback}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rating', 'created_at', 'updated_at', 'content_id', 'subscriber_id', 'status', 'like', 'site_id'], 'integer'],
            [['content_id', 'site_id'], 'required'],
            [['title'], 'string', 'max' => 100],
            [['content', 'admin_note'], 'string', 'max' => 4000]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', \Yii::t('app', 'ID')),
            'rating' => Yii::t('app', \Yii::t('app', 'Đánh giá')),
            'created_at' => Yii::t('app', \Yii::t('app', 'Ngày tạo')),
            'updated_at' => Yii::t('app', \Yii::t('app', 'Ngày thay đổi thông tin')),
            'content_id' => Yii::t('app', \Yii::t('app', 'Nội dung')),
            'subscriber_id' => Yii::t('app', \Yii::t('app', 'Người dùng')),
            'title' => Yii::t('app', \Yii::t('app', 'Tiêu đề')),
            'content' => Yii::t('app', \Yii::t('app', 'Nội dung')),
            'status' => Yii::t('app', \Yii::t('app', 'Trạng thái')),
            'like' => Yii::t('app', \Yii::t('app', 'Like')),
            'site_id' => Yii::t('app',\Yii::t('app',  'Site')),
            'admin_note' => Yii::t('app', \Yii::t('app', 'Ghi chú')),
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
    public function getContent0()
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


    public function getStatusName()
    {
        $lst = self::getListStatus();
        if (array_key_exists($this->status, $lst)) {
            return $lst[$this->status];
        }
        return $this->status;
    }


    public function approve($user = null)
    {
        if ($this->status == self::CM_STATUS_APPROVED) {
            return true;
        }
        $this->status = self::CM_STATUS_APPROVED;

        return $this->update(false);
    }

    public function reject()
    {
        if ($this->status == self::CM_STATUS_REJECTED) {
            return true;
        }
        $this->status = self::CM_STATUS_REJECTED;
        return $this->update(false);
    }

    /**
     * @param $content
     * @param $subscriber
     * @param $cm_title
     * @param $cm_content
     * @param $like
     * @param $rate
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function createFeedback($content, $subscriber, $cm_title, $cm_content, $like, $rate)
    {
        $init_rate = 0;
        $site = \common\models\User::findOne(['id' => $content->created_user_id]);
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        $contentFeedback = new ContentFeedback();
        $contentFeedback->site_id = $site->site_id;
        $contentFeedback->subscriber_id = $subscriber->id;
        $contentFeedback->content_id = $content->id;
        $contentFeedback->status = ContentFeedback::CM_STATUS_PENDING;
        $contentFeedback->like = $like;
        $contentFeedback->title = $cm_title;
        $contentFeedback->content = $cm_content;
        if (!$rate) {
            $contentFeedback->rating = $init_rate;
        } else {
            $contentFeedback->rating = $rate;
            $content->rating = ($content->rating * $content->rating_count + $rate) / ($content->rating_count + 1);
            $content->rating_count++;
        }

        if ($contentFeedback->save()) {

            if ($like == 1) {
                $content->like_count++;
            }
            if ($cm_content) {
                $content->comment_count++;
            }
            if ($content->save()) {
                $transaction->commit();
                return true;
            } else {
                $transaction->rollBack();
            }
        }
        return false;

    }

    public static function getListFeedback($object, $id)
    {
        $query = ContentFeedback::find()->andWhere([]);
        if ($object == 'content_id') {
            $query->andWhere(['content_id' => $id]);
        }
        if ($object == 'subscriber_id') {
            $query->andWhere(['subscriber_id' => $id]);
        }
        if ($object == 'site_id') {
            $query->andWhere(['site_id' => $id]);
        }
        $query->andWhere(['status' => self::CM_STATUS_APPROVED])->all();
//        $query = \api\models\ContentFeedback::find()->andWhere(['content_id' => $content_id])->andWhere(['status' => self::CM_STATUS_APPROVED])->all();
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'updated_at' => SORT_DESC,
                ],
            ],
            'pagination' => [
                'defaultPageSize' => 10,
            ]
        ]);
        return $provider;
    }
}
