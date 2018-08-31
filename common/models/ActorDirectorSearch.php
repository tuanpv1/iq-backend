<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ActorDirectorSearch represents the model behind the search form about `common\models\ActorDirector`.
 */
class ActorDirectorSearch extends ActorDirector
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type', 'content_type', 'status', 'created_at', 'updated_at'], 'integer'],
            [['name', 'description', 'image'], 'safe'],
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
    public function search($params,$pagination=false)
    {
//        $query = ActorDirector::find();
        $query = \api\models\ActorDirector::find();

        if(!$pagination){
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => false,
//            'pagination' => [
//                'defaultPageSize' => 100000,
//            ],
            ]);
        }else{
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'defaultPageSize' => 20,
                ],
                'sort' => [
                    'defaultOrder' => [
                        'updated_at' => SORT_DESC,
                    ]
                ],
            ]);
        }


        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'content_type' => $this->content_type,
//            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
        /** Không lấy thằng đã xóa */
        $query->andWhere('status != 2');
        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'image', $this->image]);

        return $dataProvider;
    }
}
