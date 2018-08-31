<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;

/**
 * This is the model class for table "content_site_asm".
 *
 * @property integer $id
 * @property integer $content_id
 * @property integer $site_id
 * @property integer $status
 * @property integer $pricing_id
 * @property string $subtitle
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $time_sync_sent
 * @property integer $time_sync_received
 * @property integer $episode_count
 *
 * @property Pricing $pricing
 * @property Content $content
 * @property Site $site
 */
class ContentSiteAsm extends \yii\db\ActiveRecord
{

    const STATUS_INACTIVE = 0; // Tam dung
    const STATUS_ACTIVE = 10; // Da san sang
    const STATUS_INVISIBLE = 4; // Ngung cung cap
    const STATUS_NOT_TRANSFER = 1; // Chua phan phoi
    const STATUS_TRANSFER_ERROR = 2; // Phan phoi loi
    const STATUS_TRANSFERING = 3; // Dang phan phoi

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $tag1 = Yii::$app->params['key_cache']['ContentPriceCoin'] ? Yii::$app->params['key_cache']['ContentPriceCoin'] : '';
            $tag2 = Yii::$app->params['key_cache']['ContentIsFree'] ? Yii::$app->params['key_cache']['ContentIsFree'] : '';

            TagDependency::invalidate(Yii::$app->cache, $tag1);
            TagDependency::invalidate(Yii::$app->cache, $tag2);
            return true;
        } else {
            return false;
        }
    }

//    public function afterSave($insert, $changedAttributes)
//    {
//        parent::afterSave($insert, $changedAttributes);
//        $this->updateEpisodeCount();
//    }

    public function updateEpisodeCount($newStatus = null)
    {
        $thisContent = Content::findOne($this->content_id);
        if ($thisContent->parent_id == null) {
            return false;
        }
        $episodes = Content::find()
            ->andWhere(['parent_id' => $thisContent->parent_id, 'status' => Content::STATUS_ACTIVE])
            ->andWhere(['type' => $thisContent->type])
            ->andWhere(['<>', 'id', $this->content_id])
            ->asArray()
            ->all();
        $countEpisodeInMySite = self::find()
            ->andWhere(['IN', 'content_id', array_column($episodes, 'id')])
            ->andWhere(['status' => self::STATUS_ACTIVE])
            ->andWhere(['site_id' => $this->site_id])
            ->count();
        if ($newStatus != null) {
            // chay master slave
            if ($newStatus == ContentSiteAsm::STATUS_ACTIVE) {
                $countEpisodeInMySite++;
            }
        } else {
            if ($thisContent->status == ContentSiteAsm::STATUS_ACTIVE) {
                $countEpisodeInMySite++;
            }
        }
        Yii::info('So tap se cap nhat vao ' . $countEpisodeInMySite);
        $parentInMySite = self::findOne(['content_id' => $thisContent->parent_id,'site_id' => $this->site_id]);
        if($parentInMySite){
            $parentInMySite->episode_count = $countEpisodeInMySite;
            if(!$parentInMySite->save(false)){
                Yii::info($parentInMySite->getErrors());
            }else{
                Yii::info('Cap nhat so tap thanh cong');
            }
        }else{
            Yii::info('Khong tim thay cha tren thi truong nay');
        }
    }

    public static function getListStatus()
    {
        $_status = [
            self::STATUS_NOT_TRANSFER => \Yii::t('app', 'Chưa phân phối'),
            self::STATUS_TRANSFERING => \Yii::t('app', 'Đang phân phối'),
            self::STATUS_TRANSFER_ERROR => \Yii::t('app', 'Phân phối lỗi'),
            self::STATUS_ACTIVE => \Yii::t('app', 'Đã sẵn sàng'),
            self::STATUS_INACTIVE => \Yii::t('app', 'Tạm dừng'),
            self::STATUS_INVISIBLE => \Yii::t('app', 'Ngừng cung cấp'),
        ];
        return $_status;
    }

    public static function getStatusNameByStatus($status)
    {
        $lst = self::getListStatus();
        if (array_key_exists($status, $lst)) {
            return $lst[$status];
        }
        return $status;
    }

    public static function getStatusNameSP($status)
    {
        $lst = self::listStatusSP();
        if (array_key_exists($status, $lst)) {
            return $lst[$status];
        }
        return $status;
    }

    public static function listStatusSP()
    {
        $_spStatus = [
            self::STATUS_ACTIVE => \Yii::t('app', 'Đã sẵn sàng'),
            self::STATUS_INACTIVE => \Yii::t('app', 'Tạm dừng'),
        ];
        return $_spStatus;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'content_site_asm';
    }

    public static function checkParentFree($parent_id, $allow = false)
    {
        Yii::info("id_parent: " . $parent_id);
        $model = ContentSiteAsm::findOne(['content_id' => $parent_id]);
        Yii::info("pricing_id: " . $model->pricing_id);
        if ($allow) {
            if ($model && $model->allo == null) {
                return true;
            }
            return false;
        } else {
            if ($model && $model->pricing_id == null) {
                return true;
            }
            return false;
        }
    }

    public static function setFree($id)
    {
        $model = ContentSiteAsm::findOne(['content_id' => $id]);
        $model->pricing_id = "";
        if (!$model->update()) {
            Yii::info('khong update con duoc');
            Yii::info($model->getErrors());
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content_id', 'site_id'], 'required'],
            [['subtitle'], 'string'],
            [['content_id', 'site_id', 'status', 'created_at', 'updated_at', 'pricing_id', 'time_sync_sent', 'time_sync_received', 'episode_count'], 'integer'],
            // [['subtitle'], 'file', 'extensions' => ['txt', 'smi', 'srt', 'ssa', 'sub', 'ass', 'style'], 'checkExtensionByMimeType' => false, 'maxSize' => 1024 * 1024 * 10],
        ];
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
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'content_id' => Yii::t('app', 'Content ID'),
            'site_id' => Yii::t('app', 'Site ID'),
            'status' => Yii::t('app', 'Status'),
            'subtitle' => Yii::t('app', 'Phụ đề'),
            'pricing_id' => Yii::t('app', 'Gia'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'time_sync_sent' => Yii::t('app', 'Time Sync Sent'),
            'time_sync_received' => Yii::t('app', 'Time Sync Received'),
            'episode_count' => Yii::t('app', 'Episode Count'),
        ];
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
    public function getPricing()
    {
        return $this->hasOne(Pricing::className(), ['id' => 'pricing_id']);
    }

    public function getSiteList($condition = [], $listFieldSelect = [])
    {
        if (count($condition) === 0) {
            $site = ContentSiteAsm::find()->all();
        } else {
            $site = ContentSiteAsm::findAll($condition);
        }
        if (count($listFieldSelect) > 0 && count($listFieldSelect) === 2) {
            $output = [];
            foreach ($site as $v) {
                $output[$v[$listFieldSelect[0]]] = $v[$listFieldSelect[1]];
            }
        } else {
            $output = $site;
        }
        return $output;
    }
}
