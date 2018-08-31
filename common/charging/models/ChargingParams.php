<?php
/**
 * Created by PhpStorm.
 * User: qhuy
 * Date: 6/23/15
 * Time: 9:23 AM
 */
namespace common\charging\models;

use common\charging\helpers\ChargingGW;

class ChargingParams {

    const CHANNEL_SMS = "SMS";
    const CHANNEL_IVR = "IVR";
    const CHANNEL_WEB = "WEB";
    const CHANNEL_WAP = "WAP";
    const CHANNEL_USSD = "USSD";
    const CHANNEL_CLIENT = "CLIENT";
    const CHANNEL_API = "API";
    const CHANNEL_UNSUB = "UNSUB";
    const CHANNEL_CSKH = "CSKH";
    const CHANNEL_MAXRETRY = "MAXRETRY";
    const CHANNEL_SUBNOTEXIST = "SUBNOTEXIST";
    const CHANNEL_SYSTEM = "SYSTEM";

    /**
     * Khi dang ki goi cuoc thi content_type se la subscription
     * Khi dang ki content le thi se truyen cac content_type tuong ung
     */
    const CONTENT_TYPE_SUBSCRIPTION = 'SUBSCRIPTION';
    const CONTENT_TYPE_MUSIC = 'MUSIC';
    const CONTENT_TYPE_VIDEO = 'VIDEO';
    const CONTENT_TYPE_GAME = 'GAME';
    const CONTENT_TYPE_NEWS = 'NEWS';
    const CONTENT_TYPE_IMAGE = 'IMAGE';
    const CONTENT_TYPE_SEARCH = 'SEARCH';
    const CONTENT_TYPE_SPECIAL = 'SPECIAL';
    const CONTENT_TYPE_GENERAL = 'GENERAL';
    const CONTENT_TYPE_V_LIVE = 'V_LIVE';
    const CONTENT_TYPE_MH1 = 'MH1';
    const CONTENT_TYPE_MH2 = 'MH2';
    const CONTENT_TYPE_MH3 = 'MH3';

    /**
     * Duoc su dung ket hoi voi content type
     * THong tin cac loai cua content type
     */
    const SUB_CONTENT_TYPE_VIDEO_VI = "VI";
    const SUB_CONTENT_TYPE_VIDEO_QTE = "QTE";
    const SUB_CONTENT_TYPE_MUSIC_VI = "VI";
    const SUB_CONTENT_TYPE_MUSIC_QTE = "QTE";
    const SUB_CONTENT_TYPE_IMAGE = "IMAGE";
    const SUB_CONTENT_TYPE_GAME_NORMAL = "NORMAL";
    const SUB_CONTENT_TYPE_GAME_RICH = "RICH";
    const SUB_CONTENT_TYPE_NEWS_NORMAL = "NORMAL";
    const SUB_CONTENT_TYPE_NEWS_RICH = "RICH";
    const SUB_CONTENT_TYPE_SEARCH = "SEARCH";
    const SUB_CONTENT_TYPE_GENERAL = "GENERAL";

    /**
     * Play type su dung trong truong hop su dung content
     */
    const PLAY_TYPE_STREAMING = "STREAMING";
    const PLAY_TYPE_DOWNLOAD = "DOWNLOAD";

    const REASON_CONTENT = 'CONTENT';


    public $msisdn = '';
    public $price = 0;
    public $original_price = 0;
    public $transaction_id = 0;
    public $promotion = 0;
    public $promotion_note = '';//Mô tả chi tiết khuyến mãi
    public $channel_type = self::CHANNEL_SMS;
    public $reason = ''; // Reason for charging, such as register, renew, content,…
    public $content_type = ''; // SUBSCRIPTION: gói CONTENT: loại content nói chung Chi tiết tham khảo bảng  contenttype
    public $subcontent_type = 'VI'; // VI/QTE: ngôn ngữ Chi tiết tham khảo bảng  subcontenttype
    public $content_id; // - [SUBSCRIPTIONID]: trong trường hợp contenttype =  SUBSCRIPTION- [CONTENTID]: trong trường hợp  contenttype = CONTENT
    public $content_name = ''; // Tên gói cước hoặc content
    public $cp_name = ''; // Tên CP:
    public $play_type = ''; // DOWNLOAD/STREAMING: dùng trong trường hợp CONTENT
    public $content_price = ''; // Giá của content, trong trường hợp 1 lần mua nhiều content thì: ∑contentprice =  PRICE
    public $cp_id;
    public $application = 1; //For the Direct Debit/Credit Function (DDCF), the ‘application’ element must be set to the value ‘1’ (1=DCCF).
    public $action = ChargingGW::CHARGING_ACTION_IMMEDIATE_DEBIT; // Refer to “Result code” section
    public $cp_transaction_id; // External Entity internal identifier for the ongoing transaction.
    public $op_transaction_id; // Contains additional information. Information are separated by ‘:’ChannelType:Receiver_Number:…ChannelType = IVR, SMS, WAP, WEB, CLI
    public $transaction_price; //Total price of transaction
    public $channel = ChargingParams::CHANNEL_SMS; // Channel originate the transaction such as SMS, WAP, CLI (Client), CSKH,…

}