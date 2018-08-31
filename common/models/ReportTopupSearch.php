<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ReportTopup;

/**
 * ReportTopupSearch represents the model behind the search form of `common\models\ReportTopup`.
 */
class ReportTopupSearch extends ReportTopup
{
    public $to_date;
    public $from_date;
    public $status;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'report_date', 'site_id', 'channel', 'count','white_list'], 'integer'],
            [['revenue','revenue_pending','revenue_error',], 'number'],
            [['from_date','to_date','status'],'safe']
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
        $query = ReportTopup::find();

        // add conditions that should always apply here

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

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
//        var_dump($params['ReportTopupSearch']['from_date']);var_dump($params['ReportTopupSearch']['from_date']);exit();

        $query->select('report_date,
                            sum(count) count,
                            sum(revenue) revenue,
                            sum(revenue_pending) revenue_pending,
                            sum(revenue_error) revenue_error,
                            channel'
        );

        if($this ->site_id){
            $query->andWhere(['site_id'=>$this->site_id]);
        }

        if($this->channel){
            $query->andWhere(['channel'=>$this->channel]);
        }

        if($this->white_list){
            $query->andWhere(['white_list'=>$this->white_list]);
        }
        if($params['ReportTopupSearch']['from_date']){
        	$query->andFilterWhere(['>=', 'report_date', strtotime($params['ReportTopupSearch']['from_date'])]);
        }
        if($params['ReportTopupSearch']['to_date']){
        	$query->andFilterWhere(['<=', 'report_date', strtotime($params['ReportTopupSearch']['to_date'])]);
        }
        $query->groupBy('report_date');
//        $query->groupBy('report_date,site_id,channel');
        return $dataProvider;
    }

    public function searchDetail($params)
    {
        $listType = [
            SubscriberTransaction::TYPE_VOUCHER,
            SubscriberTransaction::TYPE_VOUCHER_PHONE,
            SubscriberTransaction::TYPE_TOPUP_ATM,
            SubscriberTransaction::TYPE_TOPUP_VISA,
        ];
        $listStatus=[
            SubscriberTransaction::STATUS_FAIL,
            SubscriberTransaction::STATUS_PENDING,
            SubscriberTransaction::STATUS_SUCCESS,
        ];
        $query = Subscriber::find()
            ->select('subscriber.register_at,
            subscriber.full_name,
            subscriber.address,
            subscriber.ip_location_first,
            subscriber.ip_to_location,
            subscriber.msisdn,
            subscriber.phone_number,
            subscriber.machine_name,
            device.serial,
            device.device_type,
            subscriber_transaction.transaction_time,
            subscriber_transaction.subscriber_id,
            subscriber_transaction.type,
            subscriber_transaction.channel,
            subscriber_transaction.`status`,
            subscriber_transaction.cost AS total_topup,
            subscriber_transaction.error_code,
            subscriber_transaction.gateway,
            ')
            ->innerJoin('device','device.device_id = subscriber.machine_name')
            ->innerJoin('subscriber_transaction','subscriber_transaction.subscriber_id = subscriber.id');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
//            'sort'       => [
//                'defaultOrder' => [
//                    'report_date' => SORT_DESC,
//                ],
//            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        if($this ->site_id){
            $query->andWhere(['subscriber_transaction.site_id'=>$this->site_id]);
        }

        if($this->channel){
            if ($this->channel == SubscriberTransaction::CHANNEL_TYPE_VOUCHER){
                $query->andWhere(['subscriber_transaction.type' => SubscriberTransaction::TYPE_VOUCHER]);
            }
            if ($this->channel == SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VIETTEL){
                $query->andWhere(['subscriber_transaction.type' => SubscriberTransaction::TYPE_VOUCHER_PHONE])
                ->andWhere(['subscriber_transaction.channel'=>SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VIETTEL]);
            }
            if ($this->channel == SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VINAPHONE){
                $query->andWhere(['subscriber_transaction.type' => SubscriberTransaction::TYPE_VOUCHER_PHONE])
                    ->andWhere(['subscriber_transaction.channel'=>SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VINAPHONE]);
            }
            if ($this->channel == SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_MOBIPHONE) {
                $query->andWhere(['subscriber_transaction.type' => SubscriberTransaction::TYPE_VOUCHER_PHONE])
                    ->andWhere(['subscriber_transaction.channel' => SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_MOBIPHONE]);
            }
            if ($this->channel == SubscriberTransaction::CHANNEL_TYPE_ATM){
                $query->andWhere(['subscriber_transaction.type' => SubscriberTransaction::TYPE_TOPUP_ATM]);
            }
            if ($this->channel == SubscriberTransaction::CHANNEL_TYPE_VISA){
                $query->andWhere(['subscriber_transaction.type' => SubscriberTransaction::TYPE_TOPUP_VISA]);
            }
        }
        else{
            $query->andWhere(['IN', 'subscriber_transaction.type', $listType]);
        }

        if ($this->status != null){
            $query->andWhere(['subscriber_transaction.status'=>$this->status]);
        }else{
            $query->andWhere(['IN', 'subscriber_transaction.status', $listStatus]);
        }
        if($this->white_list){
            $query->andWhere(['subscriber_transaction.white_list'=>$this->white_list]);
        }
        if($params['ReportTopupSearch']['from_date']){
            $query->andFilterWhere(['>=', 'subscriber_transaction.transaction_time', strtotime($params['ReportTopupSearch']['from_date'])]);
        }
        if($params['ReportTopupSearch']['to_date']){
            $query->andFilterWhere(['<=', 'subscriber_transaction.transaction_time', strtotime($params['ReportTopupSearch']['to_date'])]);
        }
//        $query->groupBy('subscriber_transaction.subscriber_id,subscriber_transaction.type,subscriber_transaction.channel,subscriber_transaction.status')
        $query->orderBy(['subscriber_transaction.transaction_time'=>SORT_ASC]);
        return $dataProvider;
    }
}
