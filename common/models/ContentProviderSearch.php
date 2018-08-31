<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ContentProviderSearch represents the model behind the search form of `common\models\ContentProvider`.
 */
class ContentProviderSearch extends ContentProvider
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'updated_at'], 'integer'],
            [['username'], 'string'],
            [['cp_name', 'cp_address', 'cp_mst', 'created_at', 'username'], 'safe'],
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
        $query = ContentProvider::find()
            ->innerJoin('user', 'user.cp_id = content_provider.id')
            ->andWhere(['user.is_admin_cp' => ContentProvider::IS_ADMIN_CP]);
        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'cp_name',
                    'created_at',
                    'status',
                ],
            ],
        ]);

        $dataProvider->sort->attributes['username'] = [
            'asc' => ['user.username' => SORT_ASC],
            'desc' => ['user.username' => SORT_DESC],
        ];
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'content_provider.id' => $this->id,
            'content_provider.status' => $this->status,
            'content_provider.updated_at' => $this->updated_at,
        ]);
        if ($this->username) {
            $query->andWhere(['LIKE', 'user.username', $this->username]);
        }
        if ($this->created_at !== '' && $this->created_at !== null) {
            $from_time = strtotime(str_replace('/', '-', $this->created_at) . ' 00:00:00');
            $to_time = strtotime(str_replace('/', '-', $this->created_at) . ' 23:59:59');
            $query->andFilterWhere(['>=', 'content_provider.created_at', $from_time]);
            $query->andFilterWhere(['<=', 'content_provider.created_at', $to_time]);
        }

        $query->andFilterWhere(['like', 'content_provider.cp_name', $this->cp_name])
            ->andFilterWhere(['like', 'content_provider.cp_address', $this->cp_address])
            ->andFilterWhere(['like', 'content_provider.cp_mst', $this->cp_mst]);

        return $dataProvider;
    }
}
