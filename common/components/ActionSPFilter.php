<?php
/**
 * Created by PhpStorm.
 * User: qhuy
 * Date: 21/05/2015
 * Time: 09:42
 */
namespace common\components;;

use Yii;
use yii\base\ActionFilter;
use yii\di\Instance;
use yii\web\ForbiddenHttpException;
use yii\web\User;

class ActionSPFilter extends ActionFilter
{

    /**
     * @var User $user
     */
    public $user = 'user';

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
        if ($user->getIsGuest()) {
            $this->denyAccess($user);
            return false;
        }

        $loginAsSP = Yii::$app->session->get(\common\models\User::USER_ACCESS_SP, 0);
        Yii::info($loginAsSP);

        /**
         * @var \common\models\User $sp_user
         */
        $sp_user = \common\models\User::findOne($user->id);

        if (!$sp_user->isServiceProvider() && !$loginAsSP) {
            $this->denyAccess($user);
            return false;
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
            throw new ForbiddenHttpException(Yii::t('app', 'Bạn không có quyền truy cập trang yêu cầu'));
        }
    }

}
