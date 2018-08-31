<?php
namespace backend\models;

use common\models\ReportRevenuesService;
use common\models\ReportUserDaily;
use common\models\ReportUserDailySearch;
use DateTime;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class ReportUserDailyForm extends Model
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

    public $list_type = [self::TYPE_DATE => 'Theo ngày', self::TYPE_MONTH => 'Theo tháng'];

    public function rules()
    {
        return [
            [['from_date', 'to_date', 'content', 'site_id', 'service_id', 'to_month', 'from_month', 'type'], 'safe'],
            [['site_id'], 'required'],
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
            'site_id' => Yii::t('app','Nhà cung cấp dịch vụ')
        ];
    }

    /**
     * @param bool $first
     */
    public function generateReport($first = false)
    {
        if ($first) {
            $to_date = (new DateTime('now'))->setTime(23, 59, 59)->format('d/m/Y');
            $from_date = (new DateTime('now'))->setTime(0, 0)->modify('-7 days')->format('d/m/Y');
            $this->to_date = $to_date;
            $this->from_date = $from_date;
        } else {
            if ($this->type == self::TYPE_MONTH) {
                if ($this->from_month != '' && DateTime::createFromFormat("d/m/Y", $this->from_month)) {
                    $from_date = date("Y/m/01", strtotime(DateTime::createFromFormat("d/m/Y", '15/' . $this->from_month)->format('Y/m/d'))) . ' 00:00:00';
                } else {
                    $from_date = date("Y/m/01", strtotime((new DateTime('now'))->format('Y-m-d H:i:s'))) . ' 00:00:00';
                }

                if ($this->to_month != '') {
                    $to_date = date("Y/m/t", strtotime(DateTime::createFromFormat("d/m/Y", '15/' . $this->to_month)->format('Y/m/d'))) . ' 00:00:00';
                } else {
                    $to_date = date("Y/m/t", strtotime((new DateTime('now'))->format('Y-m-d H:i:s'))) . ' 00:00:00';
                }

            } elseif ($this->type == self::TYPE_DATE) {
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

            } else {
                $from_date = (new DateTime('now'))->setTime(0, 0)->modify('-7 days')->format('Y-m-d H:i:s');
                $to_date = (new DateTime('now'))->setTime(0, 0)->format('Y-m-d H:i:s');
                $this->type = self::TYPE_DATE;
            }


            $from_date = strtotime(str_replace('/', '-', $this->from_date) . ' 00:00:00');
            $to_date = strtotime(str_replace('/', '-', $this->to_date) . ' 23:59:59');
//            $from_date = strtotime(str_replace('/', '-', $this->from_date));
//            $to_date = strtotime(str_replace('/', '-', $this->to_date));


            $param = Yii::$app->request->queryParams;
            /** @var  $report_daily ReportUserDailySearch*/
            $searchModel = new ReportUserDailySearch();
            $param['ReportUserDailySearch']['site_id'] =$this->site_id;
            $param['ReportUserDailySearch']['from_date'] =$from_date;
            $param['ReportUserDailySearch']['to_date'] =$to_date;
//            echo $this->site_id;exit;
            $dataProvider = $searchModel->search($param);
//            var_dump($dataProvider->getModels());exit;

//            if($this->site_id){
//                $report_daily = ReportUserDaily::find()
//                    ->where('report_date >= :p_from_date', [':p_from_date' => $from_date])
//                    ->andWhere('report_date <= :p_to_date', [':p_to_date' => $to_date])
//                    ->andWhere('site_id=:site_id', [':site_id' => $this->site_id])
//                    ->orderBy('report_date asc');
//            }else{
//                $report_daily = ReportUserDaily::find()
//                    ->where('report_date >= :p_from_date', [':p_from_date' => $from_date])
//                    ->andWhere('report_date <= :p_to_date', [':p_to_date' => $to_date])
//                    ->orderBy('report_date asc');
//            }

//            $this->content = $report_daily;
            $this->content = $dataProvider->getModels();
            $this->dataProvider = $dataProvider;

        }
    }
}
