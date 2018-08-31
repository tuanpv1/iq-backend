<?php

use common\models\Service;
use common\models\SmsMoSyntax;
use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \common\models\ServiceProvider */
/* @var $searchModel common\models\ServiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = ''.\Yii::t('app', 'Danh sách gói cước');
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span
                        class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'MO syntax') ?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'responsive' => true,
                    'pjax' => true,
                    'hover' => true,
                    'columns' => [
                        [
                            'class'=>'kartik\grid\ExpandRowColumn',
                            'width'=>'50px',
                            'value'=>function ($model, $key, $index, $column) {
                                return GridView::ROW_COLLAPSED;
                            },
                            'detail'=>function ($model, $key, $index, $column) {
                                return Yii::$app->controller->renderPartial('_expand-mosyntax-detail', ['model'=>$model]);
                            },
                            'headerOptions'=>['class'=>'kartik-sheet-style'] ,
                            'expandOneOnly'=>true
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'syntax',
                            'format' => 'html',
                            'value'=>function ($model, $key, $index, $widget) {
                                return Html::a($model->syntax, null,['class'=>'label label-primary']);
                            },
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'type',
                            'width'=>'200px',
                            'value'=>function ($model, $key, $index, $widget) {
                                /** @var $model \common\models\SmsMoSyntax */
                                return isset(SmsMoSyntax::$mo_types[$model->type])?SmsMoSyntax::$mo_types[$model->type]:'N/A';
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => SmsMoSyntax::$mo_types,
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => "".\Yii::t('app', 'Tất cả')],
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'service_id',
                            'width'=>'200px',
                            'value'=>function ($model, $key, $index, $widget) {
                                /** @var $model \common\models\SmsMoSyntax */
                                return  $model->service? $model->service->name: '';
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => SmsMoSyntax::getServiceList($model->id),
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => "".\Yii::t('app', 'Tất cả')],
                        ],
                        [
                            'attribute' => 'status',
                            'class' => '\kartik\grid\EditableColumn',
                            'refreshGrid' => true,
                            'width'=>'200px',
                            'editableOptions' => function ($model, $key, $index) {
                                /**
                                 * @var $model SmsMoSyntax
                                 */
                                return [
                                    'header' => 'Trạng thái',
                                    'size' => 'md',
                                    'displayValueConfig' =>SmsMoSyntax::getMoStatus(),
                                    'inputType' => \kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                                    'data' => $model->getListStatus(SmsMoSyntax::SCOPE_ADMIN),
                                    'placement' => \kartik\popover\PopoverX::ALIGN_LEFT,
                                    'afterInput'=>function ($form, $widget) use ($model, $index) {
                                        /**
                                         * @var $form \kartik\widgets\ActiveForm
                                         * @var $model SmsMoSyntax
                                         */
                                        return $form->field($model, "admin_note")->textarea(['row' => 3,
                                            'name'=>'SmsMoSyntax['.$index.'][admin_note]',
                                        ]);
                                    },
                                    'formOptions' => [
                                        'action' => ['service-provider/mo-update-status', 'id' => $model->id]
                                    ]
                                ];
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => SmsMoSyntax::getMoStatus(),
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => "".\Yii::t('app', 'Tất cả')],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>