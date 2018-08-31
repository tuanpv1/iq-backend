<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SmsMoSyntax;

/**
 * SmsMoSyntaxSearch represents the model behind the search form about `common\models\SmsMoSyntax`.
 */
class SmsMoSyntaxSearch extends SmsMoSyntax
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'type', 'service_id', 'site_id', 'status'], 'integer'],
            [['syntax', 'description'], 'safe'],
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
        $query = SmsMoSyntax::find();

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
            'type' => $this->type,
            'service_id' => $this->service_id,
            'site_id' => $this->site_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'syntax', $this->syntax])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
