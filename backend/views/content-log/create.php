<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\ContentLog */

$this->title = \Yii::t('app', 'Create Content Log');
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Content Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-log-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
