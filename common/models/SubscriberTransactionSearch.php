<?php

namespace common\models;

use common\models\SubscriberTransaction;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SubscriberTransactionSearch represents the model behind the search form about `common\models\SubscriberTransaction`.
 */
class SubscriberTransactionSearch extends SubscriberTransaction
{
    public $from_date;
    public $to_date;
    public $type_list;
    public $chanel_list;
    public $cp_id;
    public $expired_time;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subscriber_id', 'type', 'created_at', 'updated_at', 'status', 'channel', 'subscriber_activity_id', 'subscriber_service_asm_id', 'site_id'], 'integer'],
            [['msisdn', 'shortcode', 'description', 'event_id', 'error_code', 'balance'], 'safe'],
            [['from_date', 'to_date', 'service_id', 'content_id', 'transaction_time', 'white_list', 'cp_id', 'expired_time', 'id', 'cost'], 'safe'],
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
    public function searchCr33($params)
    {
        $query = SubscriberTransaction::find();
        $type = [
            SubscriberTransaction::TYPE_REGISTER,
            SubscriberTransaction::TYPE_PROMOTION,
            SubscriberTransaction::TYPE_CONTENT_PURCHASE,
            SubscriberTransaction::TYPE_VOUCHER,
            SubscriberTransaction::TYPE_VOUCHER_PHONE,
            SubscriberTransaction::TYPE_TOPUP_ATM,
            SubscriberTransaction::TYPE_TOPUP_VISA,
            SubscriberTransaction::TYPE_CANCEL,
            SubscriberTransaction::TYPE_RENEW,
        ];

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'transaction_time' => SORT_DESC,
                ]
            ],
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        $query->andFilterWhere([
            'subscriber_transaction.subscriber_id' => $this->subscriber_id,
            'subscriber_transaction.status' => $this->status,
            'subscriber_transaction.subscriber_activity_id' => $this->subscriber_activity_id,
            'subscriber_transaction.subscriber_service_asm_id' => $this->subscriber_service_asm_id,
            'subscriber_transaction.site_id' => $this->site_id,
        ]);

        $query->andFilterWhere(['!=', 'subscriber_transaction.status', SubscriberTransaction::STATUS_PENDING]);

        if ($this->created_at) {
            $query->andFilterWhere(['>=', 'subscriber_transaction.created_at', strtotime($this->created_at)]);
        }
        if ($this->updated_at) {
            $query->andFilterWhere(['>=', 'subscriber_transaction.updated_at', strtotime($this->updated_at)]);
        }

        if ($this->from_date) {
            Yii::info($this->from_date);
            $query->andFilterWhere(['>=', 'subscriber_transaction.transaction_time', strtotime($this->from_date)]);
        }

        if ($this->to_date) {
            Yii::info($this->to_date);
            $query->andFilterWhere(['<=', 'subscriber_transaction.transaction_time', strtotime($this->to_date)]);
        }

        if ($this->expired_time) {
            $query->andFilterWhere(['>=', 'subscriber_transaction.expired_time', strtotime($this->expired_time)]);
            $query->andFilterWhere(['<=', 'subscriber_transaction.expired_time', strtotime($this->expired_time . ' 23:59:59')]);
        }

        if ($this->transaction_time) {
            Yii::info($this->transaction_time);
            $query->andFilterWhere(['>=', 'subscriber_transaction.transaction_time', strtotime($this->transaction_time)]);
            $query->andFilterWhere(['<=', 'subscriber_transaction.transaction_time', strtotime($this->transaction_time . ' 23:59:59')]);
        }

        if (is_array($this->type_list)) {
            $query->andFilterWhere(['in', 'subscriber_transaction.type', $this->type_list]);
        }

        if (is_array($this->chanel_list)) {
            $query->andFilterWhere(['in', 'subscriber_transaction.channel', $this->chanel_list]);
        }

        if ($this->content_id) {
            $query->innerJoin('content', 'subscriber_transaction.content_id = content.id')
                ->andFilterWhere([
                    'OR',
                    ['like', 'content.display_name', $this->content_id],
                    ['like', 'content.ascii_name', $this->content_id]
                ]);
        }

        if ($this->service_id) {
            $query->innerJoin('service', 'subscriber_transaction.service_id = service.id')
                ->andFilterWhere(['like', 'service.display_name', $this->service_id]);
        }

        if ($this->channel) {
            if ($this->channel == SubscriberTransaction::CHANNEL_TYPE_RECHAGRE_FITTER) {
                $channel = [
                    SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_MOBIPHONE,
                    SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VIETTEL,
                    SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VINAPHONE,
                ];
                $query->andFilterWhere(['IN', 'subscriber_transaction.channel', $channel]);
            } else {
                if ($this->channel == SubscriberTransaction::CHANNEL_TYPE_ATM) {
                    $query->andWhere(['subscriber_transaction.type' => SubscriberTransaction::TYPE_TOPUP_ATM]);
                } else {
                    if ($this->channel == SubscriberTransaction::CHANNEL_TYPE_VISA) {
                        $query->andWhere(['subscriber_transaction.type' => SubscriberTransaction::TYPE_TOPUP_VISA]);
                    } else {
                        if ($this->channel == SubscriberTransaction::CHANNEL_TYPE_ANDROID) {
                            $query->andWhere(['IN', 'subscriber_transaction.type', [SubscriberTransaction::TYPE_REGISTER, SubscriberTransaction::TYPE_CONTENT_PURCHASE]]);
                        } else {
                            $query->andwhere(['subscriber_transaction.channel' => $this->channel]);
                        }
                    }
                }

            }
        }

        if ($this->white_list) {
            $query->andWhere(['subscriber_transaction.white_list' => $this->white_list]);
        }

        if (isset($this->type)) {
            if ($this->type == SubscriberTransaction::TYPE_MONEY) {
                $type_f = [
                    SubscriberTransaction::TYPE_VOUCHER,
                    SubscriberTransaction::TYPE_VOUCHER_PHONE,
                    SubscriberTransaction::TYPE_CHARGE_COIN,
                    SubscriberTransaction::TYPE_TOPUP_ATM,
                    SubscriberTransaction::TYPE_TOPUP_VISA,
                ];
                $query->andWhere(['IN', 'subscriber_transaction.type', $type_f]);
            } elseif ($this->type == null) {
                $query->andWhere(['IN', 'subscriber_transaction.type', $type]);
            } else {
                $query->andWhere(['subscriber_transaction.type' => $this->type]);
            }
        } else {
            $query->andWhere(['IN', 'subscriber_transaction.type', $type]);
        }

        if ($this->id) {
            $query->andWhere(['like', 'subscriber_transaction.id', $this->id]);
        }

        if ($this->cost) {
            preg_match_all('!\d+!', $this->cost, $cost);
            Yii::info($cost);
            if ($cost[0]) {
                Yii::info($cost[0][0]);
                $query->andWhere(['like', 'subscriber_transaction.cost', $cost[0][0]]);
            } else {
                $query->andWhere(['subscriber_transaction.cost' => -1]);
            }

        }

        return $dataProvider;
    }

    public function search($params)
    {
//        $query = SubscriberTransaction::find();
        $query = \api\models\SubscriberTransaction::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'transaction_time' => SORT_DESC,
                ]
            ],
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'subscriber_transaction.id' => $this->id,
            'subscriber_transaction.subscriber_id' => $this->subscriber_id,
            'subscriber_transaction.type' => $this->type,
            'subscriber_transaction.service_id' => $this->service_id,
