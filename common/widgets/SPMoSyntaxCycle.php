<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\widgets;

use common\models\Service;
use common\models\SmsMoSyntax;
use Yii;
use yii\bootstrap\Widget;
use yii\helpers\Html;

/**
 * Nav renders a nav HTML component.
 *
 * For example:
 *
 * ```php
 * echo ServiceCycle::widget([
 *     'model' => $model (Model service),
 *     'mode' => (0: temp, 1: production)
 * ]);
 * ```
 *
 */
class SPMoSyntaxCycle extends Widget
{

    /**
     * @var $model Service
     */
    public $model;


    /**
     * Renders the widget.
     */
    public function run()
    {
        if($this->model){
            echo $this->renderButton();
        }
    }

    private function renderButton()
    {
        /**
         * Status temp -> [delete, update, publish]
         */
        $html = '';
        if ($this->model->status == SmsMoSyntax::STATUS_PENDING) {
            $html .= Html::a(Yii::t('app', 'Cập nhật'), ['update', 'id' => $this->model->id],
                ['class' => 'btn btn-primary']);
            $html .= Html::a(Yii::t('app', 'Xóa'), ['delete', 'id' => $this->model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Bạn có muốn xóa gói cước này không?'),
                    'method' => 'post',
                ],
            ]);
        } elseif ($this->model->status == SmsMoSyntax::STATUS_PAUSE) {
            $html .= Html::a(Yii::t('app', 'Kích hoạt'), ['update-status', 'id' => $this->model->id, 'status' => SmsMoSyntax::STATUS_ACTIVE],
                ['class' => 'btn btn-primary']);
        } elseif ($this->model->status == SmsMoSyntax::STATUS_ACTIVE) {
            $html .= Html::a(Yii::t('app', 'Tạm dừng'), ['update-status', 'id' => $this->model->id, 'status' => SmsMoSyntax::STATUS_PAUSE],
                ['class' => 'btn btn-primary']);
        } elseif ($this->model->status == SmsMoSyntax::STATUS_INACTIVE) {
            $html .= Html::a(Yii::t('app', 'Cập nhật'), ['update', 'id' => $this->model->id],
                ['class' => 'btn btn-primary']);
        }

        return $html;
    }
}
