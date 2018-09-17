<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "answer".
 *
 * @property int $id
 * @property int $question_id
 * @property string $answer
 * @property int $is_correct
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class Answer extends \yii\db\ActiveRecord
{
    const IS_CORRECT = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'answer';
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 10;
    const STATUS_REMOVE = -1;

    public static function getListStatus()
    {
        return
            $sp_status = [
                self::STATUS_ACTIVE   => Yii::t('app', 'Hoạt động'),
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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['question_id', 'is_correct', 'status', 'created_at', 'updated_at'], 'integer'],
            [['answer'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'question_id' => 'Id Question',
            'answer' => 'Answer',
            'is_correct' => 'Is Correct',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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

    public static function addNewAnswer($question_id, $answers, $is_correct, $status)
    {
        if (!$answers || !$is_correct) {
            return false;
        }
        self::deleteAll(['question_id' => $question_id]);
        foreach ($answers as $key => $answer) {
            $model = new Answer();
            $model->answer = $answer;
            $model->question_id = $question_id;
            $model->is_correct = $is_correct[$key];
            $model->status = $status;
            if (!$model->save()) {
                Yii::error($model->getErrors());
                return false;
            }
        }
        return true;
    }

    public function getIsCorrect()
    {
        if ($this->is_correct) {
            return ' <span style="color: #00aa00"><i class="icon-check"></i></span>';
        } else {
            return ' <span style="color: red"><i class="glyphicon glyphicon-remove"></i></span>';
        }
    }
}
