<?php

use yii\bootstrap\Modal;
use yii\helpers\Html;
use kartik\detail\DetailView;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = "".\Yii::t('app', 'Thông tin tài khoản');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption"><i class="fa fa-gift"></i><?= $this->title ?></div>
            </div>
            <div class="portlet-body form">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        ['attribute'=>'username', 'format'=>'raw', 'value'=>'<kbd>'.$model->username.'</kbd>', 'displayOnly'=>true],
                        [
                            'attribute' => 'type',
//                            'label' => 'Loại người dùng',
                            'format' => 'html',
                            'value' =>  $model->getTypeName(),
                        ],
                        'fullname',
                        'email:email',

//                        'role',
                        [
                            'attribute'=>'status',
                            'label'=>''.\Yii::t('app', 'Trạng thái'),
                            'format'=>'raw',
                            'value'=>($model->status ==User::STATUS_ACTIVE)  ?
                                '<span class="label label-success">'.$model->getStatusName().'</span>' :
                                '<span class="label label-danger">'.$model->getStatusName().'</span>',
                            'type'=>DetailView::INPUT_SWITCH,
                            'widgetOptions' => [
                                'pluginOptions' => [
                                    'onText' => 'Active',
                                    'offText' => 'Delete',
                                ]
                            ]
                        ],
                        [
                            'label' => ''.\Yii::t('app', 'Nhóm quyền'),
                            'format' => 'html',
                            'value' =>  $model->getRolesName(),
                        ],
//                        [                      // the owner name of the model
//                            'attribute'=>'created_at',
//                            'label' => 'Ngày tham gia',
//                            'value' => date('d/m/Y H:i:s',$model->created_at),
//                        ],
//                        [                      // the owner name of the model
//                            'attribute'=>'updated_at',
//                            'label' => 'Ngày thay đổi thông tin',
//                            'value' => date('d/m/Y H:i:s',$model->updated_at),
//                        ],

                    ],
                ]) ?>

                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <?php
                            Modal::begin([
                                'header' => '<h4>'.\Yii::t('app', 'Cập nhật').'</h4>',
                                'toggleButton' => ['label' => ''.\Yii::t('app', 'Cập nhật'), 'class' => 'btn btn-success'],
                                'closeButton' => ['label' => ''.\Yii::t('app', 'Hủy thao tác')]
                            ]);
                            echo $this->render('_form_owner', ['model' => $model]);
                            Modal::end();
                            ?>
                            <?php
                            Modal::begin([
                                'header' => '<h4>Đổi mật khẩu</h4>',
                                'toggleButton' => ['label' => ''.\Yii::t('app', 'Đổi mật khẩu'), 'class' => 'btn btn-success'],
                                'id' => "cuongvm",
                                'closeButton' => ['label' => ''.\Yii::t('app', 'Hủy thao tác')],
                            ]);
                            echo $this->render('_form_owner_change_password', ['model' => $model]);
                            Modal::end();
                            ?>
                            <?= Html::a(''.\Yii::t('app', 'Hủy thao tác'), ['index'], ['class' => 'btn btn-default']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?php

$js = <<<JS
    //$('#cuongvm').on('hidden.bs.modal', function () {
    //    //location.reload();
    //    //$(this).removeData('bs.modal');
    //    // $(this).find(".modal-content").empty();
    //  //$(this).removeData('modal');
    //    //$('#cuongvm').removeData();
    //
    ////    $('#cuongvm').on('hidden.bs.modal', function () {
    ////    $(this).find("input,textarea,select").val('').end();
    //});

    $(document).ready(function(){
        // codes works on all bootstrap modal windows in application
        $('#cuongvm').on('hidden.bs.modal', function(e)
        {
            $(this).removeData();
        }) ;
    });
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>


