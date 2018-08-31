<?php
use common\assets\ToastAsset;
use common\models\Category;
use common\models\Content;
use kartik\grid\GridView;
use kartik\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use \dosamigos\ckeditor\CKEditor;

/**
 * @var $model \common\models\Content
 * @var $profile \common\models\ContentProfile
 * @var $dataProvider
 */
ToastAsset::register($this);
ToastAsset::config($this, [
    'positionClass' => ToastAsset::POSITION_TOP_RIGHT
]);
$tb1 = Yii::t("app", "Chưa chọn content! Xin vui lòng chọn ít nhất một content để cập nhật.");
$loi = Yii::t("app", "Lỗi hệ thống");
$confirm = Yii::t("app", "Bạn có muốn xóa content không?");
$tb_cancel = Yii::t("app", "Cập nhật 0 content thành công");
$updateLink = Url::to(['content/update-status-episode']);
$urlReload = Url::to(['content/view', 'id' => $model->id, 'active' => 5]);
$status_delete = Content::STATUS_DELETE;

$js = <<<JS
    function changeStatus(newStatus){    
    listContentId = $("#list-episode-tab").yiiGridView("getSelectedRows");
    if(listContentId.length <= 0){
            toastr.error("$tb1");
            return;
        }
    jQuery.post(
    '{$updateLink}',
    { 
        ids:listContentId,
        newStatus:newStatus
    }
    )
    .done(function(result) {
    if(result.success){
        toastr.success(result.message);
        window.location.href = '{$urlReload}';
    }else{
        toastr.error(result.message);
    }
    })
        .fail(function() {
            toastr.error('#{$loi}');
        });
    }
    
    function deleteEpisode(){    
        listContentId = $("#list-episode-tab").yiiGridView("getSelectedRows");
        if(listContentId.length <= 0){
                toastr.error("$tb1");
                return;
            }else{
            if(confirm('{$confirm}')){
            changeStatus('{$status_delete}');             
         }else{
             toastr.success('{$tb_cancel}');
             window.location.href = '{$urlReload}';
         }
            }
         
    }
JS;
$this->registerJs($js, \yii\web\View::POS_HEAD);

?>
<?= Html::a(Yii::t('app', 'Tạo Episode'),
    Yii::$app->urlManager->createUrl(['content/create', 'type' => $model->type, 'parent' => $model->id]),
    ['class' => 'btn btn-default']) ?>
<?= Html::a(Yii::t('app', 'Thêm nội dung lẻ'),
    Yii::$app->urlManager->createUrl(['content/add-episode-modal', 'parent_id' => $model->id]),
    [
        'class' => 'btn btn-primary',
    ]);
?>

<?= Html::button(Yii::t('app', 'Publish'),
    [
        'type' => 'button',
        'class' => 'btn btn-success',
        'onclick' => 'changeStatus(' . Content::STATUS_ACTIVE . ')'
    ]);
?>

<?= Html::button(Yii::t('app', 'unPublish'),
    [
        'type' => 'button',
        'class' => 'btn btn-warning',
        'onclick' => 'changeStatus(' . Content::STATUS_INVISIBLE . ')'
    ]);
?>

<?= Html::button(Yii::t('app', 'Remove'),
    [
        'type' => 'button',
        'class' => 'btn btn-danger',
        'onclick' => 'deleteEpisode()'
    ]);
?>
    <br>
    <br>
<?php

echo GridView::widget([
    'dataProvider' => $episodeProvider,
    'filterModel' => $episodeSearch,
    'responsive' => true,
    'id' => 'list-episode-tab',
    'pjax' => true,
    'hover' => true,
    'columns' => [
        [
            'class' => 'kartik\grid\CheckboxColumn',
            'headerOptions' => ['class' => 'kartik-sheet-style'],
        ],
        [
            'class' => '\kartik\grid\DataColumn',
            'format' => 'raw',
            'label' => \Yii::t('app', 'Ảnh'),
            'value' => function ($model, $key, $index, $widget) {
                /** @var $model \common\models\Content */

                $link = $model->getFirstImageLink();
                return $link ? Html::img($link, ['alt' => 'Thumbnail', 'width' => '50', 'height' => '50']) : '';

            },
        ],
        [
            'format' => 'html',
            'class' => '\kartik\grid\DataColumn',
            'attribute' => 'display_name',
            'value' => function ($model, $key, $index) {
                return Html::a($model->display_name, ['view', 'id' => $model->id]);
            }
        ],
        [
            'class' => 'kartik\grid\EditableColumn',
            'attribute' => 'episode_order',
            'refreshGrid' => true,
            'editableOptions' => function ($model, $key, $index) {
                /* @var $model \common\models\Content */
                return [
                    'header' => 'Sắp xếp',
                    'size' => 'md',
                    'inputType' => \kartik\editable\Editable::INPUT_TEXT,
                    'formOptions' => [
                        'action' => \yii\helpers\Url::to([
                            'content/update-order-view',
                            'id' => $model->id
                        ]),
                        'enableClientValidation' => false,
                        'enableAjaxValidation' => false,
                    ],
                ];
            },
        ],
        [
            'attribute' => 'created_at',
            'label' => Yii::t("app", "Ngày kích hoạt"),
            'width' => '230px',
            'filterType' => GridView::FILTER_DATE,
            'filterWidgetOptions' => [
                'pluginOptions' => [
                    'format' => 'dd-mm-yyyy',
                    'disableMousewheel' => false
                ]
            ],
            'value' => function ($model, $key, $index, $widget) {
                /**
                 * @var $model Content
                 */
                return $model->activated_at ? date('Y-m-d H:m:i', $model->created_at) : Yii::t("app", "Chưa kích hoạt");
            },
        ],
//        [
//            'format' => 'html',
//            'width' => '150px',
//            'class' => '\kartik\grid\DataColumn',
//            'attribute' => 'status',
//            'value' => function ($model, $key, $index) {
//                /** @var $model Content */
//                return $model->getStatusName();
//            },
//            'filterType' => GridView::FILTER_SELECT2,
//            'filter' => Content::getListStatus('filter'),
//            'filterWidgetOptions' => [
//                'pluginOptions' => ['allowClear' => true],
//            ],
//
//            'filterInputOptions' => ['placeholder' => Yii::t('app', 'Tất cả')],
//
//        ],
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
            'attribute' => '',
            'format' => 'raw',
            'width' => '20%',
            'value' => function ($model, $key, $index, $widget) {

                $viewUrl = Yii::$app->urlManager->createUrl(['/content/view', 'id' => $model->id]);
                $updateUrl = Yii::$app->urlManager->createUrl(['/content/update', 'id' => $model->id]);
                /**
                 * @var $model \common\models\Content
                 */
                $res = Html::a(\Yii::t('app', 'Detail'), $viewUrl,
                    [
                        'class' => 'btn btn-primary'
                    ]);
                $res .= '&nbsp;&nbsp;&nbsp;&nbsp;';
                $res .= Html::a(\Yii::t('app', 'Update'), $updateUrl,
                    [

                        'class' => 'btn btn-info'
                    ]);

                return $res;
            },
        ],

    ]
]);
?>