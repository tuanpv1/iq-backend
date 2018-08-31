<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Service;

/**
 * ServiceSearch represents the model behind the search form about `common\models\Service`.
 */
class ServiceSearch extends Service
{
//    public $status = Service::STATUS_ACTIVE;
//    public $auto_renew = Service::TYPE_AUTO_RENEW;
    public $content_id;
    public $listCatIds;
    public $cp;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'site_id', 'status',  'updated_at', 'free_download_count', 'free_duration', 'free_view_count', 'free_gift_count', 'period', 'auto_renew', 'free_days', 'max_daily_retry', 'max_day_failure_before_cancel', 'root_service_id','service_type'], 'integer'],
            [['name', 'display_name', 'description','created_at','cp','id'], 'safe'],
            [['content_id'], 'integer'],
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
        $query = Service::find()
            ->select('service.*,service_cp_asm.service_id as cp')
            ->innerJoin('service_cp_asm','service.id = service_cp_asm.service_id')
            ->groupBy('name');

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
            'updated_at' => $this->updated_at,
            'free_download_count' => $this->free_download_count,
            'free_duration' => $this->free_duration,
            'free_view_count' => $this->free_view_count,
            'free_gift_count' => $this->free_gift_count,
            'period' => $this->period,
            'auto_renew' => $this->auto_renew,
            'free_days' => $this->free_days,
            'max_daily_retry' => $this->max_daily_retry,
            'max_day_failure_before_cancel' => $this->max_day_failure_before_cancel,
            'service.status'=>$this->status,
            'service_cp_asm.cp_id'=>$this->cp,
            'service.service_type'=>$this->service_type,
        ]);
        if ($this->created_at !== '' && $this->created_at !== null) {
            $from_time = strtotime(str_replace('/', '-', $this->created_at) . ' 00:00:00');
            $to_time = strtotime(str_replace('/', '-', $this->created_at) . ' 23:59:59');
            $query->andFilterWhere(['>=', 'service.created_at',$from_time]);
            $query->andFilterWhere(['<=', 'service.created_at',$to_time]);
        }
        if($this->content_id){
            $lstCat = ContentCategoryAsm::findAll(['content_id'=>$this->content_id]);
        }

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'display_name', $this->display_name])
            ->andFilterWhere(['like', 'description', $this->description]);
        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchEx($params,$arr =null)
    {
//        $query = Service::find();
        $query = \api\models\Service::find();

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
//            'id' => $this->id,
            'site_id' => $this->site_id,
            'status' => Service::STATUS_ACTIVE,
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
//            'free_download_count' => $this->free_download_count,
//            'free_duration' => $this->free_duration,
//            'free_view_count' => $this->free_view_count,
//            'free_gift_count' => $this->free_gift_count,
//            'price' => $this->price,
//            'period' => $this->period,
//            'auto_renew' => $this->auto_renew,
//            'free_days' => $this->free_days,
//            'max_daily_retry' => $this->max_daily_retry,
//            'max_day_failure_before_cancel' => $this->max_day_failure_before_cancel,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'display_name', $this->display_name])
            ->andFilterWhere(['like', 'description', $this->description]);

        if($arr){
            $query->andOnCondition(['in','id',$arr]);
        }

        return $dataProvider;
    }
}
