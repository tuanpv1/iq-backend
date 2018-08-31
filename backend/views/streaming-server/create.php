<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\StreamingServer */

$this->title = Yii::t('app', 'Thêm mới địa chỉ phân phối nội dung');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Quản lý địa chỉ phân phối nội dung'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i><?= \Yii::t('app', 'Tạo địa chỉ phân phối nội dung') ?>
                </div>
            </div>
            <div class="portlet-body form">

                <?= $this->render('_form', [
                    'model' => $model,
                    'primaryCached' => false
                ]) ?>

            </div>
        </div>
    </div>
</div>
