<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ReportSubscriberActivity;

/**
 * ReportSubscriberActivitySearch represents the model behind the search form about `common\models\ReportSubscriberActivity`.
 */
class ReportSubscriberActivitySearch extends ReportSubscriberActivity
{
    public $from_date;
    public $to_date;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'report_date', 'site_id', 'via_site_daily', 'total_via_site', 'content_type'], 'integer'],
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
        $query = ReportSubscriberActivity::find();

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

//        $query->andFilterWhere([
//            'id' => $this->id,
//            'report_date' => $this->report_date,
//            'site_id' => $this->site_id,
//            'via_site' => $this->via_site,
//            'view_content' => $this->view_content,
//        ]);
        $query->select('report_date,
                        sum(total_via_site) as total_via_site,
                        sum(via_site_daily) as via_site_daily,
                        sum(via_smb) as via_smb,
                        sum(via_android) as via_android,
                        sum(via_ios) as via_ios,
                        sum(via_website) as via_website'
        );

        if($this->site_id){
            $query->where(['site_id'=>$this->site_id]);
        }

        if($this->content_type){
            $query->andFilterWhere(['content_type'=>$this->content_type]);
        }

        if ($this->from_date) {
            $query->andFilterWhere(['>=', 'report_date', $this->from_date]);
        }
        if ($this->to_date) {
            $query->andFilterWhere(['<=', 'report_date', $this->to_date]);
        }
        $query->groupBy('report_date');
        return $dataProvider;
    }
}
