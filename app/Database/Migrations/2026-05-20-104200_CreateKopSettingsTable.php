<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateKopSettingsTable extends Migration
{
    public function up()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->addField([
            'key' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'value' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addPrimaryKey('key');
        $this->forge->createTable('kop_settings', true);

        // Seed default settings: direct_loan_enabled = 0 (disabled by default)
        $this->db->table('kop_settings')->insert([
            'key'   => 'direct_loan_enabled',
            'value' => '0',
        ]);

        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();
        $this->forge->dropTable('kop_settings', true);
        $this->db->enableForeignKeyChecks();
    }
}
