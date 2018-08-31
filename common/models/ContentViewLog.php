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
class ContentViewLog extends \yii\db\ActiveRecord
{
    const STATUS_SUCCESS = 10;
    const STATUS_FALSE   = 0;

    const IS_START = 1;
    const IS_STOP  = 2;

//    const TYPE_SERVICE = 1; //Xem qua gói cước ( chia sẻ doanh thu)
    //    const TYPE_CONTENT = 2; //Xem qua mua lẻ ( không chia sẻ doanh thu)

    const TYPE_VIDEO        = 1;
    const TYPE_LIVE         = 2;
    const TYPE_MUSIC        = 3;
    const TYPE_NEWS         = 4;
    const TYPE_CLIP         = 5;
    const TYPE_KARAOKE      = 6;
    const TYPE_RADIO        = 7;
    const TYPE_LIVE_CONTENT = 8;

    const CHANNEL_TYPE_API            = 1;
    const CHANNEL_TYPE_SYSTEM         = 2;
    const CHANNEL_TYPE_CSKH           = 3;
    const CHANNEL_TYPE_SMS            = 4;
    const CHANNEL_TYPE_WAP            = 5;
    const CHANNEL_TYPE_MOBILEWEB      = 6;
    const CHANNEL_TYPE_ANDROID        = 7;
    const CHANNEL_TYPE_IOS            = 8;
    const CHANNEL_TYPE_WEBSITE        = 9;
    const CHANNEL_TYPE_ANDROID_MOBILE = 10;

    public $view_date_max;

