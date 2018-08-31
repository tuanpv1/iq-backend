<?php
namespace backend\models;


use common\models\ContentProfileSearch;
use common\models\ReportContentProfileSearch;
use DateTime;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class ReportContentProfileForm extends Model
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

    public $content_type = null;    //Loại content
//    public $category_id = null;    //Danh mục
    public $categoryIds = null;    //Danh mục

    public $list_type = [self::TYPE_DATE => 'Theo ngày', self::TYPE_MONTH => 'Theo tháng'];

    public function rules()
    {
        return [
            [['from_date', 'to_date', 'content', 'site_id', 'service_id', 'to_month', 'from_month', 'type','categoryIds'], 'safe'],
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
            'service_id' => 'Gói cước',
            'to_month' => 'Đến tháng',
            'from_month' => 'Từ tháng',
            'type' => 'Loại báo cáo',
            'site_id' => 'Nhà cung cấp dịch vụ',
            'content_type' => 'Loại nội dung',
            'category_id' => 'Danh mục',
            'categoryIds' => 'Danh mục'
        ];
    }

    /**
     *
     */
    public function generateReport()
    {
        /** Cắt xâu chuyển format DateTime thành timeStamp */
        $from_date = strtotime(str_replace('/', '-', $this->from_date) . ' 00:00:00');
        $to_date = strtotime(str_replace('/', '-', $this->to_date) . ' 23:59:59');

        $param = Yii::$app->request->queryParams;
        $searchModel = new ReportContentProfileSearch();
        $param['ReportContentSearch']['from_date'] =$from_date;
        $param['ReportContentSearch']['to_date'] =$to_date;

        $dataProvider = $searchModel->search($param);
        return $dataProvider;

    }

    /**
     * @return \yii\data\ActiveDataProvider
     */
    public function generateDataExport()
    {
        /** Cắt xâu chuyển format DateTime thành timeStamp */
        $from_date = strtotime(str_replace('/', '-', $this->from_date) . ' 00:00:00');
        $to_date = strtotime(str_replace('/', '-', $this->to_date) . ' 23:59:59');

        $param = Yii::$app->request->queryParams;
        $searchModel = new ContentProfileSearch();
        $param['ContentProfileSearch']['from_date'] =   $from_date;
        $param['ContentProfileSearch']['to_date'] =     $to_date;
        $dataProvider = $searchModel->search($param);
        return $dataProvider;

    }
}
