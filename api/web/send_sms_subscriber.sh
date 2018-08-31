#!/bin/bash
subscriber_id=$1
campaign_id=$2
service_id=$3
echo "Start find campaign"
/opt/php/bin/php /opt/code/tvod2-backend/yii subscriber/send-sms $subscriber_id $campaign_id $service_id
echo "send mail thanh cong"
