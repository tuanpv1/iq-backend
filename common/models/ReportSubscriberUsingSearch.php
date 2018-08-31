<?php

namespace common\models;

use sp\models\ReportSubscriberUsingDetailForm;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ReportSubscriberUsingSearch represents the model behind the search form of `common\models\ReportSubscriberUsing`.
 */
class ReportSubscriberUsingSearch extends ReportSubscriberUsing
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'report_date', 'site_id', 'subscriber_total', 'service_total', 'type_model'], 'integer'],
            [['city'], 'safe'],
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
        $query = ReportSubscriberUsing::find()
            ->select('report_date, sum(subscriber_total) as subscriber_total, 
            sum(service_total) as service_total')
            ->andWhere([
                'between',
                'report_date',
                $params['ReportSubscriberUsingSearch']['from_date'],
                $params['ReportSubscriberUsingSearch']['to_date']
            ]);

        // add conditions that should always apply here

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

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'site_id' => $this->site_id,
            'type_model' => $this->type_model,
        ]);

        if ($params['ReportSubscriberUsingSearch']['city']) {
            $query->andFilterWhere(['city' => $this->city]);
        }

        if($params['ReportSubscriberUsingSearch']['service_id']){
            $query->andFilterWhere(['service_id' => $params['ReportSubscriberUsingSearch']['service_id']]);
        }

        $query->groupBy('report_date');

        return $dataProvider;
    }

    public function searchDetail($params)
    {
        $query = ReportSubscriberUsingDetailForm::find()
            ->select('device.device_id as mac, subscriber.register_at as report_date, 
            subscriber.ip_to_location as city_code, device.device_type as type_model, device.serial, subscriber.ip_location_first,
            subscriber.phone_number, subscriber.full_name, subscriber.msisdn, subscriber.address,
            subscriber_service_asm.expired_at as expired_service_at, service.display_name as service')
            ->innerJoin('subscriber_service_asm', 'subscriber_service_asm.subscriber_id = subscriber.id')
            ->innerJoin('device', 'device.device_id = subscriber.machine_name')
            ->innerJoin('service', 'service.id = subscriber_service_asm.service_id')
            ->andWhere(['subscriber.type' => Subscriber::TYPE_USER])
            ->andWhere(['service.service_type' => Service::TYPE_SERVICE_USER])
            ->andWhere(['subscriber_service_asm.status' => SubscriberServiceAsm::STATUS_ACTIVE])
            ->andWhere(['<>','service.status', Service::STATUS_REMOVE])
            ->andWhere(['>=', 'subscriber_service_asm.expired_at', $params['ReportSubscriberUsingSearch']['to_date']])
            ->andWhere(['subscriber.site_id' => $params['ReportSubscriberUsingSearch']['site_id']]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        if ($this->type_model) {
            $query->andWhere(['device.device_type' => $this->type_model]);
        }
        if ($this->city) {
            $query->andFilterWhere(['subscriber.ip_to_location' => $this->city]);
        }

        if($params['ReportSubscriberUsingSearch']['service_id']){
            $query->andFilterWhere(['subscriber_service_asm.service_id' => $params['ReportSubscriberUsingSearch']['service_id']]);
        }

        return $dataProvider;
    }
}
