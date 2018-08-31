<?php
/**
 * Created by PhpStorm.
 * User: mycon
 * Date: 4/26/2017
 * Time: 5:29 PM
 */

namespace common\models;


use yii\base\Model;
use yii\data\ActiveDataProvider;

class LogCampaignPromotionSearch extends LogCampaignPromotion
{
    public $from_date;
    public $to_date;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'type_campaign', 'subscriber_id', 'device_id', 'status', 'campaign_promotion_id', 'campaign_condition_id','updated_at','event_count'], 'integer'],
            [['campaign_name', 'subscriber_name', 'mac_address'], 'string', 'max' => 255],
            [['site_id', 'created_at', 'white_list', 'campaign_id','from_date', 'to_date'], 'safe'],
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
        $query = LogCampaignPromotion::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' =>false,
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

        $query->select('subscriber_name,mac_address,created_at');

        if($this->site_id){
            $query->andWhere(['site_id'=>$this->site_id]);
        }

        if($this->white_list){
            $query->andWhere(['white_list'=>$this->white_list]);
        }

        if ($this->from_date) {
            $query->andFilterWhere(['>=', 'created_at', strtotime($this->from_date)]);
        }
        if ($this->to_date) {
            $query->andFilterWhere(['<=', 'created_at',strtotime($this->to_date)]);
        }
        $query->andWhere(['campaign_id'=>$this->campaign_id]);
        $query->andWhere(['status'=>LogCampaignPromotion::STATUS_ACTIVE]);

        return $dataProvider;
    }
}