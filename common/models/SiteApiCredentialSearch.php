<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ServiceProviderApiCredential;

/**
 * ServiceProviderApiCredentialSearch represents the model behind the search form about `common\models\ServiceProviderApiCredential`.
 */
class SiteApiCredentialSearch extends SiteApiCredential
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'site_id', 'type', 'status',  'updated_at'], 'integer'],
            [['client_name', 'client_api_key', 'client_secret', 'description', 'package_name', 'certificate_fingerprint', 'bundle_id', 'appstore_id','created_at'], 'safe'],
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
        $query = SiteApiCredential::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'site_id' => $this->site_id,
            'type' => $this->type,
            'status' => $this->status,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'client_name', $this->client_name])
            ->andFilterWhere(['like', 'client_api_key', $this->client_api_key])
            ->andFilterWhere(['like', 'client_secret', $this->client_secret])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'package_name', $this->package_name])
            ->andFilterWhere(['like', 'certificate_fingerprint', $this->certificate_fingerprint])
            ->andFilterWhere(['like', 'bundle_id', $this->bundle_id])
            ->andFilterWhere(['like', 'appstore_id', $this->appstore_id]);
        if($this->created_at){
            $from_date = strtotime(str_replace('/', '-', $this->created_at) . ' 00:00:00');
            $to_date = strtotime(str_replace('/', '-', $this->created_at) . ' 23:59:59');
            $query->andFilterWhere(['>=', 'created_at', $from_date]);
            $query->andFilterWhere(['<=', 'created_at', $to_date]);
        }

        return $dataProvider;
    }
}
