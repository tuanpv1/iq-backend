<?php
/**
 * Created by PhpStorm.
 * User: Hoan
 * Date: 12/28/2016
 * Time: 10:28 AM
 */

namespace common\helpers;


use common\models\SmsMessage;
use common\models\Subscriber;
use common\models\VacResponse;
use Exception;
use Yii;

class VACHelper
{
    private static function call($function, $params, $key, $post = false)
    {
        $ch = new MyCurl();
        $ch->follow_redirects = true;
        $ch->user_agent = "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) coc_coc_browser/50.0.125 Chrome/44.0.2403.125 Safari/537.36";

        $result = null;
        $base_url = isset(Yii::$app->params['vac_url']) ? Yii::$app->params['vac_url'] : "http://localhost";
        $url = $base_url . $function;

        $query_string = '';
        $i = 0;
        foreach ($params as $param) {
            $query_string .= '&' . $key[$i] . '=' . $param;
            $i++;
        }

        Yii::info('Request params url: ' . $url . $query_string);
        try {
            if ($post) {
                $response = $ch->post($url, $params);
            } else {
                $response = $ch->get($url, $params);
            }
        } catch (Exception $e) {
            Yii::info($e->getMessage());
            $response = null;
        }
        if ($response) {
            Yii::info('Response status: ' . $response->headers['Status-Code']);
            Yii::info('Response body: ' . $response->body);

//            $result = new CommonResponse();
//            $result->data = $response->body;
//            $http_code = curl_getinfo($ch->request, CURLINFO_HTTP_CODE);
//            $result->statusCode = $response->headers['Status-Code'];
//            $result->statusCode = $response->headers['Status-Code'];
//            $result->statusCode = $response->headers['Status-Code'];

        } else {
//            $result = new CommonResponse();
//            $result->message = "Can not connect to vas gateway!!";
//            $result->statusCode = 509;
        }

//        Yii::info($response);
        return $response;
    }

