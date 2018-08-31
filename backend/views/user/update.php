<?php

use common\assets\ToastAsset;
use kartik\grid\GridView;
use yii\web\View;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use common\models\AuthItem;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\User */
$this->title = ''.\Yii::t('app', 'Cập nhật thông tin người dùng');
$this->params['breadcrumbs'][] = ['label' => ''.\Yii::t('app', 'Quản lý người dùng'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption"><i class="fa fa-gift"></i><?= $this->title ?></div>
            </div>
            <div class="portlet-body form">
                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>
            </div>
        </div>
        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i><?= \Yii::t('app', 'Khôi phục mật khẩu') ?>
                </div>
            </div>
            <div class="portlet-body form">
                <?= $this->render('_form_change_password', [
                    'model' => $model,
                ]) ?>
            </div>
        </div>
    </div>
</div>
