<?php

namespace common\models;


use yii\base\Model;
use yii\data\ActiveDataProvider;

class ReportCampaignSearch  extends  ReportCampaign
{
    public $from_date;
    public $to_date;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'report_date', 'site_id', 'campaign_id','white_list', 'total_username','total_mac_address'], 'integer'],
            [['from_date', 'to_date','site_id', 'campaign_id'], 'safe'],
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
        $query = ReportCampaign::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
            'sort' => [
                'defaultOrder' => [
                    'report_date' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);
        $query->select('report_date,
                            sum(total_username) total_username,
                            sum(total_mac_address) total_mac_address,'
        );

        if (!$this->validate()) {
            return $dataProvider;
        }

        if ($this->site_id) {
            $query->andWhere(['site_id' => $this->site_id]);
        }

        if ($this->white_list) {
            $query->andWhere(['white_list' => $this->white_list]);
        }

        if ($this->from_date) {
            $query->andFilterWhere(['>=', 'report_date', $this->from_date]);
        }
        if ($this->to_date) {
            $query->andFilterWhere(['<=', 'report_date', $this->to_date]);
        }
        if ($this->campaign_id) {
            $query->andWhere(['campaign_id' => $this->campaign_id]);
        }
        $query->groupBy('report_date');
        return $dataProvider;
    }
}