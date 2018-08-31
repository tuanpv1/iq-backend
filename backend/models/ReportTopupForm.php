<?php
namespace backend\models;

use common\helpers\CommonUtils;
use common\models\Device;
use common\models\Subscriber;
use DateTime;
use Yii;
use yii\base\Model;
use yii\base\Object;
use common\models\ReportTopupSearch;
use common\models\SubscriberTransactionSearch;
use common\models\SubscriberTransaction;
use yii\data\ArrayDataProvider;

/**
 * Login form
 */
class ReportTopupForm extends Model
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
    public $channel = null;
    public $white_list = null;
    public $type = self::TYPE_DATE;
    public $status;

    public $list_type = [self::TYPE_DATE => 'Theo ngày', self::TYPE_MONTH => 'Theo tháng'];


    public function rules()
    {
        return [
            [['from_date', 'to_date', 'content', 'site_id', 'channel', 'to_month', 'from_month', 'type','white_list','status'], 'safe'],
            [['from_date'], 'required',
                'message' => 'Thông tin không hợp lệ, Ngày bắt đầu không được để trống',
            ],
            [['to_date'], 'required',
                'message' => 'Thông tin không hợp lệ, Ngày kết thúc không được để trống',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'to_date' => 'Đến ngày',
            'from_date' => 'Từ ngày',
            'channel' => 'Kênh nạp',
            'to_month' => 'Đến tháng',
            'from_month' => 'Từ tháng',
            'type' => 'Loại báo cáo',
            'site_id' => 'Nhà cung cấp dịch vụ',
            'white_list' => 'Bộ lọc',
            'status' => 'Trạng thái giao dịch',
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
            $to_date = DateTime::createFromFormat("d/m/Y", $this->to_date)->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        } else {
            $to_date = (new DateTime('now'))->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        }
//        var_dump($from_date);var_dump($to_date);exit();
        
        $param = Yii::$app->request->queryParams;
        $searchModel = new ReportTopupSearch();
        $param['ReportTopupSearch']['site_id'] =$this->site_id;
        $param['ReportTopupSearch']['white_list'] =$this->white_list;
        $param['ReportTopupSearch']['channel'] =$this->channel;
        $param['ReportTopupSearch']['status'] =$this->status;
        $param['ReportTopupSearch']['from_date'] =$from_date;
        $param['ReportTopupSearch']['to_date'] =$to_date;

        $dataProvider = $searchModel->search($param);

        $excelDataProvider = $searchModel->searchDetail($param);
        $results = $excelDataProvider->getModels();
        $dataExcel = $this->reformatDetailReport($results);
        $dataProviderDetail = new ArrayDataProvider([
            'allModels' => $dataExcel,
            'pagination' => false,
        ]);
        return  [$dataProvider, $dataProviderDetail];
//        return $dataProvider;
    }

    private function reformatDetailReport($rawData,$dateFormat = 'd/m/Y H:i:s') {
        //Label header
        $sttLabel = Yii::t('app','STT');
        $dateCreateLabel = Yii::t('app','Ngày khởi tạo SmartBox');
        $nameLabel = Yii::t('app','Họ tên');
        $addressLabel = Yii::t('app','Địa chỉ CSKH/KH nhập');
        $cityFirtLabel = Yii::t('app','Tỉnh /T.Phố thời điểm khởi tạo (Smartbox khởi tạo)');
        $cityLabel = Yii::t('app','Tỉnh/TP theo IP to location');
        $msisdnLabel = Yii::t('app','Điện thoại 1');
        $phoneLabel = Yii::t('app','Điện thoại 2');
        $modelLabel = Yii::t('app','Model');
        $serialLabel = Yii::t('app','Serial No');
        $macLabel = Yii::t('app','Địa chỉ MAC');
        $dateTopupLabel = Yii::t('app','Ngày nạp tiền');
        $revenueLabel = Yii::t('app','Số tiền nạp thành công');
        $revenuePendingLabel = Yii::t('app','Số tiền nạp đang xử lý');
        $revenueErrorLabel = Yii::t('app','Số tiền nạp thất bại');
        $channelLabel = Yii::t('app','Kênh nạp');
        $errorLabel = Yii::t('app','Chi tiết lỗi');
        $totalLabel = Yii::t('app','Tổng');

        $data = [];
        $row = [];
        $totalRow = [];

        $row[$sttLabel]= 1;
        $row[$dateCreateLabel]= '';
        $row[$nameLabel]= '';
        $row[$addressLabel]= '';
        $row[$cityFirtLabel]= '';
        $row[$cityLabel]= '';
        $row[$msisdnLabel]= '';
        $row[$phoneLabel]= '';
        $row[$modelLabel]= '';
        $row[$serialLabel]= '';
        $row[$macLabel]= '';
        $row[$dateTopupLabel]= '';
        $row[$revenueLabel]= 0;
        $row[$revenuePendingLabel]= 0;
        $row[$revenueErrorLabel]= 0;
        $row[$channelLabel]= '';
        $row[$errorLabel]= '';

        $total_topup = 0;
        $total_topup_pending = 0;
        $total_topup_error = 0;
        $i = 0;
        $index = 1;
        $cnt = count($rawData);
        while($i < $cnt) {
            //lấy 1 row đầu, khởi tạo 1 dòng
            $line = $rawData[$i++];
            $row[$dateCreateLabel] = $line['register_at']?date($dateFormat,$line['register_at']):'';
            $row[$nameLabel] = $line['full_name']?$line['full_name']:'';
            $row[$addressLabel]= $line['address']?$line['address']:'';
            $row[$cityFirtLabel]= $line['ip_location_first']?Subscriber::getCityName($line['ip_location_first']):'';
            $row[$cityLabel]= $line['ip_to_location']?Subscriber::getCityName($line['ip_to_location']):'';
            $row[$msisdnLabel]= $line['msisdn']?$line['msisdn']:'';
            $row[$phoneLabel]= $line['phone_number']?$line['phone_number']:'';
            $row[$modelLabel]= $line['device_type']?Device::getDeviceType($line['device_type']):'';
            $row[$serialLabel]= $line['serial']?$line['serial']:'';
            $row[$macLabel]= $line['machine_name']?$line['machine_name']:'';
            $row[$dateTopupLabel]= $line['transaction_time']? date($dateFormat,$line['transaction_time']):'';
            if ($line['status'] == SubscriberTransaction::STATUS_SUCCESS){
                $row[$revenueLabel]= CommonUtils::formatNumber($line['total_topup']);
                $row[$revenuePendingLabel]= 0;
                $row[$revenueErrorLabel]= 0;
                $total_topup = $total_topup + $line['total_topup'];
            }
            if ($line['status'] == SubscriberTransaction::STATUS_PENDING){
                $row[$revenueLabel]= 0;
                $row[$revenuePendingLabel]= CommonUtils::formatNumber($line['total_topup']);
                $row[$revenueErrorLabel]= 0;
                $total_topup_pending = $total_topup_pending + $line['total_topup'];
            }
            if ($line['status'] == SubscriberTransaction::STATUS_FAIL){
                $row[$revenueLabel]= 0;
                $row[$revenuePendingLabel]= 0;
                $row[$revenueErrorLabel]= CommonUtils::formatNumber($line['total_topup']);
                $total_topup_error = $total_topup_error + $line['total_topup'];
            }
            if ($line['type'] == SubscriberTransaction::TYPE_VOUCHER){
                $row[$channelLabel]= Yii::t('app', 'Thẻ TVOD');
            }
            if ($line['type'] == SubscriberTransaction::TYPE_TOPUP_ATM)
            {
                $row[$channelLabel]= Yii::t('app', 'Thẻ ATM');
            }
            if ($line['type'] == SubscriberTransaction::TYPE_TOPUP_VISA){
                $row[$channelLabel]= Yii::t('app', 'Thẻ VISA');
            }
            if ($line['type'] == SubscriberTransaction::TYPE_VOUCHER_PHONE){
                if ($line['channel'] == SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VIETTEL){
                    $row[$channelLabel]= Yii::t('app', 'Thẻ Viettel');
                }
                if ($line['channel'] == SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VINAPHONE){
                    $row[$channelLabel]= Yii::t('app', 'Thẻ Vinaphone');
                }
                if ($line['channel'] == SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_MOBIPHONE){
                    $row[$channelLabel]= Yii::t('app', 'Thẻ Mobifone');
                }
            }
            if ($line['status'] == SubscriberTransaction::STATUS_FAIL){
                $row[$errorLabel] = SubscriberTransaction::getNameErrorCode($line['type'],(int)$line['error_code'],$line['gateway']);
            }else{
                $row[$errorLabel] = '';
            }
            $data[] = $row;

            //kết thúc một ngày, khởi tạo thêm 1 dòng cho ngày tiếp theo
            $row = [];
            $row[$sttLabel] = ++$index;
        }

        $totalRow[$sttLabel] = $totalLabel;
        $totalRow[$dateCreateLabel]= '';
        $totalRow[$nameLabel]= '';
        $totalRow[$addressLabel]= '';
        $totalRow[$cityFirtLabel]= '';
        $totalRow[$cityLabel]= '';
        $totalRow[$msisdnLabel]= '';
        $totalRow[$phoneLabel]= '';
        $totalRow[$modelLabel]= '';
        $totalRow[$serialLabel]= '';
        $totalRow[$macLabel]= '';
        $totalRow[$dateTopupLabel]= '';
        $totalRow[$revenueLabel]= CommonUtils::formatNumber($total_topup);
        $totalRow[$revenuePendingLabel]= CommonUtils::formatNumber($total_topup_pending);
        $totalRow[$revenueErrorLabel]= CommonUtils::formatNumber($total_topup_error);
        $totalRow[$channelLabel]= '';
        $totalRow[$errorLabel]= '';
        $data[] = $totalRow;
        return $data;
    }

    /**
     * data for export report
     */
    public function getExcelData()
    {
        if ($this->from_date != '' && DateTime::createFromFormat("d/m/Y", $this->from_date)) {
            $from_date = DateTime::createFromFormat("d/m/Y", $this->from_date)->format('Y-m-d');
        } else {
            $from_date = (new DateTime('now'))->format('Y-m-d');
        }

        if ($this->to_date != '' && DateTime::createFromFormat("d/m/Y", $this->to_date)) {
            $to_date = DateTime::createFromFormat("d/m/Y", $this->to_date)->format('Y-m-d');
        } else {
            $to_date = (new DateTime('now'))->format('Y-m-d');
        }
        
        $from_date = str_replace('/', '-', $this->from_date) . ' 00:00:00';
        $to_date = str_replace('/', '-', $this->to_date) . ' 23:59:59';
        
        $searchModel = new SubscriberTransactionSearch();
        $searchModel->site_id = $this->site_id;
        $searchModel->white_list = $this->white_list;
        $searchModel->type_list = array(SubscriberTransaction::TYPE_VOUCHER,SubscriberTransaction::TYPE_VOUCHER_PHONE);
        if($this->channel){
            $searchModel->channel = $this->channel;
        }
        else{
            $searchModel->chanel_list = array(SubscriberTransaction::CHANNEL_TYPE_VOUCHER,SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_MOBIPHONE,
                SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VINAPHONE,SubscriberTransaction::CHANNEL_TYPE_VOUCHER_PHONE_VIETTEL);
        }
        $searchModel->from_date = $from_date;
        $searchModel->to_date = $to_date;
        $searchModel->status = SubscriberTransaction::STATUS_SUCCESS;
        $dataProvider = $searchModel->search(null);
        return $dataProvider;
    }
}
