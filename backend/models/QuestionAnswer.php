<?php

namespace backend\models;

use common\models\Answer;
use common\models\Question;
use Yii;

/**
 * Login form
 */
class QuestionAnswer extends Question
{
    public $answers;
    public $number_answers;
    public $is_correct;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['number_answers', 'answers', 'is_correct'], 'safe'],
            ['level', 'validateUnique'],
            [['answers', 'is_correct', 'program_id', 'question', 'status', 'level'], 'required'],
            ['level', 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'number_answers' => Yii::t('app', 'Số lượng câu trả lời'),
            'answers' => Yii::t('app', 'Answers'),
        ];
    }

    public function getAnswerUpdate()
    {
        $modelAnswer = Answer::find()
            ->andWhere(['question_id' => $this->id])
            ->all();
        if ($modelAnswer) {
            /** @var Answer $answer */
            $i = 1;
            $arrayAnswers[] = null;
            $arrayCorrect[] = null;
            foreach ($modelAnswer as $answer) {
                $arrayAnswers[$i] = $answer->answer;
                $arrayCorrect[$i] = $answer->is_correct;
                $i++;
            }
            $this->answers = $arrayAnswers;
            $this->is_correct = $arrayCorrect;
        }
    }

}
