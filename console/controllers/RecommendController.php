<?php
/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 18-Jan-17
 * Time: 2:36 PM
 */

namespace console\controllers;


use api\models\ActorDirector;
use common\helpers\FileUtils;
use common\helpers\MyCurl;
use common\models\Category;
use common\models\Content;
use common\models\ContentViewLog;
use common\models\SemanticMigrate;
use Yii;
use yii\base\Exception;
use yii\console\Controller;

class RecommendController extends Controller
{

    public function actionRun(){

        $this->migrateContentToRecommendFile();
    }

    private function migrateContentToRecommendFile()
    {
        $this->writeError('******** BEGIN MIGRATE CONTENT ******');
        ini_set('memory_limit', '-1');
        $strRecommend = '';
        $firstMigrate = $this->checkFirstMigrate();
        if ($firstMigrate) {
            $this->beginMigrate();
            $migrateStatus = SemanticMigrate::find()->andWhere(['status' => SemanticMigrate::STATUS_DEACTIVE])->orderBy(['updated_at' => SORT_DESC])->one();
            $this->writeError('******* CHAY DONG BO LAN DAU TIEN');
            $listContent = Content::find()
                ->andWhere(['status' => Content::STATUS_ACTIVE])
                ->andWhere('parent_id is null')
                ->andWhere(['type' => [
                    Content::TYPE_VIDEO
//                        Content::TYPE_MUSIC,
//                        Content::TYPE_CLIP,
//                        Content::TYPE_RADIO,
                ]])->orderBy(['updated_at' => SORT_DESC])->all();
            foreach($listContent as $content){
                /** @var $content Content */
                $string = '';
                $string .= $content->id.'::'.utf8_decode($content->display_name).'::';
                $actor = \common\models\ActorDirector::find()
                    ->innerJoin('content_actor_director_asm','content_actor_director_asm.actor_director_id = actor_director.id')
                    ->andWhere(['content_actor_director_asm.content_id'=>$content->id])->all();

                if(!empty($actor)){
                    foreach($actor as $item){
                        /** @var $item ActorDirector */
                        $string .= utf8_decode($item->name);
                        $string .= '|';
                    }
                }
                $string = rtrim($string,'|');
                $string .= "::";
                $categories = Category::find()
                    ->innerJoin('content_category_asm','content_category_asm.category_id = category.id')
                    ->andWhere(['content_category_asm.content_id' => $content->id])
                    ->andWhere(['category.status'=>Category::STATUS_ACTIVE])->all();
                if($categories){
                    $strCategory = '';
                    foreach($categories as $category){
                        /** @var $category Category */
                        $strCategory .= utf8_decode($category->display_name)."|";
                    }
                    $string .= rtrim($strCategory,'|');
                }
                $strRecommend .= $string.PHP_EOL;
            }
            $strLogView = '';
            $listLogView = ContentViewLog::find()->all();
            foreach($listLogView as $logView){
                /** @var $logView ContentViewLog */
                $strLogView .= $logView->subscriber_id.'::'.$logView->content_id.'::1::'.$logView->view_count;
                $strLogView .= PHP_EOL;
            }

            $migrateStatus->status = SemanticMigrate::STATUS_ACTIVE;
            $migrateStatus->updated_at = time();
            $migrateStatus->save(false);
        }else{
            $this->writeError('******* CHAY DONG BO');
            $this->beginMigrate();

            $migrateTime = SemanticMigrate::find()->andWhere(['status' => SemanticMigrate::STATUS_ACTIVE])->orderBy(['updated_at' => SORT_DESC])->one();
            $migrateStatus = SemanticMigrate::find()->andWhere(['status' => SemanticMigrate::STATUS_DEACTIVE])->orderBy(['updated_at' => SORT_DESC])->one();

            $listContent = Content::find()
                ->andWhere(['status' => Content::STATUS_ACTIVE])
                ->andWhere('parent_id is null')
                ->andWhere(['>','updated_at',$migrateTime->updated_at])
                ->andWhere(['type' => [
                    Content::TYPE_VIDEO
//                        Content::TYPE_MUSIC,
//                        Content::TYPE_CLIP,
//                        Content::TYPE_RADIO,
                ]])->orderBy(['updated_at' => SORT_DESC])->all();
            foreach($listContent as $content){
                /** @var $content Content */
                $string = '';
                $string .= $content->id.'::'.utf8_decode($content->display_name).'::';
                $actor = \common\models\ActorDirector::find()
                    ->innerJoin('content_actor_director_asm','content_actor_director_asm.actor_director_id = actor_director.id')
                    ->andWhere(['content_actor_director_asm.content_id'=>$content->id])->all();

                if(!empty($actor)){
                    foreach($actor as $item){
                        /** @var $item ActorDirector */
                        $string .= utf8_decode($item->name);
                        $string .= '|';
                    }
                }
                $string = rtrim($string,'|');
                $string .= "::";
                $categories = Category::find()
                    ->innerJoin('content_category_asm','content_category_asm.category_id = category.id')
                    ->andWhere(['content_category_asm.content_id' => $content->id])
                    ->andWhere(['category.status'=>Category::STATUS_ACTIVE])->all();
                if($categories){
                    $strCategory = '';
                    foreach($categories as $category){
                        /** @var $category Category */
                        $strCategory .= utf8_decode($category->display_name)."|";
                    }
                    $string .= rtrim($strCategory,'|');
                }
                $strRecommend .= $string.PHP_EOL;
            }
            $strLogView = '';
            $listLogView = ContentViewLog::find()
                ->andWhere(['>=','view_date',$migrateTime->updated_at])->all();
            foreach($listLogView as $logView){
                /** @var $logView ContentViewLog */
                $strLogView .= $logView->subscriber_id.'::'.$logView->content_id.'::1::'.$logView->view_count;
                $strLogView .= PHP_EOL;
            }
            $migrateStatus->status = SemanticMigrate::STATUS_ACTIVE;
            $migrateStatus->updated_at = time();
            $migrateStatus->save(false);
        }
        $strRecommend = str_replace('<br />','',$strRecommend);
        $strLogView = str_replace('<br />','',$strLogView);

        $response = self::postCallAPI(Yii::$app->params['recommend_url'] . '/tvod/item', $strRecommend);

        $respoose_logview = self::postCallAPI(Yii::$app->params['recommend_url'] . '/tvod/event',$strLogView);

        return json_decode($response);
    }


