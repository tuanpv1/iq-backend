<?php
namespace backend\models;

use common\models\ReportRevenuesService;
use common\models\ReportViewCategory;
use common\models\ReportVoucher;
use common\models\ReportVoucherSearch;
use DateTime;
use Yii;
use yii\base\Model;

/**
 *  Voucher Report Form form
 */
class VoucherReportForm extends Model
{
    public $to_date;
    public $from_date;
    public $dataProvider;
    public $content = null;
    public $site_id = null;

    public function rules()
    {
        return [
            [['from_date', 'to_date', 'content', 'site_id', 'service_id'], 'safe'],
//            [['to_date'],'validateDate','on'=>'tp_validate_report'],
        ];
    }

    public function validateDate($attribute, $params)
    {
        $started = strtotime(DateTime::createFromFormat("d/m/Y", $this->from_date)->setTime(0, 0)->format('Y-m-d H:i:s'));
        $finished = strtotime(DateTime::createFromFormat("d/m/Y", $this->to_date)->setTime(23, 59, 59)->format('Y-m-d H:i:s'));
        if ($finished < $started) {
            $this->addError($attribute, 'Ngày kết thúc tìm kiếm không được nhỏ hơn ngày bắt đầu tìm kiếm');
        }
    }

    public function attributeLabels()
    {
        return [
            'to_date' => Yii::t('app','Đến'),
            'from_date' => Yii::t('app','Từ'),
            'to_month' => Yii::t('app','Đến tháng'),
            'from_month' => Yii::t('app','Từ tháng'),
            'category_id' => Yii::t('app','Danh mục'),
        ];
    }

    /**
     * @param $cp_id
     * @param bool $first
     */
    public function generateReport($site_id)
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
        $searchModel = new ReportVoucherSearch();
        $param['ReportVoucherSearch']['site_id'] = $site_id;
        $param['ReportVoucherSearch']['from_date'] = $from_date;
        $param['ReportVoucherSearch']['to_date'] = $to_date;

        $dataProvider = $searchModel->search($param);
        return $dataProvider;
    }
}
