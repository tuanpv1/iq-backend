<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Multilanguage;

/**
 * MultilanguageSearch represents the model behind the search form of `common\models\Multilanguage`.
 */
class MultilanguageSearch extends Multilanguage
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'updated_at', 'created_at', 'status', 'is_default'], 'integer'],
            [['name', 'code', 'description'], 'safe'],
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
        $query = Multilanguage::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'status' => $this->status,
            'is_default' => $this->is_default,
        ]);

        $query->andFilterWhere(['like', 'lower(name)', strtolower($this->name)])
            ->andFilterWhere(['like', 'lower(code)', strtolower($this->code)])
            ->andFilterWhere(['like', 'lower(description)', strtolower($this->description)]);

        return $dataProvider;
    }
}
