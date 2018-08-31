<?php

namespace common\models;

use sp\models\ReportSmartboxInitializationDetailForm;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ReportSmartboxInitializationSearch represents the model behind the search form of `common\models\ReportSmartboxInitialization`.
 */
class ReportSmartboxInitializationSearch extends ReportSmartboxInitialization
{
    public $from_date;
    public $to_date;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'report_date', 'total', 'site_id', 'type_model'], 'integer'],
            [['from_date', 'to_date', 'city'], 'safe'],
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
        $query = ReportSmartboxInitialization::find()
            ->select('report_date, sum(total) as total')
            ->andWhere([
                'between',
                'report_date',
                $params['ReportSmartboxInitializationSearch']['from_date'],
                $params['ReportSmartboxInitializationSearch']['to_date']
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
            'total' => $this->total,
            'site_id' => $this->site_id,
            'type_model' => $this->type_model,
        ]);

        $query->andFilterWhere(['like', 'city', $this->city]);
        $query->groupBy('report_date');

        return $dataProvider;
    }

    public function searchDetail($params)
    {
        $query = ReportSmartboxInitializationDetailForm::find()
            ->select('device.device_id as mac, subscriber.register_at as report_date, subscriber.ip_to_location as city_code, device.device_type as type_model, device.serial')
            ->innerJoin('device', 'device.device_id = subscriber.machine_name')
            ->andWhere(['subscriber.type' => Subscriber::TYPE_USER])
            ->andWhere(['subscriber.site_id' => $params['ReportSmartboxInitializationSearch']['site_id']])
            ->andWhere([
                'between',
                'subscriber.register_at',
                $params['ReportSmartboxInitializationSearch']['from_date'],
                $params['ReportSmartboxInitializationSearch']['to_date']
            ]);

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

        if ($this->city) {
            $query->andFilterWhere(['subscriber.ip_to_location' => $this->city]);
        }

        if ($this->type_model) {
            $query->andFilterWhere(['device.device_type' => $this->type_model]);
        }
        return $dataProvider;
    }
}
