<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\ContentRelatedAsm */

$this->title = 'Create Content Related Asm';
$this->params['breadcrumbs'][] = ['label' => 'Content Related Asms', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-related-asm-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
