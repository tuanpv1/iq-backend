<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Ads;

/**
 * AdsSearch represents the model behind the search form about `common\models\Ads`.
 */
class AdsSearch extends Ads
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'app_ads_id', 'site_id', 'type', 'status','expired_date'], 'integer'],
            [['name', 'image', 'target_url', 'extra'], 'safe'],
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
//        $query = Ads::find();
        $query = \api\models\Ads::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
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
//            'id' => $this->id,
//            'app_ads_id' => $this->app_ads_id,
            'site_id' => $this->site_id,
//            'type' => $this->type,
            'status' => $this->status,
        ]);

        if($this->type){
            $query->andWhere(['type'=>$this->type]);
        }
//
//        $query->andFilterWhere(['like', 'name', $this->name])
//            ->andFilterWhere(['like', 'image', $this->image])
//            ->andFilterWhere(['like', 'target_url', $this->target_url])
//            ->andFilterWhere(['like', 'extra', $this->extra]);

        $query->andFilterWhere(['>=', 'expired_date', time()]);

        return $dataProvider;
    }
}
