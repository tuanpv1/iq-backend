<?php
namespace backend\models;

use common\models\Service;
use common\models\ServiceProvider;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Login form
 */
class AdminNoteForm extends Model
{
    public $admin_note;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_note'], 'required'],
            [['admin_note'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'admin_note' => Yii::t('app', 'Ghi chú'),
        ];
    }

    /**
     * @param $model Service
     * @return mixed
     */
    public function saveRecord($model)
    {
        $model->admin_note = $this->admin_note;
        return $model->update();
    }

}
