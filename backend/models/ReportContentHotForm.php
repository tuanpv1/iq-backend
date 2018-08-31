<?php
/**
 * Created by PhpStorm.
 * User: mycon
 * Date: 12/24/2016
 * Time: 2:31 PM
 */
namespace backend\models;

use common\models\ReportContentHotSearch;
use DateTime;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class ReportContentHotForm extends Model
{
    public $to_date;
    public $from_date;
    public $site_id ;
    public $cp_id ;
    public $top;
    public $content_type = null;    //Loại nội dung
    public $categoryIds = null;    //Danh mục


    public function rules()
    {
        return [
            [['from_date','to_date','site_id','content_id','content_type','categoryIds','top','cp_id'], 'safe'],
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
            'site_id' => Yii::t('app','Nhà cung cấp dịch vụ'),
            'content_type' => Yii::t('app','Loại nội dung'),
            'categoryIds' => Yii::t('app','Danh mục'),
            'top'=> Yii::t('app','Top'),
            'cp_id'=> Yii::t('app','Nhà cung cấp nội dung')
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
        $searchModel = new ReportContentHotSearch();
        $param['ReportContentHotSearch']['site_id'] = $this->site_id;
        $param['ReportContentHotSearch']['cp_id'] = $this->cp_id;
        $param['ReportContentHotSearch']['content_type'] =$this->content_type;
        $param['ReportContentHotSearch']['categoryIds'] =$this->categoryIds;
        $param['ReportContentHotSearch']['from_date'] =$from_date;
        $param['ReportContentHotSearch']['to_date'] =$to_date;
        $param['ReportContentHotSearch']['top'] = $this->top;

        $dataProvider = $searchModel->search($param);
        return $dataProvider;

    }

    public function generateExport()
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
        $searchModel = new ReportContentHotSearch();
        $param['ReportContentHotSearch']['site_id'] = $this->site_id;
        $param['ReportContentHotSearch']['cp_id'] = $this->cp_id;
        $param['ReportContentHotSearch']['content_type'] =$this->content_type;
        $param['ReportContentHotSearch']['categoryIds'] =$this->categoryIds;
        $param['ReportContentHotSearch']['from_date'] =$from_date;
        $param['ReportContentHotSearch']['to_date'] =$to_date;
        $param['ReportContentHotSearch']['top'] = $this->top;

        $dataProvider = $searchModel->searchEx($param);
        return $dataProvider;

    }
}
