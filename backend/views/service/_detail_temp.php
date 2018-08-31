<?php

use common\models\Service;
use common\widgets\BEServiceCycle;
use common\widgets\SPServiceCycle;
use kartik\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Service */
/* @var $form yii\widgets\ActiveForm */
$modal_suspend = 'modal_suspend_'.$model->id;
$modal_note = 'modal_note_'.$model->id;
$model_note = new \backend\models\AdminNoteForm();
?>

<?php

Modal::begin(['id' => $modal_note,
    'header' => '<h3>'.\Yii::t('app', 'Cập nhật ghi chú').'</h3>']);

$form = ActiveForm::begin([
    'fullSpan' => 12,
    'action' => ['service/update-note', 'id' => $model->id],
    'formConfig' => [
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'deviceSize' => ActiveForm::SIZE_SMALL,
    ],
    //'enableAjaxValidation' => true,
    'enableClientValidation' => true,
]); ?>
<div class="form-body">
    <?= $form->field($model_note,'admin_note')->textarea(['rows' => 4,'placeholder' => ''.\Yii::t('app', 'Ghi chú')])->label(false) ?>
</div>
<div class="form-actions">
    <div class="row">
        <div class="col-md-offset-3 col-md-9">
            <?= Html::submitButton(''.\Yii::t('app', 'Cập nhật'),
                ['class' => 'btn btn-primary']) ?>
            <?php
                echo Html::a(''.\Yii::t('app', 'Cancel'), null, ['class' => 'btn btn-default', 'data-dismiss' => 'modal']);
            ?>
        </div>
    </div>
</div>
<?php
ActiveForm::end();
Modal::end();
?>

<?php

Modal::begin(['id' => $modal_suspend,
    'header' => '<h3>'.\Yii::t('app', 'Loại bỏ gói cước').'</h3>']);

$form = ActiveForm::begin([
    'fullSpan' => 12,
    'action' => ['service/suspend', 'id' => $model->id],
    'formConfig' => [
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'deviceSize' => ActiveForm::SIZE_SMALL,
    ],
    //'enableAjaxValidation' => true,
    'enableClientValidation' => true,
]); ?>

<div class="form-body">
    <?= $form->field($model_note,'admin_note')->textarea(['rows' => 4,'placeholder' => ''.\Yii::t('app', 'Ghi chú')])->label(false) ?>
</div>
<div class="form-actions">
    <div class="row">
        <div class="col-md-offset-3 col-md-9">
            <?= Html::submitButton(''.\Yii::t('app', 'Loại bỏ'),
                ['class' => 'btn btn-primary']) ?>
            <?php
            echo Html::a(''.\Yii::t('app', 'Cancel'), null, ['class' => 'btn btn-default', 'data-dismiss' => 'modal']);
            ?>
        </div>
    </div>
</div>
<?php
ActiveForm::end();
Modal::end();
?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Thông tin gói cước') ?> </span>(<?= '<span class="label label-'.$model->getStatusClassCss().'">'.Service::getListStatusServiceNameByStatus($model->status).'</span>' ?>)
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <p>
                    <?= BEServiceCycle::widget([
                        'model' => $model,
                        'mode' => BEServiceCycle::MODE_TEMP,
                        'modal_note' => $modal_note,
                        'modal_suspend' => $modal_suspend
                    ]) ?>
                </p>
                <div class="caption">
                    <i class="fa fa-comment-o font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Ghi chú gói cước') ?></span>
                </div>
                <div class="well">
                    <?= $model->admin_note ?>
                </div>
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'name',
                        'display_name',
                        'description:ntext',
                        [
                            'attribute' => 'pricing_id',
                            'value' => $model->pricing->getPriceInfo()
                        ],
//                        [
//                            'attribute' => 'period',
//                            'value' => $model->period . ' '.\Yii::t('app', 'ngày')
//                        ],
                        [
                            'attribute' => 'auto_renew',
                            'value' => \common\models\Service::serviceAutorenew()[$model->auto_renew]
                        ],
//                        [
//                            'attribute' => 'free_days',
//                            'value' => $model->free_days . ' '.\Yii::t('app', 'ngày')
//                        ],
//                        [
//                            'attribute' => 'max_daily_retry',
//                            'value' => $model->max_daily_retry . ' '.\Yii::t('app', 'lượt/ngày')
//                        ],
//                        [
//                            'attribute' => 'max_day_failure_before_cancel',
//                            'value' => $model->max_day_failure_before_cancel . ' '.\Yii::t('app', 'ngày')
//                        ],
                        [
                            'label' => 'Full type services',
                            'value' => $model->getFullTypeServices()
                        ],
                        [
                            'format' => 'raw',
                            'attribute' => 'status',
                            'value' => '<span class="label label-'.$model->getStatusClassCss().'">'.Service::getListStatusServiceNameByStatus($model->status).'</span>'
                        ],
                        [
                            'attribute' => 'created_at',
                            'value' => date('d/m/Y',$model->created_at)
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => date('d/m/Y',$model->updated_at)
                        ],
                    ],
                ]) ?>
            </div>

        </div>
    </div>
</div>