    public static function getUserInfo1($access_token = '', $client_id = '', $fingerprint = '',
                                        $package_name = '', $client_secret = '', $site_id = 0, $mac_address, $chanel = Subscriber::CHANNEL_TYPE_ANDROID)
    {
        $response = self::call('/account/get-user-info', [
            'access_token' => $access_token,
            'client_id' => $client_id,
            'fingerprint' => $fingerprint,
            'package_name' => $package_name,
            'client_secret' => $client_secret,
        ], [
            'access_token',
            'client_id',
            'fingerprint',
            'package_name',
            'client_secret',
        ]);
        /** @var VacResponse $result_obj */
        $result_obj = json_decode($response);

        Yii::info($result_obj);

        if (isset($result_obj) && isset($result_obj->status)) {
            $subscriber = Subscriber::findByUsername($result_obj->username, $site_id);
            if (!$subscriber) {
                $subscriber = Subscriber::findOne(['authen_type' => Subscriber::AUTHEN_TYPE_MAC_ADDRESS, 'machine_name' => $mac_address]);
                if (!$subscriber) {
                    $rs = Subscriber::register($result_obj->username,
                        '12345678', $result_obj->phone_number, $result_obj->province,
                        Subscriber::STATUS_ACTIVE, Subscriber::AUTHEN_TYPE_ACCOUNT, $site_id, $chanel, $mac_address, $result_obj->address, $result_obj->email, $result_obj->fullname);
                    if (isset($rs['subscriber']) && $rs['subscriber']) {
                        return $rs['subscriber'];
                    }
                } else {
                    $subscriber->username = $result_obj->username;
                    $subscriber->msisdn = $result_obj->phone_number;
                    $subscriber->authen_type = Subscriber::AUTHEN_TYPE_ACCOUNT;
                    $subscriber->register_at = time();
                    $subscriber->save();
                    return $subscriber;
                }

            } else {
                return $subscriber;
            }
        }
        return null;
    }

//    public static function getUserInfo($access_token = '', $client_id = '', $fingerprint = '',
//                                       $package_name = '', $client_secret = '', $site_id = 0, $mac_address, $chanel = Subscriber::CHANNEL_TYPE_ANDROID)
//    {
//        $response = self::call('/account/get-user-info', [
//            'access_token' => $access_token,
//            'client_id' => $client_id,
//            'fingerprint' => $fingerprint,
//            'package_name' => $package_name,
//            'client_secret' => $client_secret,
//        ], [
//            'access_token',
//            'client_id',
//            'fingerprint',
//            'package_name',
//            'client_secret',
//        ]);
//        /** @var VacResponse $result_obj */
//        $result_obj = json_decode($response);
//
//        Yii::info($result_obj);
//
//        if (isset($result_obj) && isset($result_obj->status)) {
//
//            $sub = Subscriber::findOne(['status' => Subscriber::STATUS_ACTIVE, 'machine_name' => $mac_address, 'authen_type' => Subscriber::AUTHEN_TYPE_ACCOUNT]);
//            if ($sub) {
//                if ($sub->username == $result_obj->username) {
//                    return null;
//                }
//            }
//
//            /** @var Subscriber $subscriber */
//            $subscriber = Subscriber::findByUsername($result_obj->username, $site_id, false);
//
//            if ($subscriber) {
//                /** Nếu user đã tồn tại và active và authen_type = username */
//                if ($subscriber->status == Subscriber::STATUS_ACTIVE
//                    && $subscriber->authen_type == Subscriber::AUTHEN_TYPE_ACCOUNT
//                ) {
//                    return $subscriber;
//                }
//
//                /** Nếu user đang bảo hành box mà đăng nhập mới thì gán mac mới vào user && xóa tài khoản có MAC cũ */
//                if ($subscriber->status == Subscriber::STATUS_MAINTAIN) {
//                    /** Nếu dùng lại luôn box cũ sau khi xóa lk 1-1 (bảo hành) */
//                    if ($mac_address == $subscriber->machine_name) {
//                        $subscriber->status = Subscriber::STATUS_ACTIVE;
//                        $subscriber->authen_type = Subscriber::AUTHEN_TYPE_ACCOUNT;
//                        $subscriber->save(false);
//
//                        $mac_subscriber = Subscriber::findOne(['authen_type' => Subscriber::AUTHEN_TYPE_MAC_ADDRESS, 'machine_name' => $mac_address]);
//                        if ($mac_subscriber) {
//                            $mac_subscriber->status = Subscriber::STATUS_DELETED;
//                            $mac_subscriber->save();
//                        }
//                        return $subscriber;
//
//                    } else {
//                        $old_mac = $subscriber->machine_name;
//                        $subscriber->status = Subscriber::STATUS_ACTIVE;
//                        $subscriber->machine_name = $mac_address;
//                        $subscriber->authen_type = Subscriber::AUTHEN_TYPE_ACCOUNT;
//                        $subscriber->save(false);
//
//                        $old_user = Subscriber::findOne(['machine_name' => $old_mac, 'authen_type' => Subscriber::AUTHEN_TYPE_MAC_ADDRESS]);
//                        if ($old_user) {
//                            $old_user->status = Subscriber::STATUS_DELETED;
//                            $old_user->save();
//                        }
//                        return $subscriber;
//                    }
//                }
//
//                /** Nếu user bị khóa trả về user luôn để bắn lỗi bị khóa bên controller */
//                if ($subscriber->status == Subscriber::STATUS_INACTIVE) {
//                    return $subscriber;
//                }
//
//                /** Các trường hợp còn lại xử lý theo flow mac mới */
//                $subscriber = Subscriber::findOne(['authen_type' => Subscriber::AUTHEN_TYPE_MAC_ADDRESS, 'machine_name' => $mac_address]);
//                if (!$subscriber) {
//                    $rs = Subscriber::register($result_obj->username,
//                        '12345678', $result_obj->phone_number, $result_obj->province,
//                        Subscriber::STATUS_ACTIVE, Subscriber::AUTHEN_TYPE_ACCOUNT, $site_id, $chanel, $mac_address, $result_obj->address, $result_obj->email, $result_obj->fullname);
//                    if (isset($rs['subscriber']) && $rs['subscriber']) {
//                        return $rs['subscriber'];
//                    }
//                } else {
//                    $subscriber->username = $result_obj->username;
//                    $subscriber->msisdn = $result_obj->phone_number;
//                    $subscriber->authen_type = Subscriber::AUTHEN_TYPE_ACCOUNT;
//                    $subscriber->register_at = time();
//                    $subscriber->save(false);
//                    return $subscriber;
//                }
//
//            } else {
//                /** Nếu ko có tài khoản nào có username trên thì tiến hành kt có mac nào như trên không để gán username */
//                $subscriber = Subscriber::find()
//                    ->andWhere(['authen_type' => Subscriber::AUTHEN_TYPE_MAC_ADDRESS])
//                    ->andWhere(['machine_name' => $mac_address])
//                    ->orderBy(['id' => SORT_DESC])
//                    ->one();
//
//                if (!$subscriber) {
//                    $rs = Subscriber::register($result_obj->username,
//                        '12345678', $result_obj->phone_number, $result_obj->province,
//                        Subscriber::STATUS_ACTIVE, Subscriber::AUTHEN_TYPE_ACCOUNT, $site_id, $chanel, $mac_address, $result_obj->address, $result_obj->email, $result_obj->fullname);
//                    if (isset($rs['subscriber']) && $rs['subscriber']) {
//                        return $rs['subscriber'];
//                    }
//                } else {
//                    /** Trường hợp tài khoản mac đó đã từng liên kết */
//                    if ($subscriber->status == Subscriber::STATUS_MAINTAIN) {
//
//                        $subscriber_mac = Subscriber::find()
//                            ->andWhere(['authen_type' => Subscriber::AUTHEN_TYPE_MAC_ADDRESS])
//                            ->andWhere(['machine_name' => $mac_address])
//                            ->andWhere(['username' => ''])
//                            ->one();
//
//                        if ($subscriber_mac) {
//                            $subscriber = $subscriber_mac;
//                            $subscriber->username = $result_obj->username;
//                            $subscriber->msisdn = $result_obj->phone_number;
//                            $subscriber->authen_type = Subscriber::AUTHEN_TYPE_ACCOUNT;
//                            $subscriber->status = Subscriber::STATUS_ACTIVE;
//                            $subscriber->register_at = time();
//                            $subscriber->save(false);
//                        } else {
//                            $rs = Subscriber::register($result_obj->username,
//                                '12345678', $result_obj->phone_number, $result_obj->province,
//                                Subscriber::STATUS_ACTIVE, Subscriber::AUTHEN_TYPE_ACCOUNT, $site_id, $chanel, $mac_address, $result_obj->address, $result_obj->email, $result_obj->fullname);
//                            if (isset($rs['subscriber']) && $rs['subscriber']) {
//                                return $rs['subscriber'];
//                            }
//                        }
//                    } else {
//                        $subscriber->username = $result_obj->username;
//                        $subscriber->msisdn = $result_obj->phone_number;
//                        $subscriber->authen_type = Subscriber::AUTHEN_TYPE_ACCOUNT;
//                        $subscriber->status = Subscriber::STATUS_ACTIVE;
//                        $subscriber->register_at = time();
//                        $subscriber->save(false);
//                    }
//
//
//                    return $subscriber;
//                }
//            }
//        }
//        return null;
//    }
//

