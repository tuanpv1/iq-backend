<?php

namespace backend\controllers;

use backend\models\SendEmailInternalForm;
use common\components\ActionLogTracking;
use common\models\Subscriber;
use common\models\SubscriberSearch;
use common\models\SubscriberServiceAsmSearch;
use common\models\UserActivity;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * SubscriberController implements the CRUD actions for Subscriber model.
 */
class SubscriberController extends BaseBEController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            [
                'class' => ActionLogTracking::className(),
                'user' => Yii::$app->user,
                'model_type_default' => UserActivity::ACTION_TARGET_TYPE_SUBSCRIBER,
                'post_action' => [
                    ['action' => 'create', 'accept_ajax' => false],
                    ['action' => 'update', 'accept_ajax' => false],
                    ['action' => 'delete', 'accept_ajax' => false],
                ],
                // 'only' => ['create', 'update', 'delete']
            ],
        ]);
    }

    /**
     * Lists all Subscriber models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SubscriberSearch();
        $dataProvider = $searchModel->searchExt(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Subscriber model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id, $active = 1)
    {
        $param = Yii::$app->request->queryParams;
        $searchModel = new SubscriberServiceAsmSearch();
        $param['SubscriberServiceAsmSearch']['subscriber_id'] = $id;
        $dataProvider = $searchModel->search($param);


        return $this->render('view', [
            'model' => $this->findModel($id),
//            'lstPackage' => null,
            'active' => $active,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Subscriber model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Subscriber();
        $model->setScenario('create');

        if ($model->load(Yii::$app->request->post())) {
            $model->setPassword($model->password);
            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app','Tạo Subscriber thành công!'));
                return $this->redirect(['view', 'id' => $model->id]);
            }

        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Subscriber model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Subscriber model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->status = Subscriber::STATUS_DELETED;

        if (!$model->save()) {
            Yii::error($model->errors);
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Subscriber model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Subscriber the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Subscriber::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app','Không tồn tại request page'));
        }
    }

}
