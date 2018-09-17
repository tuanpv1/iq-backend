<?php

namespace backend\controllers;

use common\components\ActionLogTracking;
use common\components\ActionSPFilter;
use common\components\SPOwnerFilter;
use common\helpers\CUtils;
use common\models\ApiCredential;
use common\models\ApiCredentialSearch;
use common\models\UserActivity;
use kartik\widgets\ActiveForm;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * CredentialController implements the CRUD actions for ApiCredential model.
 */
class CredentialController extends BaseBEController
{
    public function behaviors()
    {
        return parent::behaviors();
    }

    /**
     * Lists all ApiCredential models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ApiCredentialSearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ApiCredential model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new ApiCredential model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ApiCredential();
        $model->client_api_key = CUtils::randomString();
        $model->client_secret = CUtils::randomString();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            if(!$model->save()){
                Yii::error($model->getErrors());
                Yii::$app->session->setFlash('error', Yii::t("app","Tạo api key client không thành công!"));
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
            Yii::$app->session->setFlash('success', Yii::t("app","Tạo api key client ") . $model->client_name . Yii::t("app"," thành công!"));
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ApiCredential model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t("app","Cập nhật api key client ") . $model->client_name . Yii::t("app"," thành công!"));
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ApiCredential model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if($model->delete()){
            Yii::$app->session->setFlash('success', Yii::t("app","Xóa api key client ") . $model->client_name . Yii::t("app"," thành công"));
        }else{
            Yii::$app->session->setFlash('error', Yii::t("app","Xóa api key client ") . $model->client_name . Yii::t("app"," không thành công!"));
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the ApiCredential model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ApiCredential the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ApiCredential::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t("app","Trang bạn yêu cầu không tồn tại"));
        }
    }
}
