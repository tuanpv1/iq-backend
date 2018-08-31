<?php
/**
 * Created by PhpStorm.
 * User: TuanPV
 * Date: 10/19/2017
 * Time: 10:39 AM
 */
use common\models\Content;
use kartik\grid\GridView;
use kartik\helpers\Html;
use yii\helpers\Url;
\common\assets\ToastAsset::register($this);
\common\assets\ToastAsset::config($this, [
    'positionClass' => \common\assets\ToastAsset::POSITION_TOP_RIGHT
]);
/** @var Content $model */

$this->title = Yii::t('app', 'Thêm nội dung lẻ vào bộ ') . $model->display_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Danh sách phim'), 'url' => ['content/index', 'type' => $model->type]];
$this->params['breadcrumbs'][] = ['label' => $model->display_name, 'url' => ['content/view', 'id' => $model->id, 'active' => 5]];
$this->params['breadcrumbs'][] = $this->title;

$tb1 = Yii::t("app", "Chưa chọn nội dung lẻ! Xin vui lòng chọn ít nhất một nội dung lẻ để cập nhật.");
$loi = Yii::t("app", "Lỗi hệ thống");

$updateLink = Url::to(['content/add-episode-to-parent']);

$js = <<<JS
    function updateContentService(idContent){    
    listContentId = $("#content-index-grid-episode").yiiGridView("getSelectedRows");
    if(listContentId.length <= 0){
            alert("$tb1");
            return;
        }
    jQuery.post(
    '{$updateLink}',
    { 
        ids:listContentId,
        idContent:idContent
    }
    )
    .done(function(result) {
    if(result.success){
        toastr.success(result.message);
        jQuery.pjax.reload({container:'#content-index-grid-episode'});
    }else{
        toastr.error(result.message);
    }
    })
        .fail(function() {
            toastr.error('#{$loi}');
        });
    }
    
    function unCheckAll() {
        jQuery.pjax.reload({container:'#content-index-grid-episode'});
    }
JS;
$this->registerJs($js, \yii\web\View::POS_HEAD);
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <?= $this->title ?>
                </div>
            </div>
            <div class="portlet-body">
                <?php
                $gridColumn = [
                    [
                        'class' => 'kartik\grid\CheckboxColumn',
                        'headerOptions' => ['class' => 'kartik-sheet-style'],
                    ],
                    [
                        'class' => '\kartik\grid\DataColumn',
                        'format' => 'raw',
                        'label' => 'Ảnh',
                        'value' => function ($model, $key, $index, $widget) {
                            /** @var $model \common\models\Content */

                            $link = $model->getFirstImageLink();
                            return $link ? Html::img($link, ['alt' => 'Thumbnail', 'width' => '50', 'height' => '50']) : '';

                        },
                    ],
                    [
                        'format' => 'raw',
                        'class' => '\kartik\grid\DataColumn',
                        'attribute' => 'display_name',
                        'value' => function ($model, $key, $index) {
                            return Html::a($model->display_name, ['view', 'id' => $model->id], ['class' => 'label label-primary']);
                        },
                    ],
                    [
                        'format' => 'raw',
                        'class' => '\kartik\grid\DataColumn',
                        'attribute' => 'created_at',
                        'filterType' => GridView::FILTER_DATE,
                        'value' => function ($model) {
                            return date('d-m-Y H:i:s', $model->created_at);
                        }
                    ],
                    [
                        'class' => '\kartik\grid\DataColumn',
                        'attribute' => 'status',
                        'value' => function ($model, $key, $index) {
                            /** @var $model \common\models\Content */
                            return $model->getStatusName();
                        },
                        'filterType' => GridView::FILTER_SELECT2,
                        'filter' => \common\models\Content::getListStatus('filter'),
                        'filterWidgetOptions' => [
                            'pluginOptions' => ['allowClear' => true],
                        ],

                        'filterInputOptions' => ['placeholder' => 'Tất cả'],
                    ],
                    [
                        'class' => '\kartik\grid\DataColumn',
                        'attribute' => 'cp_id',
                        'label' => 'CP',
                        'filterType' => GridView::FILTER_SELECT2,
                        'filter' => \common\models\Content::getListCp(),
                        'filterWidgetOptions' => [
                            'pluginOptions' => ['allowClear' => true],
                        ],
                        'filterInputOptions' => ['placeholder' => \Yii::t('app', 'Tất cả')],
                        'value' => function ($model, $key, $index) {
                            /** @var $model \common\models\Content */
                            return $model->getNameCP($model->cp_id);
                        }
                    ],
                ];
                ?>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'id' => 'content-index-grid-episode',
                    'filterModel' => $searchEpisode,
                    'responsive' => true,
                    'pjax' => true,
                    'hover' => true,
                    'panel' => [
                        'type' => GridView::TYPE_PRIMARY,
                        'heading' => \Yii::t('app', 'Danh sách Nội dung')
                    ],
                    'toolbar' => [
                        [
                            'content' =>
                                Html::button('<i class="glyphicon glyphicon-ok"></i>' . Yii::t('app', 'Cập nhật'), [
                                    'type' => 'button',
                                    'title' => Yii::t('app', 'Cập nhật'),
                                    'class' => 'btn btn-success',
                                    'onclick' => 'updateContentService("' . $model->id . '");'
                                ])
                        ],
                        [
                            'content' =>
                                Html::button('<i class="glyphicon glyphicon-minus"></i>' . Yii::t('app', 'Hủy'), [
                                    'type' => 'button',
                                    'title' => Yii::t('app', 'Hủy'),
                                    'class' => 'btn btn-danger',
                                    'onclick' => 'unCheckAll();'
                                ])
                        ],
                        [
                            'content' =>
                                Html::a('<i class="glyphicon glyphicon-backward"></i>' . Yii::t('app', 'Quay lại'),
                                    Url::to(['content/view', 'id' => $model->id, 'active' => 5]),
                                    [
                                        'type' => 'button',
                                        'title' => Yii::t('app', 'Quay lại'),
                                        'class' => 'btn btn-primary',
                                    ]
                                )
                        ],
                    ],
                    'columns' => $gridColumn
                ]); ?>
            </div>
        </div>
    </div>
</div>
