<?php
/**
 * Created by PhpStorm.
 * User: qhuy
 * Date: 21/05/2015
 * Time: 09:42
 */
namespace common\components;;

use common\auth\filters\Yii2Auth;
use Yii;
use yii\base\ActionFilter;
use yii\di\Instance;
use yii\web\ForbiddenHttpException;
use yii\web\User;

class ActionProtectSuperAdmin extends ActionFilter
{

    /**
     * @var User $user
     */
    public $user = 'user';

    public $update_user;

    /**
     * Initializes the [[rules]] array by instantiating rule objects from configurations.
     */
    public function init()
    {
        parent::init();
        $this->user = Instance::ensure($this->user, User::className());
    }

    public function beforeAction($action)
    {
        $user = $this->user;
        $e = new Yii2Auth();
        if ($user && isset($user->identity->username) && $user->identity->username === $e->superAdmin) {
            return true;
        }
        $request = Yii::$app->request;

        if ($this->update_user !== null) {
            $model = call_user_func($this->update_user, $action, $request->getQueryParams());
        }else{
            return parent::beforeAction($action);
        }

        if($model == null){
            Yii::info('Model relation sp null');
            return parent::beforeAction($action);
        }

        if($e->superAdmin == $model->username){
            $this->denyAccess($user);
        }
        return parent::beforeAction($action);
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
            throw new ForbiddenHttpException(Yii::t('app','Bạn không có quyền tác động đến user supper admin'));
        }
    }

}
