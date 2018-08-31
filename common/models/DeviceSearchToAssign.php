<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Device;
use yii\db\Query;

/**
 * DeviceSearch represents the model behind the search form about `common\models\Device`.
 */
class DeviceSearchToAssign extends Device
{

    public $site;
    public $dealer;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['site_id', 'dealer_id'], 'integer'],
            [['device_id'], 'safe'],
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

    public function searchToAssignForSubscriber($params)
    {
        $query = Device::find();

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
        // No search? Then return data Provider
        if (!($this->load($params) && $this->validate())) {
            $query->andFilterWhere(['in', 'device.status', [Device::STATUS_ACTIVE, Device::STATUS_INACTIVE, Device::STATUS_NEW]]);
            return $dataProvider;
        }

        $query->andFilterWhere([
            'device.site_id' => $this->site_id,
            'device.dealer_id' => $this->dealer_id,
        ]);

        $query->andOnCondition(['in', 'device.status', [Device::STATUS_ACTIVE, Device::STATUS_INACTIVE, Device::STATUS_NEW]]);

        $query->andFilterWhere(['like', 'device.device_id', $this->device_id]);
        $device = SubscriberDeviceAsm::findAll(['status'=>SubscriberDeviceAsm::STATUS_ACTIVE]);
        if($device){
            foreach($device as $item){
                $list_device[] = $item->device_id;
            }
            $query->andWhere(['not in','device.id',$list_device]);
        }

        return $dataProvider;
    }
}
