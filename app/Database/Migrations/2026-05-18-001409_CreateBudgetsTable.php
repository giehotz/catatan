<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * @property \CodeIgniter\Database\BaseConnection $db
 */
class CreateBudgetsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'limit_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        // Uniqueness index per user per category
        $this->forge->addUniqueKey(['user_id', 'category_id'], 'uq_user_category_budget');
        
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'expense_categories', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('budgets', true);

        // Add check constraint for limit_amount > 0
        $this->db->query("ALTER TABLE budgets ADD CONSTRAINT chk_budget_limit CHECK (limit_amount > 0)");
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();
        $this->forge->dropTable('budgets', true);
        $this->db->enableForeignKeyChecks();
    }
}
