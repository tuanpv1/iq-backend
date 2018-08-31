<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\widgets;

use common\models\Service;
use Yii;
use yii\bootstrap\Modal;
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
class BEServiceCycle extends Widget
{

    const MODE_TEMP = 0;
    const MODE_PRODUCTION = 1;

    /**
     * @var $model Service
     */
    public $model;
    public $mode = self::MODE_TEMP;
    public $modal_note = '';
    public $modal_suspend = '';


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
        if ($this->model->status == Service::STATUS_PENDING) {
            $html .= Html::a(Yii::t('app', 'Duyệt gói cước'), ['approve', 'id' => $this->model->id, 'status' => Service::STATUS_ACTIVE],
                [   'class' => 'btn btn-primary',
                    'data' => [
                        'confirm' => "Toàn bộ thông tin này sẽ được cập nhật vào gói cước đang hoạt động. Bạn có chắc chắn duyệt gói cước này không?",
                    ],
                ]);
            $html .= Html::a(Yii::t('app', 'Loại bỏ'), null, [
                'class' => 'btn btn-danger',
                'data-toggle' => 'modal', 'data-target' => '#'.$this->modal_suspend
            ]);
            $html .= Html::a(Yii::t('app', 'Cập nhật ghi chú'), null,
                ['class' => 'btn btn-success', 'data-toggle' => 'modal', 'data-target' => '#'.$this->modal_note]);

        }

        return $html;
    }

    private function renderButtonOnProduction()
    {
        /**
         * Status temp -> [delete, update, publish]
         */
        $html = '';
        if ($this->model->status >= Service::STATUS_PAUSE ) {
            $html .= Html::a(Yii::t('app', 'Loại bỏ'), null, [
                'class' => 'btn btn-danger',
                'data-toggle' => 'modal', 'data-target' => '#'.$this->modal_suspend
            ]);
        }

        return $html;
    }
}
