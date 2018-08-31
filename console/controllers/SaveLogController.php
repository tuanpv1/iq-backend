<?php
/**
 * Created by PhpStorm.
 * User: nhocconsanhdieu
 * Date: 30/6/2015
 * Time: 3:37 PM
 */

namespace console\controllers;


use api\helpers\Message;
use common\models\Content;
use common\models\ContentSiteAsm;
use common\models\ContentViewLog;
use common\models\Subscriber;
use Yii;
use yii\console\Controller;

class SaveLogController extends Controller
{
    public function actionSaveTimeView($site_id, $content_id, $category_id, $subscriber_id, $type = ContentViewLog::TYPE_LIVE, $channel = ContentViewLog::CHANNEL_TYPE_ANDROID, $record_type = ContentViewLog::IS_START, $start_time = 0, $stop_time = 0, $duration = 0, $log_id = null)
    {
        /** @var  $subscriber Subscriber */
        $subscriber = Subscriber::findOne($subscriber_id);
        if (!$subscriber) {
            echo(Message::getAccessDennyMessage());
            return;
        }
        if (!is_numeric($content_id)) {
            echo($this->replaceParam(Message::getNumberOnlyMessage(), ['content_id']));
            return;
        }
        if (!is_numeric($category_id)) {
            echo($this->replaceParam(Message::getNumberOnlyMessage(), ['category_id']));
            return;
        }

        /** @var  $content Content */

        $content = Content::findOne($content_id);

        if (!$content) {
            echo(Message::getNotFoundContentMessage());
            return;
        }
        /** Lưu thời gian của phim */
        if ($duration) {
            $content->duration = $duration;
            $content->save();
        }

        $rs = ContentViewLog::createViewLogForConsole($subscriber, $content, $category_id, $type, $record_type, $channel, $site_id, $start_time, $stop_time, $log_id);


        echo($rs['message']);

    }

    /* replace message
    *
    * @param $message
    * @param $params
    * @return mixed
    */
    public static function replaceParam($message, $params)
    {
        if (is_array($params)) {
            $cnt = count($params);
            for ($i = 1; $i <= $cnt; $i++) {
                $message = str_replace('{' . $i . '}', $params[$i - 1], $message);
            }
        }
        return $message;
    }
} 