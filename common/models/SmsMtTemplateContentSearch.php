<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SmsMtTemplateContent;

/**
 * SmsMtTemplateContentSearch represents the model behind the search form about `\common\models\SmsMtTemplateContent`.
 */
class SmsMtTemplateContentSearch extends SmsMtTemplateContent
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type', 'created_at', 'updated_at',
                'site_id', 'sms_mo_syntax_id',
                'sms_mt_template_id','service_id'], 'integer'],
            [['code_name', 'content'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = SmsMtTemplateContent::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'service_id' => $this->service_id,
            'site_id' => $this->site_id,
            'sms_mo_syntax_id' => $this->sms_mo_syntax_id,
            'sms_mt_template_id' => $this->sms_mt_template_id,
        ]);

        $query->andFilterWhere(['like', 'code_name', $this->code_name])
            ->andFilterWhere(['like', 'content', $this->content]);

        return $dataProvider;
    }
}
