<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ReportSubscriberNumber;
use  yii\data\ArrayDataProvider;

/**
 * ReportSubscriberNumberSearch represents the model behind the search form about `common\models\ReportSubscriberNumber`.
 */

class ReportSubscriberNumberSearch extends ReportSubscriberNumber{
    public $from_date;
    public $to_date;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'report_date', 'site_id', 'subscriber_register_smb', 'subscriber_register_apps', 'subscriber_register_web','total_subscriber','subscriber_active','subscriber_register','total_subscriber_destroy','subscriber_destroy'], 'integer'],
            [['city'],'string'],
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
        $query = ReportSubscriberNumber::find();

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
        $query->select('report_date,
                         sum(total_subscriber) as total_subscriber,
                          sum(subscriber_active) as subscriber_active,
                          sum(subscriber_register)as subscriber_register,
                         sum(total_subscriber_destroy) as total_subscriber_destroy,
                          sum(subscriber_destroy) as subscriber_destroy,
                          sum(subscriber_register_smb) as subscriber_register_smb,
                          sum(subscriber_register_apps) as subscriber_register_apps,
                          sum(subscriber_register_web) as subscriber_register_web'
        );

        if($this->site_id){
            $query->where(['site_id'=>$this->site_id]);
        }

        if($this->city){
            $query->andFilterWhere(['city'=>$this->city]);
        }

        if ($this->from_date) {
//            $query->andWhere(['>=', 'report_date', $this->from_date]);
            $query->andFilterWhere(['>=', 'report_date', $this->from_date]);
        }
        if ($this->to_date) {
//            $query->andWhere(['<=', 'report_date', $this->to_date]);
            $query->andFilterWhere(['<=', 'report_date', $this->to_date]);
        }
        $query->groupBy('report_date');

        return $dataProvider;
    }

    public function searchDetail($params)
    {

        $query = Subscriber::find()->andWhere(['type' => Subscriber::TYPE_USER])
        ->andWhere(['authen_type' => Subscriber::AUTHEN_TYPE_ACCOUNT]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        if($this->site_id) {
            $query->andFilterWhere(['site_id' => $this->site_id]);
        }
        if($this->city) {
            $query->andFilterWhere(['city' => $this->city]);
        }

        if($this->from_date){
            $query->andFilterWhere(['>=', 'register_at', $this->from_date]);
        }
        if($this->to_date){
            $query->andFilterWhere(['<=', 'register_at', $this->to_date]);
        }

        return $dataProvider;
    }
}

