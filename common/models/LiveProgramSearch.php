<?php

namespace common\models;

use common\models\LiveProgram;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * LiveProgramSearch represents the model behind the search form about `common\models\LiveProgram`.
 */
class LiveProgramSearch extends LiveProgram
{
    public $from_date;
    public $to_date;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'channel_id', 'content_id', 'status', 'started_at', 'ended_at', 'created_at', 'updated_at'], 'integer'],
            [['name', 'description'], 'safe'],
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
        $query = \api\models\LiveProgram::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'started_at' => SORT_DESC,
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
//            'id' => $this->id,
            'channel_id' => $this->channel_id,
//            'content_id' => $this->content_id,
//            'status' => $this->status,
//            'started_at' => $this->started_at,
//            'ended_at' => $this->ended_at,
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
        ]);

//        $query->andFilterWhere(['like', 'name', $this->name])
//            ->andFilterWhere(['like', 'description', $this->description]);

        if($this->from_date){
            $query->andFilterWhere(['>=', 'started_at', $this->from_date]);
        }
        if($this->to_date){
            $query->andFilterWhere(['<=', 'started_at', $this->to_date]);
        }
        return $dataProvider;
    }
}
