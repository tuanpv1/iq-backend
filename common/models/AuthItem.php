<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\rbac\Item;

/**
 * This is the model class for table "{{%auth_item}}".
 *
 * @property string $name
 * @property integer $type
 * @property integer $acc_type
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property AuthAssignment[] $authAssignments
 * @property User[] $users
 * @property AuthRule $ruleName
 * @property AuthItemChild[] $authItemChildren
 * @property AuthItemChild[] $authItemChildren0
 * @property AuthItem[] $children
 * @property AuthItem[] $parent
 */
class AuthItem extends \yii\db\ActiveRecord
{
    const TYPE_ROLE = Item::TYPE_ROLE;
    const TYPE_PERMISSION = Item::TYPE_PERMISSION;

    const ACC_TYPE_BACKEND = 1;
    const ACC_TYPE_SP = 2;
    const ACC_TYPE_CP = 3;
    const ACC_TYPE_CP_PROVIDER = 4;

    public static $acc_types = [
        self::ACC_TYPE_BACKEND => '@backend',
        self::ACC_TYPE_SP => '@site',
        self::ACC_TYPE_CP => '@dealer',
        self::ACC_TYPE_CP_PROVIDER => '@cProvider',
    ];

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type','description'], 'required'],
            [['name',], 'unique', 'skipOnError' => false, 'message' =>  Yii::t('app','Tên nhóm quyền đã tồn tại trong hệ thống')],
            [['type', 'created_at', 'updated_at', 'acc_type'], 'integer'],
            [['description', 'data'], 'string'],
            [['name', 'rule_name'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Tên nhóm quyền'),
            'type' => Yii::t('app', 'Loại'),
            'acc_type' => Yii::t('app', 'Account Type'),
            'description' => Yii::t('app', 'Mô tả'),
            'rule_name' => Yii::t('app', 'Rule Name'),
            'data' => Yii::t('app', 'Dữ liệu'),
            'created_at' => Yii::t('app', 'Ngày tạo'),
            'updated_at' => Yii::t('app', 'Ngày cập nhật'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public static function findPermission() {
        return AuthItem::find()->andWhere(['type' => AuthItem::TYPE_PERMISSION]);
    }
    /**
     * @return ActiveQuery
     */
    public static function findRole() {
        return AuthItem::find()->andWhere(['type' => AuthItem::TYPE_ROLE]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'child'])->viaTable('auth_item_child', ['parent' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['item_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('{{%auth_assignment}}', ['item_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuleName()
    {
        return $this->hasOne(AuthRule::className(), ['name' => 'rule_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChildren()
    {
        return $this->hasMany(AuthItemChild::className(), ['parent' => 'name']);
    }

    /**
     * @return ActiveDataProvider
     */
    public function getChildrenProvider()
    {
        return new ActiveDataProvider([
            'query' => $this->getChildren()
        ]);
    }

    /**
     * @return ActiveDataProvider
     */
    public function getParentProvider()
    {
        return new ActiveDataProvider([
            'query' => $this->getParent()
        ]);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'parent'])->viaTable('auth_item_child', ['child' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMissingRoles()
    {
        return AuthItem::find()->andWhere(['type' => AuthItem::TYPE_ROLE, 'acc_type' => $this->acc_type])
            ->andWhere('name != :name',[':name' => $this->name])
            ->andWhere('name not in (select child from auth_item_child where parent = :name)',[':name' => $this->name])
            ->all();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMissingPermissions()
    {
        return AuthItem::find()->andWhere(['type' => AuthItem::TYPE_PERMISSION, 'acc_type' => $this->acc_type])
            ->andWhere('name != :name',[':name' => $this->name])
            ->andWhere('name not in (select child from auth_item_child where parent = :name)',[':name' => $this->name])
            ->all();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChildren0()
    {
        return $this->hasMany(AuthItemChild::className(), ['child' => 'name']);
    }
}
