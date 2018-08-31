<?php

namespace common\models;

use sp\models\ReportRevenuesDetailForm;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ReportRevenue;

/**
 * ReportRevenueSearch represents the model behind the search form about `common\models\ReportRevenue`.
 */
class ReportRevenueSearch extends ReportRevenue
{
    public $from_date;
    public $to_date;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'report_date', 'site_id', 'service_id','white_list', 'total_revenues', 'renew_revenues', 'register_revenues', 'content_buy_revenues'], 'integer'],
            [['from_date', 'to_date','cp_id'], 'safe'],
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
        $query = ReportRevenue::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'report_date' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->select('report_date,
                            sum(total_revenues) as total_revenues,
                            sum(revenues) as revenues,
                            sum(content_buy_revenues) as content_buy_revenues'
        );

        if($this->site_id){
            $query->andWhere(['site_id'=>$this->site_id]);
        }
        if($this->cp_id){
            $query->andWhere(['cp_id'=>$this->cp_id]);
        }
        if($this->white_list){
            $query->andWhere(['white_list'=>$this->white_list]);
        }

        if ($this->from_date) {
            $query->andFilterWhere(['>=', 'report_date', $this->from_date]);
        }
        if ($this->to_date) {
            $query->andFilterWhere(['<=', 'report_date', $this->to_date]);
        }
        if($this->service_id){
            $query->andWhere("service_id =:service_id or service_id IS NULL",['service_id'=>$this->service_id]);
        }
        $query->groupBy('report_date');

        return $dataProvider;
    }

    /**
     * 
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function getDetails($params)
    {
        $query = ReportRevenuesDetailForm::find()
            ->select('device.device_id as mac, subscriber.register_at as report_date, subscriber.msisdn as phone,
            subscriber.ip_to_location as city_code, subscriber.full_name, subscriber.address, device.device_type as type_model, 
            device.serial, subscriber_transaction.transaction_time as buy_time, subscriber_transaction.cost, subscriber_transaction.is_first_package, subscriber_transaction.type')
            ->innerJoin('subscriber','subscriber.id = subscriber_transaction.subscriber_id')
            ->innerJoin('device','device.device_id = subscriber.machine_name')
            ->andWhere(['subscriber_transaction.status' => SubscriberTransaction::STATUS_SUCCESS])
            ->andWhere([
                'between',
                'subscriber_transaction.transaction_time',
                $params['ReportRevenueSearch']['from_date'],
                $params['ReportRevenueSearch']['to_date']
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if($this->site_id){
            $query->andwhere(['subscriber.site_id'=>$this->site_id]);
        }

        if($this->cp_id){
            $query->andWhere(['subscriber_transaction.cp_id'=>$this->cp_id]);
        }

        if($this->white_list){
            $query->andwhere(['subscriber.white_list'=>$this->white_list]);
        }
        
        if($this->service_id){
            $query->andWhere("service_id =:service_id or service_id IS NULL",['service_id'=>$this->service_id]);
        }

//        $query->groupBy('subscriber.site_id, report_date, service_id');

        return $dataProvider;
    }
}
