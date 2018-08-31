<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Html;

/**
 * This is the model class for table "user_activity".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $username
 * @property string $ip_address
 * @property string $user_agent
 * @property string $action
 * @property integer $target_id
 * @property integer $target_type
 * @property integer $created_at
 * @property string $description
 * @property string $status
 * @property integer $site_id
 * @property integer $dealer_id
 * @property string $request_detail
 * @property string $request_params
 *
 * @property Dealer $dealer
 * @property Site $site
 * @property User $user
 */
class UserActivity extends \yii\db\ActiveRecord
{
    const ACTION_TARGET_TYPE_USER              = 1;
    const ACTION_TARGET_TYPE_CAT               = 2;
    const ACTION_TARGET_TYPE_CONTENT           = 3;
    const ACTION_TARGET_TYPE_CONTENT_PROFILE   = 4;
    const ACTION_TARGET_TYPE_SERVICE_PROVIDER  = 5;
    const ACTION_TARGET_TYPE_DEALER            = 6;
    const ACTION_TARGET_TYPE_SERVICE_GROUP     = 7;
    const ACTION_TARGET_TYPE_SERVICE           = 8;
    const ACTION_TARGET_TYPE_CREDENTIAL        = 9;
    const ACTION_TARGET_TYPE_DEVICE            = 10;
    const ACTION_TARGET_TYPE_OTHER             = 12;
    const ACTION_TARGET_TYPE_ACTOR_DIRECTOR    = 13;
    const ACTION_TARGET_TYPE_CONTENT_ATTRIBUTE = 14;
    const ACTION_TARGET_TYPE_REPORT            = 15;
    const ACTION_TARGET_TYPE_STREAMING_SERVER  = 16;
    const ACTION_TARGET_TYPE_RBAC_BE           = 17;
    const ACTION_TARGET_TYPE_RBAC_SP           = 18;
    const ACTION_TARGET_TYPE_RBAC_CP           = 19;
    const ACTION_TARGET_TYPE_ADS               = 20;
    const ACTION_TARGET_TYPE_APP_ADS           = 21;
    const ACTION_TARGET_TYPE_SUBSCRIBER        = 22;
    const ACTION_TARGET_TYPE_PRICING           = 23;
    const ACTION_TARGET_TYPE_CONTENT_LOG       = 24;
    const ACTION_TARGET_TYPE_CONTENT_FEEDBACK  = 25;
    const ACTION_TARGET_TYPE_MULTILANGUAGE     = 26;
    const ACTION_TARGET_TYPE_CONFIG_API        = 27;

    // public static $action_targets = [
    //     self::ACTION_TARGET_TYPE_USER              => 'Người dùng',
    //     self::ACTION_TARGET_TYPE_CAT               => 'Quản lý Danh mục',
    //     self::ACTION_TARGET_TYPE_CONTENT           => 'Quản lý Nội dung',
    //     self::ACTION_TARGET_TYPE_CONTENT_PROFILE   => 'File Nội dung',
    //     self::ACTION_TARGET_TYPE_SERVICE_PROVIDER  => 'Quản lý nhà cung cấp dịch vụ',
    //     self::ACTION_TARGET_TYPE_DEALER            => 'Đại lý',
    //     self::ACTION_TARGET_TYPE_SERVICE_GROUP     => 'Quản lý nhóm gói cước',
    //     self::ACTION_TARGET_TYPE_SERVICE           => 'Quản lý Gói cước',
    //     self::ACTION_TARGET_TYPE_CREDENTIAL        => 'API Key',
    //     self::ACTION_TARGET_TYPE_DEVICE            => 'Thiết bị',
    //     self::ACTION_TARGET_TYPE_OTHER             => 'Other',
    //     self::ACTION_TARGET_TYPE_ACTOR_DIRECTOR    => 'Quản lý diễn viên/đạo diễn, ca sĩ/nhạc sĩ',
    //     self::ACTION_TARGET_TYPE_CONTENT_ATTRIBUTE => 'Thuộc tính nội dung',
    //     self::ACTION_TARGET_TYPE_REPORT            => 'Báo cáo',
    //     self::ACTION_TARGET_TYPE_STREAMING_SERVER  => 'Quản lý địa chỉ phân phối nội dung',
    //     self::ACTION_TARGET_TYPE_RBAC_BE           => 'Quản lý quyền, nhóm quyền backend',
    //     self::ACTION_TARGET_TYPE_RBAC_SP           => 'Quản lý quyền, nhóm quyền nhà cung cấp dịch vụ',
    //     self::ACTION_TARGET_TYPE_RBAC_CP           => 'Quản lý quyền, nhóm quyền đại lý',
    //     self::ACTION_TARGET_TYPE_ADS               => 'Quản lý quảng cáo',
    //     self::ACTION_TARGET_TYPE_APP_ADS           => 'Quản lý app quảng cáo',
    //     self::ACTION_TARGET_TYPE_SUBSCRIBER        => 'Quản lý thuê bao',
    //     self::ACTION_TARGET_TYPE_PRICING           => 'Quản lý giá',
    //     self::ACTION_TARGET_TYPE_CONTENT_LOG       => 'Content log',
    //     self::ACTION_TARGET_TYPE_CONTENT_FEEDBACK  => 'Content feedback',
    // ];

