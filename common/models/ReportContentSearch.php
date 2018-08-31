<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ReportContent;

/**
 * ReportContentSearch represents the model behind the search form about `common\models\ReportContent`.
 */
class ReportContentSearch extends ReportContent
{
    public $from_date;
    public $to_date;
    public $categoryIds;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'report_date', 'site_id', 'content_type', 'category_id', 'total_content', 'count_content_upload_daily', 'total_content_view', 'total_content_buy','cp_id'], 'integer'],
            [['from_date', 'to_date'], 'safe'],
            [['categoryIds'], 'safe'],
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
        $query = ReportContent::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'report_date' =>SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->select('report_date,
                        sum(total_content) as total_content,
                        sum(count_content_upload_daily) as count_content_upload_daily,
                        sum(total_content_view) as total_content_view,
                        sum(total_content_buy) as total_content_buy'
        );

        if($this->site_id){
            $query->where(['site_id'=>$this->site_id]);
        }

        if($this->cp_id){
            $query->andWhere(['cp_id'=>$this->cp_id]);
        }

        if($this->content_type){
            $query->andwhere(['content_type'=>$this->content_type]);
        }

        $query->andwhere(['category_id'=>$this->category_id]);

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
