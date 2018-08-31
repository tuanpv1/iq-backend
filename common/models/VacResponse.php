<?php
/**
 * Created by PhpStorm.
 * User: Hoan
 * Date: 12/29/2016
 * Time: 10:15 AM
 */

namespace common\models;


class VacResponse {
    public $success = false;
    public $error= 0;
    public $message = '';
    public $vivas_sub_id = 0;
    public $expired_at = '';
    public $email = '';
    public $fullname = '';
    public $username = '';
    public $phone_number = '';
    public $address = '';
    public $province = '';
}