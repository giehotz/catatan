<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * @property \CodeIgniter\Database\BaseConnection $db
 */
class CreateReceivablePaymentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'receivable_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
            ],
            'payment_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'note' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'default'    => null,
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('receivable_id', 'receivables', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('receivable_payments', true);

        // Add check constraint for receivable payment amount > 0
        $this->db->query("ALTER TABLE receivable_payments ADD CONSTRAINT chk_receivable_payment_amount CHECK (amount > 0)");
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();
        $this->forge->dropTable('receivable_payments', true);
        $this->db->enableForeignKeyChecks();
    }
}
