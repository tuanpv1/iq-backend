<?php

use common\models\Category;
use common\models\Content;
use common\models\ContentProvider;
use common\models\Site;
use kartik\export\ExportMenu;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use kartik\helpers\Html;
use scotthuangzl\googlechart\GoogleChart;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use kartik\widgets\DepDrop;

/* @var $report \backend\models\ReportContentForm */
/* @var $this yii\web\View */

$this->title = ''.\Yii::t('app', 'Thống kê nội dung');
$this->params['breadcrumbs'][] = $this->title;


?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-body">

                <div class="report-user-daily-index">

                    <div class="row form-group">
                        <div class="col-md-12 col-md-offset-0">
                            <?php $form = ActiveForm::begin(
                                    ['method' => 'get',
                                    'id' => 'report-content-id',
                                    'action' => Url::to(['report/content']),]
                                );
                                $formId = $form->id;
                            ?>

                            <div class="row">

                                <div class="col-md-12">

                                    <div class="col-md-3">
                                        <?= $form->field($report, 'site_id')->dropDownList( ArrayHelper::merge([],Site::listSite()), ['id'=>'site-id']); ?>
                                    </div>

                                    <div class="col-md-3">
                                        <?= $form->field($report, 'content_type')->dropDownList( ArrayHelper::merge([],Content::listTypeBC()), ['id'=>'content-type']); ?>
                                    </div>

                                    <div class="col-md-6">
                                        <?php
                                        /**
                                         * @var $category \common\models\Category
                                         */
                                        $dataList = [];
                                        $categorys= Category::find()
                                            ->innerJoin('category_site_asm','category_site_asm.category_id = category.id')
                                            ->where(['category_site_asm.site_id'=>$site_id])
                                            ->andWhere(['category.type'=>$content_type])
                                            ->andWhere(['category.status' => Category::STATUS_ACTIVE])
                                            ->orderBy('category.path')
                                            ->all();
                                        foreach ($categorys as $category) {
                                            $patents =explode("/", $category->path);
                                            $name="";
                                            $i=1;
                                            if(count($patents)<=1){
                                                $dataList[$category->id]=$category->display_name;
                                            }
                                            else{
                                                foreach($patents as $item)
                                                {
                                                    if($i == count($patents)){
                                                        $name= $name.$category->display_name;
                                                    }else{
                                                        $name=$name."|--";
                                                    }
                                                    $i++;
                                                }
                                                $dataList[$category->id]=$name;
                                            }
                                        }
