<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "kodi_category".
 *
 * @property integer $id
 * @property string $description
 * @property string $display_name
 * @property string $image
 * @property integer $created_at
 * @property integer $parent
 * @property integer $updated_at
 * @property integer $status
 * @property KodiCategory[] $categories
 */

class KodiCategory extends \yii\db\ActiveRecord
{


    public $list_cat_id;
    private static $catTree  = array();

    const STATUS_ACTIVE   = 10;
    const STATUS_INACTIVE = 0;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'kodi_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['display_name', 'unique', 'message' => \Yii::t('app', 'Tên hiển thị đã tồn tại trên hệ thống. Vui lòng thử lại')],
            [[ 'display_name','image'], 'required', 'message' => \Yii::t('app', '{attribute} không được để trống'), 'on' => 'admin_create_update'],
            [
                [
                    'status',
                    'created_at',
                    'updated_at',
                ],
                'integer',
            ],
            [['list_cat_id'], 'string'],
            [['description','parent'], 'string'],
            [['display_name'], 'string', 'max' => 50],
            [['image'], 'string', 'max' => 255],
            [['image'], 'safe'],
            [['image'],
                'file',
                'tooBig'         => \Yii::t('app', ' File ảnh chưa đúng quy cách. Vui lòng thử lại'),
                'wrongExtension' => \Yii::t('app', ' File ảnh chưa đúng quy cách. Vui lòng thử lại'),
                'skipOnEmpty'    => true,
                'extensions'     => 'png, jpg, jpeg', 'maxSize' => 10 * 1024 * 1024],
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
            'description'      => Yii::t('app', 'Mô tả'),
            'status'           => Yii::t('app', 'Trạng thái'),
            'parent'             => Yii::t('app', 'Parent'),
            'image'           => Yii::t('app', 'Ảnh đại diện'),
            'created_at'       => Yii::t('app', 'Ngày tạo'),
            'updated_at'       => Yii::t('app', 'Ngày thay đổi thông tin'),
        ];
    }

    public function getIconUrl()
    {
        return Yii::getAlias($this->image);
    }

    public function getImageLink()
    {
        return $this->image ? Url::to(Yii::getAlias('@web') . DIRECTORY_SEPARATOR . Yii::getAlias('@cat_image') . DIRECTORY_SEPARATOR . $this->image,true) : '';
    }

    public static function getListStatus()
    {
        return [
            self::STATUS_ACTIVE   => \Yii::t('app', 'Hoạt động'),
            self::STATUS_INACTIVE => \Yii::t('app', 'Không hoạt động'),
        ];
    }

    public static function getAllCategories( $recursive = true, $sp_id = null, $cp_id = null)
    {
        $res = [];

            if ($cp_id != null) {
                $root_cats = KodiCategory::find()
                    ->andWhere('parent is null')
                    ->orderBy(['id' => SORT_DESC])->all();
            } else {
                $root_cats = KodiCategory::find()
                    ->andWhere('parent is null')
                    ->orderBy(['id' => SORT_DESC])->all();
            }
//        var_dump($root_cats);exit;
            if ($root_cats) {
                foreach ($root_cats as $cat) {
                    /* @var $cat KodiCategory */
                    $res[]  = $cat;
//                    if ($recursive) {
//                        $res = ArrayHelper::merge($res,
//                            KodiCategory::getAllCategories( $recursive, $sp_id, $cp_id));
//                    }
                }
        }

        return $res;
    }

    public function getStatusName()
    {
        $listStatus = self::getListStatus();
        if (isset($listStatus[$this->status])) {
            return $listStatus[$this->status];
        }
        return '';
    }

    public static function getMenuTreeCate($type, $sp_id = null, $admin = null)
    {
        if (empty(self::$catTree[$type])) {
            $query = KodiCategory::find();

            $query->andWhere(['kodi_category.status' => self::STATUS_ACTIVE]);
            $query->andWhere('kodi_category.parent is null');
            $query->orderBy(['kodi_category.created_at' => SORT_ASC]);
            $rows = $query->all();
            // var_dump($admin);die;
            if (count($rows) > 0) {
                foreach ($rows as $item) {
                    /** @var $item KodiCategory */

                    self::$catTree[$type][] = array('id' => intval($item['id']), 'label' => $item['display_name'], 'items' => self::getMenuItems($item->id, $type, $sp_id));
                }
            } else {
                self::$catTree[$type] = [];
            }
            Yii::info(self::$catTree[$type]);
        }
        return self::$catTree[$type];

    }


    private static function getMenuItemsCat($modelRow, $type, $sp_id)
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

    public static function getMenuTree($type, $sp_id = null, $admin = null)
    {
        if (empty(self::$catTree[$type])) {
            $query = KodiCategory::find();

            $query->andWhere(['kodi_category.status' => self::STATUS_ACTIVE]);
            $query->andWhere('kodi_category.parent is null');
            $query->orderBy(['kodi_category.created_at' => SORT_ASC]);
            $rows = $query->all();
            // var_dump($admin);die;
            if (count($rows) > 0) {
                foreach ($rows as $item) {
                    /** @var $item KodiCategory */

                    self::$catTree[$type][] = self::getMenuItemsCat($item, $type, $sp_id);
                }
            } else {
                self::$catTree[$type] = [];
            }
            Yii::info(self::$catTree[$type]);
        }
        return self::$catTree[$type];

    }

    public function getCategories( $site_id = null)
    {
            return KodiCategory::find()
                ->innerJoin('kodi_category_item_asm', 'kodi_category_item_asm.category_id=kodi_category.id')
                ->andWhere(['kodi_category_item_asm.site_id' => $site_id])
                ->andWhere(['kodi_category.status' => KodiCategory::STATUS_ACTIVE])
                ->orderBy(['order_number' => SORT_DESC])->all();

    }

    private static function getMenuItems($modelRow, $type, $sp_id)
    {

        if (!$modelRow) {

            return;
        }
        if (isset($modelRow->categories)) {
            /** @var  $modelRow KodiCategory */
            $childCategories = $modelRow->getCategories( $sp_id);
            $chump = self::getMenuItems($childCategories, $type, $sp_id);
            if ($chump != null) {
                $res = array('id' => $modelRow->id, 'label' => $modelRow->display_name, 'items' => $chump);
            } else {
                $res = array('id' => $modelRow->id, 'label' => $modelRow->display_name, 'items' => array());
            }
            return $res;
        } else {
                $arr = array();
                $cat = self::getKodiCategoryAddon($modelRow);
                foreach ($cat as $leaves) {
                    $arr[] = array('id' => intval($leaves['id']), 'label' => $leaves['display_name'], 'items' => array());
                }
                return $arr;
        }
    }

    public static function getKodiCategoryAddon($id){
        $arr = array();
        $listAddons = KodiCategory::find()->andWhere('parent is not null')
            ->andWhere(['like','parent',$id])->all();
        foreach($listAddons as $item){
            /** @var $item KodiCategory */
            $parent = explode(',',$item->parent);
            if(in_array($id,$parent)){
                $arr[] = (intval($item->id));
            }
        }
        $category = KodiCategory::find()
            ->andWhere('parent is not null')
            ->andWhere(['in','id',$arr])->asArray()->all();
        return $category;
    }

    public function getAllCategory()
    {
        $res = '';
        $category = KodiCategory::findOne(['id'=>$this->id]);
        $parent = explode(',',$category['parent']);
        for($i=0; $i<sizeof($parent);$i++){
            $cat = KodiCategory::findOne(['id'=>$parent[$i]]);
            $res .= $cat['display_name'].',';
        }
        return rtrim($res,',');
    }

    public function getAllCategoryId()
    {
        $res = '';
        $category = KodiCategory::findOne(['id'=>$this->id]);
        foreach ($category as $item) {
            $res = $category['parent'];

        }
        return rtrim($res,',');
    }

    public function getCategoryById()
    {
        $res = '';
        $category = KodiCategory::find()->
        select(['kodi_category.id'])
            ->innerJoin('category_addon_asm', 'category_addon_asm.id_category = kodi_category.id')
            ->andWhere(['category_addon_asm.id_addon'=>$this->id])
            ->andWhere(['kodi_category.status'=>self::STATUS_ACTIVE])->all();
        foreach ($category as $item) {
            $res .= $item['id'].',';

        }
        return rtrim($res,',');
    }

    public static function getCategory(){
        $result = [];
        $listCategory = KodiCategory::find()->andWhere('parent is null')
            ->andWhere(['status'=>KodiCategory::STATUS_ACTIVE])->all();

        /** @var $row KodiCategory */
        foreach($listCategory as $row){
            $group_tmp = $row->getAttributes(null,['image']);
            $group_tmp['image'] = $row->getImageLink();
            $group_tmp['addon'] = [];
            $listaddon = KodiCategory::find()->andWhere('parent is not null')
                ->andWhere(['status'=>KodiCategory::STATUS_ACTIVE])
                ->andWhere(['like','parent',$row->id])
                ->orderBy(['created_at'=>SORT_DESC])->limit(30)->all();
            /** @var $asm KodiCategory  */
            foreach($listaddon as $asm){
                $addon = [];
                $addon['id'] = $asm->id;
                $addon['description'] = $asm->description;
                $addon['display_name'] = $asm->display_name;
                $addon['status'] = $asm->status;
                $addon['created_at'] = $asm->created_at;
                $addon['updated_at'] = $asm->updated_at;
                $addon['parent'] = $asm->parent;
                $addon['items'] = [];

                $listItem = ItemKodi::find()
                    ->innerJoin('kodi_category_item_asm','kodi_category_item_asm.item_id = item_kodi.id')
                    ->innerJoin('kodi_category','kodi_category.id = kodi_category_item_asm.category_id')
                    ->andWhere(['item_kodi.status'=>ItemKodi::STATUS_ACTIVE])
                    ->andWhere(['kodi_category_item_asm.category_id'=>$asm->id])
                    ->orderBy(['created_at'=>SORT_DESC])->limit(30)->all() ;
                /** @var  $items ItemKodi */
                foreach($listItem as $items){
                    $item = [];
                    $item['id'] = $items->id;
                    $item['display_name'] = $items->display_name;
                    $item['description'] = $items->description;
                    $item['image'] = $items->getImageLink();
                    $item['path'] = $items->path;
                    $item['type'] = $items->type;
                    $item['file_download'] = $items->file_download;
                    $item['status'] = $items->status;
                    $item['honor'] = $items->honor;
                    $item['created_at'] = $items->created_at;
                    $item['updated_at'] = $items->updated_at;
                    $addon['items'][] = $item;
                }

                $group_tmp['addon'][] = $addon;
            }
            $result[] = $group_tmp;
        }
        return $result;
    }
}
