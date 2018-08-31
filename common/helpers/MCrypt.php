<?php

namespace common\helpers;

/**
 * Class MCrypt
 * encrypt: $encrypted = $mcrypt->encrypt(pkcs5_pad("Text to Encrypt"), $iv, $key);
 * decrypt: $decrypted = pkcs5_unpad($mcrypt->decrypt($encrypted), $iv, $key);
 * @package common\helpers
 * @author gthuc.nguyen@gmail.com
 */
class MCrypt
{
    const MASTER_KEY_1 = '8768f1ff177d8341';
    const MASTER_KEY_2 = 'bcc40a7210af50e9';

    //how to use *** begin
    // ENCRYPT example: $encrypted = $mcrypt->encrypt(pkcs5_pad("Text to Encrypt"));
    // DECRYPT example: $decrypted = pkcs5_unpad($mcrypt->decrypt($encrypted));
    //how to use *** end
    public static $DEFAULT_IV = MCrypt::MASTER_KEY_1;
    public static $DEFAULT_KEY = MCrypt::MASTER_KEY_2;


    function  __construct()
    {

    }

    public static function generateSecretKey($secret, $str) {
        $res = "";
        $md5 = md5($secret . $str, true);

        $odd = ord($md5[0]) % 2;

        for ($i = 0; $i < strlen($md5)/2; $i++) {
            $res .= $md5[$i * 2 + $odd];
        }

        return bin2hex($res);
    }

    public static function  encrypt($str, $iv = "", $key = "")
    {
        //$key = $this->hex2bin($key);
        if (!$iv) {
            $iv = MCrypt::$DEFAULT_IV;
        }

        if (!$key) {
            $key = MCrypt::$DEFAULT_KEY;
        }

        $td = mcrypt_module_open('rijndael-128', '', 'cbc', $iv);

        mcrypt_generic_init($td, $key, $iv);
        $encrypted = mcrypt_generic($td, $str);

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return bin2hex($encrypted);
    }

    public static function decrypt($code, $iv = "", $key = "")
    {
        //$key = $this->hex2bin($key);
        $code = MCrypt::hex2bin($code);

        if (!$iv) {
            $iv = MCrypt::$DEFAULT_IV;
        }

        if (!$key) {
            $key = MCrypt::$DEFAULT_KEY;
        }

        $td = mcrypt_module_open('rijndael-128', '', 'cbc', $iv);

        mcrypt_generic_init($td, $key, $iv);
        $decrypted = mdecrypt_generic($td, $code);

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return utf8_encode(trim($decrypted));
    }

    protected static function hex2bin($hexdata)
    {
        $bindata = '';

        for ($i = 0; $i < strlen($hexdata); $i += 2) {
            $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
        }

        return $bindata;
    }

    public static function pkcs5_pad($text)
    {
        $blocksize = 16;
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public static function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
        return substr($text, 0, -1 * $pad);
    }
}

?>
