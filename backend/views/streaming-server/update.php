<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\StreamingServer */

$this->title = Yii::t('app', 'Cập nhật {modelClass}: ', [
    'modelClass' => 'địa chỉ phân phối nội dung',
]) . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Quản lý địa chỉ phân phối nội dung'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Cập nhật địa chỉ phân phối nội dung');
?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i><?= \Yii::t('app', 'Cập nhật địa chỉ phân phối nội dung') ?>
                </div>
            </div>
            <div class="portlet-body form">

                <?= $this->render('_form', [
                    'model' => $model,
                    'primaryCached' => $primaryCached
                ]) ?>

            </div>
        </div>
    </div>
</div>
