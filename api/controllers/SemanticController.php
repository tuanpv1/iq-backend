<?php
/**
 * Created by PhpStorm.
 * User: Hoan
 * Date: 7/22/2016
 * Time: 5:14 PM
 */

namespace api\controllers;


use common\helpers\MyCurl;
use common\helpers\VACHelper;
use common\models\Site;
use Yii;
use yii\base\Exception;
use yii\web\Controller;
use yii\web\Response;

class SemanticController extends Controller
{

    const LANG_VI = 'vi';
    const LANG_EN = 'en';

    const IS_DRAMA = true;
    const IS_VIDEO = false;

    const HEADER_LANGUAGE = 'X-Language';

    /**
     * @param $function
     * @param $params
     * @return mixed|null|string
     */
    private static function call($function, $params)
    {
        $ch = new MyCurl();
        $ch->follow_redirects = true;
        $ch->user_agent = "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) coc_coc_browser/50.0.125 Chrome/44.0.2403.125 Safari/537.36";

        $result = null;
        $url = $function;
        Yii::info('Request params url: ' . $url . implode('&', $params));
        try {
            $response = $ch->get($url, $params);
            Yii::info('Response status: ' . $response);
            Yii::info('Response status: ' . $response->headers['Status-Code']);
            Yii::info('Response body: ' . $response->body);

            return $response->body;
        } catch (Exception $e) {
            Yii::error($e->getMessage());
            return null;
        }

    }

    public function actionFilmSimilar($id, $limit = 20, $offset = 0)
    {
        Yii::$app->params['response_raw'] = true;
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = self::call(Yii::$app->params['semantic_url'] . '/similar/', [
            'limit' => $limit,
            'id' => $id,
            'offset' => $offset,
        ]);
//        $language = Yii::$app->request->headers->get(self::HEADER_LANGUAGE);

        return json_decode($response);
//        $array = json_decode($response);
//        $results = [];
//        if ($array->items) {
//            foreach ($array->items as $item) {
//                $arr_item = [];
//
//                if (isset($item->actor)) {
//                    $arr_item['actor'] = $item->actor;
//                }
//                if (isset($item->year)) {
//                    $arr_item['year'] = $item->year;
//                }
//
//                if (isset($item->director)) {
//                    $arr_item['director'] = $item->director;
//                }
//
//                if (isset($item->description)) {
//                    $arr_item['description'] = $item->description;
//                }
//
//                if (isset($item->id)) {
//                    $arr_item['id'] = $item->id;
//                }
//
//                if (isset($item->pic)) {
//                    $arr_item['pic'] = $item->pic;
//                }
//
//
//                if (isset($item->title)) {
//                    $arr_item['title'] = $item->title;
//                }
//
//                if (isset($item->views)) {
//                    $arr_item['views'] = $item->views;
//                }
//
//
//                if (isset($item->titleVi)) {
//                    $arr_item['titleVi'] = $item->titleVi;
//                }
//
//                if (isset($item->title)) {
//                    $arr_item['titleEn'] = $item->title;
//                }
//
//
//                if ($language == self::LANG_VI) {
//                    if (isset($item->titleVi) && $item->titleVi) {
//                        $arr_item['title'] = $item->titleVi;
//                    }
//                }
//
//                if ($language == self::LANG_EN) {
//                    if (isset($item->title)) {
//                        $arr_item['title'] = $item->title;
//                    }
//                }
//
//
//                array_push($results, $arr_item);
//
//            }
//        }
//        return ['data' => ['total' => $array->total_quantity, 'results' => $results]];
    }

    public function actionFilmAltSearch($limit = 20, $s = '', $offset = 0)
    {
        Yii::$app->params['response_raw'] = true;
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = self::call(Yii::$app->params['semantic_url'] . '/alt-search/', [
            'limit' => $limit,
            's' => $s,
            'offset' => $offset,
        ]);

        return json_decode($response);
    }

    public function actionFilmRecommended()
    {
        Yii::$app->params['response_raw'] = true;
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = self::call(Yii::$app->params['semantic_url'] . '/recommended/', [

        ]);

        return json_decode($response);
    }

    public function actionRecommendContentV2($user_id, $count_recommend)
    {
        Yii::$app->params['response_raw'] = true;
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = self::call(Yii::$app->params['recommend_url'] . '/movielen/recommend/' . $user_id . '/' . $count_recommend, [

        ]);

        $array = json_decode($response);
        $results = [];
        if ($array->items) {
            foreach ($array->items as $item) {
                $arr_item = [];

                if (isset($item->genres)) {
                    $arr_item['genres'] = $item->genres;
                }

                if (isset($item->score)) {
                    $arr_item['score'] = $item->score;
                }

                if (isset($item->id)) {
                    $arr_item['id'] = $item->id;
                }

                if (isset($item->name)) {
                    $arr_item['name'] = $item->name;
                }

                array_push($results, $arr_item);

            }
        }
        return ['data' => ['results' => $results]];
    }

