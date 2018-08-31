#!/bin/bash
site_id=$1
echo "Start export" 
/opt/php/bin/php /opt/code/tvod2-backend/yii content/export-data-to-file $site_id 2>&1 
echo "Export run complete"
echo $?
