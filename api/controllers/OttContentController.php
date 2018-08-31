<?php
/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 27-Oct-17
 * Time: 9:57 AM
 */

namespace api\controllers;


use api\helpers\Message;
use api\modelsHtv\LiveProgramHtv;
use common\helpers\CUtils;
use common\models\Content;
use common\models\ContentProfile;
use common\models\ContentSearch;
use common\models\ContentSiteAsm;
use Yii;
use yii\base\InvalidValueException;
use yii\web\NotFoundHttpException;

class OttContentController extends ApiController
{

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = [
            'search',
            'list-days',
            'catchup',
            'get-url'
        ];

        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'search' => ['GET'],
            'list-days' => ['GET'],
            'catchup' => ['GET'],
            'get-url' => ['GET']
        ];
    }

    public function actionSearch()
    {

        $searchModel = new ContentSearch();
        $param = Yii::$app->request->queryParams;
        $searchModel->site_id = $this->site->id;
        if (!isset($param['type'])) {
            throw new InvalidValueException(Yii::t('app', 'Type không được để trống'));
        }

        if (!isset($param['is_catchup'])) {
            throw new InvalidValueException(Yii::t('app', 'is_catchup không được để trống'));
        }


        $searchModel->type = isset($param['type']) ? ($param['type']) : 0;
        /** Bổ sung trường catchup để lấy danh sách kênh live catchup */
        $searchModel->is_catchup = ($param['is_catchup']);
        $searchModel->honor = isset($param['honor']) ? ($param['honor']) : Content::HONOR_NOTHING;
        $searchModel->keyword = isset($param['keyword']) ? ($param['keyword']) : "";
        $searchModel->order = isset($param['order']) ? ($param['order']) : Content::ORDER_NEWEST;
        $searchModel->status = Content::STATUS_ACTIVE;
        $searchModel->language = isset($param['language']) ? ($param['language']) : "";
        $searchModel->is_series = isset($param['is_series']) ? ($param['is_series']) : Content::IS_MOVIES;

        if (!$searchModel->validate()) {
            $error = $searchModel->firstErrors;
            $message = "";
            foreach ($error as $key => $value) {
                $message .= $value;
                break;
            }
            throw new InvalidValueException($message);
        }

        $dataProvider = $searchModel->searchHtv($param);

        return $dataProvider;
    }

    public function actionListDays($channel_id, $days = 7)
    {
//        $channel = Content::find()
//            ->innerJoin('user', 'user.id = content.created_user_id')
//            ->andWhere(['user.site_id' => $this->site->id])
//            ->andWhere(['content.id' => $channel_id])
//            ->andWhere(['content.status' => Content::STATUS_ACTIVE])
//            ->one();
//        if (!$channel) {
//            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
//        }

        $content = Content::find()
            ->joinWith('contentSiteAsms')
            ->andWhere(['content_site_asm.site_id' => $this->site->id, 'content_site_asm.status' => ContentSiteAsm::STATUS_ACTIVE])
            ->andFilterWhere(['or',
                ['<=', 'content.activated_at', time()],
                ('content.activated_at is null')])
            ->andFilterWhere(['or',
                ['>=', 'content.expired_at', time()],
                ['=', 'content.expired_at', 0]])
            ->andWhere(['content.id' => $channel_id, 'content.status' => Content::STATUS_ACTIVE])
            ->one();
        if (!$content) {
            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
        }

        /** @var $beginTime \DateTime */
//        $beginTime = new \DateTime();
//        $beginTime->setTime(0, 0, 0);
//        $endTime = new \DateTime();
//        $endTime->setTime(23, 59, 59);
        $today = time();
        for ($i = -$days; $i < 0; $i++) {
            $days = $today + 86400 * ($i + 1);
//            $begin = $beginTime->getTimestamp() + 86400 * ($i + 1);
//            $end = $endTime->getTimestamp() + 86400 * ($i + 1);
            $day = date("D", $days);
            $ngay = '';
            switch ($day) {
                case 'Mon':
                    $ngay = Yii::t('app', 'Thứ hai');
                    break;
                case 'Tue':
                    $ngay = Yii::t('app', 'Thứ ba');
                    break;
                case 'Wed':
                    $ngay = Yii::t('app', 'Thứ tư');
                    break;
                case 'Thu':
                    $ngay = Yii::t('app', 'Thứ năm');
                    break;
                case 'Fri':
                    $ngay = Yii::t('app', 'Thứ sáu');
                    break;
                case 'Sat':
                    $ngay = Yii::t('app', 'Thứ bảy');
                    break;
                case 'Sun':
                    $ngay = Yii::t('app', 'Chủ nhật');
                    break;
            }
            $res[] = [
                'id' => $content->id,
                'display_name' => $content->display_name,
                'day' => $ngay,
                'datetime' => date("Y-m-d", $days),
//                'beginTime' => $begin,
//                'endTime' => $end,
            ];
        }
        $data['items'] = $res;
        return $data;
    }

    public function actionCatchup($channel_id, $date = null)
    {
        $site_id = $this->site->id;
        if (!is_numeric($channel_id)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['channel_id']));
        }
        $channel = Content::find()
            ->joinWith('contentSiteAsms')
            ->andWhere(['content_site_asm.site_id' => $site_id, 'content_site_asm.status' => ContentSiteAsm::STATUS_ACTIVE])
            ->andFilterWhere(['or',
                ['<=', 'content.activated_at', time()],
                ('content.activated_at is null')])
            ->andFilterWhere(['or',
                ['>=', 'content.expired_at', time()],
                ['=', 'content.expired_at', 0]])
            ->andWhere(['content.id' => $channel_id, 'content.status' => Content::STATUS_ACTIVE])
            ->one();
        if (!$channel) {
            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
        }
        if ($date) {
            if (!CUtils::validateDate($date)) {
                throw new InvalidValueException(Message::getNotDateMessage());
            }
            $begin = new \DateTime($date);
            $begin->setTime(0, 0, 0);
            $fromTimeDefault = $begin->getTimestamp();
            $end = new \DateTime($date);
            $end->setTime(23, 59, 59);
            $toTimeDefault = $end->getTimestamp();
        } else {
            $begin = new \DateTime("today");
            $begin->setTime(0, 0, 0);
            $fromTimeDefault = $begin->getTimestamp();
            $end = new \DateTime("today");
            $end->setTime(23, 59, 59);
            $toTimeDefault = $end->getTimestamp();
        }
        $res = LiveProgramHtv::getEpg($channel_id, $fromTimeDefault, $toTimeDefault, $site_id);
        if (count($res) <= 0) {
            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
        }
        return $res;

    }

    public function actionGetUrl($id, $quality, $streaming_server_ip = null)
    {
        //Validate
        if (!is_numeric($id)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['id']));
        }

        if (!is_numeric($quality)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['quality']));
        }


        /** @var $content Content */
        $contentCheck = Content::findOne($id);
        /** @var $contentCheck Content */
        if ($contentCheck->type == Content::TYPE_LIVE_CONTENT) {
            $content = Content::find()
                ->andFilterWhere(['or',
                    ['<=', 'content.activated_at', time()],
                    ('content.activated_at is null')])
                ->andFilterWhere(['or',
                    ['>=', 'content.expired_at', time()],
                    ['=', 'content.expired_at', 0]])
                ->andWhere(['content.id' => $id, 'content.status' => Content::STATUS_ACTIVE])
                ->one();
        } else {
            $content = Content::find()
                ->joinWith('contentSiteAsms')
                ->andWhere(['content_site_asm.site_id' => $this->site->id, 'content_site_asm.status' => ContentSiteAsm::STATUS_ACTIVE])
                ->andFilterWhere(['or',
                    ['<=', 'content.activated_at', time()],
                    ('content.activated_at is null')])
                ->andFilterWhere(['or',
                    ['>=', 'content.expired_at', time()],
                    ['=', 'content.expired_at', 0]])
                ->andWhere(['content.id' => $id, 'content.status' => Content::STATUS_ACTIVE])
                ->one();
        }
        if (!$content) {
            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
        }

        /** check video is_free or not */
        /** @var $contentProfile ContentProfile */
        $contentProfile = ContentProfile::findOne(['content_id' => $id, 'quality' => $quality, 'status' => ContentProfile::STATUS_ACTIVE]);
        if (!$contentProfile) {
            throw new NotFoundHttpException(Message::getNotFoungContentProfileMessage());
        }
//        print_r($content->allow_buy_content);die();

        $allow_buy_content = $content->allow_buy_content;

        $type_check = $content->type;
        $res = \api\modelsHtv\Content::getUrl($type_check, $contentProfile, $this->site->id, $content->id, $allow_buy_content, $streaming_server_ip);
        if (!$res['success']) {
            throw new NotFoundHttpException($res['message']);
        }

        /** Tăng View Count */
        $content->view_count++;
        $content->update();
        if ($content->parent_id) {
            $contentParent = Content::findOne(['id' => $content->parent_id]);
            $contentParent->view_count++;
            $contentParent->update();
        }

        $data['items'] = $res;
        return $data;
    }
}