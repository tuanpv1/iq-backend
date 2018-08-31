<?php
/**
 * Description of SMSGW
 *
 * @author Nguyen Chi Thuc
 * @email gthuc.nguyen@gmail.com
 * @skype ngoaho85
 */
namespace common\helpers;
use common\models\ContentPackage;
use common\models\Service;
use common\models\SmsMtTemplateContent;
use common\models\SmsMessage;
use common\models\Subscriber;
use common\models\SubscriberServiceAsm;
use common\models\User;
use common\models\UserPackageAsm;
use Yii;
use yii\base\Component;
use common\helpers\ResMessage;

/**
 * Class SMSGW
 * @property MyCurl $ch
 */
class SMSGW extends Component
{
    const SMS_TEMPLATE_PASSWORD_CHANGE = "Mat khau truy cap dich vu cua ban la: '%s'"; // %s la mat khau moi
    const MO_STATUS = "999: Receive message ok";

    /**
     * @param $mtParam MTParam
     * @param null $serviceNumber
     * @return SmsMessage|null
     */
    public static function sendSMS($mtParam, $serviceNumber = null)
    {
        $destination = $mtParam->destination;
        $mt_status = '';
        if (empty($destination)) {
            Yii::error("Empty destination to send mt", 'SMSGW');
            return null;
        }

        $site = $mtParam->getSite();
        if ($site == null) {
            Yii::error("Empty service provider to send mt", 'SMSGW');
            return null;
        }
        if ($serviceNumber) {
            $serviceSender = $serviceNumber;
        } else if ($mtParam->brand) {
            $serviceSender = $site->service_brand_name;
        } else {
            $serviceSender = $site->service_sms_number;
        }

        $response = null;
        //TODO

        /** @var  $subscriber Subscriber */
        if ($mtParam->subscriber) {
            $subscriber = $mtParam->subscriber;
        } else {
            $subscriber = Subscriber::findByMsisdn($destination, $site->id, false);
        }
        if ($mtParam->saveToDb && ($subscriber != null)) {
            $smsRec = new SmsMessage();
            $smsRec->source = $serviceSender;
            $smsRec->destination = $destination;
            $smsRec->message = $mtParam->getSmsToSave();
            $smsRec->mt_status = $mt_status;
            $smsRec->type = SmsMessage::TYPE_MT;
            $smsRec->sent_at = time();
            $smsRec->subscriber_id = $subscriber->id;
            $smsRec->msisdn = $subscriber->msisdn;
            $smsRec->sms_template_id = $mtParam->mt_template_id;
            $smsRec->site_id = $site->id;
            $smsRec->save();
            return $smsRec;
        } else {
            $smsRec = new SmsMessage();
            $smsRec->status = $mt_status;
        }

        return $smsRec;
    }

    /**
     * @param $mtParam
     * @param $msgParam
     * @param null $service
     * @param null $serviceNumber
     * @return mixed
     */
    public static function sendMT($mtParam, $msgParam, $service = null, $serviceNumber = null)
    {
        $SmsMtTemplateContent = SmsMtTemplateContent::getMtContent($mtParam, $msgParam, $service);
        $mt_msg = $SmsMtTemplateContent['mt'];
        $web_content = $SmsMtTemplateContent['web_content'];
        $mt_template_id = $SmsMtTemplateContent['mt_template_id'];
        $mtParam->msg = $mt_msg;
        SMSGW::sendSMS($mtParam, $serviceNumber);
        return $mt_msg;
    }

    /**
     * @param $mtParam
     * @param $message
     * @return mixed
     */
    public static function resendMT($mtParam, $message)
    {
        $mtParam->msg = $message;
        $smsRec = SMSGW::sendSMS($mtParam);
        return $smsRec;
    }

}

?>
