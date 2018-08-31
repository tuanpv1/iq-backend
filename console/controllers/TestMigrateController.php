<?php

namespace console\controllers;

use Yii;
use yii\base\Controller;

/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 08-Feb-17
 * Time: 4:47 PM
 */
class TestMigrateController extends Controller
{

    public function actionRun()
    {
        $this->migrateContentTvod1();
    }

    private function migrateContentTvod1()
    {

        $url = 'https://fe.tvod.vn/apis/api2.0/index.php?r=video/getListVideo&category=14&language=vi&page=1&rows_per_page=1&filter=all';

        $response = self::call($url);

        $array = json_decode($response, true);
        $total_quantity = $array['total_quantity'];
//        $pageCount = round($total_quantity / 100);
        $pageCount = 1;
        $pageRows = 5;
        $results = [];

        for ($i = 1; $i <= $pageCount; $i++) {
            $url = 'https://fe.tvod.vn/apis/api2.0/index.php?r=video/getListVideo&category=14&language=vi&page=' . $i . '&rows_per_page=' . $pageRows . '&filter=all';
            $response_ = self::call($url);

            $array = json_decode($response_, true);

            if ($array['items']) {
                foreach ($array['items'] as $item) {
                    $arr_item = [];
                    if (isset($item['video_id'])) {
                        $arr_item['video_id'] = $item['video_id'];
                    }
                    array_push($results, $arr_item);
                }
            }
        }
        var_dump($results);
        exit;

    }

    private static function call($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

}