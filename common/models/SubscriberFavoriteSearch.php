<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SubscriberFavorite;

/**
 * SubscriberFavoriteSearch represents the model behind the search form about `common\models\SubscriberFavorite`.
 */
class SubscriberFavoriteSearch extends SubscriberFavorite
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'subscriber_id', 'content_id', 'created_at', 'updated_at', 'site_id','type'], 'integer'],
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
//        $query = SubscriberFavorite::find();
        $query = \api\models\SubscriberFavorite::find();

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

        $query->andFilterWhere([
            'id' => $this->id,
            'subscriber_id' => $this->subscriber_id,
//            'content_id' => $this->content_id,
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
            'type' => $this->type,
            'site_id' => $this->site_id,
        ]);

        return $dataProvider;
    }
}
