<?php
/**
 * Created by PhpStorm.
 * User: qhuy
 * Date: 6/23/15
 * Time: 9:23 AM
 */
namespace common\charging\models;

class ChargingResult {

    const CHARGING_RESULT_UNKNOWN = -1;
    const CHARGING_RESULT_OK = 0;
    const CHARGING_NOK_INVALID_FORMAT = 3;
    const CHARGING_NOK_INVALID_PARAMETER = 5;
    const CHARGING_NOK_TIME_OUT = 7;
    const CHARGING_NOK_NOT_ENOUGH_CREDIT = 10;
    const CHARGING_NOK_ACCOUNT_NOT_FOUND = 201;
    const CHARGING_NOK_MSISDN_NOT_EXIST = 202;
    const CHARGING_NOK_MSISDN_BARRED = 301;
    const CHARGING_NOK_OTHER_ERROR = 999;
    const CHARGING_NOK_INVALID_MSISDN = 1001;
    const CHARGING_NOK_INVALID_SESSION = 1002;
    const CHARGING_NOK_INVALID_LOGIN = 1003;
    const CHARGING_NOK_BAD_ACCOUNT_STATUS = 1004;
    const CHARGING_NOK_NO_USER_PERMISSION = 1005;
    const CHARGING_NOK_EXCEED_MAX_CONNECTION = 1006;
    const CHARGING_NOK_1007 = 1007;
    const CHARGING_NOK_CPEX = 1007;
    const CHARGING_NOK_REGISTERED = '1008';

    public static $charging_codes = [
        self::CHARGING_RESULT_OK => 'Action success',
        self::CHARGING_RESULT_UNKNOWN => 'Init request code',
        self::CHARGING_NOK_INVALID_FORMAT => 'The request is not in the correct format',
        self::CHARGING_NOK_INVALID_PARAMETER => 'Parameter unknown or parameter not incorrect format',
        self::CHARGING_NOK_TIME_OUT => 'Can not connect to service provider',
        self::CHARGING_NOK_NOT_ENOUGH_CREDIT => 'Not enough credit on the account.',
        self::CHARGING_NOK_ACCOUNT_NOT_FOUND => 'This code is obsolete because Mobifone & Vinaphone does not distinguish postpaid & prepaid subscription anymore.',
        self::CHARGING_NOK_MSISDN_NOT_EXIST => 'MSISDN does not exist at Telco’s system.',
        self::CHARGING_NOK_MSISDN_BARRED => 'Subscriber is barred from using service.  Detail of barred status in the next table. (Only for Mobifone Subscriber)',
        self::CHARGING_NOK_OTHER_ERROR => 'Other error. Check the Error message for detail',
        self::CHARGING_NOK_INVALID_MSISDN => 'MSISDN not belong to a known operator',
        self::CHARGING_NOK_INVALID_SESSION => 'Invalid session ID. Most likely the client was not logged in or the session has expired.  Client should login again if encounter this error when charging',
        self::CHARGING_NOK_INVALID_LOGIN => 'User/password incorrect',
        self::CHARGING_NOK_BAD_ACCOUNT_STATUS => 'Bad subscriber’s status (could be locked, expired, …',
        self::CHARGING_NOK_NO_USER_PERMISSION => 'User is not permitted to run that command',
        self::CHARGING_NOK_EXCEED_MAX_CONNECTION => 'Too many concurrent connection. Login will fail.',
        self::CHARGING_NOK_1007 => 'Unknown 1007',
        self::CHARGING_NOK_CPEX => 'Unknown CPEX',
        self::CHARGING_NOK_REGISTERED => 'Lỗi đã đăng ký gói cước',
    ];

    public $cp_id;
    public $cp_transaction_id; //Same value as in the request
    public $result; // Refer to “Result code” section
    public $charged_price; // Actual fee that customer’s account was charged
    public $error; // Error description (if transaction fails)
    public $rejected_items; // Specify the invalid item in the request

}