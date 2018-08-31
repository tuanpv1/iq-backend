<?php
/**
 * Created by PhpStorm.
 * User: hungptit
 * Date: 12/28/2016
 * Time: 11:13 AM
 */

namespace backend\models;


use common\models\ReportSubscriberNumberSearch;
use DateTime;
use Yii;
use yii\base\Model;

class ReportSubscriberNumberForm extends Model{

    const TYPE_DATE = 1;
    const TYPE_MONTH = 2;

    public $to_month;
    public $to_date;
    public $from_date;
    public $from_month;
    public $dataProvider;
    public $content = null;
    public $site_id = null;
    public $city = null;

    public $type = self::TYPE_DATE;

    public $list_type = [self::TYPE_DATE => 'Theo ngày', self::TYPE_MONTH => 'Theo tháng'];

    public function rules()
    {
        return [
            [['from_date', 'to_date', 'content', 'site_id', 'city', 'to_month', 'from_month', 'type'], 'safe'],
            [['from_date'], 'required',
                'message' => Yii::t('app','Thông tin không hợp lệ, Ngày bắt đầu không được để trống'),
            ],
            [['to_date'], 'required',
                'message' => Yii::t('app','Thông tin không hợp lệ, Ngày kết thúc không được để trống'),
            ]
        ];
    }

    public function attributeLabels()
    {
        return [
            'to_date' => Yii::t('app','Đến ngày'),
            'from_date' => Yii::t('app','Từ ngày'),
            'city' => Yii::t('app','Nơi đăng ký'),
            'to_month' => Yii::t('app','Đến tháng'),
            'from_month' => Yii::t('app','Từ tháng'),
            'type' => Yii::t('app','Loại báo cáo'),
            'site_id' => Yii::t('app','Nhà cung cấp dịch vụ')
        ];
    }

    public function generateReportSP(){
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
        $searchModel = new ReportSubscriberNumberSearch();
        $param['ReportSubscriberNumberSearch']['site_id'] =$this->site_id;
        $param['ReportSubscriberNumberSearch']['city'] =$this->city;
        $param['ReportSubscriberNumberSearch']['from_date'] =$from_date;
        $param['ReportSubscriberNumberSearch']['to_date'] =$to_date;

        $dataProvider = $searchModel->search($param);

        $paramDetail = Yii::$app->request->queryParams;
        $searchModelDetail = new ReportSubscriberNumberSearch();
        $paramDetail['ReportSubscriberNumberSearch']['site_id'] =$this->site_id;
        $paramDetail['ReportSubscriberNumberSearch']['city'] =$this->city   ;
        $paramDetail['ReportSubscriberNumberSearch']['from_date'] =$from_date;
        $paramDetail['ReportSubscriberNumberSearch']['to_date'] =$to_date;

        $dataProviderDetail = $searchModelDetail->searchDetail($paramDetail);
        return  [$dataProvider, $dataProviderDetail];
    }
}