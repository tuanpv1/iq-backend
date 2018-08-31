<?php
/**
 * Created by PhpStorm.
 * User: bibon
 * Date: 4/18/2016
 * Time: 5:23 PM
 */

namespace console\controllers;



use common\models\Device;
use common\models\Subscriber;
use yii\console\Controller;


class MigrateDataController extends Controller
{



//    public function actionRun()
//    {
//        $this->migrateDevice();
//    }

    /**
     * LINUX: /opt/code/tvod2-backend/yii migrate-data/migrate-device
     * WINDOWS : yii migrate-data/migrate-device
     */
    public function actionMigrateDevice(){
        $lst = Subscriber::find()->where(['authen_type' =>Subscriber::AUTHEN_TYPE_MAC_ADDRESS])->all();
        if(!$lst){
            echo '****** actionMigrateDevice ERROR! Not found MAC ******';
        }
        $i=0;
        foreach ($lst as $subscriber){
            $device = Device::findOne(['device_id'=>$subscriber->username]);
            if(!$device){
                continue;
            }
            if($device->first_login == 0){
                $device->first_login = $subscriber->created_at;
                if($device->save()){
                    $i++;
                }
            }
        }
        echo '****** actionMigrateDevice SUCCESS: Update first_login '.$i.' device success ******';
    }
}