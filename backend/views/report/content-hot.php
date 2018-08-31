<?php
/**
 * Created by PhpStorm.
 * User: mycon
 * Date: 12/24/2016
 * Time: 2:57 PM
 */
use common\models\Content;
use common\models\Site;
use common\models\ContentProvider;
use kartik\export\ExportMenu;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use kartik\helpers\Html;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $report \backend\models\ReportContentHotForm */
/* @var $this yii\web\View */

$this->title = ''.\Yii::t('app', 'Nội dung Hot');
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
                                        'action' => Url::to(['report/content-hot']),]
                                );
                                $formId = $form->id;
                                ?>

                                <div class="row">

                                    <div class="col-md-12">

                                        <div class="col-md-2">
                                            <?= $form->field($report, 'site_id')->dropDownList( ArrayHelper::map(Site::find()->andWhere(['status'=>Site::STATUS_ACTIVE])->all(),'id','name'), ['id'=>'site-id']); ?>
                                        </div>

                                        <div class="col-md-2">
                                            <?= $form->field($report, 'cp_id')->dropDownList( ArrayHelper::merge([''=>''.Yii::t('app','Tất cả')],ContentProvider::listContentProvider()), ['id'=>'cp-id']); ?>
                                        </div>

                                        <div class="col-md-2">
                                            <?= $form->field($report, 'content_type')->dropDownList( ArrayHelper::merge(['' => ''.\Yii::t('app', 'Tất cả')],Content::listTypeBC()), ['id'=>'content-type']); ?>
                                        </div>

                                        <div id="date">
                                            <div class="col-md-2">
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
                                            <div class="col-md-2">
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

                                        <div class="col-md-2">
                                            <?=$form->field($report,'top')->dropDownList(['10'=>'10','20'=>'20','50'=>'50']);?>
                                        </div

                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <!--                                    <div class="col-md-12">-->
                                    <?php echo $form->field($report, 'categoryIds')->hiddenInput(['id' => 'categoryIds'])->label(false);?>
                                    <!--                                    </div>-->
                                    <div class="col-md-12">
                                        <div class="portlet light">
                                            <div class="portlet-title">
                                                <div class="caption">
                                                    <i class="fa fa-cogs font-green-sharp"></i>
                                                    <span class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Danh mục') ?></span>
                                                </div>
                                                <div class="tools">
                                                    <a href="javascript:;" class="collapse">
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="portlet-body">
<!--                                                --><?php // echo "<pre>"; print_r($site_id);die();?>
                                                <?= \common\widgets\Jstree::widget([
                                                    'clientOptions' => [
                                                        "checkbox" => ["keep_selected_style" => false],
                                                        "plugins" => ["checkbox"]
                                                    ],

                                                    'type' =>$content_type,
                                                    'sp_id' => $site_id,
                                                    'data' => $selectedCats,
                                                    'eventHandles' => [
                                                        'changed.jstree' => "function(e,data) {
                                                            var i, j, r = [];
                                                            var catIds='';
                                                            for(i = 0, j = data.selected.length; i < j; i++) {
                                                                var item = $(\"#\" + data.selected[i]);
                                                                var value = item.attr(\"id\");
                                                                if(i==j-1){
                                                                    catIds += value;
                                                                } else{
                                                                    catIds += value +',';

                                                                }
                                                            }
                                                            jQuery(\"#categoryIds\").val(catIds);
                                                         }"
                                                    ]
                                                ]) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="col-md-3">
                                        <div style="margin-top: 25px"></div>
                                        <?= Html::submitButton(''.\Yii::t('app', 'Xem báo cáo'), ['class' => 'btn btn-primary']) ?>
                                    </div>
                                </div>

                                <div class="col-md-12" style="height: 30px;float: left">
                                </div>
                                <div class="col-md-12">
                                    <?php if ($dataProvider->getTotalCount() != 0) { ?>
                                        <?php if(intval(Yii::$app->request->get('page'))==1 || !intval(Yii::$app->request->get('page'))){?>
                                            <?= Yii::t('app','Trình bày 1 - ').$dataProvider->getTotalCount().Yii::t('app',' trong số ').$dataProviderEx->getTotalCount().Yii::t('app',' mục') ;?>
                                        <?php }?>
                                        <?php if(intval(Yii::$app->request->get('page'))==2){?>
                                            <?= Yii::t('app','Trình bày 21 - ').($dataProvider->getTotalCount() +20).Yii::t('app',' trong số ').$dataProviderEx->getTotalCount().Yii::t('app',' mục') ;?>
                                        <?php }?>
                                        <?php if(intval(Yii::$app->request->get('page'))==3){?>
                                            <?= Yii::t('app','Trình bày 41 - ').($dataProvider->getTotalCount()+40).Yii::t('app',' trong số ').$dataProviderEx->getTotalCount().Yii::t('app',' mục') ;?>
                                        <?php }?>
                                    <?php }?>
                                </div>
                                <?php ActiveForm::end(); ?>
                            </div>
                        </div>

                        <?php if (isset($dataProvider)) { ?>
                            <?php
                            $gridColumns = [
                                [
                                    'class' => '\kartik\grid\SerialColumn',
                                    'header'=>Yii::t('app','STT')
                                ],
                                [
                                    'class' => '\kartik\grid\DataColumn',
                                    'attribute' => 'content_type',
                                    'value' => function ($model) {
                                        /**  @var $model \common\models\ReportContentHot */
                                        return Content::getTypeNameById($model->content_type);
                                    },
                                ],
                                [
                                    'class' => '\kartik\grid\DataColumn',
                                    'attribute' => 'content_id',
                                    'value' => function ($model) {
                                        /**  @var $model \common\models\ReportContentHot */
                                        return Content::findOne($model->content_id)->display_name;
                                    },
                                ],
                                [
                                    'class' => '\kartik\grid\DataColumn',
                                    'attribute' => 'total_content_view',
                                    'value' => function ($model) {
                                        /**  @var $model \common\models\ReportContentHot */
                                        return $model->total_content_view;
                                    },
                                ],

                            ];
                            $gridColumn = [
                                [
                                    'class' => '\kartik\grid\DataColumn',
                                    'attribute' => 'content_type',
                                    'value' => function ($model) {
                                        /**  @var $model \common\models\ReportContentHot */
                                        return Content::getTypeNameById($model->content_type);
                                    },
                                ],
                                [
                                    'class' => '\kartik\grid\DataColumn',
                                    'attribute' => 'content_id',
                                    'value' => function ($model) {
                                        /**  @var $model \common\models\ReportContentHot */
                                        return Content::findOne($model->content_id)->display_name;
                                    },
                                ],
                                [
                                    'class' => '\kartik\grid\DataColumn',
                                    'attribute' => 'total_content_view',
                                    'value' => function ($model) {
                                        /**  @var $model \common\models\ReportContentHot */
                                        return $model->total_content_view;
                                    },
                                ],

                            ]
                            ?>


                            <?php
                            $expMenu = ExportMenu::widget([
                                'dataProvider' => $dataProviderEx,
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
                                'summary'=>false,// bỏ 'Trình bày 1-6 trong số 6 mục.'
//                                'summary'=>'Tổng số '.$dataProviderEx->getTotalCount().' mục',
//                                'summary'=> 'Tình bày '.'-'.' trong số '.$dataProviderEx->getTotalCount().' mục',
                                'columns' => $gridColumn,
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
                                    'label' => Yii::t('app',"Page"),
                                    'fontAwesome' => true,
                                    'showConfirmAlert' => false,
                                    'target' => GridView::TARGET_BLANK,

                                ],

                                'exportConfig' => [
                                    GridView::EXCEL => ['label' => Yii::t('app','Excel'),'filename' => "Report_content_hot"],
                                ],
                                'layout' => "{summary}\n{items}"
                            ]); ?>

                            <?php
                                echo \yii\widgets\LinkPager::widget([
                                    'pagination' => $pagination,
                                ]);;
                            ?>

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
$js = <<<JS
    $("#site-id").change(function () {
        $("#report-content-id").submit();
      });

      $("#content-type").change(function () {
      jQuery("#categoryIds").val(null);
        $("#report-content-id").submit();
      });
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>