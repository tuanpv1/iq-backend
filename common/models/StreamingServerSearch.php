<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\StreamingServer;

/**
 * StreamingServerSearch represents the model behind the search form about `common\models\StreamingServer`.
 */
class StreamingServerSearch extends StreamingServer
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'port', 'status', 'created_at', 'updated_at'], 'integer'],
            [['name', 'ip', 'host', 'content_status_api', 'content_api', 'content_path', 'site_ids'], 'safe'],
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
        $query = StreamingServer::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'updated_at' => SORT_DESC,
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
            'port' => $this->port,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'ip', $this->ip])
            ->andFilterWhere(['like', 'host', $this->host])
            ->andFilterWhere(['like', 'content_status_api', $this->content_status_api])
            ->andFilterWhere(['like', 'content_api', $this->content_api])
            ->andFilterWhere(['like', 'content_path', $this->content_path]);

        if ($this->site_ids) {
            $ids = SiteStreamingServerAsm::find()
                ->select('streaming_server_id as id')
                ->where(['site_id' => $this->site_ids])
                ->asArray()
                ->all();
            $query->andOnCondition(['in', 'id', $ids]);
        }

        $query->andOnCondition(['in', 'status', [StreamingServer::STATUS_ACTIVE, StreamingServer::STATUS_INACTIVE]]);

        return $dataProvider;
    }
}
