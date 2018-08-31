<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\PriceLevel */

$this->title = 'Update Price Level: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Price Levels', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="price-level-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
