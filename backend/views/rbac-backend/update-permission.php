<?php

use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\AuthItem */

$this->title = ''.\Yii::t('app', 'Cập nhật quyền: ') . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => ''.\Yii::t('app', 'Quàn lý quyền backend'), 'url' => ['permission']];
//$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view-permission', 'name' => $model->name]];
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="row">

    <div class="col-md-6">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-globe"></i>
                    <?= \Yii::t('app', 'Thông tin chung') ?>
                </div>
            </div>
            <div class="portlet-body">

                <?= $this->render('_form-permission', [
                    'model' => $model,
                ]) ?>
            </div>
        </div>

    </div>

    <div class="col-md-6">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-globe"></i>
                    <?= \Yii::t('app', 'Quyền và nhóm quyền') ?>
                </div>
            </div>
            <div class="portlet-body">
                <h3><?= \Yii::t('app', 'Nhóm quyền cha') ?></h3>
                <?= GridView::widget([
                    'id' => 'rbac-role-parent',
                    'dataProvider' => $model->getParentProvider(),
                    'responsive' => true,
                    'pjax' => false,
                    'hover' => true,
                    'columns' => [
                        ['class' => 'kartik\grid\SerialColumn'],
                        [
                            'attribute' => 'name',
                            'format' => 'html',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model \common\models\AuthItem
                                 */
                                $res = Html::a($model->description, ['rbac-backend/update-role', 'name' => $model->name]);
                                $res .= " [" . sizeof($model->children) . "]";
                                return $res;
                            },
                        ],
                        'description',
                    ],
                ]); ?>

            </div>
        </div>

    </div>
</div>
