<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 21/05/2015
 * Time: 9:43 AM
 */

namespace api\controllers;


use api\helpers\Message;
use common\models\Content;
use common\models\ContentProfile;
use Yii;
use yii\base\InvalidValueException;
use yii\base\UnknownPropertyException;
use yii\helpers\Json;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ConverterController extends Controller{

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];
        $behaviors['corsFilter'] = ['class' => \yii\filters\Cors::className(),];

        return $behaviors;
    }
    public function setStatusCode($code) {
        Yii::$app->response->setStatusCode($code);
    }
    public function getParameterPost($param_name, $default = null) {
        return \Yii::$app->request->post($param_name, $default);
    }
    public static function replaceParam($message, $params) {
        if (is_array($params)) {
            $cnt = count($params);
            for( $i=1; $i <= $cnt; $i++ ){
                $message = str_replace('{'.$i.'}', $params[$i-1] , $message);
            }
        }
        return $message;
    }
    protected function verbs()
    {
        return [
            'contents-raw' => ['GET'],
            'content-converted' => ['POST'],
            'update-status-content' => ['POST'],
        ];
    }

    public function actionContentsRaw(){


        $raws= Content::getContentProfileRaw();
        if(isset($raws['error'])){
            $this->setStatusCode(404);
        }
        return $raws;

    }

    /**
     * status : 6: raw, 7 raw error
     * @return array
     */
    public function actionUpdateStatusContent(){

        $profile_id = $this->getParameterPost('profile_id');
        $status = $this->getParameterPost('status');
        $check = ContentProfile::getStatusNameByStatus($status);
        if($status == null || !isset($check)){
            return [
                'error'=>2,
                'message'=>Yii::t('app','Tham số không hợp lệ')
            ];
        }
        /** @var  $contentProfile ContentProfile*/
        $contentProfile = ContentProfile::findOne($profile_id);
        if (!$contentProfile) {
            return [
                'error'=>2,
                'message'=>Yii::t('app','Không tìm thấy profile')
            ];
        }
        return $contentProfile->updateStatus($status);

    }
    public function actionContentConverted()
    {

        $profile_id = $this->getParameterPost('profile_id');
        $content_id = $this->getParameterPost('content_id');
        $content_type = $this->getParameterPost('content_type', ContentProfile::TYPE_RAW);
        $url =$this->getParameterPost('url');
        $bitrate =$this->getParameterPost('bitrate');
        $width = $this->getParameterPost('width');
        $height = $this->getParameterPost('height');
        $duration = $this->getParameterPost('duration');
        $quality = $this->getParameterPost('quality', 1);

        if ($profile_id == null) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['profile_id']));
        }
        if ($content_id == null) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['content_id']));
        }
        if ($url == null) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['url']));
        }
        if ($bitrate == null) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['bitrate']));
        }
        if ($width == null) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['width']));
        }
        if ($height == null) {
            throw new InvalidValueException($this->replaceParam(Message::getNullValueMessage(), ['height']));
        }

        /**
         * @var $content Content
         */
        $content = Content::findOne($content_id);
        if($content && $content->duration <= 0 && $duration > 0){
            $content->duration = $duration;
            if(!$content->update()){
                Yii::error($content->getErrors());
            }
        }

        /** @var  $contentProfile ContentProfile */
        $contentProfile = ContentProfile::findOne(['id' => $profile_id, 'content_id' => $content_id]);
        if (!$contentProfile) {
            throw new InvalidValueException(Message::getContentProfileNotFoundMessage());
        }


        if (!$contentProfile->createNewProfile($bitrate, $width, $height, $url, $quality, $content_type)) {
            throw new InvalidValueException(Message::getFailMessage());
        }
        $this->setStatusCode(200);
        return ['message'=>Message::getContentProfileUpdateSuccessMessage()];
    }

}