<?php

namespace common\models;

//use api\models\Content;
use api\modelsHtv\ContentHtv;
use common\helpers\CVietnameseTools;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * ContentSearch represents the model behind the search form about `\common\models\Content`.
 */
class ContentSearch extends Content
{
    public $keyword;
    public $categoryIds;
    public $categoryIds_;
    public $listCatIds;
    public $cp_id;
    public $site_id;
    public $category_id;
    public $order;
    public $content_id;
    public $pricing_id;
    public $subscriber_id;
    public $last_updated;
    public $allow_buy_content;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'type', 'duration', 'version_code', 'view_count', 'download_count', 'like_count', 'dislike_count', 'rating_count', 'comment_count', 'favorite_count', 'status', 'updated_at', 'honor', 'approved_at', 'episode_order', 'is_series', 'order', 'allow_buy_content'], 'integer'],
            [['display_name', 'ascii_name', 'tags', 'language', 'cp_id', 'short_description', 'categoryIds', 'categoryIds_', 'description', 'content', 'urls', 'version', 'images', 'admin_note', 'keyword', 'episode_order', 'is_series', 'channel_name', 'csa_status', 'pricing_id', 'created_at'], 'safe'],
            [['site_id', 'category_id', 'order', 'content_id', 'subscriber_id', 'is_live'], 'integer'],
            [['rating'], 'number'],
            [['last_updated'], 'integer'],
