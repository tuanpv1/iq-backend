<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\AuthItem */

$this->title =  $model->name;
$this->params['breadcrumbs'][] = ['label' => ''.\Yii::t('app', 'Quản lý nhóm quyền trang backend'), 'url' => ['role']];
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="row">

    <div class="col-md-12">
        <p>
            <?= Html::a(''.\Yii::t('app', 'Cập nhật'), ['update-role', 'name' => $model->name], ['class' => 'btn btn-primary']) ?>
            <?= Html::a(''.\Yii::t('app', 'Xóa'), ['delete-role', 'name' => $model->name], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]) ?>
        </p>

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-globe"></i><?= $this->title ?>
                </div>
            </div>
            <div class="portlet-body">

                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'name',
                        'description',
                        [
                            'attribute'=>'created_at',
                            'format'=>['date', 'd/M/Y H:i:s '],
                            'widgetOptions'=>[
                                'class'=>\kartik\datecontrol\DateControl::classname(),
                                'type'=>\kartik\datecontrol\DateControl::FORMAT_DATE
                            ]
                        ],
                        [
                            'attribute'=>'updated_at',
                            'format'=>['date', 'd/M/Y H:i:s '],
                            'widgetOptions'=>[
                                'class'=>\kartik\datecontrol\DateControl::classname(),
                                'type'=>\kartik\datecontrol\DateControl::FORMAT_DATE
                            ]
                        ],
                    ],
                ]) ?>

            </div>
        </div>
    </div>
</div>