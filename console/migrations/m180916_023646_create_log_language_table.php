<?php

use yii\db\Migration;

/**
 * Handles the creation for table `log_language_table`.
 */
class m180916_023646_create_log_language_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('log_language', [
            'id' => $this->primaryKey(),
            'id_lang' => $this->integer(),
            'id_user' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('log_language');
    }
}
