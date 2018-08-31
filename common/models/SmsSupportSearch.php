<?php

namespace common\models;

use DateTime;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SmsSupport;

/**
 * SmsSmsSupportSearch represents the model behind the search form of `common\models\SmsSupport`.
 */
class SmsSupportSearch extends SmsSupport
{
    public $to_date;
    public $from_date;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'updated_at', 'created_at', 'type', 'status'], 'integer'],
            [['title', 'content', 'description', 'file_user', 'file_log'], 'safe'],
            [['from_date', 'to_date'],'safe'],
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
        $query = SmsSupport::find();
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'file_user', $this->file_user])
            ->andFilterWhere(['like', 'file_log', $this->file_log]);

        if(isset($params['SendEmailInternalForm']['from_date'])){
            $query->andFilterWhere(['>=', 'created_at', strtotime(DateTime::createFromFormat("d/m/Y", $params['SendEmailInternalForm']['from_date'])->setTime(0, 0)->format('Y-m-d H:i:s'))]);
        }
        if(isset($params['SendEmailInternalForm']['type'])){
            $query->andFilterWhere(['type'=>$params['SendEmailInternalForm']['type']]);
        }

        if(isset($params['SendEmailInternalForm']['to_date'])){
            $query->andFilterWhere(['<=', 'created_at', strtotime(DateTime::createFromFormat("d/m/Y", $params['SendEmailInternalForm']['to_date'])->setTime(23, 59, 59)->format('Y-m-d H:i:s'))]);
        }
        $query->andWhere(['is not','file_user',null]);

        return $dataProvider;
    }
}
