<?php
/**
 * Created by PhpStorm.
 * User: mycon
 * Date: 9/22/2017
 * Time: 9:48 AM
 */

namespace api\models;


use common\models\Subscriber;
use yii\filters\auth\AuthMethod;

class HttpBearerAuthExtend extends AuthMethod
{
    /**
     * @var string the HTTP authentication realm
     */
    public $realm = 'api';


    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get('Authorization');
//        $ip = $request->getHeaders()->get('x-real-ip');
        $ip = \Yii::$app->request->getUserIP();
        if ($authHeader !== null && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            $identity = $user->loginByAccessToken($matches[1], get_class($this));
            if ($identity === null) {
                $this->handleFailure($response);
            } else {
                $subscriber = Subscriber::findOne($identity->getId());
//                \Yii::warning($ip);
                if ($subscriber->ip_address != $ip) {
                    if ($subscriber->type == Subscriber::TYPE_NSX) {
                        if (!in_array($ip, \Yii::$app->params['factory_ip'])) {
                            $subscriber->type = Subscriber::TYPE_USER;
                            $subscriber->register_at = time();
                        }
                    }

                    $subscriber->ip_address = $ip;
                    $subscriber->save();
                }


            }
            return $identity;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function challenge($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', "Bearer realm=\"{$this->realm}\"");
    }
}