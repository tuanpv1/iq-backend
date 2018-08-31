<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\widgets;

use common\models\Service;
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
class SPServiceCycle extends Widget
{

    const MODE_TEMP = 0;
    const MODE_PRODUCTION = 1;

    /**
     * @var $model Service
     */
    public $model;
    public $mode = self::MODE_TEMP;


    /**
     * Renders the widget.
     */
    public function run()
    {
        if($this->model){
            if ($this->mode == self::MODE_TEMP) {
                echo $this->renderButtonOnTemp();
            } else {
                echo $this->renderButtonOnProduction();
            }
        }
    }

    private function renderButtonOnTemp()
    {
        /**
         * Status temp -> [delete, update, publish]
         */
        $html = '';
        if ($this->model->status == Service::STATUS_TEMP) {
            $html .= Html::a(Yii::t('app', 'Cập nhật'), ['update', 'id' => $this->model->id],
                ['class' => 'btn btn-primary']);
            $html .= Html::a(Yii::t('app', 'Xóa'), ['delete', 'id' => $this->model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Bạn có chắc chắn muốn xóa gói cước?'),
                    'method' => 'post',
                ],
            ]);
            $html .= Html::a(Yii::t('app', 'Publish'), ['update-status', 'id' => $this->model->id, 'status' => Service::STATUS_PENDING],
                ['class' => 'btn btn-success']);
        } elseif ($this->model->status == Service::STATUS_PENDING) {
            $html .= Html::a(Yii::t('app', 'Duyệt gói cước'), ['approve', 'id' => $this->model->id, 'status' => Service::STATUS_ACTIVE],
                [   'class' => 'btn btn-primary',
                    'data' => [
                        'confirm' => "Toàn bộ thông tin này sẽ được cập nhật vào gói cước đang hoạt động. Bạn có chắc chắn duyệt gói cước này không?",
                    ],
                ]);
            $html .= Html::a(Yii::t('app', 'UnPublish'), ['update-status', 'id' => $this->model->id, 'status' => Service::STATUS_TEMP],
                ['class' => 'btn btn-primary']);
        } elseif ($this->model->status == Service::STATUS_INACTIVE) {
            $html .= Html::a(Yii::t('app', 'Cập nhật'), ['update', 'id' => $this->model->id],
                ['class' => 'btn btn-primary']);
        }

        return $html;
    }

    private function renderButtonOnProduction()
    {
        /**
         * Status temp -> [delete, update, publish]
         */
        $html = '';
        if ($this->model->status == Service::STATUS_ACTIVE) {
            $html .= Html::a(Yii::t('app', 'Tạm dừng'), ['update-status', 'id' => $this->model->id, 'status' => Service::STATUS_PAUSE], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Bạn có muốn tạm dưng gói cước này không?'),
                    'method' => 'post',
                ],
            ]);
            if($this->model->tempService == null){
                $html .= Html::a(Yii::t('app', 'Cập nhật'), ['update-service', 'id' => $this->model->id],
                    ['class' => 'btn btn-success']);
            }
        } elseif ($this->model->status == Service::STATUS_PAUSE) {
            $html .= Html::a(Yii::t('app', 'Kích hoạt'), ['update-status', 'id' => $this->model->id, 'status' => Service::STATUS_ACTIVE],
                ['class' => 'btn btn-primary']);
        }

        return $html;
    }
}
