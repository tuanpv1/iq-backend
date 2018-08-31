<?php

use common\models\PriceLevel;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel common\models\PriceLevelSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $newPriceLevel PriceLevel */

$this->title = 'Quản lý giá cước mua lẻ';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span
                        class="caption-subject font-green-sharp bold uppercase">Quản lý giá cước mua lẻ</span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <p>
                    <?php
                    Modal::begin([
                        'header' => '<h4>Tạo mức giá</h4>',
                        'toggleButton' => ['label' => 'Tạo mức giá', 'class' => 'btn btn-success'],
                        'closeButton' => ['label' => 'Cancel']
                    ]);
                    echo $this->render('_form', ['model' => $newPriceLevel]);
                    Modal::end();
                    ?>
                </p>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'responsive' => true,
                    'pjax' => true,
                    'hover' => true,
                    'columns' => [
                        [
                            'class' => '\kartik\grid\EditableColumn',
                            'attribute' => 'price',
                            'refreshGrid' => true,
                            'value'=>function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model \common\models\PriceLevel
                                 */
                                return $model->price .' VND';
                            },
                            'editableOptions' => function ($model, $key, $index) {
                                return [
                                    'header' => 'Giá cước mua lẻ',
                                    'size' => 'md',
                                ];
                            }
                        ],
                        'description:ntext',
                        'created_at:datetime',
                        [
                            'class' => 'kartik\grid\ActionColumn',
                            'template' => '{delete}',
//                            'dropdown' => true,
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
