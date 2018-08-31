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

class SPOwnerFilter extends ActionFilter
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
    public $model_relation_sp;
    public $field_sp_id = 'site_id';

    public function beforeAction($action)
    {
        $request = Yii::$app->request;
        if ($this->model_relation_sp !== null) {
            $model = call_user_func($this->model_relation_sp, $action, $request->getQueryParams());
        }else{
            return parent::beforeAction($action);
        }

        if($model == null){
            Yii::info('Model relation sp null');
            return parent::beforeAction($action);
        }

        // if ($this->user != null) {
        //     Yii::info('Service provider is : '.$model->{$this->field_sp_id});
        //     if($this->user->site_id != $model->{$this->field_sp_id}){
        //         throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        //     }
        // }
        return parent::beforeAction($action);
    }

}
