<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SumServiceAmount;

/**
 * SumServiceAmountSearch represents the model behind the search form about `common\models\SumServiceAmount`.
 */
class SumServiceAmountSearch extends SumServiceAmount
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'service_id', 'site_id', 'type', 'created_at', 'updated_at'], 'integer'],
            [['amount'], 'number'],
            [['report_date'], 'safe'],
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
        $query = SumServiceAmount::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'type' => $this->type,
            'amount' => $this->amount,
            'report_date' => $this->report_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        return $dataProvider;
    }
}
