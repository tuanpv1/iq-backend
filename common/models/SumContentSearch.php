<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SumContent;

/**
 * SumContentSearch represents the model behind the search form about `common\models\SumContent`.
 */
class SumContentSearch extends SumContent
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'site_id', 'content_provider_id', 'active_count', 'inactive_count', 'reject_count', 'delete_count', 'content_purchase_count', 'type', 'created_at', 'updated_at'], 'integer'],
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
        $query = SumContent::find();

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
            'site_id' => $this->site_id,
            'content_provider_id' => $this->content_provider_id,
            'active_count' => $this->active_count,
            'inactive_count' => $this->inactive_count,
            'reject_count' => $this->reject_count,
            'delete_count' => $this->delete_count,
            'content_purchase_count' => $this->content_purchase_count,
            'type' => $this->type,
            'report_date' => $this->report_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        return $dataProvider;
    }
}
