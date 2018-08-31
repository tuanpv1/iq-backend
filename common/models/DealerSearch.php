<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class DealerSearch extends Dealer
{

    public $userAdmin;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'site_id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['name', 'code', 'description', 'phone', 'email', 'address', 'userAdmin'], 'safe'],
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
        $query = Dealer::find();

        $query->joinWith('userAdmin');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'updated_at' => SORT_DESC,
                ]
            ],
        ]);

        $dataProvider->sort->attributes['userAdmin'] = [
            'asc' => ['user.fullname' => SORT_ASC],
            'desc' => ['user.fullname' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'dealer.id' => $this->id,
            'dealer.site_id' => $this->site_id,
            'dealer.created_at' => $this->created_at,
            'dealer.updated_at' => $this->updated_at,
            'dealer.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'dealer.email', $this->email])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'description', $this->description]);

        $query->andFilterWhere(['in', 'dealer.status', [Dealer::STATUS_ACTIVE, Dealer::STATUS_INACTIVE]]);

        $query->andFilterWhere(['like', 'user.fullname', $this->userAdmin]);

        return $dataProvider;
    }
}
