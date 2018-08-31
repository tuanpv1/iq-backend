<?php
namespace backend\models;

use common\models\ReportRevenue;
use common\models\ReportRevenueSearch;
use common\models\ReportRevenuesService;
use common\models\ReportSubscriberService;
use common\models\ReportSubscriberServiceSearch;
use common\models\Subscriber;
use common\models\SubscriberServiceAsm;
use common\models\SubscriberServiceAsmSearch;
use common\models\SubscriberTransactionSearch;
use DateTime;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class ReportSubscriberServiceForm extends Model
{
    public $to_date;
    public $from_date;
    public $dataProvider;
    public $white_list=null;
    public $content = null;
    public $site_id = null;
    public $cp_id = null;
    public $dealer_id = null;
    public $service_id = null;

    public function rules()
    {
        return [
            [['from_date', 'to_date', 'content', 'site_id','dealer_id','white_list', 'service_id', 'type','cp_id'], 'safe'],
            [['from_date'], 'required',
                'message' => Yii::t('app','Thông tin không hợp lệ, Ngày bắt đầu không được để trống'),
            ],
            [['to_date'], 'required',
                'message' => Yii::t('app','Thông tin không hợp lệ, Ngày kết thúc không được để trống'),
            ],
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
            'white_list' => Yii::t('app','Bộ lọc'),
            'cp_id'=>Yii::t('app','Nhà cung cấp nội dung')
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
            $to_date = DateTime::createFromFormat("d/m/Y", $this->to_date)->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        } else {
            $to_date = (new DateTime('now'))->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        }

        $param = Yii::$app->request->queryParams;
        $searchModel = new ReportSubscriberServiceSearch();
        $param['ReportSubscriberServiceSearch']['site_id'] =$this->site_id;
        $param['ReportSubscriberServiceSearch']['cp_id'] =$this->cp_id;
        $param['ReportSubscriberServiceSearch']['service_id'] =$this->service_id;
        $param['ReportSubscriberServiceSearch']['white_list'] =$this->white_list;
        $param['ReportSubscriberServiceSearch']['from_date'] =$from_date;
        $param['ReportSubscriberServiceSearch']['to_date'] =$to_date;
        $dataProvider = $searchModel->search($param);

        $paramDetail = Yii::$app->request->queryParams;
        $searchModelDetail = new SubscriberTransactionSearch();
        $paramDetail['SubscriberTransactionSearch']['site_id'] =$this->site_id;
        $paramDetail['SubscriberTransactionSearch']['cp_id'] =$this->cp_id;
        $paramDetail['SubscriberTransactionSearch']['service_id'] =$this->service_id;
        $paramDetail['SubscriberTransactionSearch']['white_list'] =$this->white_list;
        $paramDetail['SubscriberTransactionSearch']['from_date'] =$from_date;
        $paramDetail['SubscriberTransactionSearch']['to_date'] =$to_date;
        $dataProviderDetail = $searchModelDetail->searchDetail($paramDetail);

        return [$dataProvider, $dataProviderDetail];
    }
}
