<?php
namespace backend\models;

use common\models\ReportRevenuesService;
use common\models\ReportViewCategory;
use common\models\ReportVoucher;
use common\models\ReportVoucherSearch;
use common\models\SmsMessage;
use common\models\SmsMessageSearch;
use DateTime;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class MTForm extends Model
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
    public $msisdn;
    public $sent_at;
    public $message;
//    public $msisdn;

    public function rules()
    {
        return [
            [['from_date', 'to_date', 'content', 'site_id', 'msisdn','sent_at','message'], 'safe'],
            [ 'msisdn', 'integer', 'message' => Yii::t('app','Số thuê bao không hợp lệ') ],
            [ 'to_date', 'validateDate'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'to_date' => Yii::t('app','Đến Ngày'),
            'from_date' => Yii::t('app','Từ Ngày'),
            'msisdn' => Yii::t('app','Số thuê bao'),
            'sent_at' => Yii::t('app','Ngày gửi'),
            'message' => Yii::t('app','Nội dung'),
        ];
    }

    public function validateDate($attribute, $params)
    {
        $started = strtotime(DateTime::createFromFormat("d/m/Y", $this->from_date)->setTime(0, 0)->format('Y-m-d H:i:s'));
        $finished = strtotime(DateTime::createFromFormat("d/m/Y", $this->to_date)->setTime(0, 0)->format('Y-m-d H:i:s'));
        if ($finished < $started) {
            $this->addError($attribute, 'Ngày kết thúc tìm kiếm không được nhỏ hơn ngày bắt đầu tìm kiếm');
        }
    }

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
        $searchModel = new SmsMessageSearch();
        $param['SmsMessageSearch']['site_id'] = $site_id;
        $param['SmsMessageSearch']['from_date'] = $from_date;
        $param['SmsMessageSearch']['to_date'] = $to_date;
        $param['SmsMessageSearch']['msisdn'] = $this->msisdn;
        $param['SmsMessageSearch']['type'] = SmsMessage::TYPE_MT;

        $dataProvider = $searchModel->searchTpMtReport($param);
        return $dataProvider;
    }
}
