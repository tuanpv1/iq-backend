<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ReportUserDaily;

/**
 * ReportUserDailySearch represents the model behind the search form about `common\models\ReportUserDaily`.
 */
class ReportUserDailySearch extends ReportUserDaily
{
    public $from_date;
    public $to_date;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'report_date', 'site_id', 'active_user', 'active_user_package'], 'integer'],
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
        $query = ReportUserDaily::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
            'sort' => [
                'defaultOrder' => [
                    'report_date' => SORT_ASC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

//        $query->andFilterWhere([
//            'id' => $this->id,
//            'report_date' => $this->report_date,
//            'site_id' => $this->site_id,
//            'active_user' => $this->active_user,
//            'active_user_package' => $this->active_user_package,
//        ]);
//        echo $this->from_date;exit;
        //Nếu tồn tại thì mới kiểm tra điều kiện
        if($this->site_id){
            $query->where(['site_id'=>$this->site_id]);
        }
        if ($this->from_date) {
//            $query->andWhere(['>=', 'report_date', $this->from_date]);
            $query->andFilterWhere(['>=', 'report_date', $this->from_date]);
        }
        if ($this->to_date) {
//            $query->andWhere(['<=', 'report_date', $this->to_date]);
            $query->andFilterWhere(['<=', 'report_date', $this->to_date]);
        }
        return $dataProvider;
    }

}
