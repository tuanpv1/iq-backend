<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\SmsMtTemplate */

$this->title = ''.\Yii::t('app', 'Update Sms Mt Template: ') . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => ''.\Yii::t('app', 'Sms Mt Templates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = ''.\Yii::t('app', 'Update');
?>
<div class="row">
    <div class="col-md-12">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i><?= \Yii::t('app', 'Cập nhật MT Template') ?>
                </div>
            </div>
            <div class="portlet-body form">
                <?= $this->render('_form', [
                    'model' => $model,
                    'params'=>$params
                ]) ?>
            </div>
        </div>
    </div>
</div>
