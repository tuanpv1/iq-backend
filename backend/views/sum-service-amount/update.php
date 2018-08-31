<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\SumServiceAmount */

$this->title = 'Update Sum Service Amount: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Sum Service Amounts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="sum-service-amount-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