//            'subscriber_transaction.transaction_time' => $this->transaction_time,
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
            'subscriber_transaction.status' => $this->status,
            'subscriber_transaction.cost' => $this->cost,
            'subscriber_transaction.channel' => $this->channel,
            'subscriber_transaction.subscriber_activity_id' => $this->subscriber_activity_id,
            'subscriber_transaction.subscriber_service_asm_id' => $this->subscriber_service_asm_id,
            'subscriber_transaction.site_id' => $this->site_id,
        ]);

        $query->andFilterWhere(['!=', 'subscriber_transaction.status', SubscriberTransaction::STATUS_PENDING]);

        if ($this->created_at) {
            $query->andFilterWhere(['>=', 'subscriber_transaction.created_at', strtotime($this->created_at)]);
        }
        if ($this->updated_at) {
            $query->andFilterWhere(['>=', 'subscriber_transaction.updated_at', strtotime($this->updated_at)]);
        }

        if ($this->from_date) {
            Yii::info($this->from_date);
            $query->andFilterWhere(['>=', 'subscriber_transaction.transaction_time', strtotime($this->from_date)]);
        }

        if ($this->to_date) {
            Yii::info($this->to_date);
            $query->andFilterWhere(['<=', 'subscriber_transaction.transaction_time', strtotime($this->to_date)]);
        }

        if ($this->expired_time) {
            $query->andFilterWhere(['>=', 'subscriber_transaction.expired_time', strtotime($this->expired_time)]);
            $query->andFilterWhere(['<=', 'subscriber_transaction.expired_time', strtotime($this->expired_time . ' 23:59:59')]);
        }

        if ($this->transaction_time) {
            Yii::info($this->transaction_time);
            $query->andFilterWhere(['>=', 'subscriber_transaction.transaction_time', strtotime($this->transaction_time)]);
            $query->andFilterWhere(['<=', 'subscriber_transaction.transaction_time', strtotime($this->transaction_time . ' 23:59:59')]);
        }

        if (is_array($this->type_list)) {
            $query->andFilterWhere(['in', 'subscriber_transaction.type', $this->type_list]);
        }

        if (is_array($this->chanel_list)) {
            $query->andFilterWhere(['in', 'subscriber_transaction.channel', $this->chanel_list]);
        }

        if ($this->content_id) {
            $query->innerJoin('content', 'subscriber_transaction.content_id = content.id')
                ->andFilterWhere(['like', 'content.display_name', $this->content_id]);
        }

        if ($this->channel) {
            $query->andwhere(['subscriber_transaction.channel' => $this->channel]);
        }

        if ($this->white_list) {
            $query->andWhere(['subscriber_transaction.white_list' => $this->white_list]);
        }

        return $dataProvider;
    }

    public function searchDetail($params)
    {
        $query = SubscriberTransaction::find();
        $query->innerJoin('service_cp_asm', 'service_cp_asm.service_id=subscriber_transaction.service_id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'transaction_time' => SORT_DESC,
                ]
            ],
        ]);

        $type = [
            SubscriberTransaction::TYPE_REGISTER,
            SubscriberTransaction::TYPE_RENEW,
            SubscriberTransaction::TYPE_RETRY,
            SubscriberTransaction::TYPE_REGISTER_BY_CHANGE_PACKAGE,
            SubscriberTransaction::TYPE_CANCEL,
            SubscriberTransaction::TYPE_USER_CANCEL,
            SubscriberTransaction::TYPE_CANCEL_SERVICE_BY_SYSTEM,
            SubscriberTransaction::TYPE_CANCEL_BY_API_VNPT,
        ];
        $query->andWhere(['IN', 'subscriber_transaction.type', $type]);
        $query->andWhere(['subscriber_transaction.status' => SubscriberTransaction::STATUS_SUCCESS]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['!=', 'subscriber_transaction.status', SubscriberTransaction::STATUS_PENDING]);
        
        if ($this->white_list) {
            $query->andWhere(['subscriber_transaction.white_list' => $this->white_list]);
        }
        if ($this->site_id) {
            $query->andWhere(['subscriber_transaction.site_id' => $this->site_id]);
        }
        if ($this->cp_id) {
            $query->andWhere(['service_cp_asm.cp_id' => $this->cp_id]);
        }
        if ($this->service_id) {
            $query->andWhere(['subscriber_transaction.service_id' => $this->service_id]);
        }
        if ($this->from_date) {
            $query->andWhere(['>=', 'subscriber_transaction.transaction_time', strtotime($this->from_date)]);
        }
        if ($this->to_date) {
            $query->andWhere(['<=', 'subscriber_transaction.transaction_time', strtotime($this->to_date)]);
        }

        return $dataProvider;
    }
}
