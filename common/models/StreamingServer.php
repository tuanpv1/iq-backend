<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%streaming_server}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $ip
 * @property integer $port
 * @property string $host
 * @property string $content_status_api
 * @property string $content_api
 * @property string $content_path
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property SiteStreamingServerAsm[] $siteStreamingServerAsms
 */
class StreamingServer extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 1;
    const STATUS_DELETED = 0;

    public $site_ids;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%streaming_server}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'ip', 'status', 'content_path', 'site_ids'], 'required'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['port'], 'integer', 'min' => 1, 'message' => Yii::t('app','{attribute} phải là số nguyên dương'), 'tooSmall' => Yii::t('app','{attribute} phải là số nguyên dương')],
            [['name'], 'string', 'max' => 200],
            [['ip', 'host'], 'string', 'max' => 64],
            [['content_status_api', 'content_api'], 'string', 'max' => 255],
            ['host', 'match', 'pattern' => '/^((?=[a-z0-9-]{1,63}\.)(xn--)?[a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,63}$/', 'message' => Yii::t('app','Tên miền không đúng định dạng')],
            ['ip', 'match', 'pattern' => '/^(((?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])(?:\\.(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])){3})|((?:(?:[0-9a-fA-F:]){1,4}(?:(?::(?:[0-9a-fA-F]){1,4}|:)){2,7})+))$/', 'message' => Yii::t('app','{attribute} không đúng định dạng')],
            ['ip', 'validateUnique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Tên'),
            'ip' => Yii::t('app', 'Địa chỉ IP'),
            'port' => Yii::t('app', 'Port'),
            'host' => Yii::t('app', 'Tên miền'),
            'content_status_api' => Yii::t('app', 'API cập nhật trạng thái nội dung'),
            'content_api' => Yii::t('app', 'API yêu cầu phân phối nội dung'),
            'content_path' => Yii::t('app', 'Đường dẫn đến nội dung'),
            'status' => Yii::t('app', 'Trạng thái'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'updated_at' => Yii::t('app', 'Ngày Thay đổi thông tin'),
            'site_ids' => Yii::t('app', 'Nhà cung cấp dịch vụ'),
        ];
    }

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
    public function getSiteStreamingServerAsms()
    {
        return $this->hasMany(SiteStreamingServerAsm::className(), ['streaming_server_id' => 'id']);
    }

    public function validateUnique($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $streamingServer = StreamingServer::findOne(['ip' => $this->ip, 'status' => [Subscriber::STATUS_ACTIVE, Subscriber::STATUS_INACTIVE]]);
            if ($streamingServer && $streamingServer->id != $this->id) {
                    $this->addError($attribute, "$attribute".Yii::t('app','đã tồn tại. Vui lòng nhập')." $attribute khác!");
            }
        }
    }

    /**
     * @return array
     */
    public static function listStatus()
    {
        $lst = [
            self::STATUS_ACTIVE => Yii::t('app','Hoạt động'),
            self::STATUS_INACTIVE => Yii::t('app','Không hoạt động'),
        ];
        return $lst;
    }

    public function getStatusName()
    {
        $lst = self::listStatus();
        if (array_key_exists($this->status, $lst)) {
            return $lst[$this->status];
        }
        return $this->status;
    }

    public function getSiteNames()
    {
        $asms = $this->siteStreamingServerAsms;
        $siteNames = [];
        /** @var $asm SiteStreamingServerAsm */
        foreach ($asms as $asm) {
            $site = $asm->site;
            if ($site) {
                $siteNames[] = $site->name;
            }
        }
        return implode(", ", $siteNames);
    }

    public function saveRecords($isUpdate = false)
    {
        /** Validate và save, nếu có lỗi thì return message_error */
        if (!$this->validate()) {
            $message = $this->getFirstMessageError();
            $res['success'] = false;
            $res['message'] = $message;
            return $res;
        }
        if (!$this->save()) {
            $res['success'] = false;
            $res['message'] = Message::MSG_FAIL;
            return $res;
        }

        SiteStreamingServerAsm::deleteAll(['and', 'streaming_server_id=' . $this->id, ['not in', 'site_id', $this->site_ids]]);
        foreach ($this->site_ids as $site_id) {
            SiteStreamingServerAsm::createSiteStreamingServerAsm($this->id, $site_id);
        }

        $res['success'] = true;
        if ($isUpdate) {
            $res['message'] = Yii::t('app','Cập nhật thành công');

        } else {
            $res['message'] = Yii::t('app','Thêm mới thành công');
        }
        return $res;
    }

    public function softDelete()
    {
        if ($this->status != self::STATUS_INACTIVE) {
            $res['success'] = false;
            $res['message'] = Yii::t('app','Bạn chỉ được phép xóa các địa chỉ phân phối nội dung có trạng thái "Không hoạt động"');
            return $res;
        }
        $this->status = self::STATUS_DELETED;
        if (!$this->save(true, ['status'])) {
            $res['success'] = false;
            $res['message'] = Message::MSG_FAIL;
            return $res;
        }

        Site::updateAll(['primary_streaming_server_id' => $this->id], 'primary_streaming_server_id = null');

        $res['success'] = true;
        $res['message'] = Yii::t('app','Xóa thành công');
        return $res;
    }

    private function getFirstMessageError()
    {
        $error = $this->firstErrors;
        $message = "";
        foreach ($error as $key => $value) {
            $message .= $value;
            break;
        }
        return $message;
    }
}
