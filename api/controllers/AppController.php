<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 22/05/2015
 * Time: 2:28 PM
 */

namespace api\controllers;


use common\models\Category;
use common\models\Content;
use common\models\ServiceGroup;
use common\models\SubscriberToken;

class AppController extends ApiController
{
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = [
            'home',
            'content-provider'
        ];

        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'index' => ['GET'],
        ];
    }

    /**
     * @param $order 0: newest, 1: mostview
     * @return array
     */
    public function actionHome($order = Content::ORDER_NEWEST)
    {
        $res = [];
        $film['service'] = ServiceGroup::getFirstPackage($this->serviceProvider->id, Category::TYPE_FILM);
        $film['contents'] = Content::getListContent($this->serviceProvider->id, Category::TYPE_FILM, 0, 0, '', $order)->getModels();

        $res['film'] = $film;
        $live['service'] = ServiceGroup::getFirstPackage($this->serviceProvider->id, Category::TYPE_LIVE);
        $live['contents'] = Content::getListContent($this->serviceProvider->id, Category::TYPE_LIVE, 0, 0, '', $order)->getModels();
        $res['live'] = $live;

        $music['service'] = ServiceGroup::getFirstPackage($this->serviceProvider->id, Category::TYPE_MUSIC);
        $music['contents'] = Content::getListContent($this->serviceProvider->id, Category::TYPE_MUSIC, 0, 0, '', $order)->getModels();
        $res['music'] = $music;
        $news['service'] = ServiceGroup::getFirstPackage($this->serviceProvider->id, Category::TYPE_NEWS);
        $news['contents'] = Content::getListContent($this->serviceProvider->id, Category::TYPE_NEWS, 0, 0, '', $order)->getModels();
        $res['news'] = $news;
        $clip['service'] = ServiceGroup::getFirstPackage($this->serviceProvider->id, Category::TYPE_CLIP);
        $clip['contents'] = Content::getListContent($this->serviceProvider->id, Category::TYPE_CLIP, 0, 0, '', $order)->getModels();
        $res['clip'] = $clip;
        return $res;
    }

    public function actionContentProvider()
    {

        return $this->serviceProvider->getListSP();

    }


    /**
     * Thực hiện
     * @param $parentGroup
     * @return mixed
     */
    public function groupParentOfParent(&$parentGroup)
    {
        foreach ($parentGroup as $item1) {
            foreach ($parentGroup as &$item2) {
                if (!empty($item2['children'])) {
                    foreach ($item2['children'] as &$it) {
                        if ($item1['id'] == $it['id']) {
                            $it['children'] = $item1['children'];
                            $parentGroup = $this->removeItemArray($parentGroup, $item1);
                            $this->groupParentOfParent($parentGroup);
                        }
                    }
                }

            }
        }
        return $parentGroup;
    }

    /**
     * Build lai mang
     *
     * @param $array
     * @param $item
     * @return array
     */
    public function removeItemArray(&$array, $item)
    {
        $data = array();
        if (count($array) > 0) {
            foreach ($array as $it) {
                if ($item['id'] != $it['id']) {//khong lay phan tu da duoc dua vao trong children
                    array_push($data, $it);
                }
            }
        }
        return $data;

    }

    /**
     * tra ve group parent
     *
     * @param $listCategory
     * @return array
     */
    public function groupParent($listCategory)
    {
        $arrayGroup = array();

        foreach ($listCategory as $cate) {
            $children = array();
            $i = 0;
            foreach ($listCategory as $item) {
                if ($cate['id'] == $item['parent_id']) {
                    $i++;
                    array_push($children, $item);
                    unset($listCategory[$i - 1]);
                }
            }
            if (!empty($children) || empty($cate['parent_id'])) {
                array_push($arrayGroup, [
                    'id' => $cate['id'],
                    'name' => $cate['name'],
                    'path' => $cate['path'],
                    'level' => $cate['level'],
                    'children' => $children
                ]);
            }
        }

        return $arrayGroup;
    }
//
//    public function actionTest($token_key)
//    {
//        $token = new SubscriberToken();
//        $token->subscriber_id = 2674;
//        $token->package_name = 'com.vivas.tvod.livebox2';
//        $token->token = $token_key;
//        $token->type = 2;
//        $token->ip_address = '14.177.236.154';
//        $token->status = 10;
//        $token->channel = 7;
//
//        $token->save();
//
//
//        $token_id = SubscriberToken::findOne(['token'=>$token_key])->id;
//        return $token_id;
//
//    }
}