<?php
/**
 * Created by PhpStorm.
 * User: qhuy
 * Date: 21/05/2015
 * Time: 09:42
 */
namespace common\components;

;

use common\helpers\CUtils;
use Yii;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;
use yii\web\User;

class ActionPrivateFilter extends ActionFilter
{

    public $username = '';
    public $password = '';

    public $field_username = 'username';
    public $field_password = 'password';

    public $enable_authentication = true;
    /**
     * @var User $user
     */
    private $ips_private = [];

    /**
     * Initializes the [[rules]] array by instantiating rule objects from configurations.
     */
    public function init()
    {
        parent::init();
        $params = (isset(Yii::$app->params['access_private']))?Yii::$app->params['access_private']:[];
        if (isset($params['ip_privates'])) {
            $this->ips_private = $params['ip_privates'];
        }

        if (empty($this->username) && isset($params['user_name'])){
            $this->username = $params['user_name'];
        }

        if (empty($this->password) && isset($params['password'])){
            $this->username = $params['password'];
        }
    }

    public function beforeAction($action)
    {
        $request = Yii::$app->request;

        /**
         * Validate IP
         */
        $clientIP = CUtils::clientIP();
        Yii::info("Request from IP: ".$clientIP,'API_PRIVATE');
        $isValid = false;
        foreach ($this->ips_private as $range) {
            if (CUtils::cidrMatch($clientIP, $range)) {
                $isValid = true;
                break;
            }
        }
        if (!$isValid) {
            Yii::error("Deny access api private", 'API_PRIVATE');
            $this->denyAccess();
        }

        /**
         * Validate Username, password
         */
        if($this->enable_authentication){
            $request_params = $request->getQueryParams();
            $req_username = (isset($request_params[$this->field_username]))?$request_params[$this->field_username]:'anonymous';
            $req_password = (isset($request_params[$this->field_password]))?$request_params[$this->field_password]:'';
            Yii::info('Authentication: '.$req_username.'/'.$req_password, 'API_PRIVATE');
            if($req_username != $this->username || $req_password != $this->password){
                Yii::error("Deny access api private", 'API_PRIVATE');
                $this->denyAccess();
            }
        }

        return parent::beforeAction($action);
    }

    /**
     * Denies the access of the user.
     * The default implementation will redirect the user to the login page if he is a guest;
     * if the user is already logged, a 403 HTTP exception will be thrown.
     * @throws ForbiddenHttpException if the user is already logged in.
     */
    protected function denyAccess()
    {
        throw new ForbiddenHttpException(Yii::t('app', 'Bạn không có quyền truy cập trang yêu cầu'));
    }

}
