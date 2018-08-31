<?php

namespace common\models;

use DateTime;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * GroupSubscriberSearch represents the model behind the search form about `\common\models\GroupSubscriber`.
 */

class GroupSubscriberSearch extends GroupSubscriber
{
    public  $to_time;
    public  $from_time;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'site_id', 'updated_at', 'status', 'type_import', 'last_import_at', 'subscriber_count', 'type_subsriber'], 'integer'],
            [['name', 'description','created_at'], 'safe'],
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
        $query = GroupSubscriber::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 25,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
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
            'updated_at' => $this->updated_at,
            'site_id' => $this->site_id,
            'description' => $this->description,
            'status' => $this->status,
            'type_import' => $this->type_import,
            'last_import_at' => $this->last_import_at,
            'subscriber_count' => $this->subscriber_count,
            'type_subsriber' => $this->type_subsriber,
            ]);

        if ($this->created_at) {

            $from_time = strtotime(str_replace('/', '-', $this->created_at) . ' 00:00:00');
            $to_time = strtotime(str_replace('/', '-', $this->created_at) . ' 23:59:59');
            $query->andFilterWhere(['>=', 'created_at',$from_time]);
            $query->andFilterWhere(['<=', 'created_at',$to_time]);
        }
        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;

    }
}

