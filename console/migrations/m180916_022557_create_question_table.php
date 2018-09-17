<?php

use yii\db\Migration;

/**
 * Handles the creation for table `question_table`.
 */
class m180916_022557_create_question_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('question', [
            'id' => $this->primaryKey(),
            'program_id' => $this->integer(),
            'question' => $this->string(),
            'status' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('question');
    }
}
