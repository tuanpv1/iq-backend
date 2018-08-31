<?php
/**
 * Created by PhpStorm.
 * User: mycon
 * Date: 12/23/2016
 * Time: 4:48 PM
 */
namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\helpers\VarDumper;

class ReportContentHotSearch extends ReportContentHot
{
    public $from_date;
    public $to_date;
    public $categoryIds;
    public $top;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'report_date', 'site_id', 'content_type', 'category_id', 'total_content_view'], 'integer'],
            [['from_date', 'to_date','top','cp_id'], 'safe'],
            [['categoryIds'], 'safe'],
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
        $query = ReportContentHot::find();


        $this->load($params);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $query->limit($this->top);
        $page = intval(Yii::$app->request->get('page'));

        if ($this->top % 20 != 0){
            $sumPage = $this->top/20 +1;
//            var_dump($sumPage = $this->top/20 +1);die();
        }else{
            $sumPage = $this->top/20 ;
//            var_dump($sumPage = $this->top/20 );die();
        }
        if ($sumPage>2){
            if ($page == 1) {
                $query->limit(20);
            } else {
                if ($page < $sumPage && $page > $sumPage-1) {
                    $offset = ($page-1)*20;
                    $query->limit(10);
                    $query->offset($offset);
                } else {
                    $offset = ($page-1)*20;
                    $query->limit(20);
                    $query->offset($offset);
                }
            }
        }

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->select('content_type,content_id,
                            sum(total_content_view) as total_content_view,'
        );

        if($this->site_id){
            $query->where(['site_id'=>$this->site_id]);
        }

        if($this->content_type){
            $query->andwhere(['content_type'=>$this->content_type]);
        }

        if ($this->from_date) {
            $query->andFilterWhere(['>=', 'report_date', $this->from_date]);
        }
        if ($this->to_date) {
            $query->andFilterWhere(['<=', 'report_date', $this->to_date]);
        }

        if($this->cp_id){
            $query->andwhere(['cp_id'=>$this->cp_id]);
        }

        if($this->categoryIds){
            $categoryIds = explode(',', $this->categoryIds);
            $contents = ContentCategoryAsm::findAll(['category_id'=>$categoryIds]);
            if($contents){
                foreach($contents as $item){
                    $content[]=$item->content_id;
                }
                $query->andwhere(['content_id'=>$content]);
            }
        }

        $query->groupBy('content_id,content_type');
        $query->orderBy(['total_content_view'=>SORT_DESC]);

        return $dataProvider;
    }
    public function searchEx($params)
    {
        $query = ReportContentHot::find();


        $this->load($params);
        $dataProvider = new ActiveDataProvider([
            'query' => $query->limit($this->top),
            'pagination' => false,
        ]);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->select('content_type,content_id,
                            sum(total_content_view) as total_content_view,'
        );

        if($this->site_id){
            $query->where(['site_id'=>$this->site_id]);
        }

        if($this->content_type){
            $query->andwhere(['content_type'=>$this->content_type]);
        }

        if ($this->from_date) {
            $query->andFilterWhere(['>=', 'report_date', $this->from_date]);
        }
        if ($this->to_date) {
            $query->andFilterWhere(['<=', 'report_date', $this->to_date]);
        }

        if($this->cp_id){
            $query->andwhere(['cp_id'=>$this->cp_id]);
        }

        if($this->categoryIds){
            $categoryIds = explode(',', $this->categoryIds);
            $contents = ContentCategoryAsm::findAll(['category_id'=>$categoryIds]);
            if($contents){
                foreach($contents as $item){
                    $content[]=$item->content_id;
                }
                $query->andwhere(['content_id'=>$content]);
            }
        }

        $query->groupBy('content_id,content_type');
        $query->orderBy(['total_content_view'=>SORT_DESC]);

        return $dataProvider;
    }
}
