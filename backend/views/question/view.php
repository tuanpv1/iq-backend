<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Question */

$this->title = $model->question;
$this->params['breadcrumbs'][] = ['label' => 'Questions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption"><i class="fa fa-gift"></i><?= $this->title ?></div>
            </div>
            <div class="portlet-body">
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
                        [
                            'attribute' => 'program_id',
                            'value' => $model->program->name,
                        ],
                        'question',
                        [
                            'attribute' => 'status',
                            'value' => $model->getStatusName(),
                        ],
                        [
                            'attribute' => 'status',
                            'label' => Yii::t('app', 'Answer'),
                            'format' => 'html',
                            'value' => $model->getAnswer()
                        ],
                        [
                            'attribute' => 'created_at',
                            'value' => date('d/m/Y H:i:s', $model->created_at),
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => date('d/m/Y H:i:s', $model->updated_at),
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
</div>
