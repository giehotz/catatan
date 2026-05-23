<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * @property \CodeIgniter\Database\BaseConnection $db
 */
class CreateDebtPaymentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'debt_id' => [
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
        $this->forge->addForeignKey('debt_id', 'debts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('debt_payments', true);

        // Add check constraint for debt payment amount > 0
        $this->db->query("ALTER TABLE debt_payments ADD CONSTRAINT chk_debt_payment_amount CHECK (amount > 0)");
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();
        $this->forge->dropTable('debt_payments', true);
        $this->db->enableForeignKeyChecks();
    }
}
