<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ServiceGroupSearch represents the model behind the search form about `\common\models\ServiceGroup`.
 */
class PricingSearch extends Pricing
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'site_id'], 'integer'],
            [['description'], 'safe'],
            [['id', 'site_id', 'type', 'watching_period', 'created_at', 'updated_at'], 'integer'],
            [['price_coin', 'price_sms'], 'number'],
            [['description'], 'safe']
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
    public function search($params,$sp_id)
    {
        $query = Pricing::find();
        $query->andWhere(['site_id'=>$sp_id]);

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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'site_id' => $this->site_id,
            'watching_period' => $this->watching_period,
            'type' => $this->type,
            'price_coin' => $this->price_coin,
            'price_sms' => $this->price_sms,
        ]);

        $query->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
