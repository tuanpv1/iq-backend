<?php

namespace common\models;

use common\models\ReportSubscriberService;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ReportSubscriberServiceSearch represents the model behind the search form of `common\models\ReportSubscriberService`.
 */
class ReportSubscriberServiceSearch extends ReportSubscriberService
{
    public $from_date;
    public $to_date;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'site_id', 'dealer_id', 'service_id', 'subscriber_register', 'subscriber_retry', 'subscriber_expired','subscriber_not_expiration', 'white_list','cp_id'], 'integer'],
            [['report_date'], 'safe'],
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
        $query = ReportSubscriberService::find();
        $query->addSelect('sum(subscriber_register) as subscriber_register');
        $query->addSelect('sum(subscriber_retry) as subscriber_retry');
        $query->addSelect('sum(subscriber_expired) as subscriber_expired');
        $query->addSelect('sum(subscriber_not_expiration) as subscriber_not_expiration');
        $query->addSelect('report_date');
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort'       => [
                'defaultOrder' => [
                    'report_date' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'subscriber_register' => $this->subscriber_register,
        ]);

        if ($this->site_id) {
            $query->andFilterWhere(['site_id' => $this->site_id]);
        }

        if($this->cp_id){
            $query->andFilterWhere(['cp_id' => $this->cp_id]);
        }
        if ($this->from_date) {
            $query->andFilterWhere(['>=', 'report_date', $this->from_date]);
        }
        if ($this->to_date) {
            $query->andFilterWhere(['<=', 'report_date', $this->to_date]);
        }

        if ($this->service_id) {
            $query->andFilterWhere(['service_id' => $this->service_id]);
        }
        if (!empty($this->white_list)) {
                $query->andWhere(['white_list'=>$this->white_list]);
        }

        $query->groupBy('report_date');

        return $dataProvider;
    }
}
