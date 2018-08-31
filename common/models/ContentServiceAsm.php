<?php

namespace common\models;

use common\helpers\FileUtils;
use sp\models\Checked;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "content_service_asm".
 *
 * @property integer $id
 * @property integer $content_id
 * @property integer $service_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class ContentServiceAsm extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'content_service_asm';
    }

    public static function getAllContentByService($service_id)
    {
        $model = ContentServiceAsm::find()->select(['content_id'])
            ->andWhere(['status' => ContentServiceAsm::STATUS_ACTIVE])
            ->andWhere(['service_id' => $service_id])
            ->asArray()->all();
        if ($model) {
            foreach ($model as $key => $val) {
                $ar[] = $val['content_id'];
            }
            return $ar;
        }
        return [];
    }

    public static function processContentRemove($service_id, $aray_id_content)
    {
        $model_update = ContentServiceAsm::find()
            ->andWhere(['IN', 'content_id', $aray_id_content])
            ->andWhere(['service_id' => $service_id])
            ->all();
        /** @var ContentServiceAsm $item */
        foreach ($model_update as $item) {
            $check = Content::checkContentInOtherService($item->content_id, $service_id);
            if (!$check) {
                $model_allow = Content::findOne($item->content_id);
                if(!$model_allow){
                    return false;
                }
                $model_allow->allow_buy_content = Content::ALLOW_BUY_CONTENT;
                $model_allow->update(false);
            }
            $item->status = ContentServiceAsm::STATUS_INACTIVE;
            $item->update(false);
        }
    }

    public static function getNewID($array_old, $ids, $check)
    {
        // dinh dang lai mang lay tu session
        $old_array = [];
        if ($array_old) {
            foreach ($array_old as $key => $value) {
                $old_array[] = $key;
            }
        }
        $add_array = [];
        // $check = true lay danh sach id can add nguoc lai la can loai bo
        if ($check) {
            foreach ($ids as $item) {
                if (!in_array($item, $old_array)) {
                    $add_array[] = $item;
                }
            }
        } else {
            foreach ($old_array as $item1) {
                if (!in_array($item1, $ids)) {
                    $add_array[] = $item1;
                }
            }
        }
        return $add_array;
    }

    public static function saveChecked($id)
    {
        $checked = new Checked();
        $checked->addChecked($id);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content_id', 'service_id', 'status', 'created_at', 'updated_at'], 'integer'],
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

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content_id' => 'Content ID',
            'service_id' => 'Service ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
