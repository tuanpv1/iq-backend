<?php
/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 29-Oct-17
 * Time: 11:23 AM
 */

namespace api\modelsHtv;


use api\helpers\Message;
use common\models\ContentProfile;
use common\models\ContentProfileSiteAsm;
use common\models\ContentSiteAsm;
use common\models\SiteStreamingServerAsm;
use common\models\StreamingServer;
use Yii;

class Content extends \common\models\Content
{

    public static function getUrl($type_check, $contentProfile, $site_id, $content_id = null, $allow_buy_content = Content::ALLOW_BUY_CONTENT, $streaming_server_ip = null)
    {
        switch ($contentProfile->type) {
            case ContentProfile::TYPE_RAW:
                /** Không xử lí với file RAW */
                $res = [
                    'success' => false,
                    'message' => Message::getNotFoundContentMessage(),
                    'allow_buy_content' => $allow_buy_content,
                ];
                return $res;
            case ContentProfile::TYPE_STREAM:
                /** @var  $cpsa ContentProfileSiteAsm */
                $cpsa = ContentProfileSiteAsm::findOne(['content_profile_id' => $contentProfile->id, 'status' => ContentProfileSiteAsm::STATUS_ACTIVE]);
                if (!$cpsa) {
                    $res['success'] = false;
                    $res['message'] = Message::getNotFoungContentProfileMessage();
                    $res['allow_buy_content'] = $allow_buy_content;
                    return $res;
                }

                $response = ContentProfile::getStreamUrl($cpsa->url, $content_id);
                if (!$response['success']) {
                    $res = [
                        'success' => false,
                        'message' => $response['message'],
                        'allow_buy_content' => $allow_buy_content,
                    ];
                    return $res;
                } else {
                    /** @var  $contentSiteAsm ContentSiteAsm */
                    $contentSiteAsm = ContentSiteAsm::findOne(['content_id' => $contentProfile->content_id ]);
                    $subtitle = Content::getSubtitleUrl($contentSiteAsm->subtitle);
                    $res = [
                        'success' => true,
                        'url' => $response['url'],
                        'subtitle' => $subtitle,
                        'allow_buy_content' => $allow_buy_content,
                    ];
                    return $res;
                }

            case ContentProfile::TYPE_CDN;
                /** @var  $cpsa ContentProfileSiteAsm */
                $cpsa = ContentProfileSiteAsm::findOne(['content_profile_id' => $contentProfile->id, 'site_id' => $site_id, 'status' => ContentProfileSiteAsm::STATUS_ACTIVE]);
                if (!$cpsa) {
                    $res['success'] = false;
                    $res['message'] = Message::getNotFoungContentProfileMessage();
                    $res['allow_buy_content'] = $allow_buy_content;
                    return $res;
                }
                $response = ContentProfile::getCdnUrl((int)$cpsa->url, $streaming_server_ip);
                /** Nếu CDN trả về false thì return kèm message */
                if (!$response['success']) {
                    $res = [
                        'success' => false,
                        'allow_buy_content' => $allow_buy_content,
                        'message' => isset($response['reason']) ? $response['reason'] : Yii::t('app', 'Lỗi không xác định'),
                        'code' => isset($response['errorCode']) ? $response['errorCode'] : Yii::t('app', 'Không có mã lỗi'),
                    ];
                } else {
                    /** Trường hợp CDN trả về true */
                    /** @var  $contentSiteAsm ContentSiteAsm */
                    $contentSiteAsm = ContentSiteAsm::findOne(['content_id' => $contentProfile->content_id, 'site_id' => $site_id]);
                    // TuanPV
                    if (!$contentSiteAsm) {
                        return $res = [
                            'success' => false,
                            'allow_buy_content' => $allow_buy_content,
                            'message' => Yii::t('app', 'Không tìm thấy nội dung trong bảng content_site_asm'),
                            'code' => Yii::t('app', 'Chưa định nghĩa mã lỗi'),
                        ];
                    }
                    $subtitle = Content::getSubtitleUrl($contentSiteAsm->subtitle);
                    $url = $response['url'];
                    /**
                     * Nếu không phải site VN thì makeLink đúng vào con serverCache của nó
                     */
                    if ($site_id != (int)Yii::getAlias('@default_site_id')) {
                        /** @var  $streamingServer StreamingServer */
                        $streamingServer = SiteStreamingServerAsm::getStreamingServerPriority($site_id);
                        /** Nếu  có server cache thì mới makeLink còn không thì xem ở con serverCache gốc */
                        if ($streamingServer) {
                            $url = Content::makeLink($url, $streamingServer->ip);
                            if ($type_check == Content::TYPE_LIVE) {
// add 13/01/2017
                                $url = Content::replaceUrl($url, $site_id);
                            }
                        }
                    }
                    $res = [
                        'success' => true,
                        'url' => $url,
                        'allow_buy_content' => $allow_buy_content,
                        'subtitle' => $subtitle,
                    ];
                }
                return $res;
        }
    }

}