<?php
/**
 * Description of AuthUtils
 *
 * @author Tran Bac Son
 */

namespace common\helpers;

use Yii;
use phpCAS;
use yii\helpers\Url;
use yii\web\Session;

class AuthUtils {
    /**
     * dia chi ip local cua con nay la 10.1.10.80, can tro static host 10.1.10.80 den vinaphone.com.vn
     */
    const cas_host = 'vinaphone.com.vn/auth';
    const cas_port = 443;
    const cas_context = '/';

    public static function casAuth(){
        $cas_real_hosts = array (
            'vinaphone.com.vn'
        );
//        $local_auth_url = 'http://fe.skmn.lc/index.php?r=gw/login';
//        $vinaphone_auth_url = 'https://vinaphone.com.vn/auth/login?service='.urlencode($local_auth_url);

        phpCAS::client(CAS_VERSION_2_0, static::cas_host, static::cas_port, static::cas_context, false);
        phpCAS::setNoCasServerValidation();
        phpCAS::forceAuthentication();

        if (phpCAS::isAuthenticated()) {
            $msisdn = phpCAS::getUser();
            $session = Yii::$app->session;
            $session->set("MSISDN", $msisdn);
            $session->set("CAS", true);
            return $msisdn;
        }
        return null;
    }

    public static function casLogout($url){
        $session = Yii::$app->session;
        $session->remove('MSISDN');
        $session->remove('CAS');
        $session->remove('phpCAS');
//        self::casAuth();
        phpCAS::client(CAS_VERSION_2_0, static::cas_host, static::cas_port, static::cas_context, false);
        phpCAS::setNoCasServerValidation();
//        phpCAS::forceAuthentication();
//        if (phpCAS::isAuthenticated()) {
            phpCAS::logout(["service" => $url]);
//        }
    }
}
