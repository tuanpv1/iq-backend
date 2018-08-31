#!/bin/bash

site_id=$1
id=$2
category_id=$3
subscriber_id=$4
echo "Start save log"
/opt/php/bin/php /opt/code/tvod2-backend/yii save-log/save-time-view $site_id $id $category_id $subscriber_id
echo "save log done"