    public function channelLabel($channel = null)
    {
        $channelList = [
            self::CHANNEL_TYPE_API     => \Yii::t('app', 'API'),
            self::CHANNEL_TYPE_SYSTEM  => \Yii::t('app', 'System'),
            self::CHANNEL_TYPE_CSKH  => \Yii::t('app', 'CSKH'),
            self::CHANNEL_TYPE_SMS  => \Yii::t('app', 'SMS'),
            self::CHANNEL_TYPE_WAP  => \Yii::t('app', 'Wap'),
            self::CHANNEL_TYPE_MOBILEWEB  => \Yii::t('app', 'Mobileweb'),
            self::CHANNEL_TYPE_ANDROID => \Yii::t('app', 'Android'),
            self::CHANNEL_TYPE_IOS  => \Yii::t('app', 'IOS'),
            self::CHANNEL_TYPE_WEBSITE  => \Yii::t('app', 'Website'),
            self::CHANNEL_TYPE_ANDROID_MOBILE  => \Yii::t('app', 'Android Mobile'),
        ];

        if ($channel) {
            return $channelList[$channel];
        }

        return $channelList;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%content_view_log}}';
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

    /**
     * @return array
     */
    public static function listStatus()
    {
        $lst = [
            self::STATUS_SUCCESS => 'Success',
            self::STATUS_FALSE   => 'False',
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
     * @return array
     */
    public static function listType()
    {
        $lst = [
            self::TYPE_VIDEO   => \Yii::t('app', 'Phim'),
            self::TYPE_CLIP    => \Yii::t('app', 'Clip'),
            self::TYPE_LIVE    => \Yii::t('app', 'Live'),
            self::TYPE_MUSIC   => \Yii::t('app', 'Âm nhạc'),
            self::TYPE_NEWS    => \Yii::t('app', 'Tin tức'),
            self::TYPE_KARAOKE => \Yii::t('app', 'Karaoke'),
            self::TYPE_RADIO   => \Yii::t('app', 'Radio'),
        ];
        return $lst;
    }

    /**
     * @return int
     */
    public function getTypeName()
    {
        $lst = self::listType();
        if (array_key_exists($this->type, $lst)) {
            return $lst[$this->type];
        }
        return $this->type;
    }

    /**
     * @return array
     */
    public static function listChannelType()
    {
        $lst = [
            self::CHANNEL_TYPE_API       => \Yii::t('app', 'Api'),
            self::CHANNEL_TYPE_SYSTEM    => \Yii::t('app', 'System'),
            self::CHANNEL_TYPE_CSKH      => \Yii::t('app', 'Cskh'),
            self::CHANNEL_TYPE_SMS       => \Yii::t('app', 'Sms'),
            self::CHANNEL_TYPE_WAP       => \Yii::t('app', 'Wap'),
            self::CHANNEL_TYPE_MOBILEWEB => \Yii::t('app', 'Mobile Web'),
            self::CHANNEL_TYPE_ANDROID   => \Yii::t('app', 'Android'),
            self::CHANNEL_TYPE_IOS       => \Yii::t('app', 'IOS'),
        ];
        return $lst;
    }

    /**
     * @return int
     */
    public function getChannelName()
    {
        $lst = self::listChannelType();
        if (array_key_exists($this->channel, $lst)) {
            return $lst[$this->channel];
        }
        return $this->channel;
    }

    /**
     * @param $subscriber Subscriber
     * @param $content Content
     * @param $type
     * @param $channel
     * @param $site_id
     * @param $start_time
     * @param $stop_time
     * @param $log_id
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public static function createViewLog($subscriber, $content, $category_id, $type, $record_type, $channel, $site_id, $start_time, $stop_time, $log_id)
    {
        $res = [];
        if (!$log_id) {
            /** @var  $log ContentViewLog */
            $log                = new ContentViewLog();
            $log->subscriber_id = $subscriber->id;
            $log->content_id    = $content->id;
            $log->category_id   = $category_id;
            $log->created_at    = time();
            $log->ip_address    = Yii::$app->request->getUserIP();
            $log->user_agent    = Yii::$app->request->getUserAgent();
            $log->status        = ContentViewLog::STATUS_SUCCESS;
            $log->type          = $type;
            $log->record_type   = $record_type;
            $log->channel       = $channel;
            $log->site_id       = $site_id;
            $log->cp_id         = $content->cp_id;
            if ($start_time >= 0) {
                $log->started_at = $start_time;
            }
            if ($stop_time) {
                $log->stopped_at = $stop_time;
            }
            $log->view_date = time();
            /** Vì mỗi lần vào xem phim có 2 lần gọi api: start_time và stop_time nên cần phân biệt lúc vào thì mới tăng thêm 1 view_count */
            if ($record_type == ContentViewLog::IS_STOP) {
                //Tăng số lượt xem của 1 nội dung
                $log->view_count = 0;
            } else {
                $log->view_time_date = time();
                /** Default khi tạo là bằng 1 */
                $log->view_count = 1;
            }
            /** Validate và save, nếu có lỗi thì return message_error */
            if (!$log->validate() || !$log->save()) {
                $message        = $log->getFirstMessageError();
                $res['status']  = false;
                $res['message'] = $message;
                return $res;
            }
            $res['status']  = true;
            $res['message'] = Message::getSuccessMessage();
            $res['item']    = $log;
        } else {
            /** @var  $log ContentViewLog */
            $log = ContentViewLog::findOne(['id' => $log_id, 'channel' => $channel, 'site_id' => $site_id, 'type' => $type]);
            if (!$log) {
                return false;
            }
            if ($start_time >= 0) {
                $log->started_at = $start_time;
            }
            if($record_type == ContentViewLog::IS_START){
                $log->view_time_date = time();
            }
            if ($stop_time) {
                $log->stopped_at = $stop_time;
            }
            $log->view_date = time();
            /** Vì mỗi lần vào xem phim có 2 lần gọi api: start_time và stop_time nên cần phân biệt lúc vào thì mới tăng thêm 1 view_count */
//            if($record_type == ContentViewLog::IS_START){
            //                //Tăng số lượt xem của 1 nội dung
            //                $log->view_count++;
            //            }
            /** Validate và save, nếu có lỗi thì return message_error */
            if (!$log->validate() || !$log->save()) {
                $message        = $log->getFirstMessageError();
                $res['status']  = false;
                $res['message'] = $message;
                return $res;
            }
            $res['status']  = true;
            $res['message'] = Message::getSuccessMessage();
            $res['item']    = $log;

        }
        return $res;
    }

    public static function createViewLogForConsole($subscriber, $content, $category_id, $type, $record_type, $channel, $site_id, $start_time, $stop_time, $log_id)
    {
        $res = [];
        if (!$log_id) {
            /** @var  $log ContentViewLog */
            $log                = new ContentViewLog();
            $log->subscriber_id = $subscriber->id;
            $log->content_id    = $content->id;
            $log->category_id   = $category_id;
            $log->created_at    = time();
//            $log->ip_address    = Yii::$app->request->getUserIP();
//            $log->user_agent    = Yii::$app->request->getUserAgent();
            $log->status        = ContentViewLog::STATUS_SUCCESS;
            $log->type          = $type;
            $log->record_type   = $record_type;
            $log->channel       = $channel;
            $log->site_id       = $site_id;
            $log->cp_id         = $content->cp_id;
            if ($start_time >= 0) {
                $log->started_at = $start_time;
            }
            if ($stop_time) {
                $log->stopped_at = $stop_time;
            }
            $log->view_date = time();
            /** Vì mỗi lần vào xem phim có 2 lần gọi api: start_time và stop_time nên cần phân biệt lúc vào thì mới tăng thêm 1 view_count */
            if ($record_type == ContentViewLog::IS_STOP) {
                //Tăng số lượt xem của 1 nội dung
                $log->view_count = 0;
            } else {
                /** Default khi tạo là bằng 1 */
                $log->view_count = 1;
            }
            /** Validate và save, nếu có lỗi thì return message_error */
            if (!$log->validate() || !$log->save()) {
                $message        = $log->getFirstMessageError();
                $res['status']  = false;
                $res['message'] = $message;
                return $res;
            }
            $res['status']  = true;
            $res['message'] = Message::getSuccessMessage();
            $res['item']    = $log;
        } else {
            /** @var  $log ContentViewLog */
            $log = ContentViewLog::findOne(['id' => $log_id, 'channel' => $channel, 'site_id' => $site_id, 'type' => $type]);
            if (!$log) {
                return false;
            }
            if ($start_time >= 0) {
                $log->started_at = $start_time;
            }
            if ($stop_time) {
                $log->stopped_at = $stop_time;
            }
            $log->view_date = time();
            /** Vì mỗi lần vào xem phim có 2 lần gọi api: start_time và stop_time nên cần phân biệt lúc vào thì mới tăng thêm 1 view_count */
//            if($record_type == ContentViewLog::IS_START){
            //                //Tăng số lượt xem của 1 nội dung
            //                $log->view_count++;
            //            }
            /** Validate và save, nếu có lỗi thì return message_error */
            if (!$log->validate() || !$log->save()) {
                $message        = $log->getFirstMessageError();
                $res['status']  = false;
                $res['message'] = $message;
                return $res;
            }
            $res['status']  = true;
            $res['message'] = Message::getSuccessMessage();
            $res['item']    = $log;

        }
        return $res;
    }


    private function getFirstMessageError()
    {
        $error   = $this->firstErrors;
        $message = "";
        foreach ($error as $key => $value) {
            $message .= $value;
            break;
        }
        return $message;
    }

    /**
     * @param $subscriber
     * @param $site_id
     * @param $channel
     * @param $content_id
     * @param $view_date
     * @return ActiveDataProvider
     */
    public static function viewLogSearch($subscriber, $site_id, $channel, $content_id, $view_date)
    {
        /**
         * check view logs theo kênh - theo nội dung - theo ngày
         */
        if ($view_date) {
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $view_date)) {
                $res = [
                    'status'  => false,
                    'message' => Yii::t('app', 'Sai định dạng ngày tháng'),
                ];
                return $res;
            }
        }

        $params = Yii::$app->request->queryParams;
        $from   = new \DateTime($view_date);
        $from->setTime(0, 0, 0);
        $to = new \DateTime($view_date);
        $to->setTime(23, 59, 59);
        $logSearch                = new ContentViewLogSearch();
        $logSearch->subscriber_id = $subscriber->id;
        $logSearch->site_id       = $site_id;
        if ($channel) {
            $logSearch->channel = $channel;
        }
        if ($content_id) {
            $logSearch->content_id = $content_id;
        }
        if ($view_date) {
            $logSearch->from_time = $from->getTimestamp();
            $logSearch->to_time   = $to->getTimestamp();
        }
        $items = $logSearch->search($params);
        $res   = [
            'status' => true,
            'items'  => $items,
        ];
        return $res;
        /*

    $viewLog = ContentViewLog::find()
    ->andWhere(['content_view_log.status' => ContentViewLog::STATUS_SUCCESS]);
    if ($subscriber) {
    $viewLog->andWhere(['subscriber_id' => $subscriber->id])
    ->andWhere(['site_id' => $site_id]);
    }
    if ($channel) {
    $viewLog->andWhere(['channel' => $channel]);
    }
    if ($content_id) {
    $viewLog->andWhere(['content_id' => $content_id]);
    }
    if ($view_date) {
    $from = new \DateTime($view_date);
    $from->setTime(0, 0, 0);
    $to = new \DateTime($view_date);
    $to->setTime(23, 59, 59);

    $viewLog->andWhere(['>=', 'view_date', $from->getTimestamp()])
    ->andWhere(['<=', 'view_date', $to->getTimestamp()]);
    }
    $activeData = new ActiveDataProvider([
    'query' => $viewLog,
    'sort' => [
    'defaultOrder' => SORT_DESC,
    ],
    'pagination' => [
    'defaultPageSize' => 10,
    ]
    ]);
    return $activeData;
     */
    }

}
