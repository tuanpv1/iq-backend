<?php
/**
 * Created by PhpStorm.
 * User: thuc
 * Date: 11/19/14
 * Time: 3:13 PM
 */

namespace common\auth\models;

use common\helpers\StringUtils;
use common\models\AuthItem;
use common\models\AuthItemChild;
use Yii;
use yii\base\Model;

class ControllerRole extends Model {
    public $appAlias;

    public $name;
    public $route;

    public $controllerId;
    public $controllerName;

    public $controllerClass;
    public $file;


    public function isExisted() {
        $item = AuthItem::findOne(['name' => $this->name, 'type' => AuthItem::TYPE_ROLE]);;
        if ($item) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['route', 'name'], 'required'],
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'route' => Yii::t('app', 'Controller route'),
            'name' => Yii::t('app', 'Role name'),
        ];
    }

    /**
     * Test if $this->name of current action in provided list $names
     * @param $names string[] danh sach name de test
     * @return bool
     */
    public function isNameIn($names)
    {
        if (!$names) {
            return false;
        }
        foreach ($names as $name) {
            if ($name == $this->name) {
                return true;
            }
        }
        return false;
    }

    public function createRoleIfNotExist($acc_type = AuthItem::ACC_TYPE_BACKEND)
    {
        $item = AuthItem::findOne(['name' => $this->name, 'type' => AuthItem::TYPE_ROLE, 'acc_type' => $acc_type]);;
        if (!$item) {
            $item = new AuthItem();
        }
        $item->name = $this->name;
        $item->type = AuthItem::TYPE_ROLE;
        $item->acc_type = $acc_type;
        $item->description = $this->controllerName . " Manager ";
        return $item->save();
    }

    public function autoPermissionAssign($acc_type = AuthItem::ACC_TYPE_BACKEND)
    {
        /* @var $permissions \common\auth\models\ActionPermission[] */
        $permissions = AuthItem::find()->andWhere(['type' => AuthItem::TYPE_PERMISSION, 'acc_type' => $acc_type])->all();

        if (!$permissions) {
            return;
        }

        $prefix = StringUtils::removeTail($this->name, '*');

        foreach ($permissions as $permission) {
            if (StringUtils::startsWith($permission->name, $prefix)) {
                $relation = AuthItemChild::findOne(['child' => $permission->name, 'parent' => $this->name]);
                if (!$relation) {
                    $relation = new AuthItemChild();
                    $relation->child = $permission->name;
                    $relation->parent = $this->name;
                    $relation->save();
                }
            }
        }
    }
} 