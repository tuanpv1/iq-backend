<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\ContentProvider */

$this->title = ''.\Yii::t('app', 'Tạo Nhà cung cấp nội dung');
$this->params['breadcrumbs'][] = ['label' => ''.\Yii::t('app', 'Danh sách nhà cung cấp nội dung'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i><?= \Yii::t('app', 'Tạo Nhà cung cấp nội dung') ?>
                </div>
            </div>
            <div class="portlet-body form">
                <?= $this->render('_form', [
                    'model' => $model,
                    'isNewRecord'=>true
                ]) ?>
            </div>
        </div>
    </div>
</div>
