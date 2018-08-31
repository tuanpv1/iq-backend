<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\LogSyncContentSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="log-sync-content-search">

    <?php $form = ActiveForm::begin(
        ['method' => 'get',
            'action' => Url::to(['log-sync-content/index']),]
    ); ?>

    <div class="form-body">
        <table style="margin-left: 25%;">

            <tr>
                <td>
                    <?= $form->field($model,'updated_at')->widget(\kartik\date\DatePicker::className(), [
                        'options' => ['placeholder' => 'Ngày'],
                        'type' => \kartik\widgets\DatePicker::TYPE_INPUT,
                        'convertFormat' => true,
                        'pluginOptions' => [
                            'format' => 'dd/M/yyyy',
                            'todayHighlight' => true,
                            'width' => '200px',
                            'autoclose' => true,
                        ]
                    ])->label('Ngày');
                    ?>
                </td>
                <td style="padding-left: 20px;">
                    <?= Html::submitButton('Tìm kiếm', ['class' => 'btn btn-primary']) ?>
                </td>
            </tr>
        </table>


    </div>

    <?php ActiveForm::end(); ?>

</div>
