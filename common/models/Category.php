<?php

namespace common\models;

use api\helpers\Message;
use api\helpers\UserHelpers;
//use api\models\Content;
use common\helpers\CUtils;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "{{%category}}".
 *
 * @property integer $id
 * @property integer $show_on_portal
 * @property string $display_name
 * @property string $ascii_name
 * @property integer $type
 * @property string $description
 * @property integer $status
 * @property integer $order_number
 * @property integer $parent_id
 * @property string $path
 * @property integer $level
 * @property integer $child_count
 * @property string $images
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $admin_note
 * @property integer $show_on_client
 * @property integer $is_content_service
 * @property integer $tvod1_id
 * @property integer $is_series
 *
 * @property Category $parent
 * @property Category[] $categories
 * @property ContentCategoryAsm[] $contentCategoryAsms
 * @property CategorySiteAsm[] $categorySiteAsms
 * @property ContentViewLog[] $contentViewLogs
 * @property ReportContent[] $reportContents
 * @property ServiceCategoryAsm[] $serviceCategoryAsms
 *
 */
class Category extends \yii\db\ActiveRecord
{
    const TYPE_FILM         = 1;
    const TYPE_LIVE         = 2;
    const TYPE_MUSIC        = 3;
    const TYPE_NEWS         = 4;
    const TYPE_CLIP         = 5;
    const TYPE_KARAOKE      = 6;
    const TYPE_RADIO        = 7;
    const TYPE_LIVE_CONTENT = 8;

    const STATUS_ACTIVE   = 10;
    const STATUS_INACTIVE = 0;
    const STATUS_DELETED = 1;

    const IS_MOVIES = 0;
    const IS_SERIES = 1;

