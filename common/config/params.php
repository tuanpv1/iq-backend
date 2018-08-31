<?php
return [
    'adminEmail' => 'admin@example.com',
    'semantic_url' => 'http://semantic.tvod.vn/api/film',
    'semantic_url2' => 'http://se.tvod.vn',
    'semantic_url_new' => 'http://103.31.126.219/',
    'semantic_url_search_engine' => 'http://10.84.82.139/',
    'site_id' => 5,
    'secret_key' => "VNPT-Technology", //secret_key dùng để valid khi checkMac
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,
    'CDN_TVOD' => 'http://api.cdn.tvod.com.vn',
    'ORIGIN_URL' => 'http://10.3.0.77',
    'URL_SERVER_CATCHUP' => 'http://tvod-ptp8k.static.cdn.tvod.com.vn/',

    // thanh toan the nap tvod
    'voucher_tvod_link' => 'http://10.84.75.179:8180',
    'user_voucher' => 'tvod04',
    'pass_voucher' => 'vivas124',
    'mpin_voucher' => 'w1tp5tfp',
    'key_seed_voucher' => 'adsf2s12s',
    'key_seed_voucher_phone' => 'adsf2s12s',

    //thanh toan the dien thoai
    'voucher_phone_link' => 'http://cardcharging.vivas.vn:8000',
    'cpid_phone' => 19,

    'sms_proxy' => [
        'url' => 'http://10.84.73.6:13013/cgi-bin/sendsms',
        'username' => 'tester',
        'password' => 'foobar',
        'debug' => false
    ],
    'access_private' => [
        'user_name' => 'msp_private',
        'password' => 'Msp!@123',
        'ip_privates' => [
            '192.0.0.0/8',
            '10.0.0.0/8',
            '10.84.0.0/16',
            '127.0.0.0/16',
        ],
    ],
    'tvod1Only' => false,
    'payment_gate' => [
        'active' => false,
        'url' => 'https://pay.smartgate.vn/Checkout',
        'base_url' => 'https://pay.smartgate.vn',
        'return_url' => 'http://api.tvod.dev/payment-web/response-charge',
        'cancel_url' => 'http://api.tvod.dev/payment-web/cancel-charge',
        'merchant_id' => 'tvod',
        'secret_key' => 'FDF8A9930F',
        'command' => 'PAY',
        'command_check' => 'QUERYDR',
        'order_type_digital' => 2,
    ],
    'payment_vtc_pay'=> [
        'active' => true,
        'url' => 'https://vtcpay.vn/bank-gateway/checkout.html', // Link gửi request
        'base_url' => 'https://vtcpay.vn/', // base link
        'url_return' => 'http://api.tvod.vn/payment-vtc-pay/response-charge', // Link trả về
        'url_check_transaction' => 'http://vtcpay.vn/cong-thanh-toan/WSCheckTrans.asmx?wsdl', // Link kiem tra giao dich thanh cong/that bai
        'merchant_id' => '6003', // Mã lúc đăng kí
        'secret_key' => 'Vnpt-technology@vn1', // Pass ket noi cua web
        'receiver_account' => '0904991755', // Tai khoan dang ky ket noi
        'tvod2_api_base_url' => 'http://api.tvod.vn/', // link api tvod
        'app_id' => '500002643', // App id
        'secret_key_app' => 'vntech@@123', // Pass ket noi cua app
        'check_transaction_after'=>  2400 //So giay kiem tra giao dich (luu y: phai lon hon 30p(>1800s))
    ],
    'sms_charging' => [
        'username' => 'tvod2',
        'password' => '123456',
    ],
    'auto_renew' => [
        'max_time_in_hours' => 0, // thoi gian quet gia han truoc khi het han
    ],
    'retry' => 3,
    'delay' => 1,
    'recommend_url' => 'http://10.84.82.11:5432/',
    'partner' => [
        'voucher_tvod' => [
            'key' => 'rdvias@123',
        ],
    ],

    'list_price_level' => [
        1000 => "1.000",
        2000 => "2.000",
        5000 => "5.000",
        10000 => "10.000",
        20000 => "20.000",
        50000 => "50.000",
        100000 => "100.000",
        200000 => "200.000",
        500000 => "500.000",
    ],
    'Brandname' => [
        'brandname' => 'VIVAS',
        'username' => 'TVOD2016',
        'password' => '2nIkB5jTGQgcTimVRvvHr9QIsMA=',
        'sharekey' => 'vivas123',
        'otp_content' => 'Ma xac thuc tai khoan TVOD la: {otp}',
    ],

    'key_cache' => [
        'ContentQualities' => 'ContentQualities',
        'CacheLive' => 'CacheLive',
        'ApiKey' => 'ApiKey',
        'SiteID' => 'SiteID',
        'ContentCategoryID' => 'ContentCategoryID',
        'ContentCategories' => 'ContentCategories',
        'ContentIsFree' => 'ContentIsFree',
        'ContentPriceCoin' => 'ContentPriceCoin',
        'ContentDirectors' => 'ContentDirectors',
        'ContentActors' => 'ContentActors',
    ],
    'time_expire_cache' => 3600,

    //cách sd list_cp => [cp_id => [url, mảng id service]]
    'list_cp' => [
        1 => [
            'url' => 'http://113.190.242.85:7777/',
            'list_service_id' => [21],
        ]
    ],
    //Timeout cho giao dịch đang chờ xử lý từ smartgate.
    'smartgate_timeout' => 600,
    'factory_ip' => [
        '192.168.0.0'
    ],
    'timeOutLogin' => 3, // Số phút
    'numberLogin' => 10,
    'list_number_month' => [
        1 => "1",
        2 => "2",
        3 => "3",
        4 => "4",
        5 => "5",
        6 => "6",
        7 => "7",
        8 => "8",
        9 => "9",
        10 => "10",
        11 => "11",
        12 => "12",
    ],
    'number_month_default' => 3,
];
