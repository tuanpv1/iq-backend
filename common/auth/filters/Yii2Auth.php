<?php
/**
 * @author Nguyen Chi Thuc
 * @email gthuc.nguyen@gmail.com
 */

namespace common\auth\filters;

use common\models\AuthItem;
use Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\rbac\DbManager;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\User;

class Yii2Auth extends ActionFilter
{

    public $superAdmin = 'admin';

    public $db = 'db';

    public $routeField = 'data';

    public $accType = AuthItem::ACC_TYPE_BACKEND;

    public $authManager = 'authManager';

    public $except = [];

    /**
     * @var bool default filter result if no permission found for given route
     */
    public $autoAllow = false;

    /**
     * @var User|string the user object representing the authentication status or the ID of the user application component.
     */
    public $user = 'user';
    /**
     * @var callable a callback that will be called if the access should be denied
     * to the current user. If not set, [[denyAccess()]] will be called.
     *
     * The signature of the callback should be as follows:
     *
     * ~~~
     * function ($rule, $action)
     * ~~~
     *
     * where `$rule` is the rule that denies the user, and `$action` is the current [[Action|action]] object.
     * `$rule` can be `null` if access is denied because none of the rules matched.
     */
    public $denyCallback;

    /**
     * @var callable a callback that will be called to check user is admin
     */
    public $validateAdminCallback;

    /**
     * Initializes the [[rules]] array by instantiating rule objects from configurations.
     */
    public function init()
    {
        parent::init();
        $this->user = Instance::ensure($this->user, User::className());
        $this->db = Instance::ensure($this->db, Connection::className());
        $this->authManager = Instance::ensure($this->authManager, DbManager::className());

    }

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param Action $action the action to be executed.
     * @return boolean whether the action should continue to be executed.
     */
    public function beforeAction($action)
    {
        $user = $this->user;

        if ($user && isset($user->identity->username) && $user->identity->username === $this->superAdmin) {
            return true;
        }

        /**
         * Check user is admin via callback function
         */
        if (isset($this->validateAdminCallback)) {
            if(call_user_func($this->validateAdminCallback, $user)){
                return true;
            }
        }

        $owner = $this->owner;

        /* @var $owner Controller */
        foreach ($this->except as $exception) {
            if ($exception === $action->id) {
                return true;
            }
        }

        $request = Yii::$app->getRequest();

        $route = $owner->route;
        Yii::info('Requested route: ' . $route, 'RouteAC');
//        $auth_item = AuthItem::findOne(['data' => $route]);

        /* @var $auth DbManager */
        $auth = $this->authManager;

        $auth_item = (new Query)->from($auth->itemTable)
            ->where([$this->routeField => $route, 'acc_type' => $this->accType])
            ->one($this->db);

        if (!$auth_item) {
            Yii::info("Permission for route `$route` not found", 'RouteAC');
            if ($this->autoAllow) {
                return true;
            } else {
                if (isset($this->denyCallback)) {
                    call_user_func($this->denyCallback, null, $action);
                } else {
                    $this->denyAccess($user);
                }
            }
        } else {
            Yii::info("Permission found for route `$route`: " . $auth_item['name'], 'RouteAC');
            if ($user->can($auth_item['name'])) {
                Yii::info('User #' . $user->getId() . " can access `$route` by permission: " . $auth_item['name'],
                    'RouteAC');
                return true;
            } else {
                Yii::info('User #' . $user->getId() . " CANNOT access `$route` by permission: " . $auth_item['name'],
                    'RouteAC');
                if (isset($this->denyCallback)) {
                    call_user_func($this->denyCallback, null, $action);
                } else {
                    $this->denyAccess($user);
                }
            }
        }

        return true;
    }

    public function actionAllow($route){
        $user = Yii::$app->user;

        if ($user && isset($user->identity->username) && $user->identity->username === $this->superAdmin) {
            return true;
        }

        /**
         * Check user is admin via callback function
         */
        if (isset($this->validateAdminCallback)) {
            if(call_user_func($this->validateAdminCallback, $user)){
                return true;
            }
        }

        $owner = $this->owner;

        Yii::info('Requested route: ' . $route, 'RouteAC');
//        $auth_item = AuthItem::findOne(['data' => $route]);

        /* @var $auth DbManager */
        $auth = $this->authManager;

        $auth_item = (new Query)->from($auth->itemTable)
            ->where([$this->routeField => $route, 'acc_type' => $this->accType])
            ->one($this->db);

        if (!$auth_item) {
            Yii::info("Permission for route `$route` not found", 'RouteAC');
            if ($this->autoAllow) {
                return true;
            } else{
                return false;
            }
        } else {
            Yii::info("Permission found for route `$route`: " . $auth_item['name'], 'RouteAC');
            if ($user->can($auth_item['name'])) {
                Yii::info('User #' . $user->getId() . " can access `$route` by permission: " . $auth_item['name'],
                    'RouteAC');
                return true;
            } else {
                Yii::info('User #' . $user->getId() . " CANNOT access `$route` by permission: " . $auth_item['name'],
                    'RouteAC');
                return false;
            }
        }
    }

    /**
     * Denies the access of the user.
     * The default implementation will redirect the user to the login page if he is a guest;
     * if the user is already logged, a 403 HTTP exception will be thrown.
     * @param User $user the current user
     * @throws ForbiddenHttpException if the user is already logged in.
     */
    protected function denyAccess($user)
    {
        if ($user->getIsGuest()) {
            $user->loginRequired();
        } else {
            throw new ForbiddenHttpException(Yii::t('app', 'Bạn không có quyền để thực hiện yêu cầu này'));
        }
    }
}
