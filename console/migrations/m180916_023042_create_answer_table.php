<?php

use yii\db\Migration;

/**
 * Handles the creation for table `answer_table`.
 */
class m180916_023042_create_answer_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('answer', [
            'id' => $this->primaryKey(),
            'id_question' => $this->integer(),
            'answer' => $this->string(),
            'is_correct' => $this->integer(),
            'status' => $this->integer(),
            'created_at'=> $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('answer');
    }
}
