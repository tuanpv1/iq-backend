<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Service */

$this->title = ''.\Yii::t('app', 'Tạo gói cước');
$this->params['breadcrumbs'][] = ['label' => ''.\Yii::t('app', 'Danh sách gói cước'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i><?= $this->title ?>
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
