<?php
namespace backend\models;

use common\models\ReportSubscriberActivitySearch;
use common\models\ReportSubscriberDailySearch;
use DateTime;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;


/**
 * Login form
 */
class ReportSubscriberActivityForm extends Model
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
    public $service_id = null;
    public $type = self::TYPE_DATE;
    public $content_type = null;

    public $list_type = [self::TYPE_DATE => 'Theo ngày', self::TYPE_MONTH => 'Theo tháng'];

    public function rules()
    {
        return [
            [['from_date', 'to_date', 'content', 'site_id', 'service_id', 'to_month', 'from_month', 'type','content_type'], 'safe'],
            [['from_date'], 'required',
//                'when' => function($model) {
//                    return $model->type == self::TYPE_DATE;
//                },
                'message' => Yii::t('app','Thông tin không hợp lệ, Ngày bắt đầu không được để trống'),
            ],
            [['to_date'], 'required',
//                'when' => function($model) {
//                    return $model->type == self::TYPE_DATE;
//                },
                'message' => Yii::t('app','Thông tin không hợp lệ, Ngày kết thúc không được để trống'),
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
            'content_type' => \Yii::t('app', 'Loại nội dung'),

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
        $searchModel = new ReportSubscriberActivitySearch();
        $param['ReportSubscriberActivitySearch']['site_id'] =$this->site_id;
        $param['ReportSubscriberActivitySearch']['content_type'] =$this->content_type;
        $param['ReportSubscriberActivitySearch']['from_date'] =$from_date;
        $param['ReportSubscriberActivitySearch']['to_date'] =$to_date;

        $dataProvider = $searchModel->search($param);
        return  $dataProvider;

    }

    public function generateDetailReport($rawData,$dateFormat = 'd/m/Y'){
        $dataRow = [];
        //label header
        $sttLabel = Yii::t('app','STT');
        $dateLabel = Yii::t('app','Ngày');
        $total_via_site_label = Yii::t('app','Tổng lượt truy cập');
        $total_via_site_daily_label = Yii::t('app','Số lượt truy cập trong ngày');
        $total_via_smb_label = Yii::t('app','Từ Smart box');
        $total_via_android_label = Yii::t('app','Từ ứng dụng Android');
        $total_via_ios_label = Yii::t('app','Từ ứng dụng IOS');
        $total_via_website_label = Yii::t('app','Từ website');
        $total_via_site_daily = 0;
        $total_via_smb = 0;
        $total_via_android= 0;
        $total_via_ios = 0;
        $total_via_website = 0;
        if(!empty($rawData)){
            $i=0;
            foreach ($rawData as $raw){
                $row[$sttLabel] = ++$i;
                $row[$dateLabel] = date($dateFormat,$raw['report_date']);
                $row[$total_via_site_label] = $raw['total_via_site'];
                $row[$total_via_site_daily_label] = $raw['via_site_daily'];
                $row[$total_via_smb_label] = $raw['via_smb'];
                $row[$total_via_android_label] = $raw['via_android'];
                $row[$total_via_ios_label] = $raw['via_ios'];
                $row[$total_via_website_label] = $raw['via_website'];
                $dataRow[] = $row;

                $total_via_site_daily += $raw['via_site_daily'];
                $total_via_smb += $raw['via_smb'];
                $total_via_android += $raw['via_android'];
                $total_via_ios += $raw['via_ios'];
                $total_via_website += $raw['via_website'];

                //kết thúc một ngày, khởi tạo thêm 1 dòng cho ngày tiếp theo
                $row = [];
            }
            //tinh tong cac  cot  dữ liệu
            $row[$sttLabel] = ++$i;
            $row[$dateLabel] = 'Tổng';
            $row[$total_via_site_label] = '';
            $row[$total_via_site_daily_label] = $total_via_site_daily;
            $row[$total_via_smb_label] = $total_via_smb;
            $row[$total_via_android_label] = $total_via_android;
            $row[$total_via_ios_label] = $total_via_ios;
            $row[$total_via_website_label] = $total_via_ios;
            $dataRow[] = $row;

        }
        $excelDataProvider = new ArrayDataProvider([
            'allModels' => $dataRow,
            'pagination' => false,
        ]);
        return $excelDataProvider;
    }
}
