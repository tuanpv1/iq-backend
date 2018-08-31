<?php
/**
 * Created by PhpStorm.
 * User: qhuy
 * Date: 6/17/15
 * Time: 3:00 PM
 */

namespace common\components;

use common\models\UserActivity;
use Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\base\Module;
use yii\di\Instance;
use yii\helpers\VarDumper;
use yii\web\Request;
use yii\web\Response;
use yii\web\User;

class ActionLogTracking extends ActionFilter
{

    /**
     * @var User $user
     */
    public $user = 'user';

    public $post_action = [];

    public $model_type_default = UserActivity::ACTION_TARGET_TYPE_OTHER;

    public $model_types = [];

    /**
     * @var $request Request
     */
    public $request = 'request';

    /**
     * Initializes the [[rules]] array by instantiating rule objects from configurations.
     */
    public function init()
    {
        parent::init();
        $this->user    = Instance::ensure($this->user, User::className());
        $this->request = Yii::$app->request;
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        $user                       = $this->user;
        $request                    = $this->request;
        list($is_post, $post_valid) = $this->isPostAction($action);
        if ($user->getIsGuest()) {
            return parent::beforeAction($action);
        }
        Yii::info('Request is get');
        Yii::info(($request->isGet));
        if ($is_post && !$post_valid) {
            return parent::beforeAction($action);
        }
        /**
         * @var $user_action \common\models\User
         */
        $agent       = parse_user_agent($request->getUserAgent());
        $user_action = $user->identity;
        $description = $is_post ?
        'User ' . $user_action->username . Yii::t('app',' thực hiện action ') . $action->id . Yii::t('app',' tới ') . UserActivity::actionTargets()[$this->model_type_default]
        : 'User ' . $user_action->username .Yii::t('app', ' thực hiện load trang ') . $action->id . ' ' . UserActivity::actionTargets()[$this->model_type_default];

        if($action->id == 'update-version-api'){
            $description = 'User ' . $user_action->username . Yii::t('app',' thực hiện action ') . $action->id . Yii::t('app',' tới ' ). UserActivity::actionTargets()[$this->model_type_default];
        }

        $params                    = $request->getQueryParams();
        $audit_log                 = new UserActivity();
        $audit_log->user_id        = $user->id;
        $audit_log->username       = $user_action->username;
        $audit_log->ip_address     = $request->getUserIP();
        $audit_log->user_agent     = Yii::t('app','Dùng ') . $agent['browser'] . '/' . $agent['version'] . Yii::t('app',' trên HĐH ') . $agent['platform'];
        $audit_log->action         = strtoupper($is_post ? 'POST' : 'GET') . ' : ' . $action->id;
        $audit_log->target_id      = isset($params['id']) ? $params['id'] : null;
        $audit_log->request_detail = substr($request->getAbsoluteUrl(), 0, 255);
        $audit_log->site_id        = $user_action->site_id;
        $audit_log->dealer_id      = $user_action->dealer_id;
        $audit_log->target_type    = $this->getModelType($action);
        $audit_log->request_params = ($is_post) ? json_encode($request->getBodyParams()) : json_encode($request->getQueryParams());
        $audit_log->description    = $description;
        $audit_log->status         = 'Waiting response';
        if ($audit_log->save() && $action->controller->hasProperty('audit_id')) {
            $action->controller->audit_id = $audit_log->id;
        } else {
            Yii::info('audit error', json_encode($audit_log->getErrors()));
        }
        return parent::beforeAction($action);
    }

    /**
     * @param Action $action
     * @param mixed | Response $result
     * @return mixed
     */
    public function afterAction($action, $result)
    {
        usleep(200000);
        $result = parent::afterAction($action, $result);
        if ($action->controller->audit_id) {
            Yii::info('have audit_id: ' . $action->controller->audit_id);
            /**
             * @var $audit_log UserActivity
             */
            Yii::info(json_encode($result), 'after action result');
            $audit_log = UserActivity::findOne($action->controller->audit_id);
            if(isset($audit_log)){
                if (is_string($result)) {
                    $audit_log->status = $result;
                } else if (is_array($result)) {
                    $audit_log->status = VarDumper::dumpAsString($result);
                } else if ($result instanceof Response) {
                    $audit_log->status = $result->getStatusCode() . ':' . $result->statusText;
                } else {
                    $audit_log->status = '200';
                }
                if (strlen($audit_log->status) > 255) {
                    $audit_log->status = substr($audit_log->status, 0, 254);
                }
                if (!$audit_log->update()) {
                    Yii::error($audit_log->getErrors());
                }
            }
        }
        return $result;
    }

    /**
     * Returns a value indicating whether the filer is active for the given action.
     * @param Action $action the action being filtered
     * @return boolean whether the filer is active for the given action.
     */
    protected function isPostAction($action)
    {
        if ($this->owner instanceof Module) {
            // convert action uniqueId into an ID relative to the module
            $mid = $this->owner->getUniqueId();
            $id  = $action->getUniqueId();
            if ($mid !== '' && strpos($id, $mid) === 0) {
                $id = substr($id, strlen($mid) + 1);
            }
        } else {
            $id = $action->id;
        }
        $is_post    = false;
        $post_valid = false;

        if (!$this->request->isPost) {
            return [false, false];
        }

        foreach ($this->post_action as $action) {
            if (is_array($action)) {
                $accept_ajax = isset($action['accept_ajax']) ? $action['accept_ajax'] : true;
                $action_id   = isset($action['action']) ? $action['action'] : '';
                if ($action_id == $id) {
                    $is_post = true;
                    if ($accept_ajax) {
                        $post_valid = true;
                    } else {
                        if (!$this->request->isAjax) {
                            $post_valid = true;
                        }
                    }
                }
            } else {
                if ($id == $action) {
                    $is_post    = true;
                    $post_valid = true;
                }
            }
        }
        return [
            $is_post,
            $post_valid,
        ];
    }

    /**
     * @param $action Action
     * @return int
     */
    private function getModelType($action)
    {
        if ($this->owner instanceof Module) {
            // convert action uniqueId into an ID relative to the module
            $mid = $this->owner->getUniqueId();
            $id  = $action->getUniqueId();
            if ($mid !== '' && strpos($id, $mid) === 0) {
                $id = substr($id, strlen($mid) + 1);
            }
        } else {
            $id = $action->id;
        }

        $type = $this->model_type_default;
        if (isset($this->model_types[$id])) {
            $type = $this->model_types[$id];
        }
        return $type;
    }

}
