<?php

use common\assets\ToastAsset;
use common\auth\models\ActionPermission;
use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\editable\Editable;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */

$this->title = 'Role bakcend generator';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php

ToastAsset::register($this);
ToastAsset::config($this, [
    'positionClass' => ToastAsset::POSITION_TOP_RIGHT
]);

$tableId = "rbac-role-generator";

$generateUrl = \yii\helpers\Url::to(['rbac-backend/generate-role-confirm']);

$js = <<<JS
function generateRole(){
    actions = $("#$tableId").yiiGridView("getSelectedRows");
    if(actions.length <= 0){
        alert("Chưa chọn role nào! Xin vui lòng chọn ít nhất một role để khởi tạo.");
        return;
    }

    if (confirm("Thao tác này sẽ tự động gán lại các Permission tương ứng vào các Role chọn lựa (ví dụ: MỌI permission bắt đầu bằng 'User.' như 'User.Create', 'User.Update'... sẽ tự động được gán vào Role 'User.*'), bạn có chắc chắn?")) {
        jQuery.post(
            '{$generateUrl}',
            { ids:actions }
            )
            .done(function(result) {
                if(result.success){
                    toastr.success(result.message);
                    jQuery.pjax.reload({container:'#{$tableId}'});
                }else{
                    toastr.error(result.message);
                }
            })
            .fail(function() {
                toastr.error("server error");
        });
    }
}
JS;

$this->registerJs($js, View::POS_END);
?>

<div class="user-index">

    <p>
        <?= Html::a('Manage Role', ['rbac-backend'], ['class' => 'btn btn-success']) ?>
        <?=
        Html::button('<i class="glyphicon glyphicon-ok"></i> Generate', [
            'type' => 'button',
            'title' => 'Generate operations',
            'class' => 'btn btn-success',
            'onclick' => 'generateRole();'
        ])?>
    </p>

    <?= GridView::widget([
        'id' => $tableId,
        'dataProvider' => $dataProvider,
        'responsive' => true,
        'pjax' => true,
        'hover' => true,
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => ''.\Yii::t('app', 'Danh sách action chưa tạo permission')
        ],
        'toolbar' => [
            [
                'content' =>
                    Html::button('<i class="glyphicon glyphicon-ok"></i> Generate', [
                        'type' => 'button',
                        'title' => 'Generate operations',
                        'class' => 'btn btn-success',
                        'onclick' => 'generateRole();'
                    ])

            ],
        ],
        'columns' => [
            [
                'class' => '\kartik\grid\CheckboxColumn',
//                'checkboxOptions' => function($model, $key, $index, $column) {
//                    /* @var $model ActionAuthItem */
//                    $existed = $model->isExisted();
//                    return ['checked' => !$existed, 'disabled' => $existed];
//                }
            ],
            ['class' => 'kartik\grid\SerialColumn'],
            'name',
            'appAlias',
            'route',
//            'data',
//            'type',
        ],
    ]); ?>

</div>


