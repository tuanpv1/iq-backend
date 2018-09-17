<?php

namespace backend\controllers;

use backend\models\QuestionAnswer;
use common\models\Answer;
use common\models\Question;
use common\models\QuestionSearch;
use Yii;
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * QuestionController implements the CRUD actions for Question model.
 */
class QuestionController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Question models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new QuestionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Question model.
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
     * Creates a new Question model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new QuestionAnswer();

        $model->number_answers = Yii::$app->params['number_answers'] ? Yii::$app->params['number_answers'] : 3;
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {
            if (!in_array(Answer::IS_CORRECT, $model->is_correct)) {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Vui lòng chọn câu trả lời đúng'));
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $modelQuestion = new Question();
                $modelQuestion->program_id = $model->program_id;
                $modelQuestion->status = $model->status;
                $modelQuestion->question = $model->question;
                $modelQuestion->level = $model->level;
                if (!$modelQuestion->save()) {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Tạo câu hỏi không thành công'));
                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }
                Answer::addNewAnswer($modelQuestion->id, $model->answers, $model->is_correct, $model->status);
                $transaction->commit();
                return $this->redirect(['view', 'id' => $modelQuestion->id]);
            } catch (Exception $exception) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', Yii::t('app', 'Tạo câu hỏi không thành công'));
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Question model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $modelQuestion = $this->findModel($id);

        $model = new QuestionAnswer();
        $model->id = $modelQuestion->id;
        $model->level = $modelQuestion->level;
        $model->question = $modelQuestion->question;
        $model->status = $modelQuestion->status;
        $model->program_id = $modelQuestion->program_id;
        $model->getAnswerUpdate();
        $model->number_answers = Yii::$app->params['number_answers'] ? Yii::$app->params['number_answers'] : count($model->answers);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            if (!in_array(Answer::IS_CORRECT, $model->is_correct)) {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Vui lòng chọn câu trả lời đúng'));
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $modelQuestion->program_id = $model->program_id;
                $modelQuestion->status = $model->status;
                $modelQuestion->question = $model->question;
                $modelQuestion->level = $model->level;
                if (!$modelQuestion->update()) {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Tạo câu hỏi không thành công'));
                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }
                Answer::addNewAnswer($modelQuestion->id, $model->answers, $model->is_correct, $model->status);
                $transaction->commit();
                return $this->redirect(['view', 'id' => $modelQuestion->id]);
            } catch (Exception $exception) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', Yii::t('app', 'Tạo câu hỏi không thành công'));
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Question model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model) {
            $model->status = Question::STATUS_REMOVE;
            $model->update();
            Answer::updateAll(['status' => Answer::STATUS_REMOVE], ['question_id' => $id]);
        }
        Yii::$app->session->setFlash('success', Yii::t('app', 'Xoá câu hỏi thành công'));
        return $this->redirect(['index']);
    }

    /**
     * Finds the Question model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Question the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Question::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionUpdateLevel($id)
    {
        $model = $this->findModel($id);
        if (isset($_POST['hasEditable'])) {
            $post = Yii::$app->request->post();
            if ($post['editableKey']) {
                $question = Question::findOne($post['editableKey']);
                $index = $post['editableIndex'];
                if ($question || $model->id != $question->id) {
                    $question->load($post['Question'][$index], '');
                    if ($question->update()) {
                        echo \yii\helpers\Json::encode(['output' => '', 'message' => '']);
                    } else {
                        echo \yii\helpers\Json::encode([
                            'output' => '',
                            'message' => $question->getFirstError('level'),
                        ]);
                    }
                } else {
                    echo \yii\helpers\Json::encode(['output' => '', 'message' => \Yii::t('app', 'Dữ liệu không tồn tại')]);
                }
            }
            else {
                echo \yii\helpers\Json::encode(['output' => '', 'message' => '']);
            }

            return;
        }
    }
}
