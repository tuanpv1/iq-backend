<?php

namespace common\models;

use common\models\UserActivity;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * UserActivitySearch represents the model behind the search form about `common\models\UserActivity`.
 */
class UserActivitySearch extends UserActivity
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'target_id', 'target_type', 'status', 'site_id', 'dealer_id'], 'integer'],
            [['username', 'ip_address', 'user_agent', 'action', 'created_at', 'description', 'request_detail', 'request_params'], 'safe'],
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
        $query = UserActivity::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'target_id'   => $this->target_id,
            'target_type' => $this->target_type,
            // 'created_at' => $this->created_at,
            'status'      => $this->status,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'ip_address', $this->ip_address])
            ->andFilterWhere(['like', 'user_agent', $this->user_agent])
            ->andFilterWhere(['like', 'action', $this->action])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'request_detail', $this->request_detail])
            ->andFilterWhere(['like', 'request_params', $this->request_params]);

        if ($this->created_at) {
            $create_at     = strtotime($this->created_at);
            $create_at_end = $create_at + (60 * 60 * 24);
            $query->andFilterWhere(['>=', 'created_at', $create_at]);
            $query->andFilterWhere(['<=', 'created_at', $create_at_end]);
        }

        return $dataProvider;
    }
}
