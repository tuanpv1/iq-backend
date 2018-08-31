<?php
/**
 * Created by PhpStorm.
 * User: mycon
 * Date: 9/8/2017
 * Time: 5:01 PM
 */

namespace common\models;


use yii\base\Model;
use yii\data\ActiveDataProvider;

class NotificationSearch extends Notification
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'updated_at', ], 'integer'],
            [['name','function','type','content','updated_by'], 'safe'],
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
        $query = Notification::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'name'=>$this->name,
            'function'=>$this->function,
            'type'=>$this->type,
            'content'=>$this->content,
            'updated_by'=>$this->updated_by,
            'updated_at' => $this->updated_at,

        ]);

        return $dataProvider;
    }
}