    public function actionFilmAutoComplete($s, $site_id = 0)
    {
        Yii::$app->params['response_raw'] = true;
        Yii::$app->response->format = Response::FORMAT_JSON;

        $language = Yii::$app->request->headers->get(self::HEADER_LANGUAGE);
        if ($site_id > 0) {
            $response = self::call(Yii::$app->params['semantic_url_new'] . 'app/get-list-video-suggestion', [
                "site_id" => $site_id,
                "keyword" => $s
            ]);

        } else {

            $response = self::call(Yii::$app->params['semantic_url_new'] . 'app/get-list-video-suggestion', [
//                "site_id" => $site_id,
                "keyword" => $s
            ]);
//            $response = self::call(Yii::$app->params['semantic_url2'] . '/SearchProxyAPI', [
//                "r" => "video/getListVideoSuggestion",
//                "keyword" => $s
//            ]);

        }

        $array = json_decode($response);
        $results = [];
        if ($array->items) {
            foreach ($array->items as $item) {
                if ($language == self::LANG_EN) {
                    if (isset($item->title) && $item->title) {
                        array_push($results, $item->title);
                    } else {
                        if (isset($item->vienamese_title) && $item->title) {
                            array_push($results, $item->vienamese_title);
                        }
                    }
                }
                if ($language == self::LANG_VI) {
                    if (isset($item->vienamese_title) && $item->vienamese_title) {
                        array_push($results, $item->vienamese_title);
                    } else {
                        if (isset($item->title) && $item->title) {
                            array_push($results, $item->title);
                        }
                    }
                }
            }
        }
        return ['data' => ['results' => $results]];
    }

    public function actionFilmSearch($limit = 20, $s = '', $offset = 0, $site_id = 0)
    {
        Yii::$app->params['response_raw'] = true;
        Yii::$app->response->format = Response::FORMAT_JSON;

        $language = Yii::$app->request->headers->get(self::HEADER_LANGUAGE);

        $page = $offset / $limit + 1;

        if ($site_id > 0) {
            $response = self::call(Yii::$app->params['semantic_url_new'] . 'app/search', [
                "keyword" => $s,
                "page" => $page,
                "rows_per_page" => $limit,
                "site_id" => $site_id,
                "lang" => $language,
            ]);
        } else {
//            $response = self::call(Yii::$app->params['semantic_url2'] . '/SearchProxyAPI', [
//                "r" => "video/search",
//                "keyword" => $s,
//                "page" => $page,
//                "rows_per_page" => $limit,
//            ]);

            $response = self::call(Yii::$app->params['semantic_url_new'] . 'app/search', [
                "keyword" => $s,
                "page" => $page,
                "rows_per_page" => $limit,
//                "site_id" => $site_id
            ]);
        }


        $array = json_decode($response);
        $results = [];
        if ($array->items) {
            foreach ($array->items as $item) {
                $arr_item = [];

                if (isset($item->video_actor)) {
                    $arr_item['actor'] = $item->video_actor;
                }

                if (isset($item->video_director)) {
                    $arr_item['director'] = $item->video_director;
                }

                if (isset($item->video_vietnamese_description)) {
                    $arr_item['description'] = $item->video_vietnamese_description;
                }

                if (isset($item->video_id)) {
                    $arr_item['id'] = $item->video_id;
                }

                if (isset($item->tvod2_id)) {
                    $arr_item['tvod2_id'] = $item->tvod2_id;
                }

                if (isset($item->video_picture_path)) {
                    $arr_item['pic'] = $item->video_picture_path;
                }


                if (isset($item->video_number_views)) {
                    $arr_item['views'] = $item->video_number_views;
                }

                if (isset($item->video_epi)) {
                    $arr_item['epi'] = $item->video_epi;
                }


                if (isset($item->video_english_title)) {
                    $arr_item['title'] = $item->video_english_title;
                }

                if (isset($item->video_english_title)) {
                    $arr_item['titleEn'] = $item->video_english_title;
                }

                if (isset($item->video_vietnamese_title)) {
                    $arr_item['titleVi'] = $item->video_vietnamese_title;
                }

                if ($language == self::LANG_VI) {
                    if (isset($item->video_vietnamese_title) && $item->video_vietnamese_title) {
                        $arr_item['title'] = $item->video_vietnamese_title;
                    }
                }

                if ($language == self::LANG_EN) {
                    if (isset($item->video_english_title)) {
                        $arr_item['title'] = $item->video_english_title;
                    }
                }


                if (isset($item->video_year)) {
                    $arr_item['year'] = $item->video_year;
                } else {
                    $arr_item['year'] = 1970;
                }

                if (isset($item->video_series)) {
                    if ($item->video_series == 0) {
                        $arr_item['type'] = 'singer';
                        $arr_item['isDrama'] = self::IS_VIDEO;

                    } else {
                        $arr_item['type'] = 'series';
                        $arr_item['isDrama'] = self::IS_DRAMA;
                    }
//                    $arr_item['type'] = $item->video_type;
                } else {
                    $arr_item['type'] = 'singer';
                    $arr_item['isDrama'] = self::IS_VIDEO;
                }

                array_push($results, $arr_item);

            }
        }
        return ['data' => ['total' => $array->total_quantity, 'results' => $results]];
    }