    public $is_admin;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_activity';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'target_id', 'target_type', 'created_at', 'site_id', 'dealer_id'], 'integer'],
            [['description', 'request_params'], 'string'],
            [['username', 'user_agent', 'status'], 'string', 'max' => 255],
            [['ip_address'], 'string', 'max' => 45],
            [['action'], 'string', 'max' => 126],
            [['request_detail'], 'string', 'max' => 256],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'             => Yii::t('app', 'ID'),
            'user_id'        => Yii::t('app', 'User ID'),
            'username'       => Yii::t('app', 'Tên đăng nhâpj'),
            'ip_address'     => Yii::t('app', 'Địa chỉ IP'),
            'user_agent'     => Yii::t('app', 'User Agent'),
            'action'         => Yii::t('app', 'Hành động'),
            'target_id'      => Yii::t('app', 'Target ID'),
            'target_type'    => Yii::t('app', 'Target Type'),
            'created_at'     => Yii::t('app', 'Ngày tạo'),
            'description'    => Yii::t('app', 'Mô tả'),
            'status'         => Yii::t('app', 'trạng thái'),
            'site_id'        => Yii::t('app', 'Site ID'),
            'dealer_id'      => Yii::t('app', 'ID Đại lý'),
            'request_detail' => Yii::t('app', 'Request Detail'),
            'request_params' => Yii::t('app', 'Request Params'),
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'created_at',
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDealer()
    {
        return $this->hasOne(Dealer::className(), ['id' => 'dealer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }


    public function getContentParamsDetail()
    {
        $params = json_decode($this->request_params, true);
        $params = isset($params['Content']) ? $params['Content'] : $params;
        $params = isset($params[0]) ? $params[0] : $params;
        if (isset($params['images'])) {
            $params = $this->displayImages($params);
        }

        foreach ($params as $key => $value) {
            $params[$key] = $this->requestValue($key, $value);
        }

        return $this->pp($params);
    }

    protected function pp($arr)
    {
        $content       = new Content();
        $content->type = isset($arr['type']) ? $arr['type'] : Content::TYPE_VIDEO;
        $label         = $content->attributeLabels();

        $retStr = '<ul>';
        if (is_array($arr)) {
            foreach ($arr as $key => $val) {
                $reLabel = isset($label[$key]) ? $label[$key] : $key;
                if (is_array($val)) {
                    $retStr .= '<li>' . $reLabel . ' => ' . $this->pp($val) . '</li>';
                } else {
                    $retStr .= '<li>' . $reLabel . ' => ' . $val . '</li>';
                }
            }
        }
        $retStr .= '</ul>';
        return $retStr;
    }

    protected function displayImages($params)
    {
        $images = json_decode($params['images'], true);
        if (is_array($images)) {
            foreach ($images as $n) {
                $n    = is_array($n) ? $n : json_decode($n, true);
                $link = Yii::getAlias('@web') . DIRECTORY_SEPARATOR . Yii::getAlias('@content_images') . DIRECTORY_SEPARATOR . $n['name'];
                $link = Html::img($link, ['class' => 'file-preview-image']);
                if ($n['type'] == Content::IMAGE_TYPE_THUMBNAIL) {
                    $params['thumbnail'][] = $link;
                } elseif ($n['type'] == Content::IMAGE_TYPE_SCREENSHOOT) {
                    $params['screenshoot'][] = $link;
                }
            }
        } else {
            \Yii::info(json_encode($images), 'Content Log Images');
        }

        return $params;
    }

    protected function requestValue($k, $v)
    {
        switch ($k) {
            case 'status':
                if ($v) {
                    if (!$this->is_admin) {
                        // var_dump($this->is_admin);die;
                        return ContentSiteAsm::listStatusSP()[$v];
                    } else {
                        return Content::getListStatus()[$v];

                    }
                }

                break;
            // case 'type':
            // return Content::listType()[$v];
            // break;
            case 'honor':
                if ($v) {
                    return Content::$list_honor[$v];
                }

                break;
            case 'is_series':
                if ($v) {
                    return Content::$filmType[$v];
                }

                break;
            case 'default_site_id':
                if ($v) {
                    return Site::findOne($v)->name;
                }

                break;
            case 'site_id':
                if ($v) {
                    return Site::findOne($v)->name;
                }

                break;
            case 'content_id':
                if ($v) {
                    return Content::findOne($v)->display_name;
                }

                break;
            case 'assignment_sites':
                if (is_array($v)) {
                    return array_map(function ($v) {
                        return Site::findOne($v)->name;
                    }, $v);
                }

                break;
            case 'list_cat_id':
                if ($v) {
                    return array_map(function ($v) {
                        return Category::findOne($v)->display_name;
                    }, explode(',', $v));
                }

                break;
            case 'content_directors':
                if (is_array($v)) {
                    return array_map(function ($v) {
                        return ActorDirector::findOne($v)->name;
                    }, $v);
                }

                break;
            case 'content_actors':
                if (is_array($v)) {
                    return array_map(function ($v) {
                        return ActorDirector::findOne($v)->name;
                    }, $v);
                }

                break;
            case 'pricing_id':
                if ($v) {
                    $price = Pricing::findOne($v);
                    return 'Xu: ' . $price->price_coin . ", Sms: " . $price->price_sms . ", Xem: " . $price->watching_period . "h";
                } else {
                    return 'Miễn phí';
                }
                break;
            default:
                return $v;
                break;
        }
    }
    public function actionTargets()
    {
        return [
            self::ACTION_TARGET_TYPE_USER              => \Yii::t('app', 'Người dùng'),
            self::ACTION_TARGET_TYPE_CAT               => \Yii::t('app', 'Quản lý Danh mục'),
            self::ACTION_TARGET_TYPE_CONTENT           => \Yii::t('app', 'Quản lý Nội dung'),
            self::ACTION_TARGET_TYPE_CONTENT_PROFILE   => \Yii::t('app', 'File Nội dung'),
            self::ACTION_TARGET_TYPE_SERVICE_PROVIDER  => \Yii::t('app', 'Quản lý nhà cung cấp dịch vụ'),
            self::ACTION_TARGET_TYPE_DEALER            => \Yii::t('app', 'Đại lý'),
            self::ACTION_TARGET_TYPE_SERVICE_GROUP     => \Yii::t('app', 'Quản lý nhóm gói cước'),
            self::ACTION_TARGET_TYPE_SERVICE           => \Yii::t('app', 'Quản lý Gói cước'),
            self::ACTION_TARGET_TYPE_CREDENTIAL        => \Yii::t('app', 'API Key'),
            self::ACTION_TARGET_TYPE_DEVICE            => \Yii::t('app', 'Thiết bị'),
            self::ACTION_TARGET_TYPE_OTHER             => \Yii::t('app', 'Other'),
            self::ACTION_TARGET_TYPE_ACTOR_DIRECTOR    => \Yii::t('app', 'Quản lý diễn viên/đạo diễn, ca sĩ/nhạc sĩ'),
            self::ACTION_TARGET_TYPE_CONTENT_ATTRIBUTE => \Yii::t('app', 'Thuộc tính nội dung'),
            self::ACTION_TARGET_TYPE_REPORT            => \Yii::t('app', 'Báo cáo'),
            self::ACTION_TARGET_TYPE_STREAMING_SERVER  => \Yii::t('app', 'Quản lý địa chỉ phân phối nội dung'),
            self::ACTION_TARGET_TYPE_RBAC_BE           => \Yii::t('app', 'Quản lý quyền, nhóm quyền backend'),
            self::ACTION_TARGET_TYPE_RBAC_SP           => \Yii::t('app', 'Quản lý quyền, nhóm quyền nhà cung cấp dịch vụ'),
            self::ACTION_TARGET_TYPE_RBAC_CP           => \Yii::t('app', 'Quản lý quyền, nhóm quyền đại lý'),
            self::ACTION_TARGET_TYPE_ADS               => \Yii::t('app', 'Quản lý quảng cáo'),
            self::ACTION_TARGET_TYPE_APP_ADS           => \Yii::t('app', 'Quản lý app quảng cáo'),
            self::ACTION_TARGET_TYPE_SUBSCRIBER        => \Yii::t('app', 'Quản lý thuê bao'),
            self::ACTION_TARGET_TYPE_PRICING           => \Yii::t('app', 'Quản lý giá'),
            self::ACTION_TARGET_TYPE_CONTENT_LOG       => \Yii::t('app', 'Content log'),
            self::ACTION_TARGET_TYPE_CONTENT_FEEDBACK  => \Yii::t('app', 'Content feedback'),
            self::ACTION_TARGET_TYPE_MULTILANGUAGE     => \Yii::t('app', 'Quản lý đa ngôn ngữ'),
            self::ACTION_TARGET_TYPE_CONFIG_API        => \Yii::t('app', 'Quản lý cấu hình API'),
        ];

    }
}
