<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\PriceLevel */

$this->title = 'Create Price Level';
$this->params['breadcrumbs'][] = ['label' => 'Price Levels', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="price-level-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
