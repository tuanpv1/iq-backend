<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PriceCardSearch represents the model behind the search form about `\common\models\PriceCard`.
 */
class PriceCardSearch extends PriceCard
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'site_id','status'], 'integer'],
            [['description','price','created_at', 'updated_at','updated_status_at'], 'safe'],
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
        $query = PriceCard::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'price' => SORT_ASC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'site_id' => $this->site_id,
            'status' => $this->status,
        ]);
        if($this->created_at){
            $from_time = strtotime(str_replace('/', '-', $this->created_at) . ' 00:00:00');
            $to_time = strtotime(str_replace('/', '-', $this->created_at) . ' 23:59:59');
            $query->andFilterWhere(['>=', 'created_at', $from_time]);
            $query->andFilterWhere(['<=', 'created_at', $to_time]);
        }
        if($this->price){
            $query->andFilterWhere(['like', 'price', $this->price]);
        }
        return $dataProvider;
    }
    public function searchSP($params,$site)
    {
        $query = PriceCard::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'price' => SORT_ASC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'site_id' => $site,
            'status' => $this->status,
        ]);
        if($this->created_at){
            $from_time = strtotime(str_replace('/', '-', $this->created_at) . ' 00:00:00');
            $to_time = strtotime(str_replace('/', '-', $this->created_at) . ' 23:59:59');
            $query->andFilterWhere(['>=', 'created_at', $from_time]);
            $query->andFilterWhere(['<=', 'created_at', $to_time]);
        }
        if($this->price){
            $query->andFilterWhere(['like', 'price', $this->price]);
        }
        return $dataProvider;
    }
}
