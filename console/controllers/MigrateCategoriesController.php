<?php
/**
 * Created by PhpStorm.
 * User: bibon
 * Date: 3/21/2016
 * Time: 4:30 PM
 */

namespace console\controllers;


use common\models\ActorDirector;
use common\models\Category;
use common\models\CategorySiteAsm;
use common\models\Content;
use common\models\Site;
use console\models\FieldDataFieldCategoryImage;
use console\models\FileManaged;
use console\models\TaxonomyTermData;
use console\models\TaxonomyTermHierarchy;
use Yii;
use yii\console\Controller;
use yii\helpers\Json;
use yii\helpers\VarDumper;

class MigrateCategoriesController extends Controller
{

    public function actionRun()
    {
//        $this->clearAllData();
//        $this->migrateCategories(true);
//        $this->migrateCategories(false);
//        $this->updateType();
//        $this->updateChildCount();
        $this->migrateNotRootCategories();
        $this->migrateActors();
        $this->migrateDirectors();
//        $this->fixCategoriesImage();
    }

    private function fixCategoriesImage()
    {
        $channels = Category::find()
            ->where(['not', ['tvod1_id' => null]])
            ->all();
        foreach ($channels as $channel) {
            $channel->images = $this->getImage($channel->tvod1_id);
            $channel->save(false, ['images']);
        }

    }

    private function clearAllData()
    {
        Category::deleteAll('parent_id is not null');
        Category::deleteAll();
    }

    private function migrateCategories($isParent)
    {
        $siteId = $this->getSiteId();
        if (!$siteId) {
//            echo 'Error: NOT FOUND Site';
            MigrateTvod1Controller::errorLog("Error: NOT FOUND Site");
            return;
        }

        $condition = $isParent ? 'taxonomy_term_hierarchy.parent = :parent' : 'parent != :parent';
        $condition = $condition . ' AND taxonomy_vocabulary.machine_name = :machine_name';
        $listTaxonomyTermData = TaxonomyTermData::find()
            ->select("taxonomy_term_data.*")
            ->innerJoin('taxonomy_term_hierarchy', '`taxonomy_term_data`.`tid`=`taxonomy_term_hierarchy`.`tid`')
            ->innerJoin('taxonomy_vocabulary', 'taxonomy_term_data.vid=taxonomy_vocabulary.vid')
            ->where($condition, ['parent' => 0, 'machine_name' => 'taxonomy_category'])->all();
        if ($listTaxonomyTermData) {
            foreach ($listTaxonomyTermData as $taxonomyTerm) {
                if ($this->checkCategoryExisted($taxonomyTerm->tid)) {
                    continue;
                }
                $newCategory = new Category();
                $newCategory->type = $this->getCatType($taxonomyTerm->tid, $taxonomyTerm->name);
                $newCategory->display_name = $taxonomyTerm->name;
                $newCategory->is_series =  strtolower($taxonomyTerm->name) == 'phim bộ' ? 1 : 0;
                $newCategory->images = $this->getImage($taxonomyTerm->tid);
                $newCategory->status = Category::STATUS_ACTIVE;
                if ($isParent) {
                    $newCategory->level = 0;
                } else {
                    $newCategory->level = 1;
                }
                $newCategory->created_at = time();
                $newCategory->updated_at = time();
                $newCategory->tvod1_id = $taxonomyTerm->tid;
                if (!$isParent) {
                    $oldParentId = TaxonomyTermHierarchy::findOne($taxonomyTerm->tid)->parent;
                    $newCategory->parent_id = Category::findOne(['tvod1_id' => $oldParentId])->id;
                }
//                VarDumper::dump(Json::encode($newCategory->attributes));
                if ($newCategory->save()) {
                    $newCategory->path = $isParent ? $newCategory->id : ($newCategory->parent_id . '/' . $newCategory->id);
                    if ($newCategory->save()) {
                        $categorySiteAsm = new CategorySiteAsm();
                        $categorySiteAsm->category_id = $newCategory->id;
                        $categorySiteAsm->site_id = $siteId;
                        $categorySiteAsm->created_at = time();
                        $categorySiteAsm->updated_at = time();

                        $categorySiteAsmRows[] = $categorySiteAsm;

//                        echo "\nSaved successfully: $newCategory->display_name";
                        MigrateTvod1Controller::infoLog("Saved successfully: $newCategory->display_name");
                    }
                } else {
                    Yii::error($newCategory->errors);
                    VarDumper::dump(Json::encode($newCategory->errors));
                }
            }
            if (isset($categorySiteAsmRows)) {
                Yii::$app->db->createCommand()->batchInsert(CategorySiteAsm::tableName(), (new CategorySiteAsm())->attributes(), $categorySiteAsmRows)->execute();
            }
        }
    }

