<?php
/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 04-Aug-16
 * Time: 10:03 AM
 */

namespace api\controllers;


use api\models\ItemKodi;
use common\models\KodiCategory;
use yii\data\ActiveDataProvider;
use yii\web\Response;

class KodiController extends ApiController
{
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = [
//            'index',
            'view',
            'get-list-cat',
            'get-list-item',
            'get-list-cat-json',
            'get-list-item-json',
            'test',
            'get-list-category',
            'get-list-addon',
            'get-item-addon',
            'get-category-item-addon',
        ];
        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'index' => ['GET'],
        ];
    }

    public function actionTest()
    {
        $res = [];
        $res['film'] = "a";
        $res['music'] = 'b';
        return $res;
    }

    public function actionGetListCat()
    {
        $category = \api\models\KodiCategory::find()
            ->andWhere(['status' => KodiCategory::STATUS_ACTIVE]);
        $data = new ActiveDataProvider([
            'query' => $category,
        ]);
        \Yii::$app->response->format = Response::FORMAT_XML;
        return $data;
    }

    public function actionGetListItem($cat_id = 0, $type = null, $filter = null)
    {

        $listItemKodi = ItemKodi::find()
            ->innerJoin('kodi_category_item_asm', 'kodi_category_item_asm.item_id = item_kodi.id')
            ->innerJoin('kodi_category', 'kodi_category.id = kodi_category_item_asm.category_id')
            ->andWhere(['kodi_category.id' => $cat_id]);
        if ($type != null) {
            $listItemKodi->andWhere(['item_kodi.type' => $type]);
        }
        if ($filter == 'favourite') {
            $listItemKodi->andWhere(['item_kodi.honor' => ItemKodi::IS_HONOR]);
        }
        $listItemKodi->all();
//        foreach($listItemKodi as $item){
//            var_dump($item);exit;
//        }

        $data = new ActiveDataProvider([
            'query' => $listItemKodi,
        ]);
//        var_dump($data->models);exit;
        \Yii::$app->response->format = Response::FORMAT_XML;
        return $data;
    }

    public function actionGetListCatJson()
    {
        $category = \api\models\KodiCategory::find()
            ->andWhere(['status' => KodiCategory::STATUS_ACTIVE]);
        $data = new ActiveDataProvider([
            'query' => $category,
        ]);
        return $data;
    }

    public function actionGetListItemJson($cat_id = 0, $type = null, $filter = null)
    {

        $listItemKodi = ItemKodi::find()
            ->innerJoin('kodi_category_item_asm', 'kodi_category_item_asm.item_id = item_kodi.id')
            ->innerJoin('kodi_category', 'kodi_category.id = kodi_category_item_asm.category_id')
            ->andWhere(['kodi_category.id' => $cat_id]);
        if ($type != null) {
            $listItemKodi->andWhere(['item_kodi.type' => $type]);
        }
        if ($filter == 'favourite') {
            $listItemKodi->andWhere(['item_kodi.honor' => ItemKodi::IS_HONOR]);
        }

        $data = new ActiveDataProvider([
            'query' => $listItemKodi,
        ]);
        return $data;
    }

    public function actionGetListCategory()
    {
        $listCategory =  \api\models\KodiCategory::find()->andWhere('parent is null')
        ->andWhere(['status'=>KodiCategory::STATUS_ACTIVE]);
        $data = new ActiveDataProvider([
            'query' => $listCategory
        ]);
        return $data;
    }

    public function actionGetCategoryItemAddon(){
        return KodiCategory::getCategory();
    }

    public function actionGetListAddon($id){
        $arr = array();
        $listAddons = KodiCategory::find()->andWhere('parent is not null')
            ->andWhere(['status'=>KodiCategory::STATUS_ACTIVE])
            ->andWhere(['like','parent',$id])->all();
        foreach($listAddons as $item){
            /** @var $item KodiCategory */
            $parent = explode(',',$item->parent);
            if(in_array($id,$parent)){
                $arr[] = (intval($item->id));
            }
        }
        $listAddon = \api\models\KodiCategory::find()->andWhere('parent is not null')
            ->andWhere(['status'=>KodiCategory::STATUS_ACTIVE])
            ->andWhere(['in','id',$arr]);
        $data = new ActiveDataProvider([
            'query' => $listAddon
        ]);
        return $data;
    }

    public function actionGetItemAddon($id){
        $listItem = ItemKodi::find()
            ->innerJoin('kodi_category_item_asm','kodi_category_item_asm.item_id = item_kodi.id')
            ->innerJoin('kodi_category','kodi_category.id = kodi_category_item_asm.category_id')
            ->andWhere(['item_kodi.status'=>ItemKodi::STATUS_ACTIVE])
            ->andWhere(['kodi_category_item_asm.category_id'=>$id]);

        $data = new ActiveDataProvider([
            'query' => $listItem
        ]);
        return $data;
    }

}