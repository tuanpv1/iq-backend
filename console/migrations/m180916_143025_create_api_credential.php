<?php

use yii\db\Migration;

/**
 * Handles the creation for table `api_credential`.
 */
class m180916_143025_create_api_credential extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('api_credential', [
            'id' => $this->primaryKey(),
            'client_name' => $this->string(),
            'client_api_key' => $this->string(),
            'client_secret' => $this->string(),
            'description' => $this->text(),
            'status' => $this->integer(),
            'certificate_fingerprint' => $this->string(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('api_credential');
    }
}