    public $path_name;
    public $assignment_sites = [];
    const CHILD_NODE_PREFIX  = '|--';
    private static $catTree  = array();

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['display_name', 'unique', 'when' => function ($model) {return $model->type == $this->type && $model->status != self::STATUS_INACTIVE;}, 'filter' => ['type' => $this->type], 'message' => 'Tên hiển thị đã tồn tại trên hệ thống. Vui lòng thử lại'],
            [['display_name', 'assignment_sites'], 'required', 'message' =>  Yii::t('app','{attribute} không được để trống'), 'on' => 'admin_create_update'],
            [
                [
                    'type',
                    'status',
                    'order_number',
                    'parent_id',
                    'level',
                    'child_count',
                    'created_at',
                    'updated_at',
                    'show_on_client',
                    'show_on_portal',
                    'is_content_service',
                    'tvod1_id',
                    'is_series',
                ],
                'integer',
            ],
            [['description'], 'string'],
            [['display_name', 'ascii_name'], 'string', 'max' => 200],
            [['images'], 'string', 'max' => 500],
            [['images'], 'safe'],
            [['images'],
                'file',
                'tooBig'         =>  Yii::t('app',' File ảnh chưa đúng quy cách. Vui lòng thử lại'),
                'wrongExtension' =>  Yii::t('app',' File ảnh chưa đúng quy cách. Vui lòng thử lại'),
                'skipOnEmpty'    => true,
                'extensions'     => 'png, jpg, jpeg', 'maxSize' => 10 * 1024 * 1024],
            [['admin_note'], 'string', 'max' => 4000],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'               => Yii::t('app', 'ID'),
            'display_name'     => Yii::t('app', 'Tên danh mục'),
            'ascii_name'       => Yii::t('app', 'Ascii Name'),
            'type'             => Yii::t('app', 'Type'),
            'description'      => Yii::t('app', 'Mô tả'),
            'status'           => Yii::t('app', 'Trạng thái'),
            'order_number'     => Yii::t('app', 'Sắp xếp'),
            'parent_id'        => Yii::t('app', 'Danh mục cha'),
            'path'             => Yii::t('app', 'Path'),
            'level'            => Yii::t('app', 'Level'),
            'child_count'      => Yii::t('app', 'Child Count'),
            'images'           => Yii::t('app', 'Ảnh đại diện'),
            'created_at'       => Yii::t('app', 'Ngày tạo'),
            'updated_at'       => Yii::t('app', 'Ngày thay đổi thông tin'),
            'admin_note'       => Yii::t('app', 'Admin Note'),
            'assignment_sites' => Yii::t('app', 'Nhà cung cấp dịch vụ'),
            'is_series'        => Yii::t('app', 'Phim bộ'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Category::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories($type = self::TYPE_FILM, $sp_id = null, $cp_id = null)
    {
        if ($sp_id) {
            return $this->hasMany(Category::className(), ['parent_id' => 'id'])
                ->innerJoin('category_site_asm', 'category.id=category_site_asm.category_id')
                ->where('type= :p_type', [':p_type' => $type])
                ->andWhere(['category_site_asm.site_id' => $sp_id])
                ->andWhere(['category.status' => Category::STATUS_ACTIVE]) // Ai commen phải báo loop vì comment là không đúng
                ->orderBy(['order_number' => SORT_DESC])->all();
        } else {
            return $this->hasMany(Category::className(), ['parent_id' => 'id'])
                ->where('type= :p_type', [':p_type' => $type])
                ->andWhere(['category.status' => Category::STATUS_ACTIVE]) // Ai commen phải báo loop vì comment là không đúng
                ->orderBy(['category.order_number' => SORT_DESC])->all();
        }

    }

    public function getBECategories($type = self::TYPE_FILM, $site_id = null, $cp_id = null)
    {
        if ($cp_id == null) {
            return $this->hasMany(Category::className(), ['parent_id' => 'id'])
                ->innerJoin('category_site_asm', 'category.id=category_site_asm.category_id')
                ->where('type= :p_type', [':p_type' => $type])
                ->andWhere(['IN', 'category.status', array_keys(self::getListStatus())])
            // ->andWhere(['category_site_asm.site_id' => $site_id])
                ->orderBy(['order_number' => SORT_DESC])->all();
        } else {
            return $this->hasMany(Category::className(), ['parent_id' => 'id'])
                ->innerJoin('category_site_asm', 'category.id=category_site_asm.category_id')
                ->where('type= :p_type', [':p_type' => $type])
                ->andWhere(['IN', 'category.status', array_keys(self::getListStatus())])
            // ->andWhere(['category_site_asm.site_id' => $site_id])
                ->orderBy(['category.order_number' => SORT_DESC])->all();
        }

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentProviderAsms($type = self::TYPE_FILM, $sp_id = null, $cp_id = null)
    {

        return $this->hasMany(ContentProviderCategoryAsm::className(), ['category_id' => 'id'])
            ->where('type= :p_type', [':p_type' => $type])
        // ->andFilterWhere(['site_id' => $sp_id])
            ->andFilterWhere(['content_provider_id' => $sp_id])
            ->orderBy(['order_number' => SORT_DESC])->all();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentProviderAsms1()
    {

        return $this->hasMany(ContentProviderCategoryAsm::className(), ['category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategorySiteAsms()
    {
        return $this->hasMany(CategorySiteAsm::className(), ['category_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentViewLogs()
    {
        return $this->hasMany(ContentViewLog::className(), ['category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReportContents()
    {
        return $this->hasMany(ReportContent::className(), ['category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceCategoryAsms()
    {
        return $this->hasMany(ServiceCategoryAsm::className(), ['category_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentCategoryAsms()
    {
        return $this->hasMany(ContentCategoryAsm::className(), ['category_id' => 'id']);
    }

    public static function getTreeCategories($type, $sp_id = null)
    {
        return ArrayHelper::map(Category::getAllCategories(null, true, $type, $sp_id), 'id', 'path_name');
    }

    public static function getAllCategories($cat_id = null, $recursive = true, $type = self::TYPE_FILM, $sp_id = null, $cp_id = null)
    {
        $res = [];
        if ($cat_id != null) {
            $model = Category::findOne(['id' => $cat_id]);

            if ($model === null) {
                throw new NotFoundHttpException(404, Yii::t('app',"Yêu cầu danh mục (#$cat_id) không tồn tại."));
            }

            //Thuc: them 'order'=>'order_number ASC' trong ham relations
            $children = $model->getBECategories($type, $sp_id, $cp_id);

            if ($children) {
                foreach ($children as $child) {
                    $path = "";
                    for ($i = 0; $i < $child->level; $i++) {
                        $path .= Category::CHILD_NODE_PREFIX;
                    }
//                    $child->name = $path . $child->name;
                    $child->path_name = $path . $child->display_name;
                    $res[]            = $child;
                    if ($recursive) {
                        $res = ArrayHelper::merge($res,
                            Category::getAllCategories($child->id, $recursive, $type, $sp_id, $cp_id));
                    }
                }
            }
        } else {
            if ($cp_id != null) {
                $root_cats = Category::find()
                    ->innerJoin('content_provider_category_asm', 'category.id=content_provider_category_asm.category_id')
                    ->andWhere(['content_provider_category_asm.content_provider_id' => $cp_id])
                    ->andWhere(['category.level' => 0])->andWhere('category.type=:p_type', [':p_type' => $type])
                    ->andWhere(['IN', 'category.status', array_keys(self::getListStatus())])
                // ->andFilterWhere(['category.site_id' => $sp_id])
                    ->orderBy(['category.order_number' => SORT_DESC])->all();
            } else {
                $root_cats = Category::find()->andWhere(['level' => 0])
                    ->andWhere('type=:p_type', [':p_type' => $type])
                    ->andWhere(['IN', 'category.status', array_keys(self::getListStatus())])
                    ->orderBy(['order_number' => SORT_DESC])->all();
            }

            if ($root_cats) {
                foreach ($root_cats as $cat) {
                    /* @var $cat Category */
                    $cat->path_name = $cat->display_name;
                    $res[]          = $cat;
                    if ($recursive) {
                        $res = ArrayHelper::merge($res,
                            Category::getAllCategories($cat->id, $recursive, $type, $sp_id, $cp_id));
                    }
                }
            }
        }

        return $res;
    }

    public static function getListStatus()
    {
        return [
            self::STATUS_ACTIVE   => Yii::t('app','Đang hoạt động'),
            self::STATUS_INACTIVE => Yii::t('app','Tạm khóa'),
        ];
    }

    public function getStatusName()
    {
        $listStatus = self::getListStatus();
        if (isset($listStatus[$this->status])) {
            return $listStatus[$this->status];
        }
        return '';
    }

    public function getIconUrl()
    {
        return Yii::getAlias($this->images);
    }

    public function getImageLink()
    {
        return $this->images ? Url::to(Yii::getAlias('@web') . DIRECTORY_SEPARATOR . Yii::getAlias('@cat_image') . DIRECTORY_SEPARATOR . $this->images, true) : '';
        // return $this->images ? Url::to('@web/' . Yii::getAlias('@cat_image') . DIRECTORY_SEPARATOR . $this->images, true) : '';
    }

    public static function getListType($type = null)
    {
        $listType = [
            self::TYPE_FILM         =>  Message::getNameFilm(),
            self::TYPE_LIVE         => Message::getNameLive(),
            self::TYPE_MUSIC        => Message::getNameMusic(),
            self::TYPE_NEWS         => Message::getNameNew(),
            self::TYPE_CLIP         => Message::getNameClip(),
            self::TYPE_KARAOKE      => Message::getNameKaraoke(),
            self::TYPE_RADIO        => Message::getNameRadio(),
            self::TYPE_LIVE_CONTENT => Message::getNameLiveContent()
        ];

        if ($type) {
            return $listType[$type];
        }

        return $listType;
    }

    public static function getListTypeContentCr32($type = null)
    {
        $listType = [
            self::TYPE_FILM         =>  Message::getNameFilm(),
            self::TYPE_KARAOKE      => Message::getNameKaraoke(),
            self::TYPE_CLIP         => Message::getNameClip(),
            self::TYPE_LIVE         => Message::getNameLive(),
            self::TYPE_MUSIC        => Message::getNameMusic(),
            self::TYPE_NEWS         => Message::getNameNew(),
            self::TYPE_RADIO        => Message::getNameRadio()
        ];

        if ($type) {
            return $listType[$type];
        }

        return $listType;
    }

    public static function getTypeName($type)
    {
        $types = self::getListType();
        if (isset($types[$type])) {
            return $types[$type];
        }
        return '';
    }

    /**
     * return : 1: max, 2: min, 3: middle
     */
    public function checkPositionOnTree()
    {
        if ($this->parent_id == null) {
            $minMaxOrder = Category::find()->select(['max(order_number) as max', 'min(order_number) as min'])
                ->where('parent_id is null')->andWhere(['type' => $this->type])->asArray()->one();
        } else {
            $minMaxOrder = Category::find()->select(['max(order_number) as max', 'min(order_number) as min'])
                ->where('parent_id =:p_parent_id', [':p_parent_id' => $this->parent_id])->andWhere(['type' => $this->type])->asArray()->one();
        }
        if ($minMaxOrder) {
            if ($minMaxOrder['max'] == $minMaxOrder['min']) {
                return 3;
            }
            if ($minMaxOrder['max'] <= $this->order_number) {
                return 1;
            } else if ($minMaxOrder['min'] >= $this->order_number) {
                return 2;
            }

            return 4;
        }
    }

    public static function getMenuTree($type, $sp_id = null, $cp_id = null)
    {
        if (empty(self::$catTree[$type])) {
            $query = Category::find();
            // if ($cp_id != null) {
            //     $query->innerJoin('content_provider_category_asm', 'category.id=content_provider_category_asm.category_id')
            //         ->andWhere(['content_provider_category_asm.content_provider_id' => $cp_id]);
            //
            // }
            $query->andWhere(['category.level' => 0]);
            $query->andWhere(['category.status' => self::STATUS_ACTIVE]);
            $query->andWhere('category.type=:p_type', [':p_type' => $type]);
            if (!$cp_id && $type != self::TYPE_LIVE_CONTENT) {
                $query->innerJoin('category_site_asm', 'category.id=category_site_asm.category_id');
                $query->andFilterWhere(['category_site_asm.site_id' => $sp_id]);
            }
            $query->orderBy(['category.order_number' => SORT_ASC]);
            $rows = $query->all();
            // var_dump($cp_id);die;
            if (count($rows) > 0) {
                foreach ($rows as $item) {
                    /** @var $item Category */
                    self::$catTree[$type][] = self::getMenuItems($item, $type, $sp_id);
                }
            } else {
                self::$catTree[$type] = [];
            }
            Yii::info(self::$catTree[$type]);
        }
        return self::$catTree[$type];

    }

    /**
     * @param $modelRow Category
     * @return array|void
     */
    private static function getMenuItems($modelRow, $type, $sp_id)
    {

        if (!$modelRow) {
            return;
        }

        if (isset($modelRow->categories)) {
            /** @var  $modelRow Category */
            $childCategories = $modelRow->getCategories($type, $sp_id);

            $chump = self::getMenuItems($childCategories, $type, $sp_id);
            if ($chump != null) {
                $res = array('id' => $modelRow->id, 'label' => $modelRow->display_name, 'items' => $chump);
            } else {
                $res = array('id' => $modelRow->id, 'label' => $modelRow->display_name, 'items' => array());
            }
            return $res;
        } else {
            if (is_array($modelRow)) {
                $arr = array();
                foreach ($modelRow as $leaves) {
                    $arr[] = self::getMenuItems($leaves, $type, $sp_id);
                }
                return $arr;
            } else {
                return array('id' => $modelRow->id, 'label' => ($modelRow->display_name));
            }
        }
    }

    /**
     * @param null $cat_id
     * @param bool $recursive
     * @param int $type
     * @param null $sp_id
     * @param int $is_content_service
     * @return array
     * @throws NotFoundHttpException
     */
    public static function getApiAllCategories($parren_id = null, $recursive = true, $type = self::TYPE_FILM, $site_id = null)
    {
        $res = [];
        if ($parren_id != null) {
//            $model = Category::findOne(['id' => $parren_id,'status'=>Category::STATUS_ACTIVE]);
            /** @var  $model Category*/
            $model = Category::find()
                ->innerJoin('category_site_asm', 'category.id=category_site_asm.category_id')
                ->andWhere(['category_site_asm.site_id' => $site_id])
                ->andWhere(['category.id' => $parren_id, 'status' => Category::STATUS_ACTIVE])
                ->one();

            if ($model === null) {
                throw new NotFoundHttpException(404, "The requested Vod Category (#$parren_id) does not exist.");
            }

            //Thuc: them 'order'=>'order_number ASC' trong ham relations
            $children = $model->getCategories($type, $site_id);
            if ($children) {
                /**
                 * @var $child Category
                 */
                foreach ($children as $child) {
                    $path = "";
                    for ($i = 0; $i < $child->level; $i++) {
                        $path .= Category::CHILD_NODE_PREFIX;
                    }
//                    $child->name = $path . $child->name;
                    $child->path_name         = $path . $child->display_name;
                    $child_array              = $child->getAttributes(null, ['tvod1_id', 'is_content_service', 'show_on_portal', 'show_on_client',  'admin_note', 'path']);
                    $child_array['images']    = $child->getImageLink();
                    $child_array['shortname'] = CUtils::parseTitleToKeyword($child->display_name);
                    if ($recursive) {
                        $child_array['children'] = Category::getApiAllCategories($child->id, $recursive, $type, $site_id);
                    }
                    $res[] = $child_array;
                }
            }
        } else {
//            $root_cats = Category::find()->andWhere(['level' => 0,'status'=>Category::STATUS_ACTIVE])->andWhere('type=:p_type', [':p_type' => $type])
            //                ->andFilterWhere(['site_id' => $site_id])
            //                ->orderBy(['order_number' => SORT_DESC])->all();
            $root_cats = Category::find()
                ->innerJoin('category_site_asm', 'category.id=category_site_asm.category_id')
                ->andWhere(['category_site_asm.site_id' => $site_id])
                ->andWhere(['category.level' => 0, 'category.status' => Category::STATUS_ACTIVE, 'category.type' => $type])
                ->orderBy(['order_number'=> SORT_DESC])
                ->all();

            if ($root_cats) {
                foreach ($root_cats as $cat) {
                    /* @var $cat Category */
                    $cat->path_name         = $cat->display_name;
                    $cat_array              = $cat->getAttributes(null, ['tvod1_id', 'is_content_service', 'show_on_portal', 'show_on_client', 'admin_note', 'path']);
                    $cat_array['shortname'] = CUtils::parseTitleToKeyword($cat->display_name);
                    $cat_array['images']    = $cat->getImageLink();
                    if ($recursive) {
                        $cat_array['children'] = Category::getApiAllCategories($cat->id, $recursive, $type, $site_id);
                    }
                    $res[] = $cat_array;
                }
            }
        }

        return $res;
    }

    /*
     * API của anh Cường tạm thời comment lại để viết lại và bổ sung
     */
//
//    public static function getApiRootCategories($type = self::TYPE_FILM, $id, $site_id = null)
//    {
//        $res = [];
//
//        $root_cats = Category::find()
//            ->andWhere(['level' => 0])
//            ->andWhere('type=:p_type', [':p_type' => $type])
//            ->andFilterWhere(['site_id' => $site_id])
//            ->orderBy(['order_number' => SORT_DESC])->all();
//        /**
//         * API này bị lỗi, không lấy được danh sách phim gắn với category. Logic hiện tại là đang gắn với rootCat chứ không phải CatID
//         */
//
//        if ($root_cats) {
//            foreach ($root_cats as $cat) {
//                /* @var $cat Category */
//                $cat->path_name      = $cat->display_name;
//                $cat_array           = $cat->getAttributes(null, ['show_on_portal', 'show_on_client', 'order_number', 'admin_note', 'path', 'updated_at', 'created_at']);
//                $cat_array['images'] = $cat->getImageLink();
//                $content_return      = [];
//                $contents            = Content::find()
//                    ->select(['content.display_name', 'content.id', 'content.is_free', 'content.images'])
//                    ->innerJoin('content_category_asm', 'content_category_asm.content_id=content.id')
//                    ->andWhere(['status' => Content::STATUS_ACTIVE])
//                    ->andWhere(['content_category_asm.category_id' => $cat->id])
//                    ->limit(10)->all();
//                /** @var  $content  Content */
//                foreach ($contents as $content) {
//                    $content_array['id']           = $content->id;
//                    $content_array['display_name'] = $content->display_name;
//                    if (Category::checkApp(($content->id))) {
//                        $content_array['is_free'] = 1;
//                    } else {
//                        $content_array['is_free'] = 0;
//                    }
//                    $content_array['images'] = $content->getFirstImageLink();
//                    $content_array['image']  = $content->getFirstImageLink();
//                    $content_return[]        = $content_array;
//                }
//
//                $cat_array['contents'] = $content_return;
//
//                $res[] = $cat_array;
//            }
//        }
//
//        return $res;
//    }
//
//    public static function checkApp($id)
//    {
//        UserHelpers::manualLogin();
//        $subscriber = Yii::$app->user->identity;
//        if (!$subscriber) {
//            return 0;
//        }
//
//        /* @var $model Content */
//        /* @var $subscriber Subscriber */
//
//        if ($subscriber->checkMyApp($id)) {
//            return 1;
//        } else {
//            return 0;
//        }
//    }

    public function saveCategorySiteAsm()
    {
        CategorySiteAsm::deleteAll(['category_id' => $this->id]);
        if ($this->assignment_sites) {
            foreach ($this->assignment_sites as $site_id) {
                $siteAsm              = new CategorySiteAsm();
                $siteAsm->category_id = $this->id;
                $siteAsm->site_id     = $site_id;
                $siteAsm->save();
            }

            return true;
        }

        return true;
    }

    public static function countContent($cat_id)
    {
        //validate
        /** @var $count */
        $count = ContentCategoryAsm::findAll(['category_id' => $cat_id]);
        if (count($count)) {
            return count($count);
        }
        return false;
    }

    public static function findCategoryBySiteContent($site_id, $content_type)
    {
//        return Category::find()->andWhere(['status' => Service::STATUS_ACTIVE, 'site_id'=>$site_id,'type'=>$content_type])->all();
        return Category::getAllCategories(null, $content_type, $content_type, $site_id);
    }

    public static function getAllChildCats($type, $parent = null)
    {
        if ($parent === null) {
            return [];
        }

        static $listCat = [];

        $cats = self::findAll(['type' => $type, 'parent_id' => $parent]);

        if (!empty($cats)) {
            foreach ($cats as $cat) {

                $listCat[$cat->id] = ['disabled' => true];
                self::getAllChildCats($type, $cat->id);
            }
        }

        return $listCat;
    }

    public static function findCategoryByTypeAndSite($site_id, $type)
    {
        return Category::find()
            ->innerJoin('category_site_asm','category_site_asm.category_id=category.id')
            ->where(['category_site_asm.site_id'=>$site_id])
            ->andWhere(['category.type'=>$type])
            ->andWhere(['category.status' => Category::STATUS_ACTIVE])
            ->orderBy('category.path')
            ->all();
    }
}
