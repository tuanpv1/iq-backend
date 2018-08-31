<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "log_sync_content".
 *
 * @property integer $id
 * @property integer $content_id
 * @property integer $site_id
 * @property integer $content_status
 * @property integer $sync_status
 * @property integer $retry
 * @property integer $created_at
 * @property integer $updated_at
 */
class LogSyncContent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'log_sync_content';
    }

    public $type;
    public $link;
    public $cp;

    const CONTENT_STATUS_PROFILE = 1;
    const CONTENT_STATUS_NO_PROFILE = 2;

    const STATUS_INACTIVE = 0; // Tam dung
    const STATUS_ACTIVE = 10; // Da san sang
    const STATUS_INVISIBLE = 4; // Ngung cung cap
    const STATUS_NOT_TRANSFER = 1; // Chua phan phoi
    const STATUS_TRANSFER_ERROR = 2; // Phan phoi loi
    const STATUS_TRANSFERING = 3; // Dang phan phoi
    const STATUS_GAN_ERROR = 6;

    public static function status()
    {
        return [
            self::STATUS_NOT_TRANSFER => 'Chưa phân phối',
            self::STATUS_TRANSFERING => 'Đang phân phối',
            self::STATUS_TRANSFER_ERROR => 'Phân phối lỗi',
            self::STATUS_ACTIVE => 'Đã sẵn sàng',
            self::STATUS_INACTIVE => 'Tạm dừng',
            self::STATUS_INVISIBLE => 'Ngừng cung cấp',
            self::STATUS_GAN_ERROR => 'Gán nội dung không thành công',
        ];
    }

    public static $_status = [
        self::STATUS_NOT_TRANSFER   => 'Chưa phân phối',
        self::STATUS_TRANSFERING    => 'Đang phân phối',
        self::STATUS_TRANSFER_ERROR => 'Phân phối lỗi',
        self::STATUS_ACTIVE         => 'Đã sẵn sàng',
        self::STATUS_INACTIVE       => 'Tạm dừng',
        self::STATUS_INVISIBLE      => 'Ngừng cung cấp',
        self::STATUS_GAN_ERROR => 'Gán nội dung không thành công',
    ];
    public static $_content = [
        self::CONTENT_STATUS_PROFILE    => 'Nội dung đã có content profile',
        self::CONTENT_STATUS_NO_PROFILE   => 'Nội dung chưa có content profile',

    ];

    public static function content()
    {
        return [
            self::CONTENT_STATUS_PROFILE    => 'Nội dung đã có content profile',
            self::CONTENT_STATUS_NO_PROFILE   => 'Nội dung chưa có content profile',
        ];
    }

    public function getContentName()
    {
        $listStatus = self::content();
        if (isset($listStatus[$this->content_status])) {
            return $listStatus[$this->content_status];
        }
        return '';
    }
    public function getStatusName()
    {
        $listStatus = self::status();
        if (isset($listStatus[$this->sync_status])) {
            return $listStatus[$this->sync_status];
        }
        return '';
    }

    public static function listType()
    {
        return [
            Content::TYPE_VIDEO => 'Phim',
            Content::TYPE_CLIP => 'Clip',
            Content::TYPE_LIVE => 'Live',
            Content::TYPE_MUSIC => 'Âm nhạc',
            Content::TYPE_NEWS => 'Tin tức',
            Content::TYPE_KARAOKE => 'Karaoke',
            Content::TYPE_RADIO => 'Radio',
        ];
    }

    public static function listSite(){
        $lstSite = Site::findAll(['status'=>Site::STATUS_ACTIVE]);
        $output = [];
        foreach ($lstSite as $v) {
            $output[$v->id] = $v->name;
        }
        return $output;
    }

    public function getSiteName($site)
    {
        $lst = self::listSite();
        if (array_key_exists($site, $lst)) {
            return $lst[$site];
        }
        return $site;
    }


    public function getTypeName($type)
    {
        $lst = self::listType();
        if (array_key_exists($type, $lst)) {
            return $lst[$type];
        }
        return $type;
    }

    public static function getListStatus($type = 'all')
    {
        return ['all' => [
            self::STATUS_NOT_TRANSFER => 'Chưa phân phối',
            self::STATUS_TRANSFERING => 'Đang phân phối',
            self::STATUS_TRANSFER_ERROR => 'Phân phối lỗi',
            self::STATUS_ACTIVE => 'Đã sẵn sàng',
            self::STATUS_INACTIVE => 'Tạm dừng',
            self::STATUS_INVISIBLE => 'Ngừng cung cấp',
            self::STATUS_GAN_ERROR => 'Gán nội dung không thành công',
        ],
            'filter' => [
                self::STATUS_NOT_TRANSFER => 'Chưa phân phối',
                self::STATUS_TRANSFERING => 'Đang phân phối',
                self::STATUS_TRANSFER_ERROR => 'Phân phối lỗi',
                self::STATUS_ACTIVE => 'Đã sẵn sàng',
                self::STATUS_INACTIVE => 'Tạm dừng',
                self::STATUS_INVISIBLE => 'Ngừng cung cấp',
                self::STATUS_GAN_ERROR => 'Gán nội dung không thành công',
            ],
        ][$type];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content_id', 'content_status', 'sync_status'], 'required'],
            [['link','updated_at'],'string'],
            [['content_id', 'site_id', 'content_status','type', 'sync_status', 'retry', 'created_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content_id' => 'Content ID',
            'site_id' => 'Site ID',
            'content_status' => 'Content Status',
            'sync_status' => 'Sync Status',
            'retry' => 'Retry',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function getListCp()
    {
        $arrCP = [];
        $listCp = ContentProvider::find()->andWhere(['status' => ContentProvider::STATUS_ACTIVE])->all();
        foreach ($listCp as $item) {
            /** @var $item ContentProvider */
            $arrCP[$item->id] = $item->cp_name;
        }
        return $arrCP;
    }

    public function getNameCP($cp_id)
    {
        $lst = self::getListCp();
        if (array_key_exists($cp_id, $lst)) {
            return $lst[$cp_id];
        }
        return $cp_id;
    }
}
