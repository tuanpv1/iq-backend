<?php

use common\models\Service;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Service */
/* @var $active int */
/* @var $title String */
/* @var $sp \common\models\ServiceProvider */
/* @var $video_cat_selected Array */
/* @var $live_cat_selected Array */
/* @var $music_cat_selected Array */
/* @var $new_cat_selected Array */
/* @var $clip_cat_selected Array */

/* @var $temp_model common\models\Service */
/* @var $temp_video_cat_selected Array */
/* @var $temp_live_cat_selected Array */
/* @var $temp_music_cat_selected Array */
/* @var $temp_new_cat_selected Array */
/* @var $temp_clip_cat_selected Array */


$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => ''.\Yii::t('app', 'Danh sách gói cước'), 'url' => ['service/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Chi tiết gói cước :') ?> <?php echo $title;?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse" data-original-title="" title="">
                    </a>

                </div>
            </div>
            <div class="portlet-body">
                <div class="panel-group accordion" id="accordion1">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a class="accordion-toggle accordion-toggle-styled" data-toggle="collapse" data-parent="#accordion1" href="#collapse_1">
                                    <?= \Yii::t('app', 'Đang chạy production') ?> </a>
                            </h4>
                        </div>
                        <div id="collapse_1" class="panel-collapse in">
                            <div class="panel-body" style="height:auto; overflow-y:auto;">
                                <?php if($model){ ?>
                                    <div class="tabbable-custom ">
                                        <ul class="nav nav-tabs ">
                                            <li class="<?= ($active == 1) ? 'active' : '' ?>">
                                                <a href="#tab1" data-toggle="tab" >
                                                    <?= \Yii::t('app', 'Thông tin chung') ?></a>
                                            </li>
                                            <li class=" <?= ($active == 2) ? 'active' : '' ?>">
                                                <a href="#tab2" data-toggle="tab" >
                                                    <?= \Yii::t('app', 'Danh mục phim') ?> </a>
                                            </li>
                                            <li class=" <?= ($active == 6) ? 'active' : '' ?>">
                                                <a href="#tab6" data-toggle="tab">
                                                    <?= \Yii::t('app', 'Danh mục clip') ?> </a>
                                            </li>
                                            
                                            <li class=" <?= ($active == 4) ? 'active' : '' ?>">
                                                <a href="#tab4" data-toggle="tab">
                                                    <?= \Yii::t('app', 'Danh mục âm nhạc') ?> </a>
                                            </li>
                                            <li class=" <?= ($active == 5) ? 'active' : '' ?>">
                                                <a href="#tab5" data-toggle="tab">
                                                    <?= \Yii::t('app', 'Danh mục tin tức') ?> </a>
                                            </li>
                                            
                                            <li class=" <?= ($active == 7) ? 'active' : '' ?>">
                                                <a href="#tab7" data-toggle="tab">
                                                   <?= \Yii::t('app', ' Danh mục Karaoke') ?> </a>
                                            </li>
                                            <li class=" <?= ($active == 8) ? 'active' : '' ?>">
                                                <a href="#tab8" data-toggle="tab">
                                                    <?= \Yii::t('app', 'Danh mục radio') ?> </a>
                                            </li>
                                            <li class=" <?= ($active == 3) ? 'active' : '' ?>">
                                                <a href="#tab3" data-toggle="tab">
                                                    <?= \Yii::t('app', 'Danh mục kênh Live') ?></a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane <?= ($active == 1) ? 'active' : '' ?>" id="tab1">
                                                <?=$this->render('_detail',['model'=>$model])?>
                                            </div>
                                            <div class="tab-pane <?= ($active == 2) ? 'active' : '' ?>" id="tab2">
                                                <?=$this->render('_list_video_category',['model'=>$model,
                                                    'sp_id' => $sp->id,
                                                    'selectedCats' => $video_cat_selected,
                                                    'type' => \common\models\Category::TYPE_FILM,
                                                    'readOnly' => $model->isReadOnly()
                                                ])?>
                                            </div>

                                            <div class="tab-pane <?= ($active == 3) ? 'active' : '' ?>" id="tab3">
                                                <?=$this->render('_list_live_category',['model'=>$model,
                                                    'sp_id' => $sp->id,
                                                    'selectedCats' => $live_cat_selected,
                                                    'type' => \common\models\Category::TYPE_LIVE,
                                                    'readOnly' => $model->isReadOnly()
                                                ])?>
                                            </div>

                                            <div class="tab-pane <?= ($active == 4) ? 'active' : '' ?>" id="tab4">
                                                <?=$this->render('_list_music_category',['model'=>$model,
                                                    'sp_id' => $sp->id,
                                                    'selectedCats' => $music_cat_selected,
                                                    'type' => \common\models\Category::TYPE_MUSIC,
                                                    'readOnly' => $model->isReadOnly()
                                                ])?>
                                            </div>
                                            <div class="tab-pane <?= ($active == 5) ? 'active' : '' ?>" id="tab5">
                                                <?=$this->render('_list_new_category',['model'=>$model,
                                                    'sp_id' => $sp->id,
                                                    'selectedCats' => $new_cat_selected,
                                                    'type' => \common\models\Category::TYPE_NEWS,
                                                    'readOnly' => $model->isReadOnly()
                                                ])?>
                                            </div>
                                            <div class="tab-pane <?= ($active == 6) ? 'active' : '' ?>" id="tab6">
                                                <?=$this->render('_list_clip_category',['model'=>$model,
                                                    'sp_id' => $sp->id,
                                                    'selectedCats' => $clip_cat_selected,
                                                    'type' => \common\models\Category::TYPE_CLIP,
                                                    'readOnly' => $model->isReadOnly()
                                                ])?>
                                            </div>
                                            <div class="tab-pane <?= ($active == 7) ? 'active' : '' ?>" id="tab7">
                                                <?=$this->render('_list_karaoke_category',['model'=>$model,
                                                    'sp_id' => $sp->id,
                                                    'selectedCats' => $karaoke_cat_selected,
                                                    'type' => \common\models\Category::TYPE_KARAOKE,
                                                    'readOnly' => $model->isReadOnly()
                                                ])?>
                                            </div>
                                            <div class="tab-pane <?= ($active == 8) ? 'active' : '' ?>" id="tab8">
                                                <?=$this->render('_list_radio_category',['model'=>$model,
                                                    'sp_id' => $sp->id,
                                                    'selectedCats' => $radio_cat_selected,
                                                    'type' => \common\models\Category::TYPE_RADIO,
                                                    'readOnly' => $model->isReadOnly()
                                                ])?>
                                            </div>

                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
