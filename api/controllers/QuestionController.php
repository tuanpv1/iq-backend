<?php
/**
 * Created by TuanPV.
 * User: TuanPV
 * Date: 21/05/2015
 * Time: 9:43 AM
 */

namespace api\controllers;

use api\helpers\Message;
use common\models\Question;
use Yii;
use yii\base\InvalidValueException;

class QuestionController extends ApiController
{
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'register' => ['GET'],
            'login' => ['GET'],
            'change-password' => ['GET'],
            'edit-profile' => ['GET'],
            'feedback' => ['POST'],
            'list-feedback' => ['GET'],
        ];
    }

    public function actionListQuestion()
    {
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        $program_id = Yii::$app->request->post('program_id');
        if (!$program_id) {
            throw new InvalidValueException(Yii::t('app', 'Thiếu thông tin bắt buộc program_id'));
        }
        $listQuestion = Question::getListQuestion($program_id);
        if ($listQuestion) {
            return $listQuestion;
        }
        return null;
    }

    public function actionNextQuestion()
    {
        $subscriber = Yii::$app->user->identity;
        if (!$subscriber) {
            throw new InvalidValueException(Message::getAccessDennyMessage());
        }
        $level = Yii::$app->request->post('level');
        if (!$level) {
            throw new InvalidValueException(Yii::t('app', 'Thiếu thông tin bắt buộc level'));
        }
        $program_id = Yii::$app->request->post('program_id');
        if (!$program_id) {
            throw new InvalidValueException(Yii::t('app', 'Thiếu thông tin bắt buộc program_id'));
        }
        $question = Question::getQuestion($program_id, $level);
        if ($question) {
            return $question;
        }
        return null;
    }
}