    public static function getUserInfo($access_token = '', $client_id = '', $fingerprint = '',
                                       $package_name = '', $client_secret = '', $site_id = 0, $mac_address, $chanel = Subscriber::CHANNEL_TYPE_ANDROID)
    {
        $response = self::call('/account/get-user-info', [
            'access_token' => $access_token,
            'client_id' => $client_id,
            'fingerprint' => $fingerprint,
            'package_name' => $package_name,
            'client_secret' => $client_secret,
        ], ['access_token', 'client_id', 'fingerprint', 'package_name', 'client_secret',
        ]);
        /** @var VacResponse $result_obj */
        $result_obj = json_decode($response);

        Yii::info($result_obj);

        if (isset($result_obj) && isset($result_obj->status)) {
            /** @var Subscriber $subscriber */
            $subscriber = Subscriber::find()
                ->andWhere(['username' => $result_obj->username])
                ->andWhere(['site_id' => $site_id])
                ->orderBy(['authen_type' => SORT_ASC, 'status' => SORT_DESC])
                ->one();

            if ($subscriber) {
                /** Nếu user đã tồn tại và active và authen_type = account */
                if ($subscriber->status == Subscriber::STATUS_ACTIVE
                ) {
                    if ($subscriber->authen_type == Subscriber::AUTHEN_TYPE_ACCOUNT) {
                        return $subscriber;
                    } else {
                        return null;
                    }
                }

                /** Truong hop username dang duoc bao hanh dung tai khoan dang nhap vao box moi hoac cu */
                if ($subscriber->status == Subscriber::STATUS_MAINTAIN) {
                    $subscriber->status = Subscriber::STATUS_ACTIVE;
                    $subscriber->machine_name = $mac_address;
                    $subscriber->authen_type = Subscriber::AUTHEN_TYPE_ACCOUNT;
                    $subscriber->type = Subscriber::TYPE_USER;
                    $subscriber->save(false);
                    return $subscriber;
                }

                /** Nếu user bị khóa trả về user luôn để bắn lỗi bị khóa bên controller */
                if ($subscriber->status == Subscriber::STATUS_INACTIVE || $subscriber->status == Subscriber::STATUS_DELETED) {
                    return $subscriber;
                }

                return null;
            } else {
                /** Nếu ko có tài khoản nào có username trên thì tiến hành kt có mac đó có được gán vs username nao ko */
                /** @var Subscriber $subscriber */
                $subscriber = Subscriber::find()
                    ->andWhere(['authen_type' => Subscriber::AUTHEN_TYPE_ACCOUNT])
                    ->andWhere(['machine_name' => $mac_address])
                    ->orderBy(['id' => SORT_DESC])
                    ->one();

                // Neu co thi return lai subscriber de check quyen login tren account
                if ($subscriber) {
                    return $subscriber;
                }

                // Tim tai khoan la mac va uu tien tim tai khoan co status active
                $subscriber = Subscriber::find()
                    ->andWhere(['authen_type' => Subscriber::AUTHEN_TYPE_MAC_ADDRESS])
                    ->andWhere(['machine_name' => $mac_address])
                    ->orderBy(['status' => SORT_DESC])
                    ->one();

                if ($subscriber) {
                    if($subscriber->status == Subscriber::STATUS_ACTIVE){
                        $subscriber->username = $result_obj->username;
                        $subscriber->msisdn = $result_obj->phone_number;
                        $subscriber->authen_type = Subscriber::AUTHEN_TYPE_ACCOUNT;
                        $subscriber->status = Subscriber::STATUS_ACTIVE;
                        $subscriber->register_at = time();
                        $subscriber->type = Subscriber::TYPE_USER;
                        $subscriber->save(false);
                        return $subscriber;
                    }

                    // Neu gap subscriber dang o trang thai maintain thi xoa MAC
                    if($subscriber->status == Subscriber::STATUS_MAINTAIN){
                        $subscriber->machine_name = '';
                        $subscriber->type = Subscriber::TYPE_USER;
                        $subscriber->save(false);
                        return null;
                    }else{
                        // Neu gap trang thai deleted hoac inactive cua tai khoan MAC thi flow dang loi, return null
                        Yii::error('Found MAC account inactive or deleted','Logic error');
                        return null;
                    }
                }else{
                    //Khong tim thay tai khoan MAC --> Chua login MAC bao gio --> tao tai khoan moi

                    Yii::warning('Not found MAC account, never login MAC before','Logic warning');
                    $rs = Subscriber::register($result_obj->username,
                        '12345678', $result_obj->phone_number, $result_obj->province,
                        Subscriber::STATUS_ACTIVE, Subscriber::AUTHEN_TYPE_ACCOUNT, $site_id, $chanel, $mac_address, $result_obj->address, $result_obj->email, $result_obj->fullname);
                    if (isset($rs['subscriber']) && $rs['subscriber']) {
                        return $rs['subscriber'];
                    }else{
                        return null;
                    }
                }
            }
        }
        return null;
    }
    public static function changePhoneNumber($subscriber, $phone_number)
    {
        $response = self::call('/account/change-phone-number', [
            'username' => $subscriber->username,
            'phone_number' => $phone_number,
        ], [
            'username',
            'phone_number',
        ]);

        /** @var VacResponse $result_obj */
        $result_obj = json_decode($response);

        Yii::info($result_obj);

        if (isset($result_obj) && isset($result_obj->success)) {
            return ['success' => $result_obj->success, 'message' => $result_obj->message];
        }
        return ['success' => false, 'message' => Yii::t('app', 'Có lỗi xảy ra, vui lòng thử lại')];
    }

    public static function changePassword($subscriber, $old_password, $new_password)
    {
        $response = self::call('/account/change-password', [
            'username' => $subscriber->username,
            'old_password' => $old_password,
            'new_password' => $new_password,
        ], [
            'username',
            'old_password',
            'new_password',
        ]);

        /** @var VacResponse $result_obj */
        $result_obj = json_decode($response);

        Yii::info($result_obj);
        Yii::info($result_obj->error);

        if (isset($result_obj) && isset($result_obj->success)) {
            return ['success' => $result_obj->success, 'message' => $result_obj->message, 'error' => $result_obj->error];
        }
        return ['success' => false, 'error' => 0, 'message' => Yii::t('app', 'Có lỗi xảy ra, vui lòng thử lại')];
    }
}