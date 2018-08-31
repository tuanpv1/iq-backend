<?php
namespace backend\models;

use common\models\ReportContentSearch;
use common\models\ReportSubscriberActivitySearch;
use common\models\ReportSubscriberDailySearch;
use DateTime;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class ReportContentForm extends Model
{
    const TYPE_DATE = 1;
    const TYPE_MONTH = 2;

    public $to_month;
    public $to_date;
    public $from_date;
    public $from_month;
    public $dataProvider;
    public $content = null;
    public $site_id = null;
    public $cp_id = null;
    public $service_id = null;
    public $type = self::TYPE_DATE;
    public $content_type = null;    //Loại content
    public $category_id ;    //Danh mục

    public $list_type = [self::TYPE_DATE => 'Theo ngày', self::TYPE_MONTH => 'Theo tháng'];

    public $report_date = [];
    public $arr_total_content = [];
    public $arr_count_content_upload_daily = [];
    public $arr_total_content_view = [];
    public $arr_total_content_buy = [];

    public function rules()
    {
        return [
            [['from_date', 'to_date', 'content', 'site_id', 'service_id', 'to_month', 'from_month', 'type','categoryIds','cp_id','category_id'], 'safe'],
            [['from_date'], 'required',
                'message' => Yii::t('app','Thông tin không hợp lệ, Ngày bắt đầu không được để trống'),
            ],
            [['to_date'], 'required',
                'message' => Yii::t('app','Thông tin không hợp lệ, Ngày kết thúc không được để trống'),
            ],
            [['category_id'], 'required',
                'message' => Yii::t('app','Thông tin không hợp lệ, Danh mục không được để trống'),
            ]
        ];
    }

    public function attributeLabels()
    {
        return [
            'to_date' => Yii::t('app','Đến ngày'),
            'from_date' => Yii::t('app','Từ ngày'),
            'service_id' => Yii::t('app','Gói cước'),
            'to_month' => Yii::t('app','Đến tháng'),
            'from_month' => Yii::t('app','Từ tháng'),
            'type' => Yii::t('app','Loại báo cáo'),
            'site_id' => Yii::t('app','Nhà cung cấp dịch vụ'),
            'content_type' => Yii::t('app','Loại nội dung'),
            'category_id' => Yii::t('app','Danh mục'),
            'cp_id'=>Yii::t('app','Nhà cung cấp nội dung')
        ];
    }

    /**
     *
     */
    public function generateReport()
    {
        if ($this->from_date != '' && DateTime::createFromFormat("d/m/Y", $this->from_date)) {
            $from_date = DateTime::createFromFormat("d/m/Y", $this->from_date)->setTime(0, 0)->format('Y-m-d H:i:s');
        } else {
            $from_date = (new DateTime('now'))->setTime(0, 0)->format('Y-m-d H:i:s');
        }

        if ($this->to_date != '' && DateTime::createFromFormat("d/m/Y", $this->to_date)) {
            $to_date = DateTime::createFromFormat("d/m/Y", $this->to_date)->setTime(0, 0)->format('Y/m/d H:i:s');
        } else {
            $to_date = (new DateTime('now'))->setTime(0, 0)->format('Y-m-d H:i:s');
        }


        $from_date = strtotime(str_replace('/', '-', $this->from_date) . ' 00:00:00');
        $to_date = strtotime(str_replace('/', '-', $this->to_date) . ' 23:59:59');

        $param = Yii::$app->request->queryParams;
        $searchModel = new ReportContentSearch();
        $param['ReportContentSearch']['site_id'] =$this->site_id;
        $param['ReportContentSearch']['cp_id'] =$this->cp_id;
        $param['ReportContentSearch']['content_type'] =$this->content_type;
        $param['ReportContentSearch']['category_id'] =$this->category_id;
        $param['ReportContentSearch']['from_date'] =$from_date;
        $param['ReportContentSearch']['to_date'] =$to_date;

        $dataProvider = $searchModel->search($param);
        return $dataProvider;

    }

    public function getData($listData){
        foreach ($listData as $item) {
            array_splice($this->report_date,0,0,date('d/m/Y', $item->report_date));
            array_splice($this->arr_total_content,0,0,$item->total_content);
            array_splice($this->arr_count_content_upload_daily,0,0,$item->count_content_upload_daily);
            array_splice($this->arr_total_content_view,0,0,$item->total_content_view);
            array_splice($this->arr_total_content_buy,0,0,$item->total_content_buy);
        }
        $data[0]=$this->report_date;
        $data[1]=$this->arr_total_content;
        $data[2]=$this->arr_count_content_upload_daily;
        $data[3]=$this->arr_total_content_view;
        $data[4]=$this->arr_total_content_buy;
        return $data;
    }
}
