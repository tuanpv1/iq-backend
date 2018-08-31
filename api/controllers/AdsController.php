<?php
/**
 * Created by PhpStorm.
 * User: Backtrack--
 * Date: 25-Mar-16
 * Time: 11:29 AM
 */

namespace api\controllers;

use common\models\AdsSearch;
use Yii;
use yii\base\InvalidValueException;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use common\models\Ads;

class AdsController extends ApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = [
            'search',
        ];
        return $behaviors;
    }

//    public function actionIndex()
//    {
//        //
//    }

    public function actionSearch(){
        $searchModel = new AdsSearch();
        $param = Yii::$app->request->queryParams;
        $searchModel->site_id = $this->site->id;
        $searchModel->type = isset($param['type']) ? ($param['type']) : 0;
        $searchModel->status = Ads::STATUS_ACTIVE;

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
     * HungNV creation: 05/04/16: list Ads at all or filtered by type and package
     *
     * @param null $type
     * @param null $package
     * @return string|ActiveDataProvider
     */
//    public function actionSearch1($type = null, $package = null)
//    {
//        /*
//         * TYPE: 1 - banner
//         * TYPE: 2 - open other apps
//         */
//        $ads = new Query();
//        $ads->select(['ads.*', 'app_ads.app_name', 'app_ads.package_name', 'app_ads.app_key'])
//            ->from('ads')
//            ->innerJoin('app_ads', 'app_ads.id = ads.app_ads_id')
//            ->andWhere(['ads.site_id' => $this->site->id])
//            ->andWhere(['ads.status' => Ads::STATUS_ACTIVE]);
//        /*
//         * search by TYPE of Ads such as Banner or other Apps
//         */
//        if (isset($type)) {
//            $ads->andWhere(['ads.type' => $type]);
//        }
//        /*
//         * search by package LIKE Parameter
//         */
//        if (isset($package)) {
//            $ads->andWhere(['LIKE','app_ads.package_name', '%'.$package.'%', false]);
//        }
//        $ads->all();
//        if (!$ads) {
//            return $message = "NOT FOUND";
//        }
//        $dataProvider = new ActiveDataProvider([
//            'query' => $ads,
//            'sort' => [],
//            'pagination' => [
//                'defaultPageSize' => 10,
//            ]
//        ]);
//        return $dataProvider;
//    }

    public function actionView($id){

    }


}