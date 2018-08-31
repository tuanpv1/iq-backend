<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Campaign;

/**
 * CampaignSearch represents the model behind the search form of `common\models\Campaign`.
 */
class CampaignSearch extends Campaign
{
    public $cp_id;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'site_id', 'status', 'type', 'created_at', 'updated_at',  'priority', 'number_promotion'], 'integer'],
            [['activated_at', 'expired_at','name','ascii_name', 'description','cp_id'], 'safe'],
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
        $query = Campaign::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                 'defaultPageSize' => 25, // to set default count items on one page
//                 'pageSize' => 25, //to set count items on one page, if not set will be set from defaultPageSize
            ],
            'sort'  => [
                'defaultOrder' => ['updated_at' => SORT_DESC],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        /** Không lấy những thằng đã xóa */
        $query->andWhere(['<>', 'campaign.status', Campaign::STATUS_DELETE]);
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'site_id' => $this->site_id,
            'status' => $this->status,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
//            'activated_at' => $this->activated_at,
//            'expired_at' => $this->expired_at,
            'priority' => $this->priority,
            'number_promotion' => $this->number_promotion,
        ]);
        if ($this->activated_at) {
            $query->andFilterWhere(['>=', 'activated_at', strtotime($this->activated_at)]);
        }
        if ($this->expired_at) {
            $query->andFilterWhere(['<=', 'expired_at', strtotime("+1 day",strtotime($this->expired_at))]);
        }
//        $query->andFilterWhere(['like', 'name', $this->name])
//              ->andFilterWhere(['like', 'description', $this->description]);
        $query->andFilterWhere(['or',
            ['like', 'name', $this->name],
            ['like', 'ascii_name', $this->name],
        ]);

        return $dataProvider;
    }


    public function searchCampaignCP($params){
        $query = Campaign::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => 25, // to set default count items on one page
            ],
            'sort'  => [
                'defaultOrder' => ['updated_at' => SORT_DESC],
            ],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $list = Campaign::getCampaignByCP($this->cp_id);
        $query->andWhere(['id'=>$list]);
        $query->andWhere(['<>', 'campaign.status', Campaign::STATUS_DELETE]);
        $query->andFilterWhere(['or',
            ['like', 'name', $this->name],
            ['like', 'ascii_name', $this->name],
        ]);
        return $dataProvider;
    }
}
