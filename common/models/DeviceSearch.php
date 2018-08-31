<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Device;

/**
 * DeviceSearch represents the model behind the search form about `common\models\Device`.
 */
class DeviceSearch extends Device
{

    public $site;
    public $dealer;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'device_type', 'created_at', 'updated_at', 'status', 'site_id', 'dealer_id'], 'integer'],
            [['device_id', 'device_firmware', 'last_ip','serial'], 'safe'],
            [['site', 'dealer', 'expired_at', 'activated_at'], 'safe'],
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
        $query = Device::find();

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
        // No search? Then return data Provider
        if (!($this->load($params) && $this->validate())) {
            $query->andFilterWhere(['in', 'device.status', [Device::STATUS_ACTIVE, Device::STATUS_INACTIVE, Device::STATUS_NEW]]);
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'device_type' => $this->device_type,
            'device.status' => $this->status,
            'device.site_id' => $this->site_id,
            'device.dealer_id' => $this->dealer_id,
        ]);

        if ($this->expired_at) {
            $query->andFilterWhere(['>=', 'expired_at', strtotime($this->expired_at)]);
        }
        if ($this->activated_at) {
            $query->andFilterWhere(['>=', 'activated_at', strtotime($this->activated_at)]);
        }

        $query->andOnCondition(['in', 'device.status', [Device::STATUS_ACTIVE, Device::STATUS_INACTIVE, Device::STATUS_NEW]]);

        $query->andFilterWhere(['like', 'device_id', $this->device_id])
            ->andFilterWhere(['like', 'device_firmware', $this->device_firmware])
            ->andFilterWhere(['like', 'serial', trim($this->serial)])
            ->andFilterWhere(['like', 'last_ip', $this->last_ip])
            ->andFilterWhere(['like', 'site.name', $this->site])
            ->andFilterWhere(['like', 'dealer.name', $this->dealer]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchEx($params, $arr = null)
    {
        $query = Device::find();

        $query->joinWith(['site', 'dealer']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
        // No search? Then return data Provider
        if (!($this->load($params) && $this->validate())) {
            $query->andFilterWhere(['in', 'device.status', [Device::STATUS_ACTIVE, Device::STATUS_INACTIVE, Device::STATUS_NEW]]);
            return $dataProvider;
        }

        $query->andFilterWhere([
            'device.device_type' => $this->device_type,
            'device.created_at' => $this->created_at,
            'device.updated_at' => $this->updated_at,
            'device.status' => $this->status,
            'device.site_id' => $this->site_id,
            'device.dealer_id' => $this->dealer_id,
        ]);

        $query->andFilterWhere(['like', 'device.device_id', $this->device_id])
            ->andFilterWhere(['like', 'device.device_firmware', $this->device_firmware])
            ->andFilterWhere(['like', 'device.last_ip', $this->last_ip])
            ->andFilterWhere(['like', 'site.name', $this->site])
            ->andFilterWhere(['like', 'dealer.name', $this->dealer]);

        $query->andOnCondition(['in', 'device.status', [Device::STATUS_ACTIVE, Device::STATUS_INACTIVE, Device::STATUS_NEW]]);

        if ($arr) {
            $query->andOnCondition(['in', 'device.id', $arr]);
        }

        return $dataProvider;
    }
}
