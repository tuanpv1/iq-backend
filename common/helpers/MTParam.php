<?php
/**
 * Created by PhpStorm.
 * User: linhpv
 * Date: 5/29/15
 * Time: 10:57 AM
 */

namespace common\helpers;


use common\models\Site;
use common\models\SmsMessage;
use common\models\SubscriberTransaction;

class MTParam {
    public $msg=''; //Content message can gui
    public $moId=0; // MO ID
    public $transId=0;
    public $site_id = 0;
    public $mt_template_id;
    public $saveToDb=true; // Co luu mesage vao db hay ko
    public $brand=false; // Co gui theo brand name hay ko
    public $destination; // So dien thoai
    public $code_name; //Code name cho sms template (ResMessage)
    public $subscriber;

    private $msgToDb='';

    /**
     * @return null|ServiceProvider
     */
    public function getSite(){
        return Site::find()->andWhere(['id' => $this->site_id])->one();
    }

    /**
     * @return null|SubscriberTransaction
     */
    public function getTransaction(){
        return SubscriberTransaction::findOne($this->transId);
    }

    public function getMOSMS(){
        return SmsMessage::findOne(['id' => $this->moId]);
    }

    public function setSmsToSave($content){
        $this->msgToDb = $content;
    }

    public function getSmsToSave()
    {
        if(empty($this->msgToDb)){
            return $this->msg;
        }
        return $this->msgToDb;
    }


}