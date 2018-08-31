<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Category;

/**
 * CategorySearch represents the model behind the search form about `\common\models\Category`.
 */
class CategorySearch extends Category
{
    public $site_id;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'site_id', 'type', 'status', 'order_number', 'parent_id', 'level', 'child_count', 'created_at', 'updated_at'], 'integer'],
            [['display_name', 'ascii_name', 'description', 'path', 'images', 'admin_note'], 'safe'],
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
    public function search($params, $type = null)
    {
        $query = Category::find();

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
            'type' => $type,
            'status' => $this->status,
            'order_number' => $this->order_number,
            'parent_id' => $this->parent_id,
            'level' => $this->level,
            'child_count' => $this->child_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'display_name', $this->display_name])
            ->andFilterWhere(['like', 'ascii_name', $this->ascii_name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'path', $this->path])
            ->andFilterWhere(['like', 'images', $this->images])
            ->andFilterWhere(['like', 'admin_note', $this->admin_note]);

        return $dataProvider;
    }
}
