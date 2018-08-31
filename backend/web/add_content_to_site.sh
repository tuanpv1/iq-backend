#!/bin/bash
sites=$1
cats=$2
echo "Start add content" 
/opt/php/bin/php /opt/code/tvod2-backend/yii content/add-to-site $sites $cats
echo "Add content complete"
