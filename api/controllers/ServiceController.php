<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 21/05/2015
 * Time: 9:43 AM
 */

namespace api\controllers;


use api\helpers\Message;
use api\helpers\UserHelpers;
use api\models\Help;
use common\helpers\CommonConst;
use common\helpers\CommonUtils;
use common\helpers\VNPHelper;
use common\models\Content;
use common\models\ContentCategoryAsm;
use common\models\ContentFeedback;
use common\models\ContentProfile;
use common\models\ContentProfileSiteAsm;
use common\models\ContentSiteAsm;
use common\models\ContentSiteStatusAsm;
use common\models\Service;
use common\models\ServiceCategoryAsm;
use common\models\ServiceGroup;
use common\models\ServiceGroupSearch;
use common\models\ServiceSearch;
use common\models\Subscriber;
use common\models\SubscriberFavorite;
use common\models\SubscriberToken;
use common\models\SubscriberTransaction;
use Yii;
use yii\base\ErrorException;
use yii\base\InvalidValueException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class ServiceController extends ApiController
{
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = [
            'index',
            'view',
            'test',
            'service-group'
        ];

        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'services' => ['GET'],


        ];
    }

    /**
     * cuongvm dùng cách này cũng đc nhưng k có tính đối tượng
     */
//    public function actionIndex($content_id=0){
//        if (!is_numeric($content_id) || !is_numeric($content_id)) {
//            throw new InvalidValueException($this->replaceParam(Message::MSG_NUMBER_ONLY, ['content_id']));
//        }
//        $lstServiceGroup = ServiceGroup::find()->select(['id', 'name', 'display_name', 'icon', 'description'])
//                                ->andWhere(['site_id'=>$this->site->id,'status'=>ServiceGroup::STATUS_ACTIVE])->all();
//        $lst = [];
//        /** @var  $serviceGroup ServiceGroup*/
//        foreach($lstServiceGroup as $serviceGroup){
//            $group_tmp = $serviceGroup->getAttributes(null, ['site_id','type', 'updated_at', 'created_at', 'status']);
//            $lstService = ServiceGroup::getServiceInGroupByContent($content_id,$serviceGroup->id);
//            if($lstService){
//                $group_tmp['services']=$lstService;
//                $lst[]=$group_tmp;
//            }
//        }
//        return $lst;
//    }

    /**
     * @param int $content_id
     * @param int $group_id : id của group
     * @param int $type :
     * @return array
     */
    public function actionIndex($content_id=0, $group_id=0, $type=0){
        UserHelpers::manualLogin();
        /** @var $subscriber Subscriber */
        $subscriber = Yii::$app->user->identity;

        if (!is_numeric($content_id)) {
            throw new InvalidValueException($this->replaceParam(Message::getNumberOnlyMessage(), ['content_id']));
        }
        if (!is_numeric($group_id)) {
            throw new InvalidValueException($this->replaceParam(Message::MSG_NUMBER_ONLY, ['group_id']));
        }
        if (!is_numeric($type)) {
            throw new InvalidValueException($this->replaceParam(Message::MSG_NUMBER_ONLY, ['type']));
        }

        return ServiceGroup::getServiceGroup($this->site->id,$content_id, $group_id,$type ,$subscriber);
    }

    public function actionHelpPurchase(){
        $temp = [];

        $temp[] = ['content'=>Message::getSmsChaneMessage(),'type'=>1];
        $temp[] = ['content'=>Message::getSmsChaneServiceMessage(),'type'=>2];
        $temp[] = ['content'=>Message::getSmsCoinChaneMessage(),'type'=>3];
        $temp[] = ['content'=>Message::getLinkPortalMessage(),'type'=>4];
        return $temp;
    }


    /**
     * @param null $id
     * @return \yii\data\ActiveDataProvider
     */
//    public function actionView($id = null)
//    {
//        if ($id && !is_numeric($id)) {
//            throw new InvalidValueException($this->replaceParam(Message::MSG_NUMBER_ONLY, ['id']));
//        }
//        return Service::getListService($id, $this->site->id);
//    }

//    public function actionServiceGroup($content_id=0)
//    {
//        $msisdn = VNPHelper::getMsisdn(false, true);
//        $sub = null;
//        if ($msisdn) {
//            $sub = Subscriber::findByMsisdn($msisdn, $this->site->id);
//
//        }
//
//        return ServiceGroup::getListServiceGroup($this->site->id, $content_id);
//    }

//    public function actionPurchaseServicePackage()
//    {
//        $subscriber = Yii::$app->user->identity;
//        if (!$subscriber) {
//            throw new InvalidValueException(Message::MSG_ACCESS_DENNY);
//        }
//        /*
//         * check service yes or no
//         *      no => purchase => new record
//         *      yes => expired or not
//         *          yes => renew
//         *          no => += duration
//         */
//
//
//        $service_id = $this->getParameter('service_id');
//        $channel = $this->getParameter('channel', SubscriberTransaction::CHANNEL_TYPE_SMS);
//
//        /** @var  $subscriber Subscriber */
//        $subscriber = Yii::$app->user->identity;
//
//        $service = Service::find()->andWhere(['id' => $service_id])->andWhere(['status' => Service::STATUS_ACTIVE])->one();
//
//        if (!$service) {
//            throw new NotFoundHttpException(Message::MSG_NOT_FOUND_SERVICE);
//        }
//
//        $result = $subscriber->buyPackageVas($service, $channel, '', 0, false);
//        return ['message' => $result['message']];
//    }


    public function actionTest($lst_service_id)
    {
        return $this->render("index");
    }
}