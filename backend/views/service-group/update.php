<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ServiceGroup */

$this->title = ''.\Yii::t('app', 'Cập nhật nhóm gói cước : ') . ' ' . $model->display_name;
$this->params['breadcrumbs'][] = ['label' => 'Nhóm gói cước', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = ''.\Yii::t('app', 'Update');
?>
<div class="row">
    <div class="col-md-12">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i><?= \Yii::t('app', 'Cập nhật nhóm gói cước') ?> <?php echo $model->display_name;?>
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
