<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 25/02/2015
 * Time: 9:03 AM
 */

namespace api\models;

use common\models\ActorDirector;
use common\models\BaseLogicCampaign;
use common\models\ContentActorDirectorAsm;
use common\models\ContentAttributeValue;
use common\models\ContentCategoryAsm;
use common\models\ContentProfile;
use common\models\ContentProfileSiteAsm;
use common\models\Notification;
use common\models\Subscriber;
use common\models\User;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\Url;

class ContentDetail extends \common\models\Content
{
    public $episode_count_asm = 0;

    public function fields()
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $fields = parent::fields();
//        unset($fields['tvod1_id']);
        unset($fields['version_code']);
        unset($fields['version']);
        /** Bỏ 2 trường này thừa thiết kế */
        unset($fields['actor']);
        unset($fields['director']);
//        unset($fields['episode_count']);

        $fields['contentAttributeValues'] = function ($model) {
            $lst = [];
            /**@var $model Content */
            if ($model->type != Content::TYPE_LIVE && $model->type != \common\models\Content::TYPE_LIVE_CONTENT) {
                $contentAttributeValues = ContentAttributeValue::find()
                    ->joinWith('contentAttribute')
                    ->andWhere(['content_id' => $model->id])->orderBy(['content_attribute.order' => SORT_DESC])->all();

                foreach ($contentAttributeValues as $contentAttributeValue) {
                    /** @var  $contentAttributeValue ContentAttributeValue */
                    $item = [];
                    $item['id'] = $contentAttributeValue->id;
                    $item['content_id'] = $contentAttributeValue->content_id;
                    $item['content_attribute_id'] = $contentAttributeValue->contentAttribute->id;
                    $item['content_attribute_name'] = $contentAttributeValue->contentAttribute->name;
                    $item['value'] = $contentAttributeValue->value;
                    $lst[] = $item;
                }
            }
            return $lst;
        };

        $fields['episode_count'] = function ($model) use ($user) {
            /* @var $model \api\models\Content */
//            return $user ? $model->getEpisodeCount($user->site_id) : 0;
            return $model->episode_count_asm;
        };

        $fields['image'] = function ($model) {
            /* @var $model Content */
            $link = '';
            if (!$model->images) {
                return null;
            }
            $listImages = Content::convertJsonToArray($model->images);
            foreach ($listImages as $key => $row) {
                if ($row['type'] == Content::IMAGE_TYPE_THUMBNAIL) {
                    $link = Url::to(Yii::getAlias('@web') . DIRECTORY_SEPARATOR . Yii::getAlias('@content_images') . DIRECTORY_SEPARATOR . $row['name'], true);
                }
            }
            return $link;
        };
        $fields['images'] = function ($model) {
            /* @var $model Content */
            $link = [];
            if (!$model->images) {
                return null;
            }
            $listImages = Content::convertJsonToArray($model->images);
            foreach ($listImages as $key => $row) {
                $link[] = [
                    'link' => Url::to(Yii::getAlias('@web') . DIRECTORY_SEPARATOR . Yii::getAlias('@content_images') . DIRECTORY_SEPARATOR . $row['name'], true),
//                    'link' => Yii::getAlias('@web').DIRECTORY_SEPARATOR.Yii::getAlias('@content_images') . DIRECTORY_SEPARATOR . $row['name'],
                    'type' => $row['type']
                ];
            }
            return $link;
        };

        $fields['categories'] = function ($model) {
            /* @var $model \common\models\Content */
            if ($model->type != Content::TYPE_LIVE && $model->type != \common\models\Content::TYPE_LIVE_CONTENT) {
                $cache = Yii::$app->cache;
                $key = Yii::$app->params['key_cache']['ContentCategories'] . $model->id;
                $temp = $cache->get($key);

                if ($temp === false) {
                    $categoryAsms = $model->contentCategoryAsms;
                    foreach ($categoryAsms as $asm) {
                        /** @var $asm ContentCategoryAsm */
                        $temp .= $asm->category->id . ',';
                    }
                    if (strlen($temp) > 2) {
                        $temp = substr($temp, 0, -1);
                    }

                    $cache->set($key, $temp, Yii::$app->params['time_expire_cache'], new TagDependency(['tags' => Yii::$app->params['key_cache']['ContentCategories']]));
                }
                return $temp;
            } else {
                return "";
            }
        };

        $fields['category_id'] = function ($model) {
            /* @var $model \common\models\Content */
            if ($model->type != Content::TYPE_LIVE && $model->type != \common\models\Content::TYPE_LIVE_CONTENT) {
                $cache = Yii::$app->cache;
                $key = Yii::$app->params['key_cache']['ContentCategoryID'] . $model->id;
                $category_id = $cache->get($key);
                if ($category_id === false) {
                    $categoryAsms = $model->contentCategoryAsms;
                    $category_id = null;
                    foreach ($categoryAsms as $asm) {
                        /** @var $asm ContentCategoryAsm */
                        if ($asm->category->is_content_service == 0) {
                            $category_id = $asm->category->id;
                            break;
                        }
                    }

                    $cache->set($key, $category_id, Yii::$app->params['time_expire_cache'], new TagDependency(['tags' => Yii::$app->params['key_cache']['ContentCategoryID']]));
                }

                return $category_id;
            } else {
                return 0;
            }
        };

