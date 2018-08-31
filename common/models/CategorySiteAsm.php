<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "category_site_asm".
 *
 * @property integer $id
 * @property integer $category_id
 * @property integer $site_id
 * @property integer $updated_at
 * @property integer $created_at
 *
 * @property Site $site
 * @property Category $category
 */
class CategorySiteAsm extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category_site_asm';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'site_id'], 'required'],
            [['category_id', 'site_id', 'updated_at', 'created_at'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app','ID'),
            'category_id' =>  Yii::t('app','ID Danh mục'),
            'site_id' => Yii::t('app', 'Site ID'),
            'updated_at' =>  Yii::t('app','Ngày tạo'),
            'created_at' =>  Yii::t('app','Ngày thay đổi thông tin'),
        ];
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
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    public static function getSiteList($condition = [], $listFieldSelect = []){
        // $output;

        if(count($condition) === 0){
            $site = self::find()->all();
        }else{
            $site = self::findAll($condition);
        }
        if(count($listFieldSelect) > 0 && count($listFieldSelect) === 2){
            $output = [];
            foreach ($site as $v) {
                $output[$v[$listFieldSelect[0]]] = $v[$listFieldSelect[1]];
            }
        }else{
            $output = $site;
        }
        return $output;
    }
}
