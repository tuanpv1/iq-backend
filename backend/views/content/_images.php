<?php
use common\assets\ToastAsset;
use common\widgets\MultiFileUpload;
use kartik\grid\GridView;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var $model \common\models\Content
 * @var $image \backend\models\Image
 * @var $dataProvider
 */
ToastAsset::register($this);
ToastAsset::config($this, [
    'positionClass' => ToastAsset::POSITION_BOTTOM_RIGHT
]);
$js = <<<JS
    function deleteImage(data){
        var allow = confirm("Bạn có chắc chắn muốn xóa ảnh này không");
        if(allow){
            var url = jQuery(data).attr('href');
            jQuery.get(url)
            .done(function(result) {
                if(result.success){
                    toastr.success(result.message);
                    jQuery.pjax.reload({container:'#image-grid-pjax'});
                }else{
                    toastr.error(result.message);
                }
            })
            .fail(function() {
                toastr.error("server error");
            });
        }
        return false;
    }
JS;
$this->registerJs($js,  View::POS_END);
$contentId=$model->id;
?>


<?= GridView::widget([
    'id' => 'image-grid',
    'dataProvider' => $dataProvider,
    'responsive' => true,
    'pjax' => true,
    'hover' => true,
    'columns' => [
        [
            'format' => 'html',
            'header' => 'Ảnh',
            'class' => '\kartik\grid\DataColumn',
            'attribute' => 'name',
            'value' => function($model, $key, $index){
                return Html::img($model->getImageUrl(), ['height' => '100']);
            }
        ],
        [
            'class' => 'kartik\grid\DataColumn',
            'attribute' => 'type',
            'label' => \Yii::t('app', 'Loại ảnh'),
            'value' => function($model, $key, $index){
                return $model->getImageType();
            },

        ],

//        [
//            'class' => 'kartik\grid\ActionColumn',
//              'buttons' => [
//                'image-delete' =>  function ($url, $model) use ($contentId) {
//                    $url = \yii\helpers\Url::to(['content/delete-image','content_id'=>$contentId,'name'=>$model->name]);
//                    return Html::a('<i class="glyphicon glyphicon-remove"></i>', $url, ['onclick'=> 'return deleteImage(this);']);
//                }
//            ],
//            'template' => '{image-delete}'
//        ],
    ],
]); ?>