    private function migrateNotRootCategories()
    {
        MigrateTvod1Controller::errorLog("\n\n***** Bat dau dong bo danh muc *****");
        $siteId = $this->getSiteId();
        if (!$siteId) {
//            echo "\nError: NOT FOUND Site";
            MigrateTvod1Controller::errorLog("Error: Khong tim thay Nha cung cap dich vu nao");
            return;
        }

        $condition = 'taxonomy_term_hierarchy.parent != :parent';
        $condition = $condition . ' AND taxonomy_vocabulary.machine_name = :machine_name';
        $listTaxonomyTermData = TaxonomyTermData::find()
            ->select("taxonomy_term_data.*")
            ->innerJoin('taxonomy_term_hierarchy', '`taxonomy_term_data`.`tid`=`taxonomy_term_hierarchy`.`tid`')
            ->innerJoin('taxonomy_vocabulary', 'taxonomy_term_data.vid=taxonomy_vocabulary.vid')
            ->where($condition, ['parent' => 0, 'machine_name' => 'taxonomy_category'])->all();
        $cnt = 0;
        if ($listTaxonomyTermData) {
//            echo "\nqty of categories: " . count($listTaxonomyTermData);
            foreach ($listTaxonomyTermData as $taxonomyTerm) {
                if ($this->checkCategoryExisted($taxonomyTerm->tid)) {
//                    echo "\nCategory Existed: " . $taxonomyTerm->name;
                    MigrateTvod1Controller::infoLog("Danh muc '".$taxonomyTerm->name."' da ton tai tren TVOD2. Bo qua");
                    continue;
                }
                $newCategory = new Category();
                $newCategory->type = $this->getCatType($taxonomyTerm->tid, $taxonomyTerm->name);
                $newCategory->display_name = $taxonomyTerm->name;
                $newCategory->is_series =  strtolower($taxonomyTerm->name) == 'phim bộ' ? 1 : 0;
                $newCategory->description = $taxonomyTerm->description;
                $newCategory->images = $this->getImage($taxonomyTerm->tid);
                $newCategory->status = Category::STATUS_ACTIVE;
                $newCategory->order_number = $newCategory->id;
                $newCategory->level = 0;
                $newCategory->created_at = time();
                $newCategory->updated_at = time();
                $newCategory->tvod1_id = $taxonomyTerm->tid;
//                VarDumper::dump(Json::encode($newCategory->attributes));
                if ($newCategory->save()) {
                    $newCategory->path = $newCategory->id;
                    if ($newCategory->save()) {
                        $categorySiteAsm = new CategorySiteAsm();
                        $categorySiteAsm->category_id = $newCategory->id;
                        $categorySiteAsm->site_id = $siteId;
                        $categorySiteAsm->created_at = time();
                        $categorySiteAsm->updated_at = time();

                        $categorySiteAsmRows[] = $categorySiteAsm;

//                        echo "\nSaved successfully: $newCategory->display_name";
                        MigrateTvod1Controller::infoLog("Migrate danh muc '".$taxonomyTerm->name."' thanh cong");
                        $cnt++;
                    }
                } else {
//                    echo Json::encode($newCategory->errors);
                    MigrateTvod1Controller::errorLog("Luu danh muc $newCategory->display_name that bai: " . Json::encode($newCategory->errors));
                }
            }
            if (isset($categorySiteAsmRows)) {
                Yii::$app->db->createCommand()->batchInsert(CategorySiteAsm::tableName(), (new CategorySiteAsm())->attributes(), $categorySiteAsmRows)->execute();
            }
        }
        MigrateTvod1Controller::infoLog("**** Dong bo thanh cong $cnt danh muc moi. ****");
    }

