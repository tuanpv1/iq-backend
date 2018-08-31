<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 23/05/2015
 * Time: 4:37 PM
 */

namespace api\controllers;


use api\helpers\Message;
use api\helpers\UserHelpers;
use common\helpers\CUtils;
use common\helpers\FileUtils;
use common\models\ActorDirector;
use common\models\ActorDirectorSearch;
use common\models\ApiVersion;
use common\models\BaseLogicCampaign;
use common\models\Content;
use common\models\ContentCategoryAsm;
use common\models\ContentFeedback;
use common\models\ContentProfile;
use common\models\ContentProfileSiteAsm;
use common\models\ContentSearch;
use common\models\ContentSiteAsm;
use common\models\LiveProgram;
use common\models\LogSyncContent;
use common\models\Notification;
use common\models\Service;
use common\models\Site;
use common\models\Subscriber;
use Yii;
use yii\base\ErrorException;
use yii\base\InvalidValueException;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;


class ContentController extends ApiController
{
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = [
            'karaoke-search',
            'content-attributes',
            'karaoke',
            'catchup-channels',
            'list-days',
            'catchup',
            'feedbacks',
            'view',
            'search',
            'get-content',
            'get-sub-drama',
            'list-drama-film',
            'list-sub-drama',
            'detail',
            'related',
            'comments',
            'list-content-search',
            'test',
            'film-drama-detail',
            'add-view-count',
            'sugestion',
            'go-to-drama',
            'get-version-api',
            'get-adventisment',
            'sync-content-to-site',
            'data-version',
        ];

        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'list-content' => ['GET'],
            'detail' => ['GET'],
            'related' => ['GET'],
            'add-to-site' => ['GET'],
            'favorite' => ['GET'],
            'unfavorite' => ['GET'],
            'comment' => ['POST'],
            'comments' => ['GET'],
//            'list-content-search' => ['GET'],
            'test' => ['GET'],
//            'film-drama-detail' => ['GET'],
//            'add-view-count' => ['POST'],
            'sync-content-to-site' => ['POST'],
        ];
    }


    /**
     * API get content dùng cho cả search, list video, list phim bộ
     * @return \yii\data\ActiveDataProvider
     */
    public function actionSearch()
    {
        UserHelpers::manualLogin();
        $cache = Yii::$app->cache;

        $searchModel = new ContentSearch();
        $param = Yii::$app->request->queryParams;
        $searchModel->site_id = $this->site->id;
        $searchModel->type = isset($param['type']) ? ($param['type']) : 0;
        /** Bổ sung trường catchup để lấy danh sách kênh live catchup */
        $searchModel->is_catchup = isset($param['is_catchup']) ? ($param['is_catchup']) : Content::NOT_CATCHUP;
        $searchModel->category_id = isset($param['category_id']) ? ($param['category_id']) : 0;
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


        if ($searchModel->type == Content::TYPE_LIVE && $searchModel->is_catchup != Content::IS_CATCHUP) {
            $key = Yii::$app->params['key_cache']['CacheLive'];
            $dataProvider = $cache->get($key);

            if ($dataProvider === false) {
                Yii::info("get Cache Fail");
                // $data is not found in cache, calculate it from scratch
                $dataProvider = $searchModel->search($param);
                // store $data in cache so that it can be retrieved next time
                \api\models\Content::getDb()->cache(function ($db) use ($dataProvider) {
                    $dataProvider->prepare();
                }, Yii::$app->params['time_expire_cache'], new TagDependency(['tags' => Yii::$app->params['key_cache']['CacheLive']]));

                $cache->set($key, $dataProvider, Yii::$app->params['time_expire_cache'], new TagDependency(['tags' => Yii::$app->params['key_cache']['CacheLive']]));
            }
        } else {
            $dataProvider = $searchModel->search($param);
        }

        return $dataProvider;
    }

    /**
     * @param $id
     * @return null|static
     * @throws NotFoundHttpException
     */
    public function actionView($id, $status = 0)
    {
        UserHelpers::manualLogin();
        if (!is_numeric($id)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['id']));
        }
        if (!is_numeric($status)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['status']));
        }
        /** $content \api\models\ContentDetail  */
        if ($status) {
//            $content = \api\models\Content::findOne(['tvod1_id' => $id, 'status' => Content::STATUS_ACTIVE]);
            $content = \api\models\ContentDetail::findOne(['tvod1_id' => $id]);
        } else {
            $content = \api\models\ContentDetail::findOne(['id' => $id]);
        }

        if (!$content) {
            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
        }

        $content->episode_count_asm = $content->getEpisodeCount($this->site->id);
        return $content;
    }

    public function actionGetSubDrama($id)
    {
//        UserHelpers::manualLogin();
        $content = Content::find()
            ->joinWith('contentSiteAsms')
            ->andWhere(['content_site_asm.site_id' => $this->site->id, 'content_site_asm.status' => ContentSiteAsm::STATUS_ACTIVE])
            ->andWhere(['content.id' => $id, 'is_series' => Content::IS_SERIES, 'content.status' => Content::STATUS_ACTIVE])
            ->andFilterWhere(['or',
                ['<=', 'content.activated_at', time()],
                ('content.activated_at is null')])
            ->andFilterWhere(['or',
                ['>=', 'content.expired_at', time()],
                ['=', 'content.expired_at', 0]])
            ->one();
        if (!$content) {
            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
        }

        $searchModel = new ContentSearch();
        $param = Yii::$app->request->queryParams;
        $searchModel->site_id = $this->site->id;
        //Bỏ điều kiện này vì cái này đã được mỏ rộng ra các type khác
//        $searchModel->type = Content::TYPE_VIDEO;
        $searchModel->order = Content::ORDER_EPISODE;
        $searchModel->status = Content::STATUS_ACTIVE;
        $searchModel->language = isset($param['language']) ? ($param['language']) : "";
        $searchModel->parent_id = isset($param['id']) ? ($param['id']) : 0;

        /** Validate đầu vào */
        if (!$searchModel->validate()) {
            $error = $searchModel->firstErrors;
            $message = "";
            foreach ($error as $key => $value) {
                $message .= $value;
                break;
            }
            throw new InvalidValueException($message);
        }

        $dataProvider = $searchModel->search($param);

        return $dataProvider;
    }

    /**
     * @return ActiveDataProvider
     */
    public function actionSuggestion()
    {
        $searchModel = new ContentSearch();
        $param = Yii::$app->request->queryParams;
        $searchModel->site_id = $this->site->id;
        $searchModel->type = isset($param['type']) ? ($param['type']) : 0;
        $searchModel->category_id = isset($param['category_id']) ? ($param['category_id']) : 0;
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
        $dataProvider = $searchModel->suggestion($param);

        return $dataProvider;
    }

    /**
     * @param $id
     * @return ActiveDataProvider
     * @throws NotFoundHttpException
     */
    public function actionRelated($id)
    {
//        UserHelpers::manualLogin();
//        $searchModel = new ContentSearch();
//        $param = Yii::$app->request->queryParams;
//        $searchModel->site_id = $this->site->id;
//        $searchModel->content_id = $id;
//
//        if (!$searchModel->validate()) {
//            $error = $searchModel->firstErrors;
//            $message = "";
//            foreach ($error as $key => $value) {
//                $message .= $value;
//                break;
//            }
//            throw new InvalidValueException($message);
//        }
//        $dataProvider = $searchModel->search($param);
        $MAX_ITEM = 6;
        $site_id = $this->site->id;
        $lstRelated = \api\models\Content::find()
            ->innerJoin('content_related_asm', 'content_related_asm.content_related_id = content.id')
            ->andWhere(['content_related_asm.content_id' => $id])
            ->andFilterWhere(['or',
                ['<=', 'content.activated_at', time()],
                ('content.activated_at is null')])
            ->andFilterWhere(['or',
                ['>=', 'content.expired_at', time()],
                ['=', 'content.expired_at', 0]])
            ->andWhere(['<>', 'content.status', Content::STATUS_DELETE])
            ->innerJoin('content_site_asm as csa', 'csa.content_id = content.id')
            ->andWhere(['csa.site_id' => $site_id])
            ->andWhere(['csa.status' => ContentSiteAsm::STATUS_ACTIVE])
            ->andWhere(['content.status' => Content::STATUS_ACTIVE])
            ->orderBy('content.updated_at DESC')
            ->limit($MAX_ITEM)
            ->all();
        $category_id = ContentCategoryAsm::findOne(['content_id' => $id]);
        if (count($lstRelated) < $MAX_ITEM) {
            $moreItem = $MAX_ITEM - count($lstRelated);
            $lstMore = \api\models\Content::find()
                ->innerJoin('content_category_asm', 'content_category_asm.content_id = content.id')
                ->andWhere(['content_category_asm.category_id' => $category_id])
                ->andFilterWhere(['or',
                    ['<=', 'content.activated_at', time()],
                    ('content.activated_at is null')])
                ->andFilterWhere(['or',
                    ['>=', 'content.expired_at', time()],
                    ['=', 'content.expired_at', 0]])
                ->andWhere(['<>', 'content.id', $id])//Không lấy chính nó
                ->andWhere(['<>', 'content.status', Content::STATUS_DELETE])// Không lấy thằng đã xóa
                ->innerJoin('content_site_asm as csa', 'csa.content_id = content.id')
                ->andWhere(['csa.site_id' => $site_id])
                ->andWhere(['csa.status' => ContentSiteAsm::STATUS_ACTIVE])
                ->andWhere(['content.status' => Content::STATUS_ACTIVE])
                ->orderBy('content.updated_at DESC')
                ->limit($moreItem)
                ->all();
            $lstRelated = array_merge($lstRelated, $lstMore);
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $lstRelated,
        ]);

        return $dataProvider;
    }

    /**
     * @param $id
     * @param int $status
     * @return array|null|\yii\db\ActiveRecord
     * @throws NotFoundHttpException
     */
    public function actionGoToDrama($id, $status = Content::NEXT_VIDEO)
    {
        UserHelpers::manualLogin();
        /** Check validate input */
        if (!is_numeric($id) || !is_numeric($status)) {
            throw new InvalidValueException(Yii::t('app', 'Id hoặc Trạng thái phải là kiểu số.'));
        }
        /** Check xem ID có phải là movie không */
        /** @var  $episode Content */
//        $episode = Content::findOne(['id' => $id, 'status' => Content::STATUS_ACTIVE, 'type' => Content::TYPE_VIDEO]);
//        if (!$episode) {
//            throw new InvalidValueException("Not found movie.");
//        }
        $episode = Content::find()
            ->joinWith('contentSiteAsms')
            ->andWhere(['content_site_asm.site_id' => $this->site->id, 'content_site_asm.status' => ContentSiteAsm::STATUS_ACTIVE])
//            ->andWhere(['content.id' => $id, 'type' => Content::TYPE_VIDEO, 'content.status' => Content::STATUS_ACTIVE])
            ->andWhere(['content.id' => $id, 'content.status' => Content::STATUS_ACTIVE])
            ->one();
        if (!$episode) {
            throw new InvalidValueException(Yii::t('app', 'Không tìm thấy nội dung'));
        }
        /** @var  $drama Content */
//        $drama = Content::findOne(['id' => $episode->parent_id, 'is_series' => Content::IS_SERIES, 'status' => Content::STATUS_ACTIVE]);
//        if (!$drama) {
//            throw new InvalidValueException("Not the episode.");
//        }
        $drama = Content::find()
            ->joinWith('contentSiteAsms')
            ->andWhere(['content_site_asm.site_id' => $this->site->id, 'content_site_asm.status' => ContentSiteAsm::STATUS_ACTIVE])
            ->andFilterWhere(['or',
                ['<=', 'content.activated_at', time()],
                ('content.activated_at is null')])
            ->andFilterWhere(['or',
                ['>=', 'content.expired_at', time()],
                ['=', 'content.expired_at', 0]])
            ->andWhere(['content.id' => $episode->parent_id, 'is_series' => Content::IS_SERIES, 'content.status' => Content::STATUS_ACTIVE])
            ->one();
        if (!$drama) {
            throw new InvalidValueException("Not the episode.");
        }

        $count = $drama->getEpisodeCount($this->site->id);
        $episodeCount = $count ? $count : 1;
        $episodeOrder = $episode->episode_order ? $episode->episode_order : 1;

        if ($status == Content::NEXT_VIDEO) {
            if ($episodeCount == $episodeOrder) {
                throw new NotFoundHttpException(Yii::t('app', 'Giới hạn episode +'));
            }
            /** Dùng \api\models\Content để trả thêm att cho client/ */
//            $nextContent = \api\models\Content::find()->where(['parent_id' => $drama->id, 'status' => Content::STATUS_ACTIVE])
//                ->andWhere('episode_order >:episode_order', [':episode_order' => $episodeOrder])
//                ->orderBy('episode_order')->one();

            $nextContent = \api\models\Content::find()
                ->joinWith('contentSiteAsms')
                ->andWhere(['content_site_asm.site_id' => $this->site->id, 'content_site_asm.status' => ContentSiteAsm::STATUS_ACTIVE])
                ->andWhere(['content.status' => Content::STATUS_ACTIVE])
                ->andFilterWhere(['or',
                    ['<=', 'content.activated_at', time()],
                    ('content.activated_at is null')])
                ->andFilterWhere(['or',
                    ['>=', 'content.expired_at', time()],
                    ['=', 'content.expired_at', 0]])
                ->andWhere(['parent_id' => $drama->id])
                ->andWhere('episode_order >:episode_order', [':episode_order' => $episodeOrder])
                ->orderBy('episode_order')->one();

            if ($nextContent) {
                return $nextContent;
            }
            throw new NotFoundHttpException(Yii::t('app', 'Không thể đi đến bộ phim +'));
        } else {
            if ($episodeOrder == 1) {
                throw new NotFoundHttpException(Yii::t('app', 'Giới hạn episode +'));
            }
            /** Dùng \api\models\Content để trả thêm att cho client/ */
            $previousContent = \api\models\Content::find()
                ->joinWith('contentSiteAsms')
                ->andWhere(['content_site_asm.site_id' => $this->site->id, 'content_site_asm.status' => ContentSiteAsm::STATUS_ACTIVE])
                ->andWhere(['content.status' => Content::STATUS_ACTIVE])
                ->andFilterWhere(['or',
                    ['<=', 'content.activated_at', time()],
                    ('content.activated_at is null')])
                ->andFilterWhere(['or',
                    ['>=', 'content.expired_at', time()],
                    ['=', 'content.expired_at', 0]])
                ->andWhere(['parent_id' => $drama->id])
                ->andWhere('episode_order <:episode_order', [':episode_order' => $episodeOrder])
                ->orderBy('episode_order DESC')->one();
            if ($previousContent) {
                return $previousContent;
            }
            throw new NotFoundHttpException(Yii::t('app', 'Không thể đi đến bộ phim +'));
        }

    }

    public function actionComment($id)
    {
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
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
        if (!$content) {
            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
        }

        $params = Yii::$app->request->getBodyParams();
        $title = isset($params['title']) ? ($params['title']) : '';
        $cmt = isset($params['content']) ? ($params['content']) : '';
        $like = isset($params['like']) ? ($params['like']) : 0;
        $rate = isset($params['rate']) ? ($params['rate']) : '';
        $comment = ContentFeedback::createFeedback($content, $subscriber, $title, $cmt, $like, $rate);
        if (!$comment) {
            throw new InvalidValueException(Message::getFailMessage());
        }
        return [
            'status' => true,
            'message' => Message::getSuccessMessage(),
        ];
    }

    /**
     * @param $id
     * @param $quality
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionGetUrl($id, $quality)
    {
        //Validate
        if (!is_numeric($id)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['id']));
        }

        if (!is_numeric($quality)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['quality']));
        }
        /** @var $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }

        /** @var $content Content */
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
        if (!$content) {
            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
        }

        /** check video is_free or not */
        if ($content->getIsFree($this->site->id) == Content::NOT_FREE) {
            $purchase = Subscriber::validatePurchasing($subscriber->id, $content->id);
            if (!$purchase) {

                if(!$subscriber->is_active){
                    //check noi dung co thuoc campaign hay khong
                    $campaign = BaseLogicCampaign::getCampaignActiveUser($subscriber, $this->site->id,$content->id);
                    if ($campaign) {
                        $this->setStatusCode(202);
                        return ['message' => Notification::findOne(['name' => 'ĐK02'])->content];

                    }
                    //bỏ logic nay o CR31 do bat popup mua tat ca goi va mua le luon
//                    else {
//                        $this->setStatusCode(201);
//                        $service = Service::find()
//                            ->innerJoin('service_category_asm', 'service_category_asm.service_id = service.id')
//                            ->innerJoin('content_category_asm', 'content_category_asm.category_id = service_category_asm.category_id')
//                            ->andWhere(['content_category_asm.content_id' => $id])
//                            ->andWhere(['service.status' => Service::STATUS_ACTIVE])->one();
//                        return $service;
//                    }
                }
//                throw new UnauthorizedHttpException(Yii::t('app', 'Bạn không có quyền xem nội dung này'));
                //khong co quyen xem noi dung
                $this->setStatusCode(204);
                $data['items'] = [
                    'success'=>false,
                    'allow_buy_content'=>$content->allow_buy_content,
                    'message'=>Yii::t('app', 'Bạn không có quyền xem nội dung này')
                ];
                return $data;
            }
        }
        /** get link */
        /** @var $contentProfile ContentProfile */
        $contentProfile = ContentProfile::findOne(['content_id' => $id, 'quality' => $quality, 'status' => ContentProfile::STATUS_ACTIVE]);
        if (!$contentProfile) {
            throw new NotFoundHttpException(Message::getNotFoungContentProfileMessage());
        }
