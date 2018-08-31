<?php
/**
 * Created by PhpStorm.
 * User: qhuy
 * Date: 7/7/15
 * Time: 12:44 PM
 */

namespace common\components;


use common\helpers\CommonUtils;
use common\helpers\CUtils;
use common\helpers\CVietnameseTools;
use FileUpload\FileUpload;

class FileUploadLarge extends  FileUpload{

    /**
     * Remove harmful characters from filename
     * @param  string $name
     * @param  string $type
     * @param  integer $index
     * @param  array $content_range
     * @return string
     */
    public function trimFilename($name, $type, $index, $content_range)
    {
        //Remove tat ca cac dau
        return CVietnameseTools::makeValidFileName($name);
    }

    public function getProcess($name)
    {
        $total_size = $this->getSize();
        if($total_size == 0) return 100;
        $current_size = $this->getFilesize($this->pathresolver->getUploadPath($name));
        return intval(($current_size/$total_size)*100);
    }

}