//            [['last_updated'], 'required', 'message' => Yii::t('app','last_updated không được để trống'), 'on' => 'dataVersion'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied.
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
//                $query = Content::find();
        $query = \api\models\Content::find()
            ->andFilterWhere(['or',
                ['<=', 'content.activated_at', time()],
                ('content.activated_at is null')])
            ->andFilterWhere(['or',
                ['>=', 'content.expired_at', time()],
                ['=', 'content.expired_at', 0]]);

        $orderDefault = [];

        if ($this->order == self::ORDER_MOSTVIEW) {
            $orderDefault['view_count'] = SORT_DESC;
        } else if ($this->order == self::ORDER_NEWEST) {
            $orderDefault['updated_at'] = SORT_DESC;
        } else if ($this->order == self::ORDER_EPISODE) {
            $orderDefault['episode_order'] = SORT_ASC;
        } else if ($this->order == self::ORDER_TITLE) {
            $orderDefault['display_name'] = SORT_ASC;
        } else if ($this->order == self::ORDER_ID) {
            $orderDefault['id'] = SORT_DESC;
        } else if ($this->order == self::ORDER_ORDER) {
            $orderDefault['order'] = SORT_DESC;
            $orderDefault['display_name'] = SORT_ASC;
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                 'defaultPageSize' => 30, // to set default count items on one page
                //                 'pageSize' => 25, //to set count items on one page, if not set will be set from defaultPageSize
                'pageSizeLimit' => [1, 1000], //to set range for pageSize
            ],
            'sort' => [
                'defaultOrder' => $orderDefault,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        /** Không lấy những thằng đã xóa */
        $query->andWhere(['<>', 'content.status', Content::STATUS_DELETE]);

        /* Bắt đầu xử lí các điều kiện lọc, nếu điều kiện nào truyền vào thì mới xử lí */
        /* Lấy phim liên quan */
        if ($this->content_id) {
            $query->innerJoin('content_related_asm', 'content_related_asm.content_related_id = content.id');
            $query->andWhere(['content_related_asm.content_id' => $this->content_id]);
        }

        if ($this->id) {
            $query->andWhere(['content.id' => $this->id]);
        }

        if ($this->honor) {
            $query->andWhere(['content.honor' => $this->honor]);
        }

        if ($this->type) {
            $query->andWhere(['content.type' => $this->type]);
        }

        if ($this->is_live) {
            if ($this->is_live == Content::IS_RADIO_LIVE_AND_SINGLE) {
                $query->andWhere(['content.is_live' => 0]);
            } else {
                $query->andWhere(['content.is_live' => $this->is_live]);
            }
        }

        /** Điều kiện là kênh catchup */
        if ($this->is_catchup) {
            $query->andWhere(['content.is_catchup' => $this->is_catchup]);
        }

        /* Điều kiện là phim bộ */
        if ($this->is_series) {
            if ($this->is_series == Content::IS_SERIES_CONTENT_SINGLE) {
                $query->andWhere(['content.is_series' => 0]);
            } else {
                $query->andWhere(['content.is_series' => $this->is_series]);
            }
        }

        /* Lấy toàn bộ SubDrama */
        if ($this->parent_id) {
            $query->andWhere(['content.parent_id' => $this->parent_id]);
        } else {
            /** Lấy những thằng phim mà không thuộc phim bộ nếu lấy phim lẻ */
            $query->andWhere('content.parent_id  IS NULL');
        }

        /** Lấy phim yêu thích */
        if ($this->subscriber_id) {
            $query->innerJoin('subscriber_favorite', 'subscriber_favorite.content_id = content.id');
            $query->andWhere(['subscriber_favorite.subscriber_id' => $this->subscriber_id]);
        }
        /** Lấy phim thuộc danh mục */
        if ($this->category_id) {
            $query->innerJoin('content_category_asm', 'content_category_asm.content_id = content.id');
            $query->andWhere(['content_category_asm.category_id' => $this->category_id]);
        }

        if ($this->site_id || $this->status) {
            $query->innerJoin('content_site_asm as csa', 'csa.content_id = content.id');
        }
        if ($this->status) {
            $query->andWhere(['csa.status' => $this->status]);
            $query->andWhere(['content.status' => $this->status]);
        }

        if ($this->site_id) {
            $query->andWhere(['csa.site_id' => $this->site_id]);
        }

        if ($this->language) {
            $query->andFilterWhere(['=', 'language', $this->language]);
        }

        if ($this->keyword) {
            $query->andFilterWhere(['or',
                ['like', 'display_name', $this->keyword],
                ['like', 'ascii_name', $this->keyword],
            ]);
        }

        return $dataProvider;
    }


    /** for karaoke */
    public function dataVersion($params)
    {
        $query = \api\models\Karaoke::find();

        $orderDefault = [];
        if ($this->order == self::ORDER_ID) {
            $orderDefault['id'] = SORT_ASC;
        } else if ($this->order == self::ORDER_NEWEST) {
            $orderDefault['updated_at'] = SORT_DESC;
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => $orderDefault,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->type) {
            $query->andWhere(['content.type' => $this->type]);
        }
        if ($this->site_id) {
            $query->innerJoin('content_site_asm as csa', 'csa.content_id = content.id');
            $query->andWhere(['csa.site_id' => $this->site_id]);
        }
        if ($this->last_updated) {
            $query->andWhere(['>', 'content.updated_at', $this->last_updated]);
        }

        return $dataProvider;
    }

    public function suggestion($params)
    {
        //        $query = Content::find();
        $query = \api\models\ContentSugestion::find()
            ->andFilterWhere(['or',
                ['<=', 'content.activated_at', time()],
                ('content.activated_at is null')])
            ->andFilterWhere(['or',
                ['>=', 'content.expired_at', time()],
                ['=', 'content.expired_at', 0]]);

        $orderDefault = [];
        if ($this->order == self::ORDER_MOSTVIEW) {
            $orderDefault['view_count'] = SORT_DESC;
        } else if ($this->order == self::ORDER_NEWEST) {
            $orderDefault['updated_at'] = SORT_DESC;
        } else if ($this->order == self::ORDER_EPISODE) {
            $orderDefault['episode_order'] = SORT_ASC;
        } else if ($this->order == self::ORDER_TITLE) {
            $orderDefault['display_name'] = SORT_ASC;
        } else if ($this->order == self::ORDER_ID) {
            $orderDefault['id'] = SORT_DESC;
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => $orderDefault,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        /** Không lấy những thằng đã xóa */
        $query->andWhere(['<>', 'content.status', Content::STATUS_DELETE]);

        /* Bắt đầu xử lí các điều kiện lọc, nếu điều kiện nào truyền vào thì mới xử lí */
        if ($this->honor) {
            $query->andWhere(['content.honor' => $this->honor]);
        }

        if ($this->type) {
            $query->andWhere(['content.type' => $this->type]);
        }

        if ($this->is_series) {
            $query->andWhere(['content.is_series' => $this->is_series]);
//            $query->andWhere(['content.parent_id' => 0]);
        }
        /* Lấy toàn bộ SubDrama */
        if ($this->parent_id) {
            $query->andWhere(['content.parent_id' => $this->parent_id]);
        } else {
            /** Lấy những thằng phim mà không thuộc phim bộ nếu lấy phim lẻ */
            $query->andWhere('content.parent_id  IS NULL');
        }

        if ($this->category_id) {
//            $query->joinWith('contentCategoryAsms');
            $query->innerJoin('content_category_asm', 'content_category_asm.content_id = content.id');
            $query->andWhere(['content_category_asm.category_id' => $this->category_id]);
        }

        if ($this->status) {
//            $query->joinWith('contentSiteAsms as a');
            $query->innerJoin('content_site_asm as a', 'a.content_id = content.id');
            $query->andWhere(['a.status' => $this->status]);
            $query->andWhere(['content.status' => $this->status]);
        }

        if ($this->site_id) {
//            $query->joinWith('contentSiteAsms as b');
            $query->innerJoin('content_site_asm as b', 'b.content_id = content.id');
            $query->andWhere(['b.site_id' => $this->site_id]);
        }

        if ($this->language) {
            $query->andFilterWhere(['=', 'language', $this->language]);
        }

        $query->andFilterWhere(['or',
            ['like', 'display_name', $this->keyword],
            ['like', 'ascii_name', $this->keyword],
        ]);
        /** Kết quả lấy ra bị lặp nên phải dùng distinct */
//        $query->distinct();

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied.
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
//    public function watchedVideo($params)
//    {
//        $query = \api\models\Content::find();
//
//        $dataProvider = new ActiveDataProvider([
//            'query'      => $query,
//        ]);
//
//        $this->load($params);
//
//        if (!$this->validate()) {
//            // uncomment the following line if you do not want to return any records when validation fails
//            // $query->where('0=1');
//            return $dataProvider;
//        }
//        $query->select('content.id , cvl.stopped_at, cvl.started_at');
//        /** Không lấy những thằng đã xóa */
//        $query->andWhere(['<>', 'content.status', Content::STATUS_DELETE]);
//
//        if ($this->type) {
//            $query->andWhere(['content.type' => $this->type]);
//        }
//
//        if ($this->subscriber_id) {
//            $query->innerJoin('content_view_log as cvl','cvl.content_id = content.id');
//            $query->andWhere(['cvl.subscriber_id' => $this->subscriber_id]);
//        }
//
//        if ($this->site_id || $this->status) {
//            $query->innerJoin('content_site_asm as csa', 'csa.content_id = content.id');
//        }
//        if ($this->status) {
//            $query->andWhere(['csa.status' => $this->status]);
//            $query->andWhere(['content.status' => $this->status]);
//        }
//
//        if ($this->site_id) {
//            $query->andWhere(['csa.site_id' => $this->site_id]);
//        }
//
//        $query->groupBy('cvl.content_id');
//        $query->orderBy('cvl.id ASC');
//
//        return $dataProvider;
//    }

    public function filter($params, $type, $sp_id, $admin = null, $limit = 30)
    {
        $query = Content::find();
//        $activeCategory = ArrayHelper::map(Category::findAll(['status' => Category::STATUS_ACTIVE]), 'id', 'id');
        if (!$admin) {
            $query->select('`content`.*, content_site_asm.status as csa_status, content_site_asm.pricing_id as pricing_id');
        }
        // var_dump($inactiveCategory);die;

        if ($type == Category::TYPE_LIVE_CONTENT) {
            $query->select('`content`.*, started_at, ended_at, channel.display_name as channel_name, channel_id, live_program.status as live_status, content_site_asm.status as csa_status');
            $query->innerJoin('live_program', 'content.id = live_program.content_id');
            $query->innerJoin('content as channel', 'channel.id = live_program.channel_id');
            $query->innerJoin('content_site_asm', 'content_site_asm.content_id = live_program.channel_id');
            $query->andWhere('channel.status!= :p_status_delete', [':p_status_delete' => Content::STATUS_DELETE]);
            if (!$admin) {
                $query->andFilterWhere(['content_site_asm.site_id' => $sp_id]);
                $query->andFilterWhere(['IN', 'content_site_asm.status', array_keys(ContentSiteAsm::listStatusSP())]);
            }
        } else {
            $query->leftJoin('content_site_asm', 'content.id = content_site_asm.content_id');
            if (!$admin) {
                $query->andFilterWhere(['content_site_asm.site_id' => $sp_id]);
                $query->andFilterWhere(['IN', 'content_site_asm.status', array_keys(ContentSiteAsm::listStatusSP())]);
            }

            $query->innerJoin('content_category_asm', 'content.id = content_category_asm.content_id');
//            $query->andWhere(['IN', 'content_category_asm.category_id', $activeCategory]);
            $query->andWhere('content.status!= :p_status_delete', [':p_status_delete' => Content::STATUS_DELETE]);
            $query->andWhere('content.status!= :p_status_inactive', [':p_status_inactive' => Content::STATUS_INACTIVE]);
        }

        $query->andFilterWhere(['content.type' => $type]);
        $query->groupBy('content.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'display_name',
                    'channel_name',
                    'started_at',
                    'ended_at',
                    'live_status',
                    'created_at',
                    'status',
                    'is_series',
                    'csa_status',
                    'pricing_id',
                    'order',
                    'content.updated_at',
                    'cp_id'
                ],
                'defaultOrder' => ['content.updated_at' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => $limit,
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        Yii::trace($this->getAttributes());
        if ($this->categoryIds) {
            $categoryIds = explode(',', $this->categoryIds);
            //     $this->listCatIds = $categoryIds;

            // $query->distinct();

            $query->andWhere(['IN', 'content_category_asm.category_id', $categoryIds]);
        }

        $query->andFilterWhere(['=', 'cp_id', $this->cp_id]);

        $query->andFilterWhere(['like', 'content.display_name', $this->keyword]);
        $query->andFilterWhere(['like', 'content.display_name', $this->display_name]);
        $query->andFilterWhere(['like', 'channel.display_name', $this->channel_name]);
        $query->andFilterWhere(['=', 'content.episode_order', $this->episode_order]);
        $query->andFilterWhere(['=', 'content.is_series', $this->is_series]);
        $query->andFilterWhere(['=', 'allow_buy_content', $this->allow_buy_content]);

        if ($admin) {
            $query->andFilterWhere(['=', 'content.status', $this->status]);
        } else {
            $query->andFilterWhere(['=', 'content_site_asm.status', $this->status]);
        }

        if ($this->pricing_id == 0 && $this->pricing_id !== null && $this->pricing_id !== "") {
            $query->andWhere(['IS', 'content_site_asm.pricing_id', null]);
        } else {
            $query->andFilterWhere(['=', 'content_site_asm.pricing_id', $this->pricing_id]);
        }

        $query->andFilterWhere(['=', 'content.order', $this->order]);

        $query->andFilterWhere(['=', 'content.language', $this->language]);
        $query->andFilterWhere(['=', 'content_site_asm.status', $this->csa_status]);
        if ($this->created_at !== '' && $this->created_at !== null) {
            $from_time = strtotime(str_replace('/', '-', $this->created_at) . ' 00:00:00');
            $to_time = strtotime(str_replace('/', '-', $this->created_at) . ' 23:59:59');
            $query->andFilterWhere(['>=', 'content.created_at', $from_time]);
            $query->andFilterWhere(['<=', 'content.created_at', $to_time]);
        }
        // var_dump($query->createCommand()->rawSql);die;
        return $dataProvider;
    }

    public function fillContent($type, $params, $sp)
    {
        $dataProvider = new ArrayDataProvider([
            'key' => 'id',
            'allModels' => Content::findAll(['type' => $type]),
        ]);
    }

    public function filterEpisode($params, $type, $sp_id, $parent_id)
    {
        $query = Content::find();
        $query->innerJoin('content_site_asm', 'content_site_asm.content_id = content.id');
        $query->select('content.*, content_site_asm.pricing_id');
        // $query->andWhere(['created_user_id' => $sp_id]);
        $query->andWhere(['parent_id' => $parent_id]);
        $query->andWhere(['type' => $type]);
        $query->andWhere(['<>', 'content.status', Content::STATUS_DELETE]);
        $query->groupBy('id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['episode_order' => SORT_ASC]
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        Yii::trace($this->getAttributes());
        $fullCat = array_keys(Category::getTreeCategories($type, $this->created_user_id));
        $categoryIds = [];
        if (!$this->categoryIds) {
            //            $this->categoryIds = $fullCat;
            $categoryIds = $fullCat;
        } else {
            $categoryIds = explode(',', $this->categoryIds);
        }
        if (count($categoryIds) != count($fullCat)) {
            $this->listCatIds = $categoryIds;
            $query->distinct();
            $query->innerJoinWith(['contentCategoryAsms' => function ($query) {
                $query->andWhere(['IN', 'category_id', $this->listCatIds]);
            }]);
        }
        $query->andFilterWhere(['=', 'content_provider_id', $this->cp_id]);
        $query->andFilterWhere(['like', 'ascii_name', CVietnameseTools::makeSearchableStr($this->display_name)]);
        $query->andFilterWhere(['=', 'episode_order', CVietnameseTools::makeSearchableStr($this->episode_order)]);
        $query->andFilterWhere(['=', 'is_series', $this->is_series]);

        if ($this->created_at) {
            $from_date = strtotime($this->created_at . ' 00:00:00');
            $to_date = strtotime($this->created_at . ' 23:59:59');
            $query->andFilterWhere(['>=', 'content.created_at', $from_date]);
            $query->andFilterWhere(['<=', 'content.created_at', $to_date]);
        }

        if ($this->pricing_id == 0 && $this->pricing_id !== null && $this->pricing_id !== "") {
            $query->andWhere(['IS', 'content_site_asm.pricing_id', null]);
        } else {
            $query->andFilterWhere(['=', 'content_site_asm.pricing_id', $this->pricing_id]);
        }
        if ($this->status) {
            Yii::info($this->status . 'hhihi');
            $query->andWhere(['content.status' => $this->status]);
        }
        return $dataProvider;
    }

    public function filterEpisodeSp($params, $type, $sp_id, $parent_id, $admin = null, $limit = 30)
    {
        $query = Content::find();
        $query->andWhere(['parent_id' => $parent_id]);
//        $activeCategory = ArrayHelper::map(Category::findAll(['status' => Category::STATUS_ACTIVE]), 'id', 'id');
        if (!$admin) {
            $query->select('`content`.*, content_site_asm.status as csa_status, content_site_asm.pricing_id as pricing_id');
        }
        // var_dump($inactiveCategory);die;

        if ($type == Category::TYPE_LIVE_CONTENT) {
            $query->select('`content`.*, started_at, ended_at, channel.display_name as channel_name, channel_id, live_program.status as live_status, content_site_asm.status as csa_status');
            $query->innerJoin('live_program', 'content.id = live_program.content_id');
            $query->innerJoin('content as channel', 'channel.id = live_program.channel_id');
            $query->innerJoin('content_site_asm', 'content_site_asm.content_id = live_program.channel_id');
            $query->andWhere('channel.status!= :p_status_delete', [':p_status_delete' => Content::STATUS_DELETE]);
            if (!$admin) {
                $query->andFilterWhere(['content_site_asm.site_id' => $sp_id]);
                $query->andFilterWhere(['IN', 'content_site_asm.status', array_keys(ContentSiteAsm::listStatusSP())]);
            }
        } else {
            $query->leftJoin('content_site_asm', 'content.id = content_site_asm.content_id');
            if (!$admin) {
                $query->andFilterWhere(['content_site_asm.site_id' => $sp_id]);
                $query->andFilterWhere(['IN', 'content_site_asm.status', [ContentSiteAsm::STATUS_ACTIVE,ContentSiteAsm::STATUS_INACTIVE]]);
                Yii::info('vao day');
            }

            $query->innerJoin('content_category_asm', 'content.id = content_category_asm.content_id');
            $query->andWhere(['NOT IN', 'content.status', [Content::STATUS_DELETE,Content::STATUS_INVISIBLE]]);
        }

        $query->andFilterWhere(['content.type' => $type]);
        $query->groupBy('content.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['episode_order' => SORT_ASC]
            ],
            'pagination' => [
                'pageSize' => $limit,
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        Yii::trace($this->getAttributes());
        if ($this->categoryIds) {
            $categoryIds = explode(',', $this->categoryIds);
            //     $this->listCatIds = $categoryIds;

            // $query->distinct();

            $query->andWhere(['IN', 'content_category_asm.category_id', $categoryIds]);
        }

        $query->andFilterWhere(['=', 'cp_id', $this->cp_id]);

        $query->andFilterWhere(['like', 'content.display_name', $this->keyword]);
        $query->andFilterWhere(['like', 'content.display_name', $this->display_name]);
        $query->andFilterWhere(['like', 'channel.display_name', $this->channel_name]);
        $query->andFilterWhere(['=', 'content.episode_order', $this->episode_order]);
        $query->andFilterWhere(['=', 'content.is_series', $this->is_series]);
        $query->andFilterWhere(['=', 'allow_buy_content', $this->allow_buy_content]);

        if ($admin) {
            $query->andFilterWhere(['=', 'content.status', $this->status]);
        } else {
            $query->andFilterWhere(['=', 'content_site_asm.status', $this->status]);
        }

        if ($this->pricing_id == 0 && $this->pricing_id !== null && $this->pricing_id !== "") {
            $query->andWhere(['IS', 'content_site_asm.pricing_id', null]);
        } else {
            $query->andFilterWhere(['=', 'content_site_asm.pricing_id', $this->pricing_id]);
        }

        $query->andFilterWhere(['=', 'content.order', $this->order]);

        $query->andFilterWhere(['=', 'content.language', $this->language]);
        $query->andFilterWhere(['=', 'content_site_asm.status', $this->csa_status]);
        if ($this->created_at !== '' && $this->created_at !== null) {
            $from_time = strtotime(str_replace('/', '-', $this->created_at) . ' 00:00:00');
            $to_time = strtotime(str_replace('/', '-', $this->created_at) . ' 23:59:59');
            $query->andFilterWhere(['>=', 'content.created_at', $from_time]);
            $query->andFilterWhere(['<=', 'content.created_at', $to_time]);
        }
        // var_dump($query->createCommand()->rawSql);die;
        return $dataProvider;
    }

    public static function validateDate($date, $format = "YYYY-MM-DD")
    {
        if (!preg_match("/^[-]+$/", substr($date, 4, 1)) || !preg_match("/^[-]+$/", substr($date, 7, 1))) {
            return false;
        }
        switch ($format) {
            case "YYYY-MM-DD":
                list($y, $m, $d) = preg_split('/[-\.\/ ]/', $date);
                break;
            default:
                return false;
        }
        return checkdate($m, $d, $y);
    }

    public static function filterValues($attributeName, $type, $params = null)
    {

        /** @var $attr ContentAttribute */
        $attr = ContentAttribute::findOne(['name' => $attributeName, 'content_type' => $type]);
        if (!$attr) {
            return false;
        }
        /** find at least one existed record */
        $content = ContentAttributeValue::find()
            ->innerJoin('content', 'content.id = content_attribute_value.content_id')
            ->where(['content_attribute_id' => $attr->id])
            ->andWhere(['content.status' => Content::STATUS_ACTIVE])
            ->andWhere(['content.type' => Content::TYPE_KARAOKE])
            ->one();
        if (!$content) {
            return false;
        }
        /** @var $query \api\models\Content */
        $query = \api\models\Content::find()
            ->innerJoin('content_attribute_value', 'content_attribute_value.content_id = content.id')
            ->andWhere(['content_attribute_value.content_attribute_id' => $attr->id]);
        if ($params) {
            $query->andWhere(['LIKE', 'content_attribute_value.value', $params]);
        }
        $query->andWhere(['content.type' => $type])
            ->andWhere(['content.status' => Content::STATUS_ACTIVE])
            ->orderBy('content.ascii_name ASC');
        $dataPrivider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => 20,
            ],
        ]);
        return $dataPrivider;
    }

    public function searchHtv($params)
    {
//                $query = Content::find();
        $query = ContentHtv::find()
            ->andFilterWhere(['or',
                ['<=', 'content.activated_at', time()],
                ('content.activated_at is null')])
            ->andFilterWhere(['or',
                ['>=', 'content.expired_at', time()],
                ['=', 'content.expired_at', 0]]);

        $orderDefault = [];

        if ($this->order == self::ORDER_MOSTVIEW) {
            $orderDefault['view_count'] = SORT_DESC;
        } else if ($this->order == self::ORDER_NEWEST) {
            $orderDefault['updated_at'] = SORT_DESC;
        } else if ($this->order == self::ORDER_EPISODE) {
            $orderDefault['episode_order'] = SORT_ASC;
        } else if ($this->order == self::ORDER_TITLE) {
            $orderDefault['display_name'] = SORT_ASC;
        } else if ($this->order == self::ORDER_ID) {
            $orderDefault['id'] = SORT_DESC;
        } else if ($this->order == self::ORDER_ORDER) {
            $orderDefault['order'] = SORT_DESC;
            $orderDefault['display_name'] = SORT_ASC;
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
//                 'defaultPageSize' => 20, // to set default count items on one page
                //                 'pageSize' => 25, //to set count items on one page, if not set will be set from defaultPageSize
                'pageSizeLimit' => [1, 1000], //to set range for pageSize
            ],
            'sort' => [
                'defaultOrder' => $orderDefault,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        /** Không lấy những thằng đã xóa */
        $query->andWhere(['<>', 'content.status', Content::STATUS_DELETE]);

        /* Bắt đầu xử lí các điều kiện lọc, nếu điều kiện nào truyền vào thì mới xử lí */
        /* Lấy phim liên quan */
        if ($this->content_id) {
            $query->innerJoin('content_related_asm', 'content_related_asm.content_related_id = content.id');
            $query->andWhere(['content_related_asm.content_id' => $this->content_id]);
        }

        if ($this->id) {
            $query->andWhere(['content.id' => $this->id]);
        }

        if ($this->honor) {
            $query->andWhere(['content.honor' => $this->honor]);
        }

        if ($this->type) {
            $query->andWhere(['content.type' => $this->type]);
        }

        if ($this->is_live) {
            if ($this->is_live == Content::IS_RADIO_LIVE_AND_SINGLE) {
                $query->andWhere(['content.is_live' => 0]);
            } else {
                $query->andWhere(['content.is_live' => $this->is_live]);
            }
        }

        /** Điều kiện là kênh catchup */
        if ($this->is_catchup) {
            $query->andWhere(['content.is_catchup' => $this->is_catchup]);
        }

        /* Điều kiện là phim bộ */
        if ($this->is_series) {
            if ($this->is_series == Content::IS_SERIES_CONTENT_SINGLE) {
                $query->andWhere(['content.is_series' => 0]);
            } else {
                $query->andWhere(['content.is_series' => $this->is_series]);
            }
        }

        /* Lấy toàn bộ SubDrama */
        if ($this->parent_id) {
            $query->andWhere(['content.parent_id' => $this->parent_id]);
        } else {
            /** Lấy những thằng phim mà không thuộc phim bộ nếu lấy phim lẻ */
            $query->andWhere('content.parent_id  IS NULL');
        }

        /** Lấy phim yêu thích */
        if ($this->subscriber_id) {
            $query->innerJoin('subscriber_favorite', 'subscriber_favorite.content_id = content.id');
            $query->andWhere(['subscriber_favorite.subscriber_id' => $this->subscriber_id]);
        }
        /** Lấy phim thuộc danh mục */
        if ($this->category_id) {
            $query->innerJoin('content_category_asm', 'content_category_asm.content_id = content.id');
            $query->andWhere(['content_category_asm.category_id' => $this->category_id]);
        }

        if ($this->site_id || $this->status) {
            $query->innerJoin('content_site_asm as csa', 'csa.content_id = content.id');
        }
        if ($this->status) {
            $query->andWhere(['csa.status' => $this->status]);
            $query->andWhere(['content.status' => $this->status]);
        }

        if ($this->site_id) {
            $query->andWhere(['csa.site_id' => $this->site_id]);
        }

        if ($this->language) {
            $query->andFilterWhere(['=', 'language', $this->language]);
        }

        if ($this->keyword) {
            $query->andFilterWhere(['or',
                ['like', 'display_name', $this->keyword],
                ['like', 'ascii_name', $this->keyword],
            ]);
        }
        return $dataProvider;
    }

    public function filterService($params, $type, $status, $sp_id, $admin = null, $limit = 30)
    {
        $query = Content::find();
        $query->andWhere(['AND', ['content.parent_id' => null], ['<>', 'content.is_series', Content::IS_SERIES]]);
        $activeCategory = ArrayHelper::map(Category::findAll(['status' => Category::STATUS_ACTIVE]), 'id', 'id');
        if (!$admin) {
            $query->select('`content`.*, content_site_asm.status as csa_status, content_site_asm.pricing_id as pricing_id');
        }
        // var_dump($inactiveCategory);die;

        if ($type == Category::TYPE_LIVE_CONTENT) {
            $query->select('`content`.*, started_at, ended_at, channel.display_name as channel_name, channel_id, live_program.status as live_status, content_site_asm.status as csa_status');
            $query->innerJoin('live_program', 'content.id = live_program.content_id');
            $query->innerJoin('content as channel', 'channel.id = live_program.channel_id');
            $query->innerJoin('content_site_asm', 'content_site_asm.content_id = live_program.channel_id');
            $query->andWhere('channel.status!= :p_status_delete', [':p_status_delete' => Content::STATUS_DELETE]);
            if (!$admin) {
                $query->andFilterWhere(['content_site_asm.site_id' => $sp_id]);
                $query->andFilterWhere(['IN', 'content_site_asm.status', array_keys(ContentSiteAsm::listStatusSP())]);
            }
        } else {
            $query->leftJoin('content_site_asm', 'content.id = content_site_asm.content_id');
            if (!$admin) {
                $query->andFilterWhere(['content_site_asm.site_id' => $sp_id]);
                $query->andFilterWhere(['IN', 'content_site_asm.status', array_keys(ContentSiteAsm::listStatusSP())]);
            }
            $query->innerJoin('content_category_asm', 'content.id = content_category_asm.content_id');
            $query->andWhere(['IN', 'content_category_asm.category_id', $activeCategory]);
            $query->andWhere(['content.status' => Content::STATUS_ACTIVE]);
        }
        if ($type == null) {
            $query->andFilterWhere(['<>', 'content.type', Category::TYPE_LIVE_CONTENT]);
        } else {
            $query->andFilterWhere(['content.type' => $type]);
        }
        $query->groupBy('content.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'display_name',
                    'channel_name',
                    'started_at',
                    'ended_at',
                    'live_status',
                    'created_at',
                    'status',
                    'is_series',
                    'csa_status',
                    'pricing_id',
                    'order',
                    'content.updated_at',
                    'cp_id'
                ],
                'defaultOrder' => ['content.updated_at' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => $limit,
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        Yii::trace($this->getAttributes());
        if ($this->categoryIds) {
            $categoryIds = explode(',', $this->categoryIds);
            //     $this->listCatIds = $categoryIds;

            // $query->distinct();

            $query->andWhere(['IN', 'content_category_asm.category_id', $categoryIds]);
        }

        $query->andFilterWhere(['=', 'cp_id', $this->cp_id]);

        $query->andFilterWhere(['like', 'content.display_name', $this->keyword]);
        $query->andWhere([
            'OR',
            ['like', 'content.display_name', $this->display_name],
            ['like', 'content.ascii_name', $this->display_name]
        ]);
        $query->andFilterWhere(['like', 'channel.display_name', $this->channel_name]);
        $query->andFilterWhere(['=', 'content.episode_order', $this->episode_order]);
        $query->andFilterWhere(['=', 'content.is_series', $this->is_series]);

        if ($admin) {
            $query->andFilterWhere(['=', 'content.status', $this->status]);
        } else {
            $query->andFilterWhere(['=', 'content_site_asm.status', $status]);
        }

        if ($this->pricing_id == 0 && $this->pricing_id !== null && $this->pricing_id !== "") {
            $query->andWhere(['IS', 'content_site_asm.pricing_id', null]);
        } else {
            $query->andFilterWhere(['=', 'content_site_asm.pricing_id', $this->pricing_id]);
        }

        $query->andFilterWhere(['=', 'content.order', $this->order]);

        $query->andFilterWhere(['=', 'content.language', $this->language]);
        if ($status) {
            $query->andFilterWhere(['content_site_asm.status' => $status]);
        }
        if ($this->created_at !== '' && $this->created_at !== null) {
            $from_time = strtotime(str_replace('/', '-', $this->created_at) . ' 00:00:00');
            $to_time = strtotime(str_replace('/', '-', $this->created_at) . ' 23:59:59');
            $query->andFilterWhere(['>=', 'content.created_at', $from_time]);
            $query->andFilterWhere(['<=', 'content.created_at', $to_time]);
        }
        // var_dump($query->createCommand()->rawSql);die;

        return $dataProvider;
    }

    public function filterAddEpisode($params)
    {
        $query = Content::find()
            ->andWhere(['parent_id' => NULL])
//            ->andWhere(['cp_id' => $params['ContentSearch']['cp_id']])
            ->andWhere(['type' => $params['ContentSearch']['type']])
            ->andWhere(['!=', 'content.status', Content::STATUS_DELETE])
            ->andWhere(['!=', 'content.status', Content::STATUS_INACTIVE])
            ->andWhere(['<>', 'is_series', Content::IS_SERIES]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => 30,
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'ascii_name', CVietnameseTools::makeSearchableStr($this->keyword)]);
        $query->andFilterWhere(['=', 'episode_order', CVietnameseTools::makeSearchableStr($this->episode_order)]);
        $query->andFilterWhere(['=', 'is_series', $this->is_series]);
        $query->andFilterWhere(['=', 'language', $this->language]);
        if ($this->display_name) {
            $query
                ->andWhere([
                    'OR',
                    ['like', 'ascii_name', $this->display_name],
                    ['like', 'display_name', $this->display_name]
                ]);
        }
        if ($this->status) {
            $query->andWhere(['content.status' => $this->status]);
        }
        if ($this->cp_id) {
            $query->andWhere(['cp_id' => $this->cp_id]);
        }
        if ($this->created_at) {
            $from_time = strtotime(str_replace('/', '-', $this->created_at) . ' 00:00:00');
            $to_time = strtotime(str_replace('/', '-', $this->created_at) . ' 23:59:59');
            $query->andFilterWhere(['>=', 'created_at', $from_time]);
            $query->andFilterWhere(['<=', 'created_at', $to_time]);
        }

        return $dataProvider;
    }
}
