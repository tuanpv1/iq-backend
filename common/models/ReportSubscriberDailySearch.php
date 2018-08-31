<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ReportSubscriberDaily;

/**
 * ReportSubscriberDailySearch represents the model behind the search form about `common\models\ReportSubscriberDaily`.
 */
class ReportSubscriberDailySearch extends ReportSubscriberDaily
{
    public $from_date;
    public $to_date;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'report_date', 'site_id','user_admin_id','dealer_id', 'service_id', 'total_subscriber', 'total_active_subscriber', 'subscriber_register_daily', 'total_cancel_subscriber', 'subscriber_cancel_daily'], 'integer'],
            [['from_date', 'to_date'], 'safe'],
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
    public function search($params)
    {
        $query = ReportSubscriberDaily::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'report_date' => SORT_ASC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

//        $query->andFilterWhere([
//            'id' => $this->id,
//            'report_date' => $this->report_date,
//            'site_id' => $this->site_id,
//            'service_id' => $this->service_id,
//            'total_subscriber' => $this->total_subscriber,
//            'total_active_subscriber' => $this->total_active_subscriber,
//            'subscriber_register_daily' => $this->subscriber_register_daily,
//            'total_cancel_subscriber' => $this->total_cancel_subscriber,
//            'subscriber_cancel_daily' => $this->subscriber_cancel_daily,
//        ]);
        $query->select('report_date,
                            sum(total_subscriber) as total_subscriber,
                            sum(total_active_subscriber) as total_active_subscriber,
                            sum(subscriber_register_daily) as subscriber_register_daily,
                            sum(total_cancel_subscriber) as total_cancel_subscriber,
                            sum(subscriber_cancel_daily) as subscriber_cancel_daily'
                        );

        if($this->site_id){
            $query->where(['site_id'=>$this->site_id]);
        }
        if($this->user_admin_id){
            $query->where(['user_admin_id'=>$this->user_admin_id]);
        }

        if($this->dealer_id){
            $query->andFilterWhere(['dealer_id'=>$this->dealer_id]);
        }

        if($this->service_id){
            $query->andFilterWhere(['service_id'=>$this->service_id]);
        }

        if ($this->from_date) {
//            $query->andWhere(['>=', 'report_date', $this->from_date]);
            $query->andFilterWhere(['>=', 'report_date', $this->from_date]);
        }
        if ($this->to_date) {
//            $query->andWhere(['<=', 'report_date', $this->to_date]);
            $query->andFilterWhere(['<=', 'report_date', $this->to_date]);
        }
        $query->groupBy('report_date');

        return $dataProvider;
    }
}
