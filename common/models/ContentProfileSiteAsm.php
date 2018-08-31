<?php

namespace common\models;

use api\helpers\Message;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;

/**
 * This is the model class for table "content_profile_site_asm".
 *
 * @property integer $id
 * @property integer $content_profile_id
 * @property string  $url
 * @property integer $site_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property ContentProfile $contentProfile
 * @property Site $site
 */
class ContentProfileSiteAsm extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE           = 10;
    const STATUS_INACTIVE         = 0;
    const STATUS_TEST             = 3;
    const STATUS_TRANCODED        = 4; // DA TRANSCOE
    const STATUS_TRANCODE_PENDING = 5; // DANG TRANSCOE, Khoa
    const STATUS_RAW              = 6; // raw chua transcode
    const STATUS_RAW_ERROR        = 7; // raw error
    const STATUS_UPLOADING        = 8; // raw error
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'content_profile_site_asm';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content_profile_id', 'site_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['url'], 'string', 'max' => 1000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::className(),
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
            'id'                 => Yii::t('app', 'ID'),
            'content_profile_id' => Yii::t('app', 'Content Profile ID'),
            'url'                => Yii::t('app', 'Url'),
            'site_id'            => Yii::t('app', 'Site ID'),
            'status'             => Yii::t('app', 'Trạng thái'),
            'created_at'         => Yii::t('app', 'Ngày tạo'),
            'updated_at'         => Yii::t('app', 'Ngày thay đổi thông tin'),
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $tag = Yii::$app->params['key_cache']['ContentQualities'] ? Yii::$app->params['key_cache']['ContentQualities'] : '';

        TagDependency::invalidate(Yii::$app->cache, $tag);

        return true;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentProfile()
    {
        return $this->hasOne(ContentProfile::className(), ['id' => 'content_profile_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }

    /**
     * @param $content_profile_id
     * @param $url
     * @param $site_id
     * @param int $status
     * @return array
     */
    public static function createContentProfileSiteAsm($content_profile_id, $url, $site_id, $status = ContentProfileSiteAsm::STATUS_ACTIVE)
    {
        $contentProfileSiteAsm = ContentProfileSiteAsm::findOne(['content_profile_id' => $content_profile_id, 'site_id' => $site_id]);
        if (!$contentProfileSiteAsm) {
            $contentProfileSiteAsm                     = new ContentProfileSiteAsm();
            $contentProfileSiteAsm->content_profile_id = $content_profile_id;
            $contentProfileSiteAsm->site_id            = $site_id;
        }
        $contentProfileSiteAsm->url    = (String)$url;
        $contentProfileSiteAsm->status = $status;

        if (!$contentProfileSiteAsm->save()) {
            $res = [
                'success' => false,
                'message' => Message::getFailMessage(),
            ];
            return $res;
        }
        $res = [
            'success' => true,
            'message' => Message::getSuccessMessage(),
            'item'    => $contentProfileSiteAsm,
        ];
        return $res;
    }
}
