<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ContentProfile;

/**
 * ContentProfileSearch represents the model behind the search form about `\common\models\ContentProfile`.
 */
class ContentProfileSearch extends ContentProfile
{
    public $from_date;
    public $to_date;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'content_id', 'type', 'status', 'created_at', 'updated_at', 'bitrate', 'width', 'height', 'quality'], 'integer'],
            [['name', 'url', 'description'], 'safe'],
            [['progress'], 'number'],
            [['from_date', 'to_date'], 'safe'],
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
        $query = ContentProfile::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
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
            'id' => $this->id,
            'content_id' => $this->content_id,
            'type' => $this->type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'bitrate' => $this->bitrate,
            'width' => $this->width,
            'height' => $this->height,
            'quality' => $this->quality,
            'progress' => $this->progress,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'description', $this->description]);

        if ($this->from_date) {
            $query->andFilterWhere(['>=', 'created_at', $this->from_date]);
        }
        if ($this->to_date) {
            $query->andFilterWhere(['<=', 'created_at', $this->to_date]);
        }
        return $dataProvider;
    }
}
