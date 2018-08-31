<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "file_transcoded".
 *
 * @property integer $id
 * @property string $title
 * @property string $basedir
 * @property integer $type
 * @property integer $cdn_id
 * @property string $picture
 * @property integer $duration
 * @property string $resolution
 * @property integer $created_at
 * @property integer $updated_at
 */
class FileTranscoded extends \yii\db\ActiveRecord
{

    /**
     * "SD") type="1" ;;
    "HD") type="2" ;;
    "MB") type="3" ;;
    "AD") type="4" ;;
    "SU") type="5" ;;
    "FP") type="6" ;;
     */
    const QUALITY_SD = 1;
    const QUALITY_HD = 2;
    const QUALITY_MB = 3;
    const QUALITY_AD = 4;
    const QUALITY_SU = 5;
    const QUALITY_FP = 6;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'file_transcoded';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'cdn_id', 'duration', 'created_at', 'updated_at'], 'integer'],
            [['cdn_id'], 'required'],
            [['cdn_id'], 'unique'],
            [['title', 'basedir', 'picture', 'resolution'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'basedir' => Yii::t('app', 'Basedir'),
            'type' => Yii::t('app', 'Type'),
            'cdn_id' => Yii::t('app', 'Cdn ID'),
            'picture' => Yii::t('app', 'Ảnh'),
            'duration' => Yii::t('app', 'Duration'),
            'resolution' => Yii::t('app', 'Resolution'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'updated_at' => Yii::t('app', 'Ngày thay đổi thông tin'),
        ];
    }
}
