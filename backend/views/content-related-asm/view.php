<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ContentRelatedAsm */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Content Related Asms', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-related-asm-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'content_id',
            'content_related_id',
            'updated_at',
            'created_at',
        ],
    ]) ?>

</div>
