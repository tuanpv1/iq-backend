<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var \common\models\ContentAttribute  $model */

$this->title = \Yii::t('app', 'Thuộc tính nội dung');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-attribute-index portlet light">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if(!Yii::$app->params['tvod1Only']) echo Html::a(\Yii::t('app', 'Thêm mới thuộc tính'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            [
                'attribute' => 'content_type',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $widget) {
                    return \common\models\Category::getListType($model->content_type);
                }
            ],
            [
                'attribute' => 'data_type',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $widget) {
                    return  $model->getDatatype($model->data_type);
                }
            ],
            [
                'attribute' => 'created_at',
                'value' => function($model){
                    return date('d-m-Y H:i:s', $model->created_at);
                }
            ],
            [
                'class' => 'kartik\grid\EditableColumn',
                'attribute' => 'order',
                'refreshGrid' => true,
                'editableOptions' => function ($model, $key, $index) {
                    return [
                        'header' => Yii::t("app","Sắp xếp"),
                        'size' => 'md',
                        'displayValueConfig' =>$model->order,
                        'inputType' => \kartik\editable\Editable::INPUT_TEXT,
                        'placement' => \kartik\popover\PopoverX::ALIGN_LEFT,
                        'formOptions' => [
                            'action' => ['content-attribute/update-order', 'id' => $model->id]
                        ],
                    ];
                },
            ],
            // 'updated_at',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => ' {view}{update}{delete}',
                'buttons' => [
                    'delete' => function($url, $model){
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['delete', 'id' => $model->id], [
                            'class' => '',
                            'data' => [
                                'confirm' => \Yii::t('app', 'Bạn có muốn xóa không?'),
                                'method' => 'post',
                            ],
                        ]);
                    }
                ]
            ],
        ],
    ]); ?>

</div>
