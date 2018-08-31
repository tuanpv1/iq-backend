<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ReportContentProfile;

/**
 * ReportContentProfileSearch represents the model behind the search form of `common\models\ReportContentProfile`.
 */
class ReportContentProfileSearch extends ReportContentProfile
{
    public $from_date;
    public $to_date;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'report_date', 'total_content_profile'], 'integer'],
            [['from_date', 'to_date'], 'safe'],
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
        $query = ReportContentProfile::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'report_date' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->select('report_date, sum(total_content_profile) as total_content_profile');
        if ($this->from_date) {
            $query->andFilterWhere(['>=', 'report_date', $this->from_date]);
        }
        if ($this->to_date) {
            $query->andFilterWhere(['<=', 'report_date', $this->to_date]);
        }
        $query->groupBy('report_date');

//        // grid filtering conditions
//        $query->andFilterWhere([
//            'id' => $this->id,
//            'report_date' => $this->report_date,
//            'total_content_profile' => $this->total_content_profile,
//        ]);

        return $dataProvider;
    }
}
