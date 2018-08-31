<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "kodi_category_item_asm".
 *
 * @property integer $id
 * @property integer $category_id
 * @property integer $item_id
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property KodiCategory $category
 * @property ItemKodi $item
 */
class KodiCategoryItemAsm extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'kodi_category_item_asm';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'item_id'], 'required'],
            [['category_id', 'item_id', 'created_at', 'updated_at'], 'integer'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => KodiCategory::className(), 'targetAttribute' => ['category_id' => 'id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => ItemKodi::className(), 'targetAttribute' => ['item_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('app', 'ID'),
            'category_id' => \Yii::t('app', 'ID danh mục'),
            'item_id' => \Yii::t('app', 'Item ID'),
            'created_at' => \Yii::t('app', 'Ngày tạo'),
            'updated_at' => \Yii::t('app', 'Ngày thay đổi thông tin'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(KodiCategory::className(), ['id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(ItemKodi::className(), ['id' => 'item_id']);
    }

    public static function DeleteAsm($id= null, $cat_id = null){
        if($id){
            KodiCategoryItemAsm::deleteAll('item_id = :item_id',['item_id'=>$id]);
            return true;
        }
        if($cat_id){
         KodiCategoryItemAsm::deleteAll('category_id = :category_id',['category_id'=>$cat_id]);
            return true;
        }
        return false;
    }

}
