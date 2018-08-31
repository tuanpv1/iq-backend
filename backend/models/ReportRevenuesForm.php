<?php

namespace backend\models;

use common\models\ReportRevenueSearch;
use DateTime;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class ReportRevenuesForm extends Model
{
    const TYPE_DATE = 1;
    const TYPE_MONTH = 2;

    public $to_month;
    public $to_date;
    public $from_date;
    public $from_month;
    public $dataProvider;
    public $white_list = null;
    public $content = null;
    public $site_id = null;
    public $cp_id = null;
    public $service_id = null;
    public $type = self::TYPE_DATE;

    public $list_type = [self::TYPE_DATE => 'Theo ngày', self::TYPE_MONTH => 'Theo tháng'];

    public $report_date = [];
    public $arr_total_revenues = [];
    public $arr_revenues = [];
    public $arr_content_buy_revenues = [];

    public function rules()
    {
        return [
            [['from_date', 'to_date', 'content', 'site_id', 'service_id', 'to_month', 'from_month', 'type', 'white_list', 'cp_id'], 'safe'],
            [['from_date'], 'required',
                'message' => Yii::t('app', 'Thông tin không hợp lệ, Ngày bắt đầu không được để trống'),
            ],
            [['to_date'], 'required',
                'message' => Yii::t('app', 'Thông tin không hợp lệ, Ngày kết thúc không được để trống'),
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'to_date' => Yii::t('app', 'Đến ngày'),
            'from_date' => Yii::t('app', 'Từ ngày'),
            'service_id' => Yii::t('app', 'Gói cước'),
            'to_month' => Yii::t('app', 'Đến tháng'),
            'from_month' => Yii::t('app', 'Từ tháng'),
            'type' => Yii::t('app', 'Loại báo cáo'),
            'site_id' => Yii::t('app', 'Nhà cung cấp dịch vụ'),
            'white_list' => Yii::t('app', 'Bộ lọc'),
            'cp_id' => Yii::t('app', 'Nhà cung cấp nội dung')
        ];
    }

    /**
     * @param bool $first
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
        $searchModel = new ReportRevenueSearch();
        $param['ReportRevenueSearch']['site_id'] = $this->site_id;
        $param['ReportRevenueSearch']['cp_id'] = $this->cp_id;
        $param['ReportRevenueSearch']['service_id'] = $this->service_id;
        $param['ReportRevenueSearch']['white_list'] = $this->white_list;
        $param['ReportRevenueSearch']['from_date'] = $from_date;
        $param['ReportRevenueSearch']['to_date'] = $to_date;
        $dataProvider = $searchModel->search($param);

        return $dataProvider;
    }

    /**
     * @param bool $first
     */
    public function generateDetailReport()
    {
        $from_date = strtotime(str_replace('/', '-', $this->from_date) . ' 00:00:00');
        $to_date = strtotime(str_replace('/', '-', $this->to_date) . ' 23:59:59');

        $param = Yii::$app->request->queryParams;
        $searchModel = new ReportRevenueSearch();
        $param['ReportRevenueSearch']['site_id'] = $this->site_id;
        $param['ReportRevenueSearch']['cp_id'] = $this->cp_id;
        $param['ReportRevenueSearch']['service_id'] = $this->service_id;
        $param['ReportRevenueSearch']['white_list'] = $this->white_list;
        $param['ReportRevenueSearch']['from_date'] = $from_date;
        $param['ReportRevenueSearch']['to_date'] = $to_date;

        $dataProvider = $searchModel->getDetails($param);
        return $dataProvider;
    }

    public function getData($listData)
    {
        foreach ($listData as $item) {
            array_splice($this->report_date, 0, 0, date('d/m/Y', $item->report_date));
            array_splice($this->arr_total_revenues, 0, 0, $item->total_revenues);
            array_splice($this->arr_revenues, 0, 0, $item->revenues);
            array_splice($this->arr_content_buy_revenues, 0, 0, $item->content_buy_revenues);
        }
        $data[0] = $this->report_date;
        $data[1] = $this->arr_total_revenues;
        $data[2] = $this->arr_revenues;
        $data[3] = $this->arr_content_buy_revenues;
        return $data;
    }
}
