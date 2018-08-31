<?php

namespace console\models\tvcatchup;

use Yii;

/**
 * This is the model class for table "node".
 *
 * @property string $nid
 * @property string $vid
 * @property string $type
 * @property string $language
 * @property string $title
 * @property integer $uid
 * @property integer $status
 * @property integer $created
 * @property integer $changed
 * @property integer $comment
 * @property integer $promote
 * @property integer $sticky
 * @property string $tnid
 * @property integer $translate
 */
class Node extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'node';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_tvcatchup');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vid', 'uid', 'status', 'created', 'changed', 'comment', 'promote', 'sticky', 'tnid', 'translate'], 'integer'],
            [['type'], 'string', 'max' => 32],
            [['language'], 'string', 'max' => 12],
            [['title'], 'string', 'max' => 255],
            [['vid'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'nid' => 'Nid',
            'vid' => 'Vid',
            'type' => 'Type',
            'language' => 'Language',
            'title' => 'Title',
            'uid' => 'Uid',
            'status' => 'Status',
            'created' => 'Created',
            'changed' => 'Changed',
            'comment' => 'Comment',
            'promote' => 'Promote',
            'sticky' => 'Sticky',
            'tnid' => 'Tnid',
            'translate' => 'Translate',
        ];
    }
}
