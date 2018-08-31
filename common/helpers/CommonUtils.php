<?php
/**
 *
 * @author Nguyen Chi Thuc
 */

namespace common\helpers;

use Yii;

class CommonUtils
{
    public static function pre($content)
    {
        echo '<pre>';
        print_r($content);
        echo '</pre>';
        die;
    }

    public static function rrmdir($path)
    {
        $path = rtrim($path, '/') . '/';

        // Remove all child files and directories.
        $items = glob($path . '*');

        foreach ($items as $item) {
            is_dir($item) ? self::rrmdir($item) : unlink($item);
        }

        // Remove directory.
        rmdir($path);
    }

    public static function getListParent($item, &$result = [])
    {
        if ($item->parent === null) {
            return $result;
        } else {
            if (!in_array($item->parent->id, $result)) {
                $result[] = $item->parent->id;
                CommonUtils::getListParent($item->parent, $result);
            }
            return $result;
        }
    }

    public static function columnLabel($value, $data)
    {
        if (array_key_exists($value, $data)) {
            return $data[$value];
        }
        return $value;
    }

    public static function displayDate($ts, $format = "d/m/Y")
    {
        if (!$ts) return '';
        $date = new \DateTime("@$ts");
        return $date->format($format);
    }

    public static function displayDateTime($ts, $format = "d/m/Y , H:i:s")
    {
        if (!$ts) return '';
        $date = new \DateTime("@$ts");
        return $date->format($format);
    }

    public static function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }

    public static function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

    /**
     *
     * @param string $mobileNumber
     * @param int type format: 0: format 84xxx, 1: format 0xxxx, 2: format xxxx
     * @return String valid mobile
     */
    public static function validateMobile($mobileNumber, $typeFormat = 0)
    {
        $valid_number = '';

        // Remove string "+"
        $mobileNumber = str_replace('+84', '84', $mobileNumber);
        if (substr($mobileNumber, 0, 1) != '0') {
            if (substr($mobileNumber, 0, 2) != '84') {
                $mobileNumber = "0" . $mobileNumber;
            }
        }

        //TODO: for testing: dung so dung cua VMS goi qua charging test ko thanh cong
        if (preg_match('/^(84|0)(987878787)$/', $mobileNumber, $matches)) {
            return "84987878787";
        }
        /* chưa đầy đủ các đầu số */
        if (preg_match('/^(84|0)(91|94|098|097|123|124|125|127|129|164|166|167|169|)\d{7}$/', $mobileNumber, $matches)) {
            /**
             * $typeFormat == 0: 8491xxxxxx
             * $typeFormat == 1: 091xxxxxx
             * $typeFormat == 2: 91xxxxxx
             */
            if ($typeFormat == 0) {
                if ($matches[1] == '0' || $matches[1] == '') {
                    $valid_number = preg_replace('/^(0|)/', '84', $mobileNumber);
                } else {
                    $valid_number = $mobileNumber;
                }
            } else if ($typeFormat == 1) {
                if ($matches[1] == '84' || $matches[1] == '') {
                    $valid_number = preg_replace('/^(84|)/', '0', $mobileNumber);
                } else {
                    $valid_number = $mobileNumber;
                }
            } else if ($typeFormat == 2) {
                if ($matches[1] == '84' || $matches[1] == '0') {
                    $valid_number = preg_replace('/^(84|0)/', '', $mobileNumber);
                } else {
                    $valid_number = $mobileNumber;
                }
            }

        }
        return $valid_number;
    }

    public static function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENTIP'])) {
            return $_SERVER['HTTP_CLIENTIP'];
        }

        if (!empty($_SERVER['X_REAL_ADDR'])) {
            return $_SERVER['X_REAL_ADDR'];
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(':', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return $ips[0];
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return gethostbyname(gethostname()); // tra ve ip local khi chay CLI
    }

    public static function formatNumber($number)
    {
        $formatter = new \yii\i18n\Formatter();
        $formatter->thousandSeparator = ',';
        $formatter->decimalSeparator = '.';
//       return number_format($number, 2, '.', ',');
        return $formatter->asInteger($number);
    }

    public static function getBeginOfDay($timestamp) {
        return strtotime("midnight", $timestamp);
    }

    public static function getEndOfDay($timestamp) {
        $beginOfDay = strtotime("midnight", $timestamp);
        return strtotime("tomorrow", $beginOfDay) - 1;
    }

    public static function validateEmail($email){
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