    public function actionFilmSearchByActor($limit = 20, $s = '', $offset = 0, $site_id = 0)
    {
        Yii::$app->params['response_raw'] = true;
        Yii::$app->response->format = Response::FORMAT_JSON;

        $page = $offset / $limit;
        if ($site_id > 0) {
            $response = self::call(Yii::$app->params['semantic_url_new'] . 'app/search', [
                "keyword" => $s,
                "page" => $page,
                "rows_per_page" => $limit,
                "site_id" => $site_id
            ]);
        } else {
            $response = self::call(Yii::$app->params['semantic_url_new'] . 'app/search', [
                "keyword" => $s,
                "page" => $page,
                "rows_per_page" => $limit,
                "site_id" => $site_id
            ]);
//            $response = self::call(Yii::$app->params['semantic_url2'] . '/SearchProxyAPI', [
//                "r" => "video/searchByActor",
//                "keyword" => $s,
//                "page" => $page,
//                "rows_per_page" => $limit,
//            ]);
        }


        $array = json_decode($response);
        $results = [];
        if ($array->items) {
            foreach ($array->items as $item) {
                $arr_item = [];

                if (isset($item->video_actor)) {
                    $arr_item['actor'] = $item->video_actor;
                }

                if (isset($item->video_director)) {
                    $arr_item['director'] = $item->video_director;
                }

                if (isset($item->video_vietnamese_description)) {
                    $arr_item['description'] = $item->video_vietnamese_description;
                }

                if (isset($item->video_id)) {
                    $arr_item['id'] = $item->video_id;
                }

                if (isset($item->video_picture_path)) {
                    $arr_item['pic'] = $item->video_picture_path;
                }

                if (isset($item->video_english_title)) {
                    $arr_item['title'] = $item->video_english_title;
                }

                if (isset($item->video_number_views)) {
                    $arr_item['views'] = $item->video_number_views;
                }

                if (isset($item->video_vietnamese_title)) {
                    $arr_item['titleVi'] = $item->video_vietnamese_title;
                }

                if (isset($item->video_year)) {
                    $arr_item['year'] = $item->video_year;
                } else {
                    $arr_item['year'] = 1970;
                }

                if (isset($item->video_type)) {
                    if ($item->video_type == 0) {
                        $arr_item['type'] = 'singer';
                    } else {
                        $arr_item['type'] = 'series';
                    }
                    $arr_item['type'] = $item->video_type;
                } else {
                    $arr_item['type'] = 'singer';
                }

                array_push($results, $arr_item);

            }
        }
        return ['data' => ['total' => $array->total_quantity, 'results' => $results]];
    }


    public function actionTest()
    {
        $primaryServer = Site::findOne(2)->primaryStreamingServer;
        $primaryServer = $primaryServer == null ? null : $primaryServer->id;
        var_dump($primaryServer);
        exit;

        return VACHelper::getUserInfo('AK`n^nD-tvBd4F636GOyVe8QcvQMfKeF', 'film-tvod-version-3-i4outdr1g19thmmn011r-udgfode2_oy.account.vac.com',
            '22:10:AD:06:20:DB:77:F3:B8:20:ED:4A:B4:8A:40:91:2E:6C:D6:F6', 'com.vivas.tvod.film');
    }

    // Start Add 07/02/2017 TuanPV Search Engine

