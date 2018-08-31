<?php
/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 03-Aug-16
 * Time: 11:28 AM
 */

namespace common\models;
use common\helpers\CVietnameseTools;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class ItemKodiSearch extends ItemKodi
{
    public $keyword;
    public $categoryIds;
    public $listCatIds;
    public $cp_id;
    public $site_id;
    public $category_id;
    public $order;
    public $content_id;
    public $pricing_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
{
    return [
        [['type', 'status', 'honor', 'created_at', 'updated_at'], 'integer'],
        [['display_name'],'integer']
    ];
}

    /**
     * {@inheritdoc}
     */
    public function scenarios()
{
    // bypass scenarios() implementation in the parent class
    return Model::scenarios();
}

    /**
     * Creates data provider instance with search query applied.
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
{
//                $query = Content::find();
    $query = KodiCategory::find();
        $query->innerJoin('kodi_category_item_asm as kasm');
        $query->andWhere('kasm.category_id = kodi_category.id');
        $query->andWhere(['kodi_category.status' => self::STATUS_ACTIVE]);

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'sort'  => [
        ],
    ]);

    $this->load($params);

    if (!$this->validate()) {
        // uncomment the following line if you do not want to return any records when validation fails
        // $query->where('0=1');
        return $dataProvider;
    }



    return $dataProvider;
}

}
