<?php
/**
 * Created by PhpStorm.
 * User: Thuc
 * Date: 5/28/2015
 * Time: 3:14 PM
 */

$headers =[];

if (function_exists('getallheaders')) {
    $headers = getallheaders();
} elseif (function_exists('http_get_request_headers')) {
    $headers = http_get_request_headers();
} else {
    foreach ($_SERVER as $name => $value) {
        if (strncmp($name, 'HTTP_', 5) === 0) {
            $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
            $headers[$name] = $value;
        }
    }
}

$lcHeaders = [];
foreach ($headers as $name => $value) {
    $lcHeaders[strtolower($name)] = $value;
}

$headers = $lcHeaders;

header("Content-Type: text/plain");

var_dump($headers);