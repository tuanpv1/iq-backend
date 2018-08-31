<?php

namespace common\models;

use DateTime;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * GroupSubscriberSearch represents the model behind the search form about `\common\models\GroupSubscriber`.
 */

class LogSubscriberSwapSearch extends LogSubscriberSwap
{
    public  $to_time;
    public  $from_time;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'number_change', 'status', 'updated_at','subscriber_id'], 'integer'],
            [['description','device_id_old', 'device_id_new','actor_id', 'created_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$site_id)
    {
        $query = LogSubscriberSwap::find()
            ->innerJoin('device', 'device.id = log_subscriber_swap.device_id_old')
            ->where(['device.site_id'=>$site_id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if ($this->created_at) {

            $from_time = strtotime(str_replace('/', '-', $this->created_at) . ' 00:00:00');
            $to_time = strtotime(str_replace('/', '-', $this->created_at) . ' 23:59:59');
            $query->andFilterWhere(['>=', 'log_subscriber_swap.created_at',$from_time]);
            $query->andFilterWhere(['<=', 'log_subscriber_swap.created_at',$to_time]);
        }
        if($this->actor_id){
            $query->innerJoin('user', 'user.id = log_subscriber_swap.actor_id')
                ->andWhere(['like', 'user.username', $this->actor_id]);
        }
        if($this->device_id_new){
            $device = Device::find()
                ->where(['site_id'=>$site_id])
                ->andWhere(['like', 'device_id', $this->device_id_new])
                ->all();
            if($device){
                /** @var  $item Device */
                foreach($device as $item){
                    $list_device_id[] = $item->id;
                }
                $query->andWhere(['log_subscriber_swap.device_id_new'=>$list_device_id]);
            }
        }
        $query->andFilterWhere(['like', 'log_subscriber_swap.description', $this->description])
            ->andFilterWhere(['like', 'device.device_id', $this->device_id_old]);

        return $dataProvider;

    }
}

