<?php

namespace common\models;

/**
 * This is the model class for table "report_subscriber_using".
 *
 * @property int $id
 * @property int $report_date
 * @property int $site_id
 * @property int $subscriber_total
 * @property int $service_total
 * @property int $type_model
 * @property int $service_id
 * @property string $city
 */
class ReportSubscriberUsing extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'report_subscriber_using';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['report_date', 'site_id', 'subscriber_total', 'service_total', 'type_model', 'service_id'], 'integer'],
            [['city'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'report_date' => 'Report Date',
            'site_id' => 'Site ID',
            'subscriber_total' => 'Subscriber Total',
            'service_total' => 'Service Total',
            'type_model' => 'Type Model',
            'city' => 'City',
            'service_id' => 'Service ID',
        ];
    }
}
