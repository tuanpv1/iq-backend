<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\AuthItem */

$this->title =  $model->name;
$this->params['breadcrumbs'][] = ['label' => ''.\Yii::t('app', 'Quản lý quyền backend'), 'url' => ['permission']];
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="row">

    <div class="col-md-12">
        <p>
            <?= Html::a(''.\Yii::t('app', 'Cập nhật'), ['update-permission', 'name' => $model->name], ['class' => 'btn btn-primary']) ?>
            <?= Html::a(''.\Yii::t('app', 'Xóa'), ['delete-permission', 'name' => $model->name], [
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
                        'data',
                        'rule_name',
                        'created_at:time',
                        'updated_at:time',
                    ],
                ]) ?>

            </div>
        </div>
    </div>
</div>