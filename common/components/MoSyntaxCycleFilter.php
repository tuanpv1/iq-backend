<?php
/**
 * Created by PhpStorm.
 * User: qhuy
 * Date: 21/05/2015
 * Time: 09:42
 */
namespace common\components;;

use common\models\Service;
use common\models\SmsMoSyntax;
use common\models\User;
use Yii;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;

class MoSyntaxCycleFilter extends ActionFilter
{

    /**
     * @var callable a PHP callback that returns model relation with service model
     *
     * ~~~
     * function ($action, $params)
     * ~~~
     *
     * where `$action` is the [[Action]] object that this filter is currently handling;
     * `$params` takes the value of [[params]]
     */
    public $model_mo;
    public $scope = SmsMoSyntax::SCOPE_SP;

    public function beforeAction($action)
    {
        $request = Yii::$app->request;
        if ($this->model_mo !== null) {
            /**
             * @var $model SmsMoSyntax
             */
            $model = call_user_func($this->model_mo, $action, $request->getQueryParams());
        }else{
            return parent::beforeAction($action);
        }

        if($model == null){
            Yii::error('MO Syntax null');
            return parent::beforeAction($action);
        }

        if($model->isReadOnly($this->scope)){
            throw new ForbiddenHttpException("MO Syntax ".$model->syntax.Yii::t('app'," đang ở trang thái ").SmsMoSyntax::getMoStatusNameByStatus($model->status).Yii::t('app',", nên bạn không có quyền cập nhật"));
        }
        return parent::beforeAction($action);
    }

}