//                                        echo"<pre>";print_r($dataList);die();
                                        echo $form->field($report, 'category_id')->widget(DepDrop::classname(),
                                            [
                                                'data' => $dataList,
                                                'type' => DepDrop::TYPE_SELECT2 ,
                                                'options' => ['id'=>'category-id','placeholder' => ''.\Yii::t('app', '-Chọn danh mục-')],
                                                'select2Options' => ['pluginOptions' => ['allowClear' => true]],
                                                'pluginOptions' => [
                                                    'depends' => ['content-type','site-id'],
                                                    'placeholder'=>'-Chọn danh mục-',
                                                    'url' => Url::to(['/report/find-category-by-type-and-site']),
                                                ],
                                            ]);
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-3">
                                        <?= $form->field($report, 'cp_id')->dropDownList( ArrayHelper::merge([''=>''.Yii::t('app','Tất cả')],ContentProvider::listContentProvider()), ['id'=>'cp-id']); ?>
                                    </div>

                                    <div id="date">
                                        <div class="col-md-3">
                                            <?= $form->field($report, 'from_date')->widget(\kartik\widgets\DatePicker::classname(), [
                                                'options' => ['placeholder' => ''.\Yii::t('app', 'Ngày bắt đầu')],
                                                'type' => \kartik\widgets\DatePicker::TYPE_INPUT,
                                                'pluginOptions' => [
                                                    'autoclose' => true,
                                                    'todayHighlight' => true,
                                                    'format' => 'dd/mm/yyyy'
                                                ]
                                            ]); ?>

                                        </div>
                                        <div class="col-md-3">
                                            <?= $form->field($report, 'to_date')->widget(\kartik\widgets\DatePicker::classname(), [
                                                'options' => ['placeholder' => ''.\Yii::t('app', 'Ngày kết thúc')],
                                                'type' => \kartik\widgets\DatePicker::TYPE_INPUT,
                                                'pluginOptions' => [
                                                    'autoclose' => true,
                                                    'todayHighlight' => true,
                                                    'format' => 'dd/mm/yyyy'
                                                ]
                                            ]); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div style="margin-top: 25px"></div>
                                        <?= Html::submitButton(''.\Yii::t('app', 'Xem báo cáo'), ['class' => 'btn btn-primary']) ?>
                                    </div>
                                </div>
                            </div>

                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>

                    <?php if ($dataProvider) { ?>
                        <?= \dosamigos\highcharts\HighCharts::widget([
                            'clientOptions' => [
                                'height'=>500,
                                'title' => [
                                    'text' => ''
                                ],
                                'xAxis' => [
                                    'categories' => $dataCharts[0]
                                ],
                                'yAxis' => [
                                    'min' => 0,
                                    'allowDecimals' => false,
                                    'title' => [
                                        'text' => Yii::t('app', '')
                                    ]
                                ],
                                'series' => [
                                    ['name' => Yii::t('app', 'Tổng số nội dung'), 'data' => $dataCharts[1]],
                                    ['name' => Yii::t('app', 'Tổng nội dung upload trong ngày'), 'data' => $dataCharts[2]],
                                    ['name' => Yii::t('app', 'Tổng lượt xem'), 'data' => $dataCharts[3]],
                                    ['name' => Yii::t('app', 'Tổng lượt mua'), 'data' => $dataCharts[4]],
                                ]
                            ]
                        ]);
                        ?>
                        <?php
                            $gridColumns = [
                                                [
                                                    'class' => '\kartik\grid\DataColumn',
                                                    'attribute' => 'report_date',
                                                    'width' => '150px',
                                                    'value' => function ($model) {
                                                        /**  @var $model \common\models\ReportContent */
                                                        return !empty($model->report_date) ? date('d-m-Y', $model->report_date) : '';
                                                    },
                                                    'pageSummary' => "".\Yii::t('app', 'Tổng số')
                                                ],
                                                [
                                                    'class' => '\kartik\grid\DataColumn',
                                                    'attribute' => 'total_content',
                                                    'value' => function ($model) {
                                                        /**  @var $model \common\models\ReportContent */
                                                        return $model->total_content;
                                                    },
                                                ],
                                                [
                                                    'class' => '\kartik\grid\DataColumn',
                                                    'attribute' => 'count_content_upload_daily',
                                                    'value' => function ($model) {
                                                        /**  @var $model \common\models\ReportContent */
                                                        return $model->count_content_upload_daily;
                                                    },
                                                ],
                                                [
                                                    'class' => '\kartik\grid\DataColumn',
                                                    'attribute' => 'total_content_view',
                                                    'value' => function ($model) {
                                                        /**  @var $model \common\models\ReportContent */
                                                        return $model->total_content_view;
                                                    },
                                                ],
                                                [
                                                    'class' => '\kartik\grid\DataColumn',
                                                    'attribute' => 'total_content_buy',
                                                    'value' => function ($model) {
                                                        /**  @var $model \common\models\ReportContent */
                                                        return $model->total_content_buy;
                                                    },
                                                ],
                                            ]
                        ?>


                        <?php
                            $expMenu = ExportMenu::widget([
                                'dataProvider' => $dataProvider,
                                'columns' => $gridColumns,
                                'showConfirmAlert' => false,
                                'fontAwesome' => true,
                                'showColumnSelector' => true,
                                'dropdownOptions' => [
                                    'label' => ''.\Yii::t('app', 'All'),
                                    'class' => 'btn btn-default'
                                ],
                                'exportConfig' => [
                                    ExportMenu::FORMAT_CSV => false,
                                    ExportMenu::FORMAT_EXCEL_X => [
                                        'label' => ''.\Yii::t('app', 'Excel'),
                                    ],
                                    ExportMenu::FORMAT_HTML => false,
                                    ExportMenu::FORMAT_PDF => false,
                                    ExportMenu::FORMAT_TEXT => false,
                                    ExportMenu::FORMAT_EXCEL => false,
                                ],
                                'target' => ExportMenu::TARGET_SELF,
                                'filename' => "Report"
                            ])
                        ?>

                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'responsive' => true,
                            'pjax' => true,
                            'hover' => true,
                            'showPageSummary' => false,
                            'columns' => $gridColumns,
                            'panel' => [
                                'type' => GridView::TYPE_DEFAULT,
                            ],
                            'toolbar' => [
                                '{export}',
                                $expMenu,
                                ['content'=>
                                    Html::a('<i class="glyphicon glyphicon-repeat"></i>', ['content'], [
                                        'data-pjax'=>0,
                                        'class' => 'btn btn-default',
                                        'title'=>Yii::t('kvgrid', 'Reset Grid')
                                    ])
                                ],
                            ],
                            'export' => [
                                'label' => "Page",
                                'fontAwesome' => true,
                                'showConfirmAlert' => false,
                                'target' => GridView::TARGET_BLANK,

                            ],

                            'exportConfig' => [
                                GridView::EXCEL => ['label' => 'Excel','filename' => "Report_content"],
                            ],
                        ]); ?>
                    <?php }else{ ?>
                        <div class="portlet-body">
                            <div class="well well-sm">
                                <p><?= \Yii::t('app', 'Không có dữ liệu') ?></p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
//$js = <<<JS
//    $("#site-id").change(function () {
//        $("#report-content-id").submit();
//    });
//
//    $("#content-type").change(function () {
//        jQuery("#categoryIds").val(null);
//        $("#report-content-id").submit();
//    });
//JS;
//$this->registerJs($js, \yii\web\View::POS_END);
?>