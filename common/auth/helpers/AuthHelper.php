<?php
/**
 * Created by PhpStorm.
 * User: thuc
 * Date: 11/19/14
 * Time: 3:11 PM
 */

namespace common\auth\helpers;


use common\auth\models\ActionPermission;
use common\auth\models\ControllerRole;
use common\helpers\StringUtils;
use ReflectionClass;
use Yii;

class AuthHelper {

    /**
     * list route cua tat ca cac action trong tat ca cac controller cua app chi dinh boi $alias
     * @param string $alias - application ('@backend', '@frontend', '@app', '@console'...), actions of which to be listed
     * @param bool $notExistedOnly
     * @return ActionPermission[]
     */
    public static function listActions($alias = '@app', $notExistedOnly = false) {
        $res = [];

        $path = Yii::getAlias($alias) . "/controllers";
//        echo $path . "\n";

        $controllerFiles = glob($path . "/*Controller.php");
//        VarDumper::dump($controllerFiles);

        foreach ($controllerFiles as $file) {
            $h = fopen($file, 'r') or die('wtf');
            $ns = '';
            $class = '';

            while (!feof($h))
            {
                $line = fgets($h);
                if (!$ns && strpos($line, 'namespace') !== false) {
                    $ns = trim(preg_replace('~\s*namespace\s+([\S]+).*~', '$1', $line));
                }

                if (!$class && strpos($line, 'class') !== false) {
                    $class = trim(preg_replace('/\s*class\s+(\w+).+/', '$1', $line));
                }

                if (!empty($ns) && !empty($class)) {
                    break;
                }
            }

            fclose($h);

            if (!empty($ns) && !empty($class)) {


                $ns = StringUtils::removeTail($ns, ";"); // bo dau ; cuoi cung )neu co
                $className = $ns . '\\' . $class;

                $controller = StringUtils::removeTail($class, "Controller");
                $controllerId = StringUtils::camel2Dash($controller);

                Yii::info("Controller found: " . $className . " (in $file)");

                $class = new ReflectionClass($className);
//                $methods = $class->getMethods();

                $objInstance = $class->newInstanceArgs(['id' => 'dummy', 'module' => 'dummy']);
//                echo $objInstance->id;
                $methods = (get_class_methods ($className));
                foreach ($methods as $method) {
                    $action = StringUtils::removeHead($method, "action");
                    $actionId = StringUtils::camel2Dash($action);
                    if (StringUtils::startsWith($method, "action") && !StringUtils::equal($method, "actions")) {

                        $route = new ActionPermission();
                        $route->appAlias = $alias;
                        $route->name = $controller . "." . $action;
                        $route->controllerId = $controllerId;
                        $route->controllerName = $controller;
                        $route->actionId = $actionId;
                        $route->actionName = $action;
                        $route->actionType = ActionPermission::ACTION_TYPE_INLINE;
                        $route->controllerClass = $className;
                        $route->actionMethod = $method;
                        $route->file = $file;
                        $route->route = $controllerId . "/" .$actionId;
                        if (!$notExistedOnly || !$route->isExisted()) {
                            $res[] = $route;
                        }
                        Yii::info("Inline action found: " . $controllerId . "/" .$actionId);
                    }
                }
                $standaloneActions = $objInstance->actions();
//                VarDumper::dump($standaloneActions);
                if (!empty($standaloneActions)) {
                    foreach ($standaloneActions as $actionName => $actionConfig) {
                        $route = new ActionPermission();
                        $route->appAlias = $alias;
                        if (isset($actionConfig["class"])) {
                            $route->actionClass = $actionConfig["class"];
                        }
                        $route->name = $controller . "." . $actionName;
                        $route->actionType = ActionPermission::ACTION_TYPE_STANDALONE;
                        $route->controllerId = $controllerId;
                        $route->controllerName = $controller;
                        $route->actionId = $actionName;
                        $route->actionName = $actionName;
                        $route->controllerClass = $className;
                        $route->actionMethod = "";
                        $route->file = $file;
                        $route->route = $controllerId . "/" .$actionName;
                        if (!$notExistedOnly || !!$route->isExisted()) {
                            $res[] = $route;
                        }
//                        echo "Standalone action found: " . $controllerId . "/" . $actionName . "\n";
                    }
                }
            }
            else {
                Yii::info("Controller NOT found in $file");
            }
        }

        return $res;
    }

    /**
     * list route cua tat ca cac controller cua app chi dinh boi $alias
     * @param string $alias - application ('@backend', '@frontend', '@app', '@console'...), actions of which to be listed
     * @param bool $notExistedOnly
     * @return ControllerRole[]
     */
    public static function listControllers($alias = '@app', $notExistedOnly = false) {
        $res = [];

        $path = Yii::getAlias($alias) . "/controllers";
//        echo $path . "\n";

        $controllerFiles = glob($path . "/*Controller.php");
//        VarDumper::dump($controllerFiles);

        foreach ($controllerFiles as $file) {
            $h = fopen($file, 'r') or die('wtf');
            $ns = '';
            $class = '';

            while (!feof($h)) {
                $line = fgets($h);
                if (!$ns && strpos($line, 'namespace') !== false) {
                    $ns = trim(preg_replace('~\s*namespace\s+([\S]+).*~', '$1', $line));
                }

                if (!$class && strpos($line, 'class') !== false) {
                    $class = trim(preg_replace('/\s*class\s+(\w+).+/', '$1', $line));
                }

                if (!empty($ns) && !empty($class)) {
                    break;
                }
            }

            fclose($h);

            if (!empty($ns) && !empty($class)) {


                $ns = StringUtils::removeTail($ns, ";"); // bo dau ; cuoi cung )neu co
                $className = $ns . '\\' . $class;

                $controller = StringUtils::removeTail($class, "Controller");
                $controllerId = StringUtils::camel2Dash($controller);

                Yii::info("Controller found: " . $className . " (in $file)");

                $route = new ControllerRole();
                $route->appAlias = $alias;
                $route->name = $controller . ".*";
                $route->controllerId = $controllerId;
                $route->controllerName = $controller;
                $route->controllerClass = $className;
                $route->file = $file;
                $route->route = $controllerId;
                if (!$notExistedOnly || !$route->isExisted()) {
                    $res[] = $route;
                } else {
                    Yii::info("Controller NOT found in $file");
                }
            }
        }

        return $res;
    }
}