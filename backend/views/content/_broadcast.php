<?php
use common\models\Category;
use kartik\grid\GridView;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use common\assets\ToastAsset;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

?>
<?php
$url =\yii\helpers\Url::to(['content/create-epg']);
$epgNotValid = \Yii::t('app', 'Lịch phát sóng sai định dạng');
$checkEpg = \Yii::t('app', 'Kiểm tra lịch phát sóng');
$epgCantBlank = \Yii::t('app', 'Ngày phát sóng không được để trống');
$epgDateCantBlank = \Yii::t('app', 'Ngày phát sóng không được để trống');
$js = <<<JS
var url = '{$url}';
var checked = [], checkResult = false, epgTime = [], epgTitle = [];

$('#live_content').focusout(function(){
    var obj = $(this),
        stack = obj.val().split("\\n"),
        checkRs = [];
    epgTime = [];
    epgTitle = [];

    checked = [];

    stack.forEach(function(item, index){
        var time = item.split(" ")[0],
            start = time.split('-')[0],
            end = time.split('-')[1],
            title = item.substring(12);

        var regex = /([01]\d|2[0-3]):([0-5]\d)/;

        if(!start || !end || end <= start || !regex.test(start) || !regex.test(end)){
            checked.push('<p style="color:red">' + item + '</p>');
            checkRs.push(index);
        }else{
            checked.push('<p style="color:green">' + item + '</p>');
            epgTime.push(time);
            epgTitle.push(title);
        }
    });
    console.log(checkRs);
    checkResult = checkRs.length === 0?true:false;
});

var checking = false;

$('#checkBtn').click(function(e){
    e.preventDefault();

    if(!checking){
        $('#live_content').parents('.form-group').hide();
        $('#checker').show();

        if(checkResult){
            $('#checker .help-block').html("");
        }else{
            $('#checker .help-block').html("$epgNotValid");
        }

        $('#checkError').html(checked);
        checking = true;
        $('#checkBtn').text('Thử lại');
    }else{
        $('#live_content').parents('.form-group').show();
        $('#checker').hide();
        checking = false;
        $('#checkBtn').text('$checkEpg');
    }
    
});

$('#submit').click(function(e){
    console.log(checkResult);
    e.preventDefault();

    if($('#liveprogram-date').val() === ""){
        $('#liveprogram-date').parents('.form-group').addClass('has-error');
        $('#liveprogram-date').next().remove();
        $( "<p>$epgDateCantBlank</p>" ).css('color', 'red').insertAfter( '#liveprogram-date' )
        checkResult = false;
    }else{
        $('#liveprogram-date').parents('.form-group').removeClass('has-error');
        $('#liveprogram-date').next().remove();
    }

    if($('#live_content').val() === ""){
        $('#live_content').parents('.form-group').addClass('has-error');
        $('#live_content').next().remove();
        $( "<p>$epgCantBlank</p>" ).css('color', 'red').insertAfter( '#live_content' )
    }else{
        $('#live_content').parents('.form-group').removeClass('has-error');
        $('#live_content').next().remove();
    }

    if(checkResult){
        var postData = {
            "date": $('#liveprogram-date').val(),
            "time": epgTime,
            "title":epgTitle,
            "channel": $('#liveChannel').val(),
        }

        $.post(url, postData).success(function(data){
            toastr.success(data.message);
            jQuery.pjax.reload({container:'#list-live'});
        });
    }
});

JS;

$this->registerJs($js, View::POS_END);

?>
<div class="modal fade" id="modal_create_epg" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><?= \Yii::t('app', 'Tạo lịch phát sóng'); ?></h4>
            </div>
            <div class="modal-body">
                <?php $form = ActiveForm::begin([
                    // 'id' => $formID,
                    'type' => ActiveForm::TYPE_HORIZONTAL,
                    'options' => ['enctype' => 'multipart/form-data'],
                    'action' => \yii\helpers\Url::to(['content/create-epg']),
                    'fullSpan' => 8,
                    'formConfig' => [
                        'type' => ActiveForm::TYPE_HORIZONTAL,
                        'labelSpan' => 3,
                        'deviceSize' => ActiveForm::SIZE_SMALL,
                    ],
                ]); ?>
                <?php
                    echo $form->field($liveModel, 'date')->widget(DatePicker::classname(), [
                        'options' => ['placeholder' => ''],
                        'type' => DatePicker::TYPE_INPUT,
                        'pluginOptions' => [
                            'autoclose'=>true,
                            'format' => 'dd-mm-yyyy'
                        ]
                    ])->label(\Yii::t('app', 'Ngày phát sóng'));
                ?>
                <?= $form->field($liveModel, 'liveContent')->textArea(['id' => 'live_content', 'rows' => 10, 'style' => 'width:300px'])->label('Lịch phát sóng')
                ->hint(Yii::t('app','Lịch phát sóng có dạng "20:00-21:00 Tên chương trình"')); ?>
                <?= $form->field($liveModel, 'liveContent')->textArea(['id' => 'live_content', 'rows' => 10, 'style' => 'width:300px'])->label(\Yii::t('app', 'Lịch phát sóng')); ?>
                <?= $form->field($liveModel, 'channel_id')->hiddenInput(['id' => 'liveChannel'])->label(false) ?>

                <div class="form-group field-live_content" id="checker" style="display: none;">
                    <label class="control-label col-sm-3" for="live_content"><?= \Yii::t('app', 'Lịch phát sóng'); ?></label>
                    <div class="col-sm-5" id="checkError">

                    </div>
                    <br>
                    <div class="col-sm-8 col-sm-offset-3"><div class="help-block" style="color:red;"><?=Yii::t('app','Lịch phát sóng sai định dạng')?></div></div>
                </div>
                                
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">
                        <?= Html::submitButton(\Yii::t('app', 'Thêm lịch phát sóng'), ['class' => 'btn btn-success', 'id' => 'submit']) ?>
                        <?= Html::a(\Yii::t('app', 'Kiểm tra lịch phát sóng'), '', ['class' => 'btn btn-warning', 'id' => 'checkBtn']) ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<?php
