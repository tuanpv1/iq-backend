<?php
/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 31-Oct-17
 * Time: 9:34 AM
 */

namespace api\modelsHtv;


use common\models\ContentProfile;
use common\models\ContentProfileSiteAsm;
use common\models\ContentSiteAsm;
use common\models\LiveProgram;

class LiveProgramHtv extends \common\models\LiveProgram
{
    public static function getEpg($channel_id,$fromDate, $toDate,$site_id){
        $livePrograms = LiveProgram::find()
            ->innerJoin('content', 'content.id=live_program.content_id')
            ->andWhere(['content.status' => Content::STATUS_ACTIVE ])
            ->innerJoin('content_site_asm', 'content_site_asm.content_id=live_program.content_id')
            ->andWhere(['content_site_asm.status' => ContentSiteAsm::STATUS_ACTIVE])
            ->andWhere(['live_program.channel_id' => $channel_id])
            ->andWhere('live_program.started_at between :fromDate and :toDate')->addParams([':fromDate' => $fromDate, ':toDate' => $toDate])
            ->orderBy('live_program.started_at')
            ->all();
        $arrItems = [];
        /** Duyệt từng bản ghi EPG */
        foreach($livePrograms as $liveProgram){
            $item = $liveProgram->getAttributes(['id','channel_id','content_id','status','name','started_at','ended_at'], ['created_at','updated_at']);
            /** Với mỗi bản ghi EPG lấy ra danh sách các content_profile */
            $contentProfiles = $liveProgram->content->contentProfiles?$liveProgram->content->contentProfiles:[];
            $strQualities = "";
            /** Duyệt từng content_profile kiểm tra để lấy đúng content_profile map với site */
            foreach ($contentProfiles as $contentProfile) {
                /** Không lấy thằng file RAW */
                if($contentProfile->type == ContentProfile::TYPE_RAW){
                    continue;
                }
                $contentProfileSiteAsm = ContentProfileSiteAsm::findOne(['content_profile_id'=>$contentProfile->id, 'site_id'=>$site_id,'status'=>ContentProfileSiteAsm::STATUS_ACTIVE]);
                /** Nếu content_profile không thuộc site thì bỏ qua */
                if(!$contentProfileSiteAsm){
                    continue;
                }
                /** Get object content_priofile để xử lí*/
                $cp = $contentProfileSiteAsm->contentProfile;
                $strQualities .= $cp->quality . ',';
            }
            if(strlen($strQualities) >= 2){
                $strQualities = substr($strQualities,0,-1);
            }
            $item['qualities']      = $strQualities;
            $arrItems[]      = $item;
        }
        return $arrItems;
    }

}