        $fields['is_favorite'] = function ($model) {
            /** @var  $subscriber Subscriber */
            $subscriber = Yii::$app->user->identity;
            if (!$subscriber) {
                return false;
            }
            $sf = SubscriberFavorite::getFavorite($subscriber->id, $model->id);
            if (!$sf) {
                return false;
            }
            return true;
        };
        /** Check free hay không */
        $fields['is_free'] = function ($model) {
            /** @var User $user */
            $user = Yii::$app->user->identity;
            if (!$user) {
                $site_id = Yii::$app->params['site_id'];
            } else {
                $site_id = $user->site_id;
            }

            /* @var $model Content */
            if ($model->type != Content::TYPE_LIVE && $model->type != \common\models\Content::TYPE_LIVE_CONTENT) {
                $cache = Yii::$app->cache;
                $key = Yii::$app->params['key_cache']['ContentIsFree'] . $model->id . "_" . $site_id;
                $price = $cache->get($key);
                if ($price === false) {
                    $price = $model->getIsFree($site_id);

                    $cache->set($key, $price, Yii::$app->params['time_expire_cache'], new TagDependency(['tags' => Yii::$app->params['key_cache']['ContentIsFree']]));
                }

                return $price;
            }
            return Content::IS_FREE;
        };


        /** Nếu là free thì không hiển thị giá */
//        if ($this->is_free == \common\models\Content::NOT_FREE) {
        $fields['price_coin'] = function ($model) {
            /** @var User $user */
            $user = Yii::$app->user->identity;
            if (!$user) {
                $site_id = Yii::$app->params['site_id'];
            } else {
                $site_id = $user->site_id;
            }

            /* @var $model Content */
            if ($model->type != \common\models\Content::TYPE_LIVE_CONTENT) {
                $cache = Yii::$app->cache;
                $key = Yii::$app->params['key_cache']['ContentPriceCoin'] . $model->id . "_" . $site_id;
                $price = $cache->get($key);
                if ($price === false) {
                    $price = $model->getPriceCoin($site_id);

                    $cache->set($key, $price, Yii::$app->params['time_expire_cache'], new TagDependency(['tags' => Yii::$app->params['key_cache']['ContentPriceCoin']]));
                }
                return $price;
            } else {
                return 0;
            }
        };
        $fields['price_sms'] = function ($model) {
            /* @var $model Content */
            //return $model->getPriceSms(Yii::$app->params['site_id']);
            return 0;
        };
        $fields['watching_period'] = function ($model) {
            /* @var $model Content */
//            return $model->getWatchingPriod(Yii::$app->params['site_id']);
            return 0;
        };
//        }

        $fields['qualities'] = function ($model) {
            /** @var \common\models\Content $model */
            $cache = Yii::$app->cache;
            $key = Yii::$app->params['key_cache']['ContentQualities'] . $model->id;
            $str = $cache->get($key);

            if ($str === false) {
                $str = "";
                $site_id = Yii::$app->params['site_id'];
                $contentProfiles = ContentProfile::find()->andWhere(['content_id' => $model->id])->all();
                foreach ($contentProfiles as $contentProfile) {
                    $contentProfileSiteAsm = ContentProfileSiteAsm::findOne(['content_profile_id' => $contentProfile->id, 'site_id' => $site_id, 'status' => ContentProfileSiteAsm::STATUS_ACTIVE]);

                    /** Nếu content_profile không thuộc site thì bỏ qua */
                    if (!$contentProfileSiteAsm) {
                        continue;
                    }

                    /** Get object content_profile để xử lí*/
                    $cp = ContentProfile::findOne(['id' => $contentProfileSiteAsm->content_profile_id]);
                    if ($cp) {
                        $str .= $cp->quality . ',';
                    }
                }
                if (strlen($str) >= 2) {
                    $str = substr($str, 0, -1);
                }
                $cache->set($key, $str, Yii::$app->params['time_expire_cache'], new TagDependency(['tags' => Yii::$app->params['key_cache']['ContentQualities']]));
            }
            return $str;
        };