    private function writeError($mes)
    {
        FileUtils::appendToFile(Yii::getAlias('@runtime/logs/semantic.log'), date('y-m-d H:i:s') . ' ' . $mes);
    }

    private function beginMigrate()
    {
        $semantic = new SemanticMigrate();
        $semantic->status = SemanticMigrate::STATUS_DEACTIVE;
        $semantic->time = time();
        $semantic->created_at = time();
        $semantic->updated_at = time();
        $semantic->save(false);
        return true;
    }

    private function checkFirstMigrate()
    {
        $count = SemanticMigrate::find()->andWhere(['status' => SemanticMigrate::STATUS_ACTIVE])->count();
        if ($count < 1) {
            return true;
        }
        return false;
    }

    private static function call($function, $params)
    {
        $ch = new MyCurl();
        $ch->follow_redirects = true;
        $ch->user_agent = "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) coc_coc_browser/50.0.125 Chrome/44.0.2403.125 Safari/537.36";

        $result = null;
        $url = $function;
        Yii::info('Request params url: ' . $url . implode('&', $params));
        try {
            $response = $ch->post($url, $params);
            Yii::info('Response status: ' . $response);
            Yii::info('Response status: ' . $response->headers['Status-Code']);
            Yii::info('Response body: ' . $response->body);

            return $response->body;
        } catch (Exception $e) {
            Yii::error($e->getMessage());
            return null;
        }

    }

    private function postCallAPI($url,$str)
    {
        $charging_url = $url;

        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_URL, $charging_url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/plain;charset=UTF-8"));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        if ($result === false) {
//            $this->writeError(curl_error($ch));
        } else {
//            $this->writeError($result);
        }
        curl_close($ch);
        return $result;
    }
    public function actionTest(){
        $string = 'aaaa'.PHP_EOL;
        $string .= 'bbbb';
        echo nl2br($string);
    }
}