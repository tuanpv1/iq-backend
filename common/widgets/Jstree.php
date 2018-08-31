<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\widgets;

use common\assets\JstreeAsset;
use common\models\Category;
use common\models\KodiCategory;
use yii\helpers\Html;

class Jstree extends \yii\bootstrap\Widget
{
    /**
     * Add event handle function
     * [
     *   'select_node.jstree' => 'function(e,data){...}'
     * ]
     * @var array
     */
    public $eventHandles = [];

    public $options             = [];
    public $data                = [];
    public $type                = Category::TYPE_FILM;
    public $type_kodi           = 0;
    public $sp_id               = null;
    public $cp_id               = null;
    private $catTree            = [];
    public static $counter      = 0;
    public static $autoIdPrefix = 'jstree';

    private $_id;

    public function init()
    {
        parent::init();
        if ($this->type == 100) {
            $this->catTree = KodiCategory::getMenuTree($this->type, $this->sp_id);
        } else if ($this->type_kodi) {
            $this->catTree = KodiCategory::getMenuTreeCate($this->type, $this->sp_id);
        } else {
            $this->catTree = Category::getMenuTree($this->type, $this->sp_id, $this->cp_id);
        }

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
        echo Html::beginTag('div', $this->options);
    }

    /**
     * Returns the ID of the widget.
     * @param boolean $autoGenerate whether to generate an ID if it is not set previously
     * @return string ID of the widget.
     */
    public function getId($autoGenerate = true)
    {
        if ($autoGenerate && $this->_id === null) {
            $this->_id = static::$autoIdPrefix . static::$counter++;
        }

        return $this->_id;
    }

    /**
     * Sets the ID of the widget.
     * @param string $value id of the widget.
     */
    public function setId($value)
    {
        $this->_id = $value;
    }

    /**
     * Renders the menu.
     */
    public function run()
    {
//        $selectedCats = [];
        //        foreach($this->catTree as $cat){
        //            array_push($selectedCats,$cat['id']);
        //        }
        //        /** default checkAll when renders */
        //        if( isset($this->data[0]) && !$this->data[0] ){
        //            $this->data  = $selectedCats;
        //        }
        echo $this->visualjsTree($this->catTree, $this->data);
        echo Html::endTag('div');
        JstreeAsset::register($this->getView());
        $this->registerPlugin('jstree');
        $js = '';
        if (count($this->eventHandles) > 0) {
            foreach ($this->eventHandles as $event => $handle) {
                $js .= <<<JS
                $('#{$this->_id}').on('{$event}', {$handle});
JS;

            }
        }
        $this->getView()->registerJs($js);
    }

    private function visualjsTree($catTree, $cats)
    {
        $res = '<ul>';
        foreach ($catTree as $item) {
            $selected = '';
            if (is_array($cats) && in_array($item['id'], $cats)) {
                $selected = 'data-jstree={"selected":true}';
            }
            $res .= "<li class='jstree-open' $selected id='" . $item['id'] . "' >" . $item['label'];

            if (isset($item['items']) && count($item['items']) > 0) {
                $res .= $this->visualjsTree($item['items'], $cats);
            }

            $res .= '</li>';
        }
        $res .= '</ul>';
        return $res;
    }
}
