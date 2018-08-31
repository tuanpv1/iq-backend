<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ContentRelatedAsm */

$this->title = 'Update Content Related Asm: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Content Related Asms', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="content-related-asm-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
