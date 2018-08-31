



<?php

use yii\db\Schema;
use yii\db\Migration;

class m150520_040002_init_db_schema extends Migration
{
    public function up()
    {
        echo "\ninitial schema\n";
        $dbSchema = dirname((dirname(__DIR__))) . "/db/tvod2.sql";
        if (!file_exists($dbSchema)) {
            echo "***** DB schema not found: " . $dbSchema . " *****\n";
            return false;
        }
        else {
            echo "DB schema found: " . $dbSchema . ", applying...\n";
        }

        // Temporary variable, used to store current query
        $currentQuery = '';

        $lines = file($dbSchema);

        foreach ($lines as $line) {
            // Skip it if it's a comment
            if (substr($line, 0, 2) == '--' || trim($line) == '') {
                continue;
            }

            // Add this line to the current segment
            $currentQuery .= $line;
            // If it has a semicolon at the end, it's the end of the query
            if (substr(trim($line), -1, 1) == ';')
            {
                // Perform the query
//                echo "Query found: $currentQuery\n\n";
                $this->execute($currentQuery);
                echo "###########################\n\n";
                // Reset temp variable to empty
                $currentQuery = '';
            }
        }


        echo "Schema imported successfully";

        return true;
    }

    public function down()
    {
        echo "m150511_174029_init_schema cannot be reverted.\n";

        return false;
    }
    
    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }
    
    public function safeDown()
    {
    }
    */
}
