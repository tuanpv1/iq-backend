<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Delay;

/**
 * DelaySearch represents the model behind the search form of `common\models\Delay`.
 */
class DelaySearch extends Delay
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'created_at', 'updated_at'], 'integer'],
            ['site_id','safe'],
            [['delay'], 'number'],
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

        $query = Delay::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->andWhere("delay.status != :status")->addParams([':status'=>Delay::STATUS_DELETED]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
//            'site_id' => $this->site_id,
            'delay' => $this->delay,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        if($this->site_id){
//             = Site::find()
            $query->innerJoin('site','site.id = delay.site_id')
                ->andFilterWhere(['like', 'name', $this->site_id]);
        }

        return $dataProvider;
    }
}