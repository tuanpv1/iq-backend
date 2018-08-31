<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * IpAddressSearch represents the model behind the search form about `\common\models\IpAddress`.
 */
class IpAddressSearch extends IpAddress
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','ip','type','country','city','stateprov','ip_start','ip_end'], 'safe'],
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
        $query = IpAddress::find()->andWhere(['type'=>IpAddress::TYPE_NSX]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'city' => SORT_ASC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'ip_start'=>$this->ip_start,
            'ip_end'=>$this->ip_end,
            'ip'=>$this->ip,
            'stateprov'=>$this->stateprov,
            'city'=>$this->city,
            'country'=>$this->country,
        ]);
        return $dataProvider;
    }

}
