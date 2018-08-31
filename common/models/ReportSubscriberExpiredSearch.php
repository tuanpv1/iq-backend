<?php
namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class ReportSubscriberExpiredSearch extends ReportSubscriberExpired
{
    public $from_date;
    public $to_date;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_date', 'site_id','device_type','total_subscriber_expired','service_id','ip_to_location','total_service_expired'], 'safe'],
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

        $query = ReportSubscriberExpired::find()
            ->select('sum(total_subscriber_expired) as total_subscriber_expired')
            ->addSelect('sum(total_service_expired) as total_service_expired')
            ->addSelect('report_date');


        $this->load($params);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort'       => [
                'defaultOrder' => [
                    'report_date' => SORT_DESC,
                ],
            ],
        ]);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if($this->site_id){
            $query->where(['site_id'=>$this->site_id]);
        }

        if($this->device_type){
            $query->andwhere(['device_type'=>$this->device_type]);
        }

        if ($this->from_date) {
            $query->andFilterWhere(['>=', 'report_date', strtotime($this->from_date)]);
        }
        if ($this->to_date) {
            $query->andFilterWhere(['<=', 'report_date', strtotime($this->to_date)]);
        }

        if($this->service_id){
            $query->andwhere(['service_id'=>$this->service_id]);
        }else{
            $query->andwhere(['service_id'=>0]);
        }

        if($this->ip_to_location){
            $query->andwhere(['ip_to_location'=>$this->ip_to_location]);
        }

        $query->groupBy('report_date');

        return $dataProvider;
    }

    public function searchDetail($params)
    {
        $query = Subscriber::find()
            ->select('subscriber.full_name,
            subscriber.address,
            subscriber.ip_location_first,
            subscriber.ip_to_location,
            subscriber.msisdn,
            subscriber.phone_number,
            subscriber.machine_name,
            device.serial,
            device.device_type,
            subscriber_service_asm.service_name,
            subscriber_service_asm.created_at,
            subscriber_service_asm.expired_at,
            ')
        ->innerJoin('device','device.device_id = subscriber.machine_name')
        ->innerJoin('subscriber_service_asm','subscriber_service_asm.subscriber_id = subscriber.id');


        $this->load($params);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
//            'sort'       => [
//                'defaultOrder' => [
//                    'subscriber_service_asm.created_at' => SORT_ASC, // TĂNG DẦN
//                ],
//            ],
        ]);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if($this->site_id){
            $query->where(['subscriber.site_id'=>$this->site_id]);
        }

        if($this->device_type){
            $query->andwhere(['device.device_type'=>$this->device_type]);
        }

        if ($this->from_date) {
            $query->andFilterWhere(['>=', 'subscriber_service_asm.expired_at', strtotime($this->from_date)]);
        }
        if ($this->to_date) {
            $query->andFilterWhere(['<=', 'subscriber_service_asm.expired_at', strtotime($this->to_date)]);
        }

        if($this->service_id){
            $query->andwhere(['subscriber_service_asm.service_id'=>$this->service_id]);
        }

        if($this->ip_to_location){
            $query->andwhere(['subscriber.ip_to_location'=>$this->ip_to_location]);
        }

        $query
            ->orderBy(['subscriber_service_asm.expired_at'=>SORT_ASC] );

        return $dataProvider;
//        return $dataProvider->getModels();
    }
}
