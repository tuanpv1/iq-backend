<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\AppAds;

/**
 * AppAdsSearch represents the model behind the search form about `common\models\AppAds`.
 */
class AppAdsSearch extends AppAds
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'site_id', 'created_at', 'updated_at'], 'integer'],
            [['app_name', 'package_name', 'app_key'], 'safe'],
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
        $query = AppAds::find();

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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'app_name', $this->app_name])
            ->andFilterWhere(['like', 'package_name', $this->package_name])
            ->andFilterWhere(['like', 'app_key', $this->app_key]);

        return $dataProvider;
    }
}
