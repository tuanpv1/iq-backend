<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 25/02/2015
 * Time: 9:03 AM
 */

namespace api\models;

use common\helpers\CUtils;

class Category extends \common\models\Category
{
    public $children;

    public function rules()
    {
        return [
            [['site_id', 'display_name'], 'required'],
            [
                [
                    'site_id',
                    'type',
                    'status',
                    'order_number',
                    'parent_id',
                    'level',
                    'child_count',
                    'created_at',
                    'updated_at',
                    'show_on_client',
                    'show_on_portal'
                ],
                'integer'
            ],
            [['description'], 'string'],
            [['display_name', 'ascii_name'], 'string', 'max' => 200],
            [['images'], 'string', 'max' => 500],
            [['admin_note'], 'string', 'max' => 4000],

        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['tvod1_id']);
        /** Lấy thêm trường shortname cho karaoke */
        if($this->type == \common\models\Category::TYPE_KARAOKE){
            $fields['shortname'] = function ($model) {
                $shortname =  CUtils::parseTitleToKeyword($model->display_name);
                return $shortname;
            };
        }
        if(empty($this->ascii_name)){
            $fields['ascii_name'] = function ($model) {
                $ascii_name = CUtils::convertVi2Eng($model->display_name);
                return $ascii_name;
            };
        }
        return $fields;
    }

}