    private function checkCategoryExisted($nodeId)
    {
        return Category::findOne(['tvod1_id' => $nodeId]) != null;
    }

    private function migrateActors()
    {
        MigrateTvod1Controller::errorLog("\n\n**** Bat dau dong bo dien vien ****");
        $siteId = $this->getSiteId();
        if (!$siteId) {
//            echo "\nError: NOT FOUND Site";
            MigrateTvod1Controller::errorLog("Error: Khong tim thay Nha cung cap dich vu nao");
            return;
        }

        $listTaxonomyTermData = TaxonomyTermData::find()
            ->select("taxonomy_term_data.*")
            ->innerJoin('taxonomy_vocabulary', 'taxonomy_term_data.vid=taxonomy_vocabulary.vid')
            ->where(['machine_name' => ['category_actor']])->all();
        if ($listTaxonomyTermData) {
//            echo "\nqty: " . count($listTaxonomyTermData);
            foreach ($listTaxonomyTermData as $taxonomyTerm) {
                $actor = ActorDirector::findOne(['name' => $taxonomyTerm->name, 'type' => ActorDirector::TYPE_ACTOR, 'content_type' => Content::TYPE_KARAOKE, 'status' => ActorDirector::STATUS_ACTIVE]);
                if (!$actor) {
                    $actor = new ActorDirector();
                    $actor->name = $taxonomyTerm->name;
                    $actor->type = ActorDirector::TYPE_ACTOR;
                    $actor->status = ActorDirector::STATUS_ACTIVE;
                    $actor->content_type = ActorDirector::TYPE_KARAOKE;
                    $image = $this->getActorAndDirectorImage($taxonomyTerm->tid);
                    if ($image) {
                        $actor->image = $this->getActorAndDirectorImage($taxonomyTerm->tid);
                    }
                    $actor->tvod1_id = $taxonomyTerm->tid;
                    $actorsRows[] = $actor->attributes;
                }
            }
            if (isset($actorsRows)) {
                Yii::$app->db->createCommand()->batchInsert(ActorDirector::tableName(), (new ActorDirector())->attributes(), $actorsRows)->execute();
            }
        }
        MigrateTvod1Controller::infoLog("**** Ket thuc dong bo dien vien ****");
    }

    private function migrateDirectors()
    {
        MigrateTvod1Controller::errorLog("\n\n****** Bat dau dong bo nhac si, dao dien *******");
        $siteId = $this->getSiteId();
        if (!$siteId) {
//            echo "\nError: NOT FOUND Site";
            MigrateTvod1Controller::errorLog("Error: Khong tim thay Nha cung cap dich vu nao");
            return;
        }

        $listTaxonomyTermData = TaxonomyTermData::find()
            ->select("taxonomy_term_data.*")
            ->innerJoin('taxonomy_vocabulary', 'taxonomy_term_data.vid=taxonomy_vocabulary.vid')
            ->where(['machine_name' => ['category_director']])->all();
        if ($listTaxonomyTermData) {
//            echo "\nqty: " . count($listTaxonomyTermData);
            foreach ($listTaxonomyTermData as $taxonomyTerm) {
                $director = ActorDirector::findOne(['name' => $taxonomyTerm->name, 'type' => ActorDirector::TYPE_DIRECTOR, 'content_type' => Content::TYPE_KARAOKE, 'status' => ActorDirector::STATUS_ACTIVE]);
                if (!$director) {
                    $director = new ActorDirector();
                    $director->name = $taxonomyTerm->name;
                    $director->type = ActorDirector::TYPE_DIRECTOR;
                    $director->status = ActorDirector::STATUS_ACTIVE;
                    $director->content_type = ActorDirector::TYPE_KARAOKE;
                    $image = $this->getActorAndDirectorImage($taxonomyTerm->tid);
                    if ($image) {
                        $director->image = $this->getActorAndDirectorImage($taxonomyTerm->tid);
                    }
                    $director->tvod1_id = $taxonomyTerm->tid;
                    $directorsRows[] = $director->attributes;
                }
            }
            if (isset($directorsRows)) {
                Yii::$app->db->createCommand()->batchInsert(ActorDirector::tableName(), (new ActorDirector())->attributes(), $directorsRows)->execute();
            }
        }
        MigrateTvod1Controller::infoLog("**** Ket thuc dong bo nhac si, dao dien. ****");
    }