        $fields['purchased'] = function ($model) {
            /** @var  $subscriber Subscriber */
            $subscriber = Yii::$app->user->identity;
            if (!$subscriber) {
                Yii::info($model->display_name, "Không tồn tại user");
                return false;
            }
            if ($subscriber->authen_type == Subscriber::AUTHEN_TYPE_MAC_ADDRESS) {
                Yii::info($model->display_name, "Là user mac");
                return false;
            }
            /** Check xem người dùng đã mua nội dung này chưa hoặc đã mua gói cước chứa nội dung này không */
            $isCheck = Subscriber::validatePurchasing($subscriber->id, $this->id);
            return $isCheck;
        };
        /** Lấy cả đạo diễn, diễn viên cho content */
        if ($this->type == \common\models\Content::TYPE_KARAOKE) {
            $fields['actors'] = function ($model) {
                /* @var $model \common\models\Content */
                $items = $model->contentActorDirectorAsms;
                $temp = "";
                if (!$items) {
                    return $temp;
                }
                foreach ($items as $item) {
                    /** @var $item ContentActorDirectorAsm */
                    if (!$item->actorDirector) {
                        continue;
                    }
                    if ($item->actorDirector->type == ActorDirector::TYPE_ACTOR) {
                        $temp .= $item->actorDirector->id . ', ';
                    }

                }
                if (strlen($temp) >= 2) {
                    $temp = substr($temp, 0, -2);
                }

                return $temp;
            };

            $fields['directors'] = function ($model) {
                /* @var $model \common\models\Content */
                $items = $model->contentActorDirectorAsms;
                $temp = "";
                if (!$items) {
                    return $temp;
                }
                foreach ($items as $item) {
                    /** @var $item ContentActorDirectorAsm */
                    if (!$item->actorDirector) {
                        continue;
                    }
                    if ($item->actorDirector->type == ActorDirector::TYPE_DIRECTOR) {
                        $temp .= $item->actorDirector->id . ', ';
                    }
                }
                if (strlen($temp) >= 2) {
                    $temp = substr($temp, 0, -2);
                }

                return $temp;

            };
        } else {
            $fields['actors'] = function ($model) {
                /* @var $model \common\models\Content */
                $temp = "";
                if ($model->type != Content::TYPE_LIVE && $model->type != \common\models\Content::TYPE_LIVE_CONTENT) {
                    $cache = Yii::$app->cache;
                    $key = Yii::$app->params['key_cache']['ContentActors'] . $model->id;
                    $temp = $cache->get($key);

                    if ($temp === false) {
                        $temp = "";
                        $items = $model->contentActorDirectorAsms;
                        if (!$items) {
                            return $temp;
                        }
                        foreach ($items as $item) {
                            /** @var $item ContentActorDirectorAsm */
                            if (!$item->actorDirector) {
                                continue;
                            }
                            if ($item->actorDirector->type == ActorDirector::TYPE_ACTOR) {
                                $temp .= $item->actorDirector->name . ', ';
                            }
                        }
                        if (strlen($temp) >= 2) {
                            $temp = substr($temp, 0, -2);
                        }

                        $cache->set($key, $temp, Yii::$app->params['time_expire_cache'], new TagDependency(['tags' => Yii::$app->params['key_cache']['ContentActors']]));
                    }

                }
                return $temp;
            };

            $fields['directors'] = function ($model) {
                /* @var $model \common\models\Content */
                $temp = "";
                if ($model->type != Content::TYPE_LIVE && $model->type != \common\models\Content::TYPE_LIVE_CONTENT) {
                    $cache = Yii::$app->cache;
                    $key = Yii::$app->params['key_cache']['ContentDirectors'] . $model->id;
                    $temp = $cache->get($key);
                    if ($temp === false) {
                        $temp = "";
                        $items = $model->contentActorDirectorAsms;
                        if (!$items) {
                            return $temp;
                        }
                        foreach ($items as $item) {
                            /** @var $item ContentActorDirectorAsm */
                            if (!$item->actorDirector) {
                                continue;
                            }
                            if ($item->actorDirector->type == ActorDirector::TYPE_DIRECTOR) {
                                $temp .= $item->actorDirector->name . ', ';
                            }
                        }
                        if (strlen($temp) >= 2) {
                            $temp = substr($temp, 0, -2);
                        }

                        $cache->set($key, $temp, Yii::$app->params['time_expire_cache'], new TagDependency(['tags' => Yii::$app->params['key_cache']['ContentDirectors']]));
                    }

                }
                return $temp;
            };
        }

        //check content co thuoc chien dich hay khong null la khong co se tra ve data
        $fields['isCampaign'] = function ($model) {
            /** @var  $subscriber Subscriber */
            /* @var $model \common\models\Content */
            $subscriber = Yii::$app->user->identity;
            if (!$subscriber) {
                Yii::info($model->display_name, "Không tồn tại user");
                return null;
            }
            $campaign = BaseLogicCampaign::getCampaignActiveUser($subscriber, Yii::$app->params['site_id'], $model->id);
            if (!$campaign || $subscriber->is_active) {
                return false;
            }
            return true;
        };

        //hien thi popup message CR31 thong bao kich hoat
        $fields['message_popup'] = function ($model) {
            $message = Notification::findOne(['name' => 'ĐK02']);
            if ($message) {
                return $message->content;
            }
            return false;
        };


        return $fields;
    }

    public function extraFields()
    {
        return ['contentAttributeValues'];
    }


}