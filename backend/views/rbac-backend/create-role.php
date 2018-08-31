<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\AuthItem */

$this->title = ''.\Yii::t('app', 'Tạo nhóm quyền');
$this->params['breadcrumbs'][] = ['label' => ''.\Yii::t('app', 'Quản lý nhóm quyền trang backend'), 'url' => ['role']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">

    <div class="col-md-8 col-md-offset-2">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-globe"></i><?= $this->title ?>
                </div>
            </div>
            <div class="portlet-body">

                <?= $this->render('_form-role', [
                    'model' => $model,
                ]) ?>

            </div>
        </div>

    </div>
</div>