    private function getImage($nodeId)
    {
        $image = null;
        //Anh dai dien
        $fid = FieldDataFieldCategoryImage::find()
            ->select(['field_category_image_fid'])
            ->where(['entity_id' => $nodeId])
            ->one();

        if ($fid) {
            $file = FileManaged::find()
                ->select(['uri', 'filesize'])
                ->where(['fid' => $fid->field_category_image_fid])
                ->one();
            if ($file) {
                $image = str_replace("public://", "", $file->uri);
            }
        }

        return $image;
    }

    private function getActorAndDirectorImage($nodeId)
    {
        $image = null;
        //Anh dai dien
        $fid = FieldDataFieldCategoryImage::find()
            ->select(['field_category_image_fid'])
            ->where(['entity_id' => $nodeId])
            ->one();

        if ($fid) {
            $file = FileManaged::find()
                ->select(['uri', 'filesize'])
                ->where(['fid' => $fid->field_category_image_fid])
                ->one();
            if ($file) {
                $image = str_replace("public://", "", $file->uri);
            }
        }

        return $image;
    }

    private function updateChildCount()
    {
        $parents = Category::find()->all();
        if ($parents) {
            foreach ($parents as $parent) {
                $childCount = Category::find()->where(['parent_id' => $parent->id])->count();
                $parent->child_count = $childCount;
                if (!$parent->save()) {
                    Yii::error($parent->errors);
                    VarDumper::dump(Json::encode($parent->errors));
                }
            }
        }
    }

    private function getCatType($tvod1_id, $name)
    {
//        echo 'Hard code get category type\n';
        $nameToType = $this->getTvod1ParentCatName($tvod1_id);
        if (!$nameToType) {
            $nameToType = $name;
        }
        return $this->getHardCodeType($nameToType);
    }

    private function getTvod1ParentCatName($tvod1_id)
    {
        $hierarchy = TaxonomyTermHierarchy::find()
            ->innerJoin('taxonomy_term_data', 'taxonomy_term_data.tid=taxonomy_term_hierarchy.tid')
            ->where(['taxonomy_term_hierarchy.tid' => $tvod1_id])
            ->one();
        $parent_id = $hierarchy->parent;
        if ($parent_id == 0) {
            return;
        }
        $parent = TaxonomyTermData::findOne(['tid'=>$parent_id]);
        return $parent->name;

    }

    private function getSiteId()
    {
        $site = Site::findOne(['status' => Site::STATUS_ACTIVE]);
        if ($site) {
            return $site->id;
        }
    }

    private function getAdminId()
    {
        $admin = User::findOne(['status' => User::STATUS_ACTIVE, 'type' => User::USER_TYPE_ADMIN]);
        if ($admin) {
            return $admin->id;
        }
    }

    private function getHardCodeType($name)
    {
        switch ($name) {
            case 'Phim':
                return Category::TYPE_FILM;
            case 'Truyền hình':
                return Category::TYPE_LIVE;
            case 'Âm nhạc':
                return Category::TYPE_MUSIC;
            case 'Thông tin':
                return Category::TYPE_NEWS;
            case 'Clips':
            case 'WC Clips':
            case 'WC Trận đấu':
                return Category::TYPE_CLIP;
            case 'Karaoke Plus':
                return Category::TYPE_KARAOKE;
            case 'Radio':
                return Category::TYPE_RADIO;
        }
        return 0;
    }
}