echo Html::button(\Yii::t('app', 'Tạo lịch phát sóng'),
    [
        'data-toggle' => 'modal',
        'data-target' => '#modal_create_epg',
        'class' => 'btn btn-primary',
    ]);
?>

<?=Html::a(\Yii::t('app', 'Tạo EPG'),
    Yii::$app->urlManager->createUrl(['content/create', 'type' => Category::TYPE_LIVE_CONTENT, 'parent' => $id]),
    ['class' => 'btn btn-success'])?>
<br>
<br>
<?php

echo GridView::widget([
    'dataProvider' => $liveProvider,
    'responsive'   => true,
    'id'           => 'list-live',
    'pjax'         => true,
    'hover'        => true,
    'columns'      => [
        [
            'class'  => '\kartik\grid\DataColumn',
            'format' => 'raw',
            'label'  => \Yii::t('app', 'Ảnh'),
            'value'  => function ($model, $key, $index, $widget) {
                /** @var $model \common\models\Content */
//                $image = Json::decode($model->images)[0];
                if( isset(Json::decode($model->images)[0])){
                    $image = Json::decode(Json::decode($model->images)[0]);
                    if(isset($image['name'])){
                        return  Html::img(Yii::getAlias('@web').'/static/content_images/' . $image['name'], ['height' => '50']) ;
                    }
                }
                return '';

            },
        ],
        [
            'class'  => '\kartik\grid\DataColumn',
            'format' => 'raw',
            'label'  => \Yii::t('app', 'Tên chương trình'),
            'value'  => function ($model, $key, $index, $widget) {
                /** @var $model \common\models\Content */
                return Html::a($model->name, ['view', 'id' => $model->content_id], ['class' => 'label label-primary']);

            },
        ],
        [
            'format'    => 'html',
            'class'     => '\kartik\grid\DataColumn',
            'attribute' => \Yii::t('app', 'Mô tả'),
            'value'     => function ($model, $key, $index) {
                return $model->description;
            },
        ],
        [
            'attribute' => 'started_at',
            'label'     => \Yii::t('app', 'Thời gian bắt đầu'),
            'format'    => ['date', 'php:d-m-Y H:i:s'],
        ],
        [
            'attribute' => 'ended_at',
            'label'     => \Yii::t('app', 'Thời gian kết thúc'),
            'format'    => ['date', 'php:d-m-Y H:i:s'],
        ],
        // [
        //     'format'    => 'html',
        //     'class'     => '\kartik\grid\DataColumn',
        //     'attribute' => 'status',
        //     'label'     => 'Trạng thái',
        //     'value'     => function ($model, $key, $index) {
        //         return $model->getStatus($model->status);
        //     },
        // ],
        [
            'class' => 'kartik\grid\EditableColumn',
            'attribute' => 'status',
            'label' => \Yii::t('app', 'Trạng thái'),
            'refreshGrid' => true,
            'editableOptions' => function ($model, $key, $index) {
                return [
                    'header' => 'Status',
                    'size' => 'md',
                    'displayValueConfig' => \common\models\LiveProgram::getListStatus(),
                    'inputType' => \kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                    'placement' => \kartik\popover\PopoverX::ALIGN_LEFT,
                    'data' => \common\models\LiveProgram::getListStatus(),
                    'formOptions' => [
                        'action' => \yii\helpers\Url::to([
                            'content/change-liveprogram-status',
                            'lp_id' => $model->id
                        ]),
                        'enableClientValidation' => false,
                        'enableAjaxValidation' => true,
                    ],
                ];
            },
        ],
    ],
]);
?>
