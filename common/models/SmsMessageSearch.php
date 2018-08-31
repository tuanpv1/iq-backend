<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SmsMessage;

/**
 * SmsMessageSearch represents the model behind the search form about `common\models\SmsMessage`.
 */
class SmsMessageSearch extends SmsMessage
{
    public $from_date;
    public $to_date;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'subscriber_id', 'sms_template_id', 'type', 'status', 'received_at', 'sent_at', 'mo_id', 'site_id'], 'integer'],
            [['msisdn', 'source', 'destination', 'message', 'mt_status', 'mo_status', 'from_date','to_date'], 'safe'],
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
        $query = SmsMessage::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'sent_at' => SORT_DESC,
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
            'subscriber_id' => $this->subscriber_id,
            'sms_template_id' => $this->sms_template_id,
            'type' => $this->type,
            'status' => $this->status,
            'received_at' => $this->received_at,
            'sent_at' => $this->sent_at,
            'mo_id' => $this->mo_id,
            'site_id' => $this->site_id,
        ]);

        $query
            ->andFilterWhere(['like', 'source', $this->source])
            ->andFilterWhere(['like', 'destination', $this->destination])
            ->andFilterWhere(['like', 'message', $this->message])
            ->andFilterWhere(['like', 'mt_status', $this->mt_status])
            ->andFilterWhere(['like', 'mo_status', $this->mo_status]);

        if($this->sent_at){
            $query->andFilterWhere(['>=', 'sent_at', strtotime($this->sent_at)]);
        }
        if($this->received_at){
            $query->andFilterWhere(['>=', 'received_at', strtotime($this->received_at)]);
        }

        if($this->from_date){
            $query->andFilterWhere(['>=', 'sent_at', strtotime($this->from_date)]);
        }
        if($this->to_date){
            $query->andFilterWhere(['<=', 'sent_at', strtotime($this->to_date)]);
        }

        $query->orderBy(['sent_at' => SORT_DESC, 'received_at' => SORT_DESC,'id' => SORT_DESC]);

        return $dataProvider;
    }

    public function searchTpMtReport($params)
    {
        $query = SmsMessage::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'sent_at' => SORT_DESC,
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
            'subscriber_id' => $this->subscriber_id,
            'sms_template_id' => $this->sms_template_id,
            'type' => $this->type,
            'type_mt' => self::TYPE_MT_OTP,
            'status' => $this->status,
            'sent_at' => $this->sent_at,
            'mo_id' => $this->mo_id,
            'site_id' => $this->site_id,
        ]);

        if($this->sent_at){
            $query->andFilterWhere(['>=', 'sent_at', strtotime($this->sent_at)]);
        }

        if($this->from_date){
            $query->andFilterWhere(['>=', 'sent_at', strtotime($this->from_date)]);
        }
        if($this->to_date){
            $query->andFilterWhere(['<=', 'sent_at', strtotime($this->to_date)]);
        }

        if($this->msisdn){
            $query->andFilterWhere(['like', 'msisdn', $this->msisdn]);
        }

        return $dataProvider;
    }
}
