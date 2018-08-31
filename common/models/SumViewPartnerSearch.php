<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SumViewPartner;

/**
 * SumViewPartnerSearch represents the model behind the search form about `common\models\SumViewPartner`.
 */
class SumViewPartnerSearch extends SumViewPartner
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'subscriber_transaction_id', 'site_id', 'content_provider_id', 'cp_view_count', 'sp_view_count', 'created_at', 'updated_at'], 'integer'],
            [['amount', 'cp_revernue_percent'], 'number'],
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
        $query = SumViewPartner::find();

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
            'subscriber_transaction_id' => $this->subscriber_transaction_id,
            'site_id' => $this->site_id,
            'content_provider_id' => $this->content_provider_id,
            'cp_view_count' => $this->cp_view_count,
            'sp_view_count' => $this->sp_view_count,
            'amount' => $this->amount,
            'cp_revernue_percent' => $this->cp_revernue_percent,
            'report_date' => $this->report_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        return $dataProvider;
    }
}
