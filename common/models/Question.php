<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "question".
 *
 * @property int $id
 * @property int $program_id
 * @property string $question
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property int $level
 *
 * @property array $program[]
 */
class Question extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'question';
    }

    public function getProgram()
    {
        $model = Program::findOne($this->program_id);
        if ($model) {
            return $model;
        }
        return null;
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 10;
    const STATUS_REMOVE = -1;

    public static function getListStatus()
    {
        return
            $sp_status = [
                self::STATUS_ACTIVE => Yii::t('app', 'Hoạt động'),
                self::STATUS_INACTIVE => Yii::t('app', 'Tạm ngừng'),
            ];
    }

    public static function getListStatusNameByStatus($status)
    {
        $lst = self::getListStatus();
        if (array_key_exists($status, $lst)) {
            return $lst[$status];
        }
        return $status;
    }

    public function getStatusName()
    {
        $lst = self::getListStatus();
        if (array_key_exists($this->status, $lst)) {
            return $lst[$this->status];
        }
        return $this->status;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['program_id', 'status', 'created_at', 'updated_at'], 'integer'],
            ['level', 'number'],
            ['level', 'validateUnique'],
            [['question'], 'string', 'max' => 255],
        ];
    }

    public function validateUnique($attribute, $params)
    {
        if ($this->id) {
            $modelQuestion = Question::find()->andWhere(['program_id' => $this->program_id, 'level' => $this->level])
                ->andWhere(['<>', 'status', Question::STATUS_REMOVE])
                ->andWhere(['<>', 'id', $this->id])->one();
        } else {
            $modelQuestion = Question::find()->andWhere(['program_id' => $this->program_id, 'level' => $this->level])
                ->andWhere(['<>', 'status', Question::STATUS_REMOVE])->one();
        }
        /** @var Question $modelQuestion */
        if ($modelQuestion) {
            $this->addError($attribute, Yii::t('app', 'Thứ tự đã tồn tại trong hệ thống'));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'program_id' => Yii::t('app', 'Program'),
            'question' => Yii::t('app', 'Question'),
            'status' => Yii::t('app', 'Status'),
            'level' => Yii::t('app', 'Level'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }

    public function getAnswer()
    {
        $model = Answer::find()
            ->andWhere(['question_id' => $this->id])
            ->all();
        $result = '';
        if ($model) {
            /** @var Answer $answer */
            $i = 1;
            foreach ($model as $answer) {
                $result .= Yii::t('app', 'Đáp án số ') . $i . ': ' . $answer->answer . $answer->getIsCorrect() . "<br>";
                $i++;
            }
        }
        return $result;
    }

    public static function getListQuestion($program_id)
    {
        $list_questions = Question::find()
            ->andWhere(['program_id' => $program_id])
            ->andWhere(['status' => self::STATUS_ACTIVE])
            ->all();
        if (!$list_questions) {
            return null;
        }
        /** @var Question $question */
        foreach ($list_questions as $question) {
            $answers = Answer::findAll(['question_id' => $question->id, 'status' => Answer::STATUS_ACTIVE]);
            if (!$answers) {
                continue;
            }
            $arrayQuestion[$question->id][] = $question->question;
            /** @var Answer $answer */
            foreach ($answers as $answer) {
//                if ($answer->is_correct) {
//                    $answerCorrect[$question->id][] = $answer->answer;
//                }
                $answerList[$question->id][] = $answer->answer;
            }
        }
        $array[] = $arrayQuestion;
        $array[] = $answerList;
//        $array[] = $answerCorrect;

        return $array;
    }
}
