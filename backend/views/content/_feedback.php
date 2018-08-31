<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ContentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */



?>
<div class="row">

    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span
                        class="caption-subject font-green-sharp bold uppercase"> <?= \Yii::t('app', 'Danh sách Feedback'); ?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">

                <?php
                $gridColumn = [
                    [
                        'class' => '\kartik\grid\DataColumn',
                        'attribute' => 'subscriber_id',
                        'format' => 'html',
                        'value' => function ($model, $key, $index, $widget) {
                            /** @var $model \common\models\ContentFeedback */

                            return $model->subscriber ?    Html::a($model->subscriber->msisdn, ['/subscriber/view', 'id' => $model->subscriber->id],['class'=>'label label-primary']) : '';
                        },
                    ],
                    [
                        'attribute' => 'content',
                        'label' => \Yii::t('app', 'Nhận xét'),
                        'format' => 'html',
                    ],
                    [
                        'attribute' => 'created_at',
                        'label' => \Yii::t('app', 'Ngày tạo'),
                        'format' => 'datetime',
                    ],
                    [
                        'class' => 'kartik\grid\EditableColumn',
                        'attribute' => 'status',
                        'label' => \Yii::t('app', 'Trạng thái'),
                        'width' => '200px',
                        'refreshGrid' => true,
                        'value'=> function ($model, $key, $index, $widget) {
                            /** @var $model \common\models\ContentFeedback */

                            return $model->getStatusName();
                        },
                        'editableOptions' => function ($model, $key, $index) {
                            return [
                                'header' => \Yii::t('app', 'Trạng thái'),
                                'size' => 'md',
                                'displayValueConfig' => \common\models\ContentFeedback::getListStatus(),
                                'inputType' => \kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                                'data' => \common\models\ContentFeedback::getListStatus(),
                                'placement' => \kartik\popover\PopoverX::ALIGN_LEFT
                            ];
                        },
                        'filterType' => GridView::FILTER_SELECT2,
                        'filter' => \common\models\ContentFeedback::getListStatus(),
                        'filterWidgetOptions' => [
                            'pluginOptions' => ['allowClear' => true],
                        ],
                        'filterInputOptions' => ['placeholder' => \Yii::t('app', 'Tất cả')],
                    ],

                    [
                        'class' => 'kartik\grid\EditableColumn',
                        'attribute' => 'admin_note',
                        'label' => \Yii::t('app', 'Ghi chú'),
                        'width' => '200px',
                        'refreshGrid' => true,
                        'editableOptions' => function ($model, $key, $index) {
                            return [
                                'header' => \Yii::t('app', 'Admin Note'),
                                'size' => 'md',
                                'value'=>$model->admin_note,
                                'inputType' => \kartik\editable\Editable::INPUT_TEXT,
                                'placement' => \kartik\popover\PopoverX::ALIGN_LEFT
                            ];
                        },

                    ],

                ];

                ?>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $feedbackSearch,

                    'responsive' => true,
                    'pjax' => true,
                    'hover' => true,
                    'columns' => $gridColumn
                ]); ?>
            </div>
        </div>
    </div>
</div>
<?php
$js = <<<JS
function submitForm(){
jQuery("#Form_Grid_Content").submit();
}
JS;
$this->registerJs($js, \yii\web\View::POS_HEAD);
?>
