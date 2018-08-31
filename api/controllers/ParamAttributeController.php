<?php
/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 30-Jun-17
 * Time: 10:43 AM
 */

namespace api\controllers;


use yii\data\ActiveDataProvider;

class ParamAttributeController extends ApiController
{

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = [
            'get-list-param-attribute'
        ];
        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'index' => ['GET'],
            'get-list-param-attribute' => ['GET']
        ];
    }

    public function actionGetListParamAttribute($type_app = \common\models\ParamAttribute::TYPE_APP_FILM)
    {

        $query = \api\models\ParamAttribute::find()->andWhere(['type_app'=>$type_app]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_DESC],
            ],
        ]);
        if(!$query->one()){
            $this->setStatusCode(500);
        }
        return $dataProvider;
    }
}