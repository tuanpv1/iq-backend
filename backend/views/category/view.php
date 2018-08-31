<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Category */

$this->title = $model->display_name;
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Danh mục ') . \common\models\Category::getTypeName($model->type), 'url' => Yii::$app->urlManager->createUrl(['category/index', 'type' => $model->type])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(\Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= \kartik\detail\DetailView::widget([
        'model' => $model,
        'attributes' => [

            'display_name',
            [
                'attribute' => 'type',
                'format' => 'raw',
                'value' => \common\models\Category::getTypeName($model->type)
            ],
            [
                'label' => $model->getAttributeLabel('status'),
                'attribute' => 'status',
                'value' => $model->getStatusName()
            ],
            'description:ntext',


        ],
    ]) ?>

</div>
