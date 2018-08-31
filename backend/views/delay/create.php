<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Delay */

$this->title = Yii::t('app','Thêm mới');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app','Độ trễ nội dung theo nhà cung cấp dịch vụ'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-offset-2 col-md-8">
        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i><?= $this->title?>
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

