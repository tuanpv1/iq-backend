<?php
namespace common\helpers;
/**
 * This is the helper class for you to have api-specific stuff in WebApplication instance.
 * Note that you might want to describe your application components
 * using `@property` declarations in this docblock.
 *
 * @package YiiBoilerplate\Api
 */
class ResMtParams
{
    const PARAM_SERVICE_NAME = 'subscription_plan_name';
    const PARAM_SERVICE_CODE = 'subscription_plan_code';
    const PARAM_USED_SERVICE_NAME = 'used_subscription_plan_name';
    const PARAM_SERVICE_PRICE = 'subscription_plan_price';
    const PARAM_SERVICE_EXPIRED_DATE = 'subscription_plan_interval';
    const PARAM_MSISDN = 'msisdn';
    const PARAM_PASSWORD = 'password';
    const CHARGING_AMOUNT = 'charging_amount';
    const CURRENT_BALANCE = 'current_balance';


    public static function listMTParams()
    {
        return [

            ResMessage::DANG_KY_LAN_DAU_THANH_CONG => [self::PARAM_SERVICE_NAME, self::PARAM_SERVICE_PRICE, self::PARAM_SERVICE_EXPIRED_DATE],
            ResMessage::DANG_KY_LAN_DAU_THANH_CONG_CHUA_CO_TAI_KHOAN => [self::PARAM_PASSWORD, self::PARAM_SERVICE_NAME, self::PARAM_SERVICE_PRICE, self::PARAM_SERVICE_EXPIRED_DATE],
            ResMessage::DANG_KY_LAI_THANH_CONG => [self::PARAM_SERVICE_NAME, self::PARAM_SERVICE_PRICE, self::PARAM_SERVICE_EXPIRED_DATE],
            ResMessage::DANG_KY_LOI_TRUNG_GOI_CUOC => [self::PARAM_SERVICE_NAME],
            ResMessage::DANG_KY_LOI_TRUNG_GOI_CUOC_CUNG_NHOM => [self::PARAM_USED_SERVICE_NAME],
            ResMessage::DANG_KY_LOI_KHONG_DU_TIEN => [self::PARAM_SERVICE_NAME],
            ResMessage::DANG_KY_LOI_DO_LOI_HE_THONG => [self::PARAM_SERVICE_NAME],

            ResMessage::HUY_GOI_CUOC_THANH_CONG => [self::PARAM_SERVICE_NAME],
            ResMessage::HUY_GOI_CUOC_LOI_CHUA_DANG_KY => [self::PARAM_SERVICE_NAME],
            ResMessage::HUY_GOI_CUOC_LOI_DO_LOI_HE_THONG => [self::PARAM_SERVICE_NAME],
            ResMessage::HUY_GOI_DO_GIA_HAN_KHONG_THANH_CONG => [self::PARAM_MSISDN, self::PARAM_SERVICE_NAME, self::PARAM_SERVICE_PRICE, self::PARAM_SERVICE_EXPIRED_DATE],
            ResMessage::HUY_THAT_BAI_DO_SAI_TAI_KHOAN => [self::PARAM_MSISDN, self::PARAM_SERVICE_CODE],

            ResMessage::LAY_LAI_MAT_KHAU => [self::PARAM_PASSWORD],

            ResMessage::LOI_SAI_CU_PHAP => [],
            ResMessage::LOI_HE_THONG => [],
            ResMessage::TRO_GIUP => [],

            ResMessage::GIA_HAN_THANH_CONG => [self::PARAM_SERVICE_NAME, self::PARAM_SERVICE_PRICE, self::PARAM_SERVICE_EXPIRED_DATE],
            ResMessage::THONG_BAO_TRUOC_KHI_GIA_HAN => [self::PARAM_SERVICE_NAME, self::PARAM_SERVICE_PRICE, self::PARAM_SERVICE_EXPIRED_DATE]


        ];
    }
}