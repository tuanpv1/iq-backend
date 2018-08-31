<?php
/**
 * Created by PhpStorm.
 * User: thuc
 * Date: 11/19/14
 * Time: 3:13 PM
 */

namespace common\auth\models;

use common\models\AuthItem;
use Yii;
use yii\base\Model;

class ActionPermission extends Model {
    const ACTION_TYPE_INLINE = 1;
    const ACTION_TYPE_STANDALONE= 2;

    public $actionType = ActionPermission::ACTION_TYPE_INLINE;

    public $appAlias;

    public $name;
    public $route;

    public $controllerId;
    public $controllerName;
    public $actionId;
    public $actionName;
    public $actionClass = null;  // for standalone action

    public $controllerClass;
    public $actionMethod;
    public $file;


    public function isExisted() {
        $auth_name = $this->appAlias.'.'.$this->name;
        $item = AuthItem::findOne(['name' => $auth_name, 'type' => AuthItem::TYPE_PERMISSION]);
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
            'route' => Yii::t('app', 'Action route'),
            'name' => Yii::t('app', 'Permission name'),
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

    public function createPermission($acc_type = AuthItem::ACC_TYPE_BACKEND)
    {
        $item = new AuthItem();
        $item->name = AuthItem::$acc_types[$acc_type].'.'.$this->name;
        $item->type = AuthItem::TYPE_PERMISSION;
        $item->acc_type = $acc_type;
        $item->data = $this->route;
        $item->description = $this->controllerName . " > " . $this->actionName;
        return $item->save();
    }
} 