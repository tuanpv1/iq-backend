#!/bin/bash
price=$1
subscriber_id=$2
echo "Start find campaign"
/opt/php/bin/php /opt/code/tvod2-backend/yii subscriber/find-campaign-for-recharge $price $subscriber_id
echo "tim chien dich thanh cong"
