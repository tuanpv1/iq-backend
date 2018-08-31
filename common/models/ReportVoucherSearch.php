<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ReportVoucher;

/**
 * ReportVoucherSearch represents the model behind the search form of `common\models\ReportVoucher`.
 */
class ReportVoucherSearch extends ReportVoucher
{
    public $from_date;
    public $to_date;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'site_id', 'dealer_id', 'total_voucher_created'], 'integer'],
            [['report_date'], 'safe'],
            [['revenues_voucher', 'total_revenues'], 'number'],
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
        $query = ReportVoucher::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
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

        // grid filtering conditions
        $query->andFilterWhere([
            'dealer_id' => $this->dealer_id,
            'total_voucher_created' => $this->total_voucher_created,
            'revenues_voucher' => $this->revenues_voucher,
            'total_revenues' => $this->total_revenues,
        ]);

        if($this->site_id){
            $query->andWhere(['site_id'=>$this->site_id]);
        }
        if ($this->from_date) {
            $query->andFilterWhere(['>=', 'report_date', $this->from_date]);
        }
        if ($this->to_date) {
            $query->andFilterWhere(['<=', 'report_date', $this->to_date]);
        }
//        $query->groupBy('report_date');

        return $dataProvider;
    }
}
