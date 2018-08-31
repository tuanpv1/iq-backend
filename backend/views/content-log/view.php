<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ContentLog */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Content Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-log-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(\Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(\Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => \Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'content_id',
                'format' =>'html',
                'value' =>   Html::a($model->content_name, ['/content/view', 'id' => $model->content->id],['class'=>'label label-primary'])
            ],
            'created_at:datetime',
            'ip_address',
            'status',
            [
                'attribute' => 'type',
                'value' => $model->getTypeName()
            ],
            'description:ntext',
            'user_agent',
            [
                'attribute' => 'user_id',
                'format' =>'html',
                'value' =>   Html::a($model->user->username, ['/user/view', 'id' => $model->user->id],['class'=>'label label-primary'])
            ],
        ],
    ]) ?>

</div>
