<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddThemePreferenceToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'theme_preference' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'system',
                'null'       => false,
                'after'      => 'status_message',
            ],
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'theme_preference');
    }
}
