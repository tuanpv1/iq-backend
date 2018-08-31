<?php
namespace common\helpers;
use common\models\CpSysnc;
use common\models\Subscriber;
use common\models\SubscriberServiceAsm;
use common\models\SubscriberTransaction;
use Yii;
use yii\helpers\Json;

/**
 * Created by PhpStorm.
 * User: TuanPV
 * Date: 7/26/2017
 * Time: 1:38 PM
 */
class SysCproviderService
{
    public static function SysPurchaseService($transaction_type, $transaction_id, $purchaseService, $subscriber, $price, $channel_type, $ssa, $check_register_promotion = false)
    {
        /** @var  Subscriber $subscriber */
        /** @var  SubscriberServiceAsm $ssa */
        // Kiểm tra service_id có thuộc vào CP cần đồng bộ hay không.
        $list_cp = Yii::$app->params['list_cp'];
        foreach ($list_cp as $cp_id => $cp) {
            $list_service = $cp['list_service_id'];
            $url = $cp['url'];
            if (in_array($purchaseService->id, $list_service)) {
                if (!$transaction_id) {
                    $check = CpSysnc::saveCpSysnc(
                        $cp_id,
                        $purchaseService->id,
                        $transaction_id,
                        CpSysnc::STATUS_ERROR,
                        CpSysnc::ERROR_CODE_NOT_SAVE_TRANSACTION,
                        'Không lưu được transaction nên không đồng bộ',
                        0,
                        '',
                        $transaction_type,
                        $subscriber->id
                    );
                    if (!$check) {
                        Yii::info('Ghi log loi thanh cong');
                    } else {
                        Yii::info('Ghi log loi thanh cong');
                    }
                } else {
                    Yii::info('Dung goi cua Qnet, bat dau dong bo');
                    if ($transaction_type == SubscriberTransaction::TYPE_REGISTER) {
                        self::RegisterServiceQnet($subscriber, $purchaseService, $ssa, $price, $channel_type, $url, $cp_id, $transaction_id, $transaction_type);
                    } elseif($transaction_type == SubscriberTransaction::TYPE_PROMOTION && $check_register_promotion == false) {
                        self::RegisterServiceQnet($subscriber, $purchaseService, $ssa, $price, $channel_type, $url, $cp_id, $transaction_id, $transaction_type);
                    }else{
                        self::MonfeeServiceQnet($subscriber, $purchaseService, $ssa, $price, $url, $cp_id, $transaction_id, $transaction_type);
                    }
                }
            }
        }
    }

    function qnetSoap($args, $soapAction, $url)
    {
        $request = new MyCurl();
        $request->soap = true;
        $request->soapAction = $soapAction;
        $request->headers = [
            'Content-type' => 'text/xml',
        ];
        $resp = $request->post($url, $args)->__toString();
        Yii::info($resp);
        $resp = new \SimpleXMLElement($resp);
        Yii::info("ket qua");
        Yii::info($resp);
        if ($soapAction == 'Subscribe') {
            $code = $resp->xpath('/soap:Envelope/soap:Body')[0]->SubscribeResponse->SubscribeResult->code->__toString();
            $message = $resp->xpath('/soap:Envelope/soap:Body')[0]->SubscribeResponse->SubscribeResult->message->__toString();
        } elseif ($soapAction == 'Monfee') {
            $code = $resp->xpath('/soap:Envelope/soap:Body')[0]->MonfeeResponse->MonfeeResult->code->__toString();
            $message = $resp->xpath('/soap:Envelope/soap:Body')[0]->MonfeeResponse->MonfeeResult->message->__toString();
        }
        $resp = compact("code", "message");
        \Yii::trace($resp, 'Qnet response');
        return $resp;
    }

    public
    static function replaceSubscriberXxx($msisdn)
    {
        $lenght = strlen($msisdn);
        if ($lenght >= 3) {
            $msisdn1 = substr($msisdn, $lenght - 3);
            $msisdn = str_replace($msisdn1, "xxx", $msisdn);
        }
        return $msisdn;
    }

    public
    static function RegisterServiceQnet($subscriber, $purchaseService, $ssa, $price, $channel_type, $url, $cp_id, $transaction_id, $transaction_type)
    {
        $args = [
            'cusId' => $subscriber->id . "-" . SysCproviderService::replaceSubscriberXxx($subscriber->msisdn),
            'packageId' => $purchaseService->id,
            'subTime' => $ssa->activated_at,
            'exTime' => $ssa->expired_at,
            'price' => $price,
            'user' => null,
            'pass' => null,
            'mobile' => $subscriber->msisdn,
            'deviceType' => SubscriberTransaction::listChannelType()[$channel_type],
            'fullName' => $subscriber->full_name,
        ];
        $soapAction = 'Subscribe';
        $qmResp = SysCproviderService::qnetSoap($args, $soapAction, $url);
        $error_code = $qmResp['code'];
        $message_code = $qmResp['message'];
        if ($error_code == CpSysnc::STATUS_SUCCESS) {
            $status = CpSysnc::STATUS_SUCCESS;
        } else {
            $status = CpSysnc::STATUS_ERROR;
        }
        $check = CpSysnc::saveCpSysnc(
            $cp_id,
            $purchaseService->id,
            $transaction_id,
            $status,
            $error_code,
            $message_code,
            $price,
            json_encode($args),
            $transaction_type,
            $subscriber->id
        );
        if (!$check) {
            Yii::info('Dong bo gia han that bai');
        } else {
            Yii::info('Dong bo gia han hoan tat');
        }
    }

    public
    static function MonfeeServiceQnet($subscriber, $purchaseService, $ssa, $price, $url, $cp_id, $transaction_id, $transaction_type)
    {
        $args = [
            'cusId' => $subscriber->id . "-" . SysCproviderService::replaceSubscriberXxx($subscriber->msisdn),
            'packageId' => $purchaseService->id,
            'monfeeTime' => $ssa->renewed_at,
            'exTime' => $ssa->expired_at,
            'price' => $price,
            'user' => null,
            'pass' => null,
        ];

        $soapAction = 'Monfee';
        $qmResp = SysCproviderService::qnetSoap($args, $soapAction, $url);
        $error_code = $qmResp['code'];
        $message_code = $qmResp['message'];
        if ($error_code == CpSysnc::STATUS_SUCCESS) {
            $status = CpSysnc::STATUS_SUCCESS;
        } else {
            $status = CpSysnc::STATUS_ERROR;
        }
        $check = CpSysnc::saveCpSysnc(
            $cp_id,
            $purchaseService->id,
            $transaction_id,
            $status,
            $error_code,
            $message_code,
            $price,
            json_encode($args),
            $transaction_type,
            $subscriber->id
        );
        if (!$check) {
            Yii::info('Dong bo gia han that bai');
        } else {
            Yii::info('Dong bo gia han hoan tat');
        }
    }
}

?>

