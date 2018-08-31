<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SumService;

/**
 * SumServiceSearch represents the model behind the search form about `common\models\SumService`.
 */
class SumServiceSearch extends SumService
{
    public $from_date;
    public $to_date;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'service_id', 'site_id', 'status', 'subscriber_count', 'register_count_success', 'register_count_false', 'renew_count', 'user_cancel_count', 'provider_cancel_count', 'created_at', 'updated_at'], 'integer'],
            [['report_date'], 'safe'],
            [['from_date','to_date'], 'safe'],
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
        $query = SumService::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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

        $query->andFilterWhere([
            'id' => $this->id,
            'service_id' => $this->service_id,
            'site_id' => $this->site_id,
            'status' => $this->status,
            'subscriber_count' => $this->subscriber_count,
            'register_count_success' => $this->register_count_success,
            'register_count_false' => $this->register_count_false,
            'renew_count' => $this->renew_count,
            'user_cancel_count' => $this->user_cancel_count,
            'provider_cancel_count' => $this->provider_cancel_count,
//            'report_date' => $this->report_date,
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
        ]);

        if($this->from_date){
            $query->andFilterWhere(['>=', 'report_date', date_format(date_create($this->from_date), 'Y-m-d') ]);
        }
        if($this->to_date){
            $query->andFilterWhere(['<=', 'report_date', date_format(date_create($this->to_date), 'Y-m-d') ]);
        }
        $query->groupBy('report_date');
        return $dataProvider;
    }
}