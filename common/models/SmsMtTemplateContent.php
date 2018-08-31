<?php

namespace common\models;

use common\helpers\CUtils;
use common\helpers\MTParam;
use common\helpers\ResMessage;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%sms_mt_template_content}}".
 *
 * @property integer $id
 * @property string $code_name
 * @property string $content
 * @property integer $type
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $site_id
 * @property integer $sms_mo_syntax_id
 * @property integer $sms_mt_template_id
 * @property integer $service_id
 * @property integer $web_content
 *
 * @property SmsMessage[] $smsMessages
 * @property ServiceProvider $serviceProvider
 * @property SmsMoSyntax $smsMoSyntax
 * @property SmsMtTemplate $smsMtTemplate
 * @property Service $service
 */
class SmsMtTemplateContent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    const TYPE_SUBSCRIPTION_PLAN = 1;
    const TYPE_GENERAL = 2;


    public static function tableName()
    {
        return '{{%sms_mt_template_content}}';
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
//        $mo_syntax = function ($model) {
//            return in_array($model->code_name, ResMessage::listCodeNameRequireMO());
//        };
//        $service_syntax = function ($model) {
//            Yii::trace(in_array($model->code_name, ResMessage::listCodeNameRequireService()));
//            return in_array($model->code_name, ResMessage::listCodeNameRequireService());
//        };
        return [
            [['content', 'site_id', 'sms_mt_template_id', 'web_content', 'sms_mt_template_id'], 'required'],
            [['type', 'created_at', 'updated_at', 'site_id', 'sms_mo_syntax_id', 'sms_mt_template_id', 'service_id'], 'integer'],
            [['code_name'], 'string', 'max' => 255],
            [['content', 'web_content'], 'string', 'max' => 4000],
            ['service_id', 'required', 'when' => function ($model) {
                return $model->type == self::TYPE_SUBSCRIPTION_PLAN;
            }, 'whenClient' => "function (attribute, value) {

            }"],

//            [['sms_mo_syntax_id'], 'required', 'when' => $mo_syntax],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'code_name' => Yii::t('app', 'Code Name'),
            'content' => Yii::t('app', 'SMS Format'),
            'web_content' => Yii::t('app', 'Web Format'),
            'type' => Yii::t('app', 'Type'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'updated_at' => Yii::t('app', 'Ngày thay đổi thông tin'),
            'site_id' => Yii::t('app', 'Service Provider ID'),
            'sms_mo_syntax_id' => Yii::t('app', 'Sms Mo Syntax ID'),
            'sms_mt_template_id' => Yii::t('app', 'Event'),
            'service_id' => Yii::t('app', 'Subscription Plan'),
        ];
    }

    public static function getListType()
    {
        return [
            self::TYPE_SUBSCRIPTION_PLAN => 'Subscription Plan',
            self::TYPE_GENERAL => 'General',
        ];
    }

    public function getTypeName()
    {
        $listStatus = self::getListType();
        if (isset($listStatus[$this->type])) {
            return $listStatus[$this->type];
        }
        return '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSmsMessages()
    {
        return $this->hasMany(SmsMessage::className(), ['sms_template_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceProvider()
    {
        return $this->hasOne(ServiceProvider::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSmsMoSyntax()
    {
        return $this->hasOne(SmsMoSyntax::className(), ['id' => 'sms_mo_syntax_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSmsMtTemplate()
    {
        return $this->hasOne(SmsMtTemplate::className(), ['id' => 'sms_mt_template_id']);
    }

    public static function getListMtTemplate($sp_id)
    {
        $querySPTemplate = SmsMtTemplateContent::find()->select(['sms_mt_template_id'])->andWhere(['site_id' => $sp_id]);
//        $mtTemplate = SmsMtTemplate::find()->select(['id', 'code_name'])->andWhere(['not in ', 'id', $querySPTemplate])->asArray()->all();
        $mtTemplate = SmsMtTemplate::find()->select(['id', 'code_name'])->asArray()->all();
        return $mtTemplate;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
    }

    /**
     * @param $mtParam
     * @param $msgParam
     * @param Service $service
     * @return array
     */
    public static function getMtContent($mtParam, $msgParam, $service = null)
    {
//        echo "code_name: $mtParam->code_name";
//        echo "site_id: $mtParam->site_id";
//        echo "service_id: $service->id";
        /** @var  $mtTemplate SmsMtTemplateContent */
        $query = SmsMtTemplateContent::find()->andWhere(['code_name' => $mtParam->code_name, 'site_id' => $mtParam->site_id]);
        if ($service) {
            $query->andWhere(['service_id' => $service->id]);
        }
        $mtTemplate = $query->one();
        if (!$mtTemplate && $service) {
            $query = SmsMtTemplateContent::find()->andWhere(['code_name' => $mtParam->code_name, 'site_id' => $mtParam->site_id]);
            $mtTemplate = $query->one();
        }
        if ($mtTemplate) {
            $msg = $mtTemplate->content;
            $web_content = $mtTemplate->web_content;
            $listMtParam = $mtTemplate->getListParams();
            foreach ($listMtParam as $param) {
                if (isset($msgParam[$param])) {
                    $msg = CUtils::utf8Convert(str_replace($param, $msgParam[$param], $msg));
                    $web_content = str_replace($param, $msgParam[$param], $web_content);
                }
            }
            return [
                'mt' => $msg,
                'mt_template_id' => $mtTemplate->sms_mt_template_id,
                'web_content' => $web_content
            ];
        }
        return [
            'mt' => '',
            'mt_template_id' => '',
            'web_content' => ''
        ];
    }

    /**
     * @return array|mixed list mt content param
     */
    public function getListParams()
    {
        $listParam = explode(',', $this->smsMtTemplate->params);
        if (is_array($listParam)) {
            return $listParam;
        }
        return [];
    }


    public static function getMtTemplateViaType($type)
    {
        if ($type == self::TYPE_SUBSCRIPTION_PLAN) {
            $result = SmsMtTemplate::find()
                ->select(['id', 'code_name as name'])
                ->andWhere(['type' => self::TYPE_SUBSCRIPTION_PLAN])
                ->asArray()
                ->all();
        } else {
            $result = SmsMtTemplate::find()
                ->select(['id', 'code_name as name'])
                ->andWhere(['type' => self::TYPE_GENERAL])
                ->asArray()
                ->all();
        }
        return $result;
    }
}
