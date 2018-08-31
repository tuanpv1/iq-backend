<?php

namespace common\models;

use DateTime;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\LogSyncContent;

/**
 * LogSyncContentSearch represents the model behind the search form of `common\models\LogSyncContent`.
 */
class LogSyncContentSearch extends LogSyncContent
{
    public $cp;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','type', 'content_status', 'sync_status', 'created_at', 'updated_at'], 'integer'],
            [['site_id','content_id'],'string'],
            [['cp'],'safe']
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

        $query = LogSyncContent::find();
        $query->select('log_sync_content.*, content_provider.cp_name as cp');
        $query->innerJoin('content','content.id = log_sync_content.content_id')
        ->innerJoin('content_provider','content_provider.id=content.cp_id');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'       => [
                'attributes' => [
                    'cp'
                ],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'content_status' => $this->content_status,
            'sync_status' => $this->sync_status,
            'created_at' => $this->created_at,
            'content.cp_id'=>$this->cp,
        ]);
        $from_date = isset($params['LogSyncContent']['updated_at']) ? $params['LogSyncContent']['updated_at'] : false;
        if($from_date){
            $created_at_arr = explode('/', $from_date);
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $created_at_arr['2'] . '-' . $created_at_arr['1'] . '-' . $created_at_arr['0'] . ' 00:00:00');
            $updated_at = strtotime($date->format('m/d/Y'));
            $updated_at_end = $updated_at + (60 * 60 * 24);
            $query->andFilterWhere(['>=', 'log_sync_content.updated_at', $updated_at]);
            $query->andFilterWhere(['<=', 'log_sync_content.updated_at', $updated_at_end]);
        }
        if($this->type){
                $query->andWhere(['content.type'=>$this->type]);
        }
        if($this->site_id){
            $query->innerJoin('site','site.id = log_sync_content.site_id');
            $query->andWhere(['site_id'=>$this->site_id]);
        }
        if($this->content_id){
            $query->andFilterWhere(['like', 'lower(content.display_name)', strtolower($this->content_id)]);
        }
//        $query->orderBy(['updated_at'=>SORT_DESC]);

        return $dataProvider;
    }
}