//        print_r($content->allow_buy_content);die();

        $allow_buy_content = $content->allow_buy_content;

        $type_check = $content->type;
        $res = Content::getUrl($type_check, $contentProfile, $this->site->id, $content->id, $allow_buy_content);
        if (!$res['success']) {
            throw new NotFoundHttpException($res['message']);
        }

        if ($content->type == Content::TYPE_LIVE || $content->type == Content::TYPE_LIVE_CONTENT) {
            $site_id = $this->site->id;
            $category_asm = ContentCategoryAsm::findOne(['content_id' => $id]);
            $category_id = $category_asm->category_id;
            $subscriber_id = $subscriber->id;
            $message = shell_exec("/usr/bin/nohup  ./save_log_live.sh $site_id  $id $category_id $subscriber_id > /dev/null 2>&1 &");
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

    /**
     * @return ActiveDataProvider
     */
    public function actionGetActor()
    {
        $searchModel = new ActorDirectorSearch();
        $param = Yii::$app->request->queryParams;
        $searchModel->type = ActorDirector::TYPE_ACTOR;
        $searchModel->status = ActorDirector::STATUS_ACTIVE;
        $searchModel->content_type = ActorDirector::TYPE_KARAOKE;

        $dataProvider = $searchModel->search($param);
        return $dataProvider;
    }

    public function actionGetDirector()
    {
        $searchModel = new ActorDirectorSearch();
        $param = Yii::$app->request->queryParams;
        $searchModel->type = ActorDirector::TYPE_DIRECTOR;
        $searchModel->status = ActorDirector::STATUS_ACTIVE;
        $searchModel->content_type = ActorDirector::TYPE_KARAOKE;

        $dataProvider = $searchModel->search($param);
        return $dataProvider;
    }

    /**
     * @return null|static
     * @throws NotFoundHttpException
     */
    public function actionGetVersionApi()
    {
        $model = ApiVersion::findOne(['type' => ApiVersion::TYPE_KARAOKE, 'site_id' => $this->site->id]);
        if (!$model) {
            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
        }
        return $model;
    }


    public function actionGetAdventisment()
    {
        return ["message" => Message::getAdventismentMessage()];
    }

    /**
     * HungNV
     * @return ActiveDataProvider
     * @throws NotFoundHttpException
     */
    public function actionComments($id)
    {
        /**
         * comments under channel or content
         */
        if (!is_numeric($id)) {
            throw new InvalidValueException(Message::getNumberOnlyMessage());
        }
        /** @var $content Content */
//        $content = Content::findOne(['id' => $id, 'status' => Content::STATUS_ACTIVE]);
//        if (!$content) {
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
            ->andWhere(['content.id' => $id, 'content.status' => Content::STATUS_ACTIVE])
            ->one();
        if (!$content) {
            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
        }
        $comment = $content->getContentFeedbacks();
        $dataProvider = new ActiveDataProvider();
        $dataProvider->query = $comment;
        return $dataProvider;
    }


    /**
     * @param $channel_id
     * @param int $days
     * @return mixed
     * @throws NotFoundHttpException
     */
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
                    $ngay = Yii::t('app','Thứ hai');
                    break;
                case 'Tue':
                    $ngay = Yii::t('app','Thứ ba');
                    break;
                case 'Wed':
                    $ngay = Yii::t('app','Thứ tư');
                    break;
                case 'Thu':
                    $ngay = Yii::t('app','Thứ năm');
                    break;
                case 'Fri':
                    $ngay = Yii::t('app','Thứ sáu');
                    break;
                case 'Sat':
                    $ngay = Yii::t('app','Thứ bảy');
                    break;
                case 'Sun':
                    $ngay = Yii::t('app','Chủ nhật');
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

    /**
     * @param $channel_id
     * @param null $date
     * @return array
     * @throws NotFoundHttpException
     */
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
        $res = LiveProgram::getEpg($channel_id, $fromTimeDefault, $toTimeDefault, $site_id);
        if (count($res) <= 0) {
            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
        }
        return $res;


    }


    /**
     * @param $content_id
     * @return array
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionSyncContentToSite()
    {
        $data = Yii::$app->request->post('data', null);
        $retry = Yii::$app->params['retry'];
        $json_data = json_decode($data);
        $request_id = $json_data->request_id;
        $content_id = $json_data->content_id;
        $site_id = $json_data->site_id;
        $contentProfiles = $json_data->data;
        $token = $json_data->token;
        $retry = Yii::$app->params['retry'];
        CUtils::log('#### actionSyncContentToSite receive from Downloader: ' . $data);

        $tokent_validate = md5($request_id . $site_id);
        /** Kiểm tra tokent */
        if ($token !== $tokent_validate) {
            throw new UnauthorizedHttpException(Message::getTokenFailMessage());
        }

        /** Tổng số bản ghi ACTIVE của file gốc */
        $countContentProfilesDefault = ContentProfile::find()
            ->andWhere(['content_id' => $content_id, 'type' => ContentProfile::TYPE_CDN, 'status' => Content::STATUS_ACTIVE])
            ->count();
        /** Đếm số bản ghi ACTIVE hiện tại có trước khi update */
        $countContentProfilesBefore = Content::countQualityWhenDownload($content_id, $site_id);
        $countSuccess = 0;

        foreach ($contentProfiles as $contentProfile) {
            $rs = ContentProfileSiteAsm::createContentProfileSiteAsm($contentProfile->content_profile_id, $contentProfile->cdn_content_id, $site_id, $contentProfile->success ? ContentProfileSiteAsm::STATUS_ACTIVE : ContentProfileSiteAsm::STATUS_INACTIVE);
            if (!$rs['success']) {
                CUtils::log('#### actionSyncContentToSite - createContentProfileSiteAsm : ' . $rs['message']);
            }
            if ($contentProfile->success && $rs['success']) {
                $countSuccess++;
            }
        }
        if ($countSuccess == 0) {
            CUtils::log('#### actionSyncContentToSite : ' . Message::getFailMessage());
            throw new NotFoundHttpException(Message::getFailMessage());
        }

        /** Nếu thành công hết cả các contentProfile thì mới update  ContentSiteAsm STATUS_ACTIVE */
        /** @var  $csa ContentSiteAsm */
        $csa = ContentSiteAsm::findOne(['content_id' => $content_id, 'site_id' => $site_id]);
        $logSyncContent = LogSyncContent::findOne(['content_id' => $content_id, 'site_id' => $site_id]);
        if (!$csa) {
            CUtils::log('#### actionSyncContentToSite : ' . Message::getNotFoundContentMessage());
            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
        }
        /** Tổng số file đã phân phối được */
        $countAfter = $countSuccess + $countContentProfilesBefore;

        /** So sánh số lượng file phân phối đã đủ với file gốc chưa? Đủ thì publish chưa đủ thì đổi trạng thái error. */
        if ($countAfter == $countContentProfilesDefault) {
            if ($logSyncContent) {
                $logSyncContent->sync_status = ContentSiteAsm::STATUS_ACTIVE;
                $logSyncContent->updated_at = time();
                $logSyncContent->save(false);
            }
            $csa->status = ContentSiteAsm::STATUS_ACTIVE;
            $csa->time_sync_received = time();
            if (!$csa->save()) {
                CUtils::log('#### actionSyncContentToSite : ' . Message::getErrorSystemMessage());
                throw new ServerErrorHttpException(Message::getErrorSystemMessage());
            }
        } else {
            if ($logSyncContent) {
                $logSyncContent->sync_status = ContentSiteAsm::STATUS_TRANSFER_ERROR;
                $logSyncContent->updated_at = time();
                $logSyncContent->save(false);
            }
            $csa->status = ContentSiteAsm::STATUS_TRANSFER_ERROR;
            $csa->time_sync_received = time();
            if (!$csa->save()) {
                CUtils::log('#### actionSyncContentToSite : ' . Message::getErrorSystemMessage());
                throw new ServerErrorHttpException(Message::getErrorSystemMessage());
            }
            $logSyncContent_ = LogSyncContent::findOne(['content_id' => $content_id, 'site_id' => $site_id]);
            if ($logSyncContent_) {
                $site_ = Site::findOne(['id' => $site_id]);
                for ($t = $logSyncContent_->retry + 1; $t <= $retry; $t++) {
                    if ($logSyncContent_->sync_status == ContentSiteAsm::STATUS_TRANSFER_ERROR) {
                        $this->writeLog('**** test tăng retry' . $content_id);
                        $this->writeLog($logSyncContent_->sync_status);
                        $logSyncContent_->retry = $logSyncContent_->retry + 1;
                        $logSyncContent_->updated_at = time();
                        $sync = Content::syncContentToSite($site_id, $content_id, $site_->primary_streaming_server_id, true, true);
                        $logSyncContent_->save(false);
                    }
                }
            }
            CUtils::log('#### actionSyncContentToSite : ' . Message::getTranferErrorMessage());
            /** Hiển thị message update lỗi chưa phân phổi đủ chất lượng video */
            throw new ErrorException(Message::getTranferErrorMessage());
        }
        /** Tất cả các bản ghi thành công thì báo là thành công */
        CUtils::log('#### actionSyncContentToSite : ' . Message::getSuccessMessage());
        return ['message' => Message::getSuccessMessage()];
    }

    private function writeLog($mes)
    {
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/content-error.log'), date('y-m-d H:i:s') . ' ' . $mes);
    }

    public function actionSyncDataToSite()
    {
        $site_id = $this->site->id;
//        $lst = Content::syncDataToSite(6, 1);
//        $lst = Content::syncContentToSite(6,35304,24);
//        var_dump($lst);
    }


    /** API lấy dữ liệu karaoke theo last_updated */
    public function actionDataVersion($last_updated)
    {

        $searchModel = new ContentSearch();

        $param = Yii::$app->request->queryParams;
        $searchModel->site_id = $this->site->id;
        $searchModel->type = Content::TYPE_KARAOKE;
        $searchModel->last_updated = $last_updated;
        $searchModel->order = Content::ORDER_ID;


        if (!$searchModel->validate()) {
            $error = $searchModel->firstErrors;
            $message = "";
            foreach ($error as $key => $value) {
                $message .= $value;
                break;
            }
            throw new InvalidValueException($message);
        }
        $dataProvider = $searchModel->dataVersion($param);
        return $dataProvider;

    }

    public function actionTest()
    {
        echo "this is test";
    }
}