<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ContentLog;

/**
 * ContentLogSearch represents the model behind the search form about `\common\models\ContentLog`.
 */
class ContentLogSearch extends ContentLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'content_id', 'created_at', 'status', 'type', 'site_id', 'user_id'], 'integer'],
            [['ip_address', 'description', 'user_agent'], 'safe'],
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
        $query = ContentLog::find();
        $query->andFilterWhere(['site_id' => $sp_id]);
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
            'content_id' => $this->content_id,
            'created_at' => $this->created_at,
            'status' => $this->status,
            'type' => $this->type,
            'site_id' => $this->site_id,
            'user_id' => $this->user_id,
        ]);

        $query->andFilterWhere(['like', 'ip_address', $this->ip_address])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'user_agent', $this->user_agent]);

        return $dataProvider;
    }
}
