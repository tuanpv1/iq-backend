<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ContentAttribute */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Thuộc tính nội dung'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-attribute-view portlet light">

    <h1><?= Html::encode(\Yii::t('app', 'Thuộc tính: '). $this->title) ?></h1>

    <p>
        <?= Html::a('Cập nhật ', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(\Yii::t('app', 'Xóa'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => \Yii::t('app', 'Bạn có muốn xóa không?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'name',
            [
                'attribute' => 'content_type',
                'format' => 'raw',
                'value' => \common\models\Category::getListType($model->content_type)
            ],
            [
                'attribute' => 'data_type',
                'format' => 'raw',
                'value' => $model->getDatatype($model->data_type)
            ],
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => date('d-m-Y H:i:s', $model->created_at)
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'raw',
                'value' => date('d-m-Y H:i:s', $model->updated_at)
            ],
        ],
    ]) ?>

</div>
