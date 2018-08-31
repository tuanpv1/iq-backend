<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 22/05/2015
 * Time: 2:28 PM
 */

namespace api\controllers;


use api\helpers\Message;
use api\helpers\UserHelpers;
use common\models\AccessSystem;
use common\models\Category;

use common\models\Subscriber;
use Yii;

use common\models\CategorySearch;

use yii\base\InvalidValueException;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

class CategoryController extends ApiController
{
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = [
            'index',
            'view',
            'test',
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
     * @param int $type
     * @return array
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionIndex($type = 0)
    {
        UserHelpers::manualLogin();
        $res = array();
        if ($type == 0) {
//            array_push($res,Category::getApiAllCategories(null, true, Category::TYPE_FILM, $this->site->id));
//            array_push($res,Category::getApiAllCategories(null, true, Category::TYPE_LIVE, $this->site->id));
//            array_push($res,Category::getApiAllCategories(null, true, Category::TYPE_MUSIC, $this->site->id));
//            array_push($res,Category::getApiAllCategories(null, true, Category::TYPE_NEWS, $this->site->id));
//            array_push($res,Category::getApiAllCategories(null, true, Category::TYPE_CLIP, $this->site->id));
            $res['film'] = Category::getApiAllCategories(null, true, Category::TYPE_FILM, $this->site->id);
            $res['live'] = Category::getApiAllCategories(null, true, Category::TYPE_LIVE, $this->site->id);
            $res['music'] = Category::getApiAllCategories(null, true, Category::TYPE_MUSIC, $this->site->id);
            $res['clip'] = Category::getApiAllCategories(null, true, Category::TYPE_NEWS, $this->site->id);
            $res['new'] = Category::getApiAllCategories(null, true, Category::TYPE_CLIP, $this->site->id);
        } else {
            $res['items'] = Category::getApiAllCategories(null, true, $type, $this->site->id);
        }

        /**
         * Đổ dữ liệu vào bảng access_system để lấy thống kê lượt truy cập hệ thống tvod
         */
        $site_id = $this->site->id;
        /** @var  $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;
//        if (!$subscriber) {
//            throw new InvalidValueException(Message::getAccessDennyMessage());
//        }
        AccessSystem::createAccessSystem($subscriber?$subscriber->id:null, $site_id);
        return $res;

    }

    public function actionView($id)
    {
        if (!is_numeric($id)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['id']));
        }
        $cat = Category::find()
            ->andWhere(['id' => $id])
            ->andWhere(['site_id' => $this->site->id])
            ->andWhere(['status' => Category::STATUS_ACTIVE]);
        if (!$cat) {
            throw new NotFoundHttpException(Message::getNotFoundContentMessage());
        }
        $data = new ActiveDataProvider([
            'query' => $cat,
        ]);
        return $data;
    }

//    //TODO: xem lai nghiệp vụ: API này để làm gì?
//    public function actionRootCategory($type = Category::TYPE_FILM)
//    {
//        $res = [];
//        $id = $this->getParameter('id');
//
//        $res[] = Category::getApiRootCategories($type, $id, $this->site->id);
//
//        return $res;
//    }


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

    public function actionTest()
    {
        $res = [];
        $res['film'] = "a";
        $res['music'] = 'b';
        return $res;
    }
}