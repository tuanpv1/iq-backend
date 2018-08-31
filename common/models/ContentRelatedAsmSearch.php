<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ContentRelatedAsm;

/**
 * ContentRelatedAsmSearch represents the model behind the search form about `common\models\ContentRelatedAsm`.
 */
class ContentRelatedAsmSearch extends ContentRelatedAsm
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'content_id', 'content_related_id', 'updated_at', 'created_at'], 'integer'],
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
        $query = ContentRelatedAsm::find();

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
            'content_related_id' => $this->content_related_id,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ]);

        return $dataProvider;
    }
}
