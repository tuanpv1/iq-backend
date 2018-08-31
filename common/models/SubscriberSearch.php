<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SubscriberSearch represents the model behind the search form about `common\models\Subscriber`.
 */
class SubscriberSearch extends Subscriber
{

    public $site;
    public $dealer;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'last_login_at', 'last_login_session', 'birthday', 'sex', 'client_type', 'using_promotion', 'auto_renew', 'verification_code'], 'integer'],
            [['msisdn', 'username', 'machine_name', 'email', 'full_name', 'avatar_url', 'skype_id', 'google_id', 'facebook_id', 'user_agent'], 'safe'],
            [['site_id', 'dealer_id', 'created_at', 'updated_at', 'register_at'], 'safe'],
            [['site', 'dealer', 'city', 'ip_address'], 'safe'],
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
        $query = Subscriber::find();

        $query->joinWith(['site' => function ($querySite) {
            $querySite->onCondition(['<>', 'site.status', Site::STATUS_REMOVE]);
        }, 'dealer' => function ($queryDl) {
            $queryDl->onCondition(['<>', 'dealer.status', Dealer::STATUS_DELETED]);
        }]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'updated_at' => SORT_DESC,
                ]
            ],
        ]);

        // Important: here is how we set up the sorting
        // The key is the attribute name on our "TourSearch" instance
        $dataProvider->sort->attributes['site'] = [
            // The tables are the ones our relation are configured to
            // in my case they are prefixed with "tbl_"
            'asc' => ['site.name' => SORT_ASC],
            'desc' => ['site.name' => SORT_DESC],
        ];
        // Lets do the same with country now
        $dataProvider->sort->attributes['dealer'] = [
            'asc' => ['dealer.name' => SORT_ASC],
            'desc' => ['dealer.name' => SORT_DESC],
        ];

        $this->load($params);
        // No search? Then return data Provider
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'subscriber.id' => $this->id,
            'subscriber.site_id' => $this->site_id,
            'subscriber.dealer_id' => $this->dealer_id,
            'subscriber.status' => $this->status,
            'subscriber.client_type' => $this->client_type,
        ]);

        if ($this->city) {
            $city = City::findOne(['name' => $this->city]);
            if ($city) {
                $query->andWhere(['province_code' => $city->code]);
            }
        }

        $query->andFilterWhere(['like', 'subscriber.msisdn', $this->msisdn])
            ->andFilterWhere(['like', 'subscriber.username', $this->username])
            ->andFilterWhere(['like', 'subscriber.email', $this->email])
            ->andFilterWhere(['like', 'subscriber.ip_address', $this->ip_address]);

        if ($this->created_at) {
            $query->andFilterWhere(['>=', 'subscriber.created_at', strtotime($this->created_at)]);
        }
        if ($this->updated_at) {
            $query->andFilterWhere(['>=', 'subscriber.updated_at', strtotime($this->updated_at)]);
        }

        $query->andWhere(['=', 'subscriber.authen_type', Subscriber::AUTHEN_TYPE_ACCOUNT])
            ->andWhere(['is not', 'subscriber.username', null]);;

        $query->andOnCondition(['in', 'subscriber.status', [Subscriber::STATUS_ACTIVE, Subscriber::STATUS_INACTIVE, Subscriber::STATUS_DELETED]]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchExt($params)
    {
        $query = Subscriber::find();

        $query->joinWith(['site', 'dealer']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'updated_at' => SORT_DESC,
                ]
            ],
        ]);

        // Important: here is how we set up the sorting
        // The key is the attribute name on our "TourSearch" instance
        $dataProvider->sort->attributes['site'] = [
            // The tables are the ones our relation are configured to
            // in my case they are prefixed with "tbl_"
            'asc' => ['site.name' => SORT_ASC],
            'desc' => ['site.name' => SORT_DESC],
        ];
        // Lets do the same with country now
        $dataProvider->sort->attributes['dealer'] = [
            'asc' => ['dealer.name' => SORT_ASC],
            'desc' => ['dealer.name' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'subscriber.id' => $this->id,
            'subscriber.status' => $this->status,
            'subscriber.client_type' => $this->client_type,
        ]);

//        if ($this->site_id) {
//            $query->joinWith('serviceProvider');
//            $query->andWhere(['like', 'site.name', $this->site_id]);
//        }


        if ($this->created_at) {
            $query->andFilterWhere(['>=', 'subscriber.created_at', strtotime($this->created_at)]);
        }
        if ($this->updated_at) {
            $query->andFilterWhere(['>=', 'subscriber.updated_at', strtotime($this->updated_at)]);
        }

        $query->andFilterWhere(['like', 'subscriber.msisdn', $this->msisdn])
            ->andFilterWhere(['like', 'site.name', $this->site])
            ->andFilterWhere(['like', 'dealer.name', $this->dealer]);

        return $dataProvider;
    }
}
