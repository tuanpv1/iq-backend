<?php
/**
 * Created by PhpStorm.
 * User: qhuy
 * Date: 21/05/2015
 * Time: 09:42
 */
namespace common\components;;

use common\models\Service;
use common\models\User;
use Yii;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;

class ContentOwnerFilter extends ActionFilter
{

    /**
     * @var User $user
     */
    public $user = 'user';
    /**
     * @var callable a PHP callback that returns model relation with service_provider model
     *
     * ~~~
     * function ($action, $params)
     * ~~~
     *
     * where `$action` is the [[Action]] object that this filter is currently handling;
     * `$params` takes the value of [[params]]
     */
    public $model_relation_user;
    public $field_user_id = 'created_user_id';

    public function beforeAction($action)
    {
        $request = Yii::$app->request;
        if ($this->model_relation_user !== null) {
            $model = call_user_func($this->model_relation_user, $action, $request->getQueryParams());
        }else{
            return parent::beforeAction($action);
        }

        if($model == null){
            Yii::info('Model relation user null');
            return parent::beforeAction($action);
        }

        if ($this->user != null) {
            Yii::info('created user is : '.$model->{$this->field_user_id});
            if($this->user->id != $model->{$this->field_user_id}){
                throw new ForbiddenHttpException(Yii::t('app', 'Bạn không có quyền truy cập trang yêu cầu.'));
            }
        }
        return parent::beforeAction($action);
    }

}
