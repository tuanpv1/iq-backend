<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Service */

$this->title = ''.\Yii::t('app', 'Cập nhật gói cước: ') . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => ''.\Yii::t('app', 'Danh sách gói cước'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = ''.\Yii::t('app', 'Câp nhật');
?>
<div class="row">
    <div class="col-md-12">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i><?= \Yii::t('app', 'Tạo gói cước') ?>
                </div>
            </div>
            <div class="portlet-body form">
                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>
            </div>
        </div>
    </div>
</div>
