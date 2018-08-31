<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ServiceGroup;

/**
 * ServiceGroupSearch represents the model behind the search form about `\common\models\ServiceGroup`.
 */
class ServiceGroupSearch extends ServiceGroup
{
    public $listCatIds;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'created_at', 'updated_at', 'site_id'], 'integer'],
            [['name', 'display_name', 'description'], 'safe'],
            [['listCatIds'], 'safe'],
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
    public function search($params,$sp_id = null)
    {
//        $query = ServiceGroup::find();
        $query = \api\models\ServiceGroup::find();
       if($sp_id){
           $query->andWhere(['site_id'=>$sp_id]);
       }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if ($this->listCatIds) {
            $query->andWhere(['id' => $this->listCatIds]);
        }
        $query->andFilterWhere([
//            'id' => $this->id,
            'status' => $this->status,
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
            'site_id' => $this->site_id,
        ]);

        if ($this->listCatIds) {
            $query->andWhere(['id' => $this->listCatIds]);
        }

       $query->andFilterWhere(['like', 'name', $this->name])
           ->andFilterWhere(['like', 'display_name', $this->display_name])
           ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
