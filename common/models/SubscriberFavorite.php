<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%subscriber_favorite}}".
 *
 * @property integer $id
 * @property integer $subscriber_id
 * @property integer $content_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $site_id
 * @property integer $type
 *
 * @property Site $site
 * @property Subscriber $subscriber
 * @property Content $content
 */
class SubscriberFavorite extends \yii\db\ActiveRecord
{
    const TYPE_VIDEO        = 1;
    const TYPE_LIVE         = 2;
    const TYPE_MUSIC        = 3;
    const TYPE_NEWS         = 4;
    const TYPE_CLIP         = 5;
    const TYPE_KARAOKE      = 6;
    const TYPE_RADIO        = 7;
    const TYPE_LIVE_CONTENT = 8;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%subscriber_favorite}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subscriber_id', 'content_id', 'site_id'], 'required'],
            [['subscriber_id', 'content_id', 'created_at', 'updated_at', 'site_id', 'type'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app','ID'),
            'subscriber_id' => Yii::t('app','ID thuê bao'),
            'content_id' => Yii::t('app','Content ID'),
            'created_at' => Yii::t('app','Ngày tạo'),
            'updated_at' => Yii::t('app','Ngày thay đổi thông tin'),
            'site_id' => Yii::t('app','Site ID'),
            'type' => Yii::t('app', '1: video, 2: live, 3: music, 4:news, 5: clips, 6:karaoke, 7:radio, 8: live_content'),
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->hasOne(Content::className(), ['id' => 'content_id']);
    }

    /**
     * @param $subscriber Subscriber
     * @param $content Content
     * @param $site_id
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
//    public static function createFavorite($subscriber,$content,$site_id){
//        $connection = Yii::$app->db;
//        $transaction = $connection->beginTransaction();
//        $favorite= SubscriberFavorite::findOne(['subscriber_id'=>$subscriber->id,'content_id'=>$content->id]);
//        if(!$favorite){
//            $favorite = new SubscriberFavorite();
//            $favorite->subscriber_id= $subscriber->id;
//            $favorite->content_id=$content->id;
//            $favorite->site_id = $site_id;
//            $content->favorite_count++;
//            if($content->save() && $favorite->save()){
//                $transaction->commit();
//                return true;
//            }
//            $transaction->rollBack();
//            return false;
//        } else{
//            $content->favorite_count--;
//            if($content->save() && $favorite->delete()){
//                $transaction->commit();
//                return true;
//            }
//            $transaction->rollBack();
//            return false;
//        }
//    }

    public static function createFavorite($subscriber,$content,$site_id,$status, $type = SubscriberFavorite::TYPE_VIDEO){
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        $favorite= SubscriberFavorite::findOne(['subscriber_id'=>$subscriber->id,'content_id'=>$content->id, 'type'=>$type]);
        if(!$favorite && $status==1){
            $favorite = new SubscriberFavorite();
            $favorite->subscriber_id= $subscriber->id;
            $favorite->content_id=$content->id;
            $favorite->site_id = $site_id;
            $favorite->type = $type;

            $content->favorite_count++;
            if($content->save() && $favorite->save()){
                $transaction->commit();
                return true;
            }
            $transaction->rollBack();
            return false;
        } else if($favorite && $status==0){
            $content->favorite_count--;
            if($content->save() && $favorite->delete()){
                $transaction->commit();
                return true;
            }
            $transaction->rollBack();
            return false;
        }
    }
//    public static function removeFavorite($subscriber,$content,$site_id){
//        $connection = Yii::$app->db;
//        $transaction = $connection->beginTransaction();
//        $favorite= SubscriberFavorite::findOne(['subscriber_id'=>$subscriber->id,'content_id'=>$content->id]);
//        if(!$favorite){
//            $favorite = new SubscriberFavorite();
//            $favorite->subscriber_id= $subscriber->id;
//            $favorite->content_id=$content->id;
//            $favorite->site_id = $site_id;
//            $content->favorite_count++;
//            if($content->save() && $favorite->save()){
//                $transaction->commit();
//                return true;
//            }
//            $transaction->rollBack();
//            return false;
//        } else{
//            $content->favorite_count--;
//            if($content->save() && $favorite->delete()){
//                $transaction->commit();
//                return true;
//            }
//            $transaction->rollBack();
//            return false;
//        }
//    }

    public static function  getFavorite($subscriber_id,$content_id){
        $sf= SubscriberFavorite::findOne(['subscriber_id'=>$subscriber_id,'content_id'=>$content_id]);
        if(!$sf){
            return false;
        }
        return $sf;
    }

}
