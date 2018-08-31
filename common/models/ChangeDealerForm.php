<?php
/**
 * Created by PhpStorm.
 * User: bibon
 * Date: 5/19/2016
 * Time: 12:40 AM
 */

namespace common\models;


use yii\base\Model;

/**
 *
 * @property integer $src_dealer_id
 * @property integer $dest_dealer_id
 * @property integer[] ids
 *
 */

class ChangeDealerForm extends Model
{
    public $src_dealer_id;
    public $dest_dealer_id;
    public $ids;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['src_dealer_id', 'dest_dealer_id'], 'integer'],
            ['ids', 'each', 'rule' => ['integer']],
            [['src_dealer_id', 'dest_dealer_id', 'ids'], 'required'],
        ];
    }
}