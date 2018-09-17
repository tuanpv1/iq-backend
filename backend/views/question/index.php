<?php

use common\models\Question;
use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel common\models\QuestionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Questions';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?= $this->title ?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <p><?= Html::a(\Yii::t('app', 'Tạo câu hỏi'), ['create'], ['class' => 'btn btn-success']) ?> </p>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],

                        [
                            'attribute' => 'program_id',
                            'format' => 'raw',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model \common\models\Question
                                 */
                                $action = "program/view";
                                $res = Html::a('<kbd>'.$model->program->name.'</kbd>', [$action, 'id' => $model->program_id ]);
                                return $res;

                            },
                        ],
                        'question',
                        [
                            'class' => 'kartik\grid\EditableColumn',
                            'attribute' => 'level',
                            'refreshGrid' => true,
                            'editableOptions' => function ($model, $key, $index) {
                                return [
                                    'header' => \Yii::t('app', 'Level'),
                                    'size' => 'md',
                                    'inputType' => \kartik\editable\Editable::INPUT_TEXT,
                                    'data' => $model->level,
                                    'placement' => \kartik\popover\PopoverX::ALIGN_LEFT,
                                    'formOptions' => [
                                        'action' => ['question/update-level', 'id' => $model->id]
                                    ],
                                ];
                            },
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute'=>'status',
                            'label'=>''.\Yii::t('app', 'Trạng thái'),
                            'format'=>'raw',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model Question
                                 */
                                if($model->status == Question::STATUS_ACTIVE){
                                    return '<span class="label label-success">'.$model->getStatusName().'</span>';
                                }else{
                                    return '<span class="label label-danger">'.$model->getStatusName().'</span>';
                                }

                            },
                            'filter' => Question::getListStatus(),
                            'filterType' => GridView::FILTER_SELECT2,
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => "".\Yii::t('app', 'Tất cả')],
                        ],

                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
