<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ContentFeedback;

/**
 * ContentFeedbackSearch represents the model behind the search form about `\common\models\ContentFeedback`.
 */
class ContentFeedbackSearch extends ContentFeedback
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'rating', 'created_at', 'updated_at', 'content_id', 'subscriber_id', 'status', 'like', 'site_id'], 'integer'],
            [['title', 'content', 'admin_note'], 'safe'],
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
        $query = ContentFeedback::find();

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
            'rating' => $this->rating,
            'updated_at' => $this->updated_at,
            'content_id' => $this->content_id,
            'subscriber_id' => $this->subscriber_id,
            'status' => $this->status,
            'like' => $this->like,
            'site_id' => $this->site_id,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'admin_note', $this->admin_note]);
        if($this->created_at){
            $from_date = strtotime(str_replace('/', '-', $this->created_at) . ' 00:00:00');
            $to_date = strtotime(str_replace('/', '-', $this->created_at) . ' 23:59:59');
            $query->andFilterWhere(['>=', 'created_at', $from_date]);
            $query->andFilterWhere(['<=', 'created_at', $to_date]);
        }

        return $dataProvider;
    }
    public function filter($params,$content_id)
    {
        $query = ContentFeedback::find()->where(['content_id'=>$content_id]);

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
            'rating' => $this->rating,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'content_id' => $this->content_id,
            'subscriber_id' => $this->subscriber_id,
            'status' => $this->status,
            'like' => $this->like,
            'site_id' => $this->site_id,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'admin_note', $this->admin_note]);

        return $dataProvider;
    }
}
