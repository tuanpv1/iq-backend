<?php

use common\models\Service;
use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
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
                        class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'API cung cấp cho vinaphone') ?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'responsive' => true,
                    'pjax' => true,
                    'hover' => true,
                    'columns' => [
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'label' => 'Name',
                            'value'=>function ($model, $key, $index, $widget) {
                                return $model->name;
                            },
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'label' => 'Links',
                            'format' => 'html',
                            'value'=>function ($model, $key, $index, $widget) {
                                return '<a href = "#">'.$model->link.'</a>';
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>