    public function actionSearchEngine($limit = 20, $s = '', $offset = 0, $site_id)
    {

        Yii::$app->params['response_raw'] = true;
        Yii::$app->response->format = Response::FORMAT_JSON;

        $language = Yii::$app->request->headers->get(self::HEADER_LANGUAGE);

        $page = $offset / $limit + 1;

        $response = self::callSearchEngine(Yii::$app->params['semantic_url_search_engine'] .
            'app/search?keyword=' . $s
            . '&page=' . $page
            . '&rows_per_page=' . $limit
            . '&site_id=' . $site_id
        );

        $array = json_decode($response);
        $results = [];
        if ($array->items) {
            foreach ($array->items as $item) {
                $arr_item = [];

                if (isset($item->video_actor)) {
                    $arr_item['actor'] = $item->video_actor;
                }

                if (isset($item->video_director)) {
                    $arr_item['director'] = $item->video_director;
                }

                if (isset($item->video_vietnamese_description)) {
                    $arr_item['description'] = $item->video_vietnamese_description;
                }

                if (isset($item->video_id)) {
                    $arr_item['id'] = $item->video_id;
                }

                if (isset($item->video_picture_path)) {
                    $arr_item['pic'] = $item->video_picture_path;
                }


                if (isset($item->video_number_views)) {
                    $arr_item['views'] = $item->video_number_views;
                }

                if (isset($item->video_epi)) {
                    $arr_item['epi'] = $item->video_epi;
                }


                if (isset($item->video_english_title)) {
                    $arr_item['title'] = $item->video_english_title;
                }

                if (isset($item->video_english_title)) {
                    $arr_item['titleEn'] = $item->video_english_title;
                }

                if (isset($item->video_vietnamese_title)) {
                    $arr_item['titleVi'] = $item->video_vietnamese_title;
                }

                if ($language == self::LANG_VI) {
                    if (isset($item->video_vietnamese_title) && $item->video_vietnamese_title) {
                        $arr_item['title'] = $item->video_vietnamese_title;
                    }
                }

                if ($language == self::LANG_EN) {
                    if (isset($item->video_english_title)) {
                        $arr_item['title'] = $item->video_english_title;
                    }
                }

                if (isset($item->video_year)) {
                    $arr_item['year'] = $item->video_year;
                } else {
                    $arr_item['year'] = 1970;
                }

                if (isset($item->video_series)) {
                    if ($item->video_series == 0) {
                        $arr_item['type'] = 'singer';
                        $arr_item['isDrama'] = self::IS_VIDEO;

                    } else {
                        $arr_item['type'] = 'series';
                        $arr_item['isDrama'] = self::IS_DRAMA;
                    }
//                    $arr_item['type'] = $item->video_type;
                } else {
                    $arr_item['type'] = 'singer';
                    $arr_item['isDrama'] = self::IS_VIDEO;
                }

                array_push($results, $arr_item);

            }
        }
        return ['data' => ['total' => $array->total_quantity, 'results' => $results]];
    }

    public function actionAutoCompleteSearchEngine($s, $site_id)
    {
        Yii::$app->params['response_raw'] = true;
        Yii::$app->response->format = Response::FORMAT_JSON;

        $language = Yii::$app->request->headers->get(self::HEADER_LANGUAGE);

        $response = self::callSearchEngine(Yii::$app->params['semantic_url_search_engine']
            . 'app/get-list-video-suggestion?site_id=' . $site_id
            . '&keyword=' . $s);

        $array = json_decode($response);
        $results = [];
        if ($array->items) {
            foreach ($array->items as $item) {
                if ($language == self::LANG_EN) {
                    if (isset($item->title) && $item->title) {
                        array_push($results, $item->title);
                    } else {
                        if (isset($item->vienamese_title) && $item->title) {
                            array_push($results, $item->vienamese_title);
                        }
                    }
                }
                if ($language == self::LANG_VI) {
                    if (isset($item->vienamese_title) && $item->vienamese_title) {
                        array_push($results, $item->vienamese_title);
                    } else {
                        if (isset($item->title) && $item->title) {
                            array_push($results, $item->title);
                        }
                    }
                }
            }
        }
        return ['data' => ['results' => $results]];
    }

    private static function callSearchEngine($function)
    {
        $ch = new MyCurl();
        $ch->follow_redirects = true;
        $ch->user_agent = "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) coc_coc_browser/50.0.125 Chrome/44.0.2403.125 Safari/537.36";
        $url = $function;
        Yii::info('Request params url: ' . $url);
        try {
            $response = $ch->get($url);
            Yii::info('Response status: ' . $response);
            Yii::info('Response status: ' . $response->headers['Status-Code']);
            Yii::info('Response body: ' . $response->body);

            return $response->body;
        } catch (Exception $e) {
            Yii::error($e->getMessage());
            return null;
        }

    }

    // End Add TuanPV
}