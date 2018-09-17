<?php

use yii\db\Migration;

/**
 * Handles the creation for table `program_table`.
 */
class m180916_022654_create_program_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('program', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'order' => $this->integer(),
            'level' => $this->integer(),
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
        $this->dropTable('program');
    }
}
