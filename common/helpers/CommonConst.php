<?php
namespace common\helpers;
/**
 * This is the helper class for you to have api-specific stuff in WebApplication instance.
 * Note that you might want to describe your application components
 * using `@property` declarations in this docblock.
 *
 * @package YiiBoilerplate\Api
 */
class CommonConst
{
    const API_ERROR_UNKNOWN = -1;
    const API_ERROR_NO_ERROR = 0;
    const API_ERROR_AUTHENTICATION_ERROR = 1;
    const API_ERROR_INVALID_PARAMS = 2;
    const API_ERROR_INVALID_SERVICE_PACKAGE = 3;
    const API_ERROR_CONTENT_ALREADY_PURCHASED = 4;
    const API_ERROR_SERVICE_PACKAGE_ALREADY_PURCHASED = 5; // goi cuoc dang ky da ton tai (dang co asm)
    const API_ERROR_ANOTHER_SERVICE_PACKAGE_IN_GROUP_PURCHASED = 6;    // da dang ky goi cung group truoc do
    const API_ERROR_SERVICE_PACKAGE_NOT_PURCHASED = 7;         // yeu cau huy goi chua ton tai
    const API_ERROR_SYSTEM_ERROR = 8;                       // loi he thong
    const API_ERROR_ADDITIONAL_TIME_NOT_APPLY = 9;

    const API_ERROR_INVALID_CHANNEL_ID = 10;
    const API_ERROR_NO_MORE_FREE_TIME = 11;
    const API_ERROR_SERVICE_PACKAGE_PURCHASE_REQUIRED = 12;
    const API_ERROR_NO_STREAM_FOUND = 13;
    const API_ERROR_INVALID_VIDEO_ID = 14;
    const API_ERROR_EMPTY_FEEDBACK_CONTENT = 15;
    const API_ERROR_INVALID_PASSWORD = 16;
    const API_ERROR_INVALID_NEW_PASSWORD = 17;

    const API_ERROR_INVALID_MSISDN = 18;
    const API_ERROR_INVALID_USERNAME = 26;

    const API_ERROR_ADDITIONAL_TIME_RETRY_EXTEND = 19;

    const API_ERROR_ADDITIONAL_TIME_NOT_ZERO = 20;

    const API_ERROR_ADDITIONAL_TIME_OVER_EXPIRY = 21;
    const API_ERROR_NO_SERVICE_PACKAGE = 22;

    const API_ERROR_NOT_FOR_SALE = 23;
    const API_ERROR_DEVICE_EXPIRED = 24;

    const API_RENEW_INDEFINITELY_SERVICE = 25;

    const API_ERROR_CHARGING_FAIL = 101;
    const API_ERROR_LINK_FAIL = 111;
    const API_ERROR_NOT_AUTHENTICATED_IP_FOR_MO_RECEIVER = 201;

    const API_ERROR_SESSION_NOT_ACTIVE=204;
    const API_ERROR_SESSION_NOT_EXIST=205;
    const API_ERROR_SESSION_DANG_NHAP_THIET_BI_KHAC=206;
    const API_ERROR_PURCHASE_CONTENT_NOT_APPLY=207;

    const PLAYER_TYPE_HLS = 0;
    const PLAYER_TYPE_SMOOTH_STREAMING = 1;
    const PLAYER_TYPE_RTSP = 2;


    const PLAYER_TYPE_HTTP_PROGRESSIVE = 3;
    const HOST_IMAGE_ROOT='http://alotv.mobifone.com.vn/poster/';

    const MAX_LOGIN_FAIL=5;
    const API_ERROR_LIVE_HD_THU_NGHIEM=208;


    const MOBILE_ADS_NEW_CUS_CONFIRM = 0;
    const MOBILE_ADS_OLD_CUS_NOTIFY = 1;
    const MOBILE_ADS_OLD_CUS_CONFIRM = 2;
    const MOBILE_ADS_CURRENT_CUS_NOTIFY = 3;
    const MOBILE_ADS_WRONG_PROVIDER_NOTIFY = 4;


}