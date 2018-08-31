<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ContentFeedback */

$this->title = \Yii::t('app', 'Update Content Feedback: ') . ' ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Content Feedbacks'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="content-feedback-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
