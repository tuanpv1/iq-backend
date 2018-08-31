<?php

namespace backend\controllers;

use common\models\UserActivity;
use common\models\UserActivitySearch;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * UserActivityController implements the CRUD actions for UserActivity model.
 */
class UserActivityController extends BaseBEController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(),[
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * Lists all UserActivity models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserActivitySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        // $dataProvider->query->andWhere(['is', 'site_id', null]);
        // $dataProvider->query->andWhere(['is', 'dealer_id', null]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Finds the UserActivity model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return UserActivity the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserActivity::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app','Không tồn tại request page'));
        }
    }
}
