<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

//use common\models\SubscriberServiceAsm;

/**
 * SubscriberServiceAsmSearch represents the model behind the search form about `common\models\SubscriberServiceAsm`.
 */
class SubscriberServiceAsmSearch extends SubscriberServiceAsm
{
    public $status = SubscriberServiceAsm::STATUS_ACTIVE;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'service_id', 'subscriber_id', 'site_id', 'dealer_id', 'activated_at', 'renewed_at', 'expired_at', 'last_renew_fail_at', 'renew_fail_count', 'status', 'created_at', 'updated_at', 'pending_date', 'view_count', 'download_count', 'gift_count', 'watching_time', 'subscriber2_id', 'transaction_id', 'cancel_transaction_id', 'last_renew_transaction_id'], 'integer'],
            [['msisdn', 'service_name', 'description'], 'safe'],
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
    public function search($params, $view = false)
    {
//        $query = SubscriberServiceAsm::find();
        $query = \api\models\SubscriberServiceAsm::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'site_id' => SORT_DESC,
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
//            'id' => $this->id,
//            'service_id' => $this->service_id,
            'subscriber_id' => $this->subscriber_id,
            'site_id' => $this->site_id,
            'dealer_id' => $this->dealer_id,
//            'activated_at' => $this->activated_at,
//            'renewed_at' => $this->renewed_at,
//            'expired_at' => $this->expired_at,
//            'last_renew_fail_at' => $this->last_renew_fail_at,
//            'renew_fail_count' => $this->renew_fail_count,
            'status' => $this->status,
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
//            'pending_date' => $this->pending_date,
//            'view_count' => $this->view_count,
//            'download_count' => $this->download_count,
//            'gift_count' => $this->gift_count,
//            'watching_time' => $this->watching_time,
//            'subscriber2_id' => $this->subscriber2_id,
//            'transaction_id' => $this->transaction_id,
//            'cancel_transaction_id' => $this->cancel_transaction_id,
//            'last_renew_transaction_id' => $this->last_renew_transaction_id,
        ]);
        // Chỉ lấy những gói có thời gian hết hạn lớn hơn thời gian hiện tại
        if ($view) {
            $query->andWhere(['>=', 'expired_at', time()]);
        }
//
//        $query->andFilterWhere(['like', 'msisdn', $this->msisdn])
//            ->andFilterWhere(['like', 'service_name', $this->service_name])
//            ->andFilterWhere(['like', 'description', $this->description]);
//        $query->andWhere(['or', ['expired_at' => null], ['>=', 'expired_at', time()]]);


        return $dataProvider;
    }
}
