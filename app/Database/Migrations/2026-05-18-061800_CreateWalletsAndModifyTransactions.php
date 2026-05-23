<?php
 
namespace App\Database\Migrations;
 
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;
 
class CreateWalletsAndModifyTransactions extends Migration
{
    public function up()
    {
        // 1. Create 'wallets' table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['cash', 'bank', 'e-wallet', 'investment', 'other'],
                'default'    => 'cash',
            ],
            'balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('wallets', true);
 
        // 2. Insert "Dompet Utama" for all existing users
        $users = $this->db->table('users')->select('id')->get()->getResultArray();
        foreach ($users as $user) {
            $this->db->table('wallets')->insert([
                'user_id' => $user['id'],
                'name'    => 'Dompet Utama',
                'type'    => 'cash',
                'balance' => 0.00
            ]);
        }
 
        // 3. Add wallet fields to 'transactions'
        $this->forge->addColumn('transactions', [
            'wallet_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'user_id',
            ],
            'to_wallet_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'wallet_id',
            ],
        ]);
 
        // 4. Add constraints and make category_id nullable in 'transactions'
        $this->db->query("ALTER TABLE transactions ADD CONSTRAINT fk_transactions_wallet_id FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE SET NULL");
        $this->db->query("ALTER TABLE transactions ADD CONSTRAINT fk_transactions_to_wallet_id FOREIGN KEY (to_wallet_id) REFERENCES wallets(id) ON DELETE SET NULL");
        $this->db->query("ALTER TABLE transactions MODIFY category_id INT(11) NULL");
        $this->db->query("ALTER TABLE transactions MODIFY type ENUM('income', 'expense', 'transfer') NOT NULL");
 
        // 5. Associate existing transactions with the new "Dompet Utama"
        foreach ($users as $user) {
            $defaultWallet = $this->db->table('wallets')
                ->where('user_id', $user['id'])
                ->where('name', 'Dompet Utama')
                ->get()
                ->getRowArray();
            
            if ($defaultWallet) {
                $this->db->table('transactions')
                    ->where('user_id', $user['id'])
                    ->update(['wallet_id' => $defaultWallet['id']]);
            }
        }
 
        // 6. Recalculate balances for existing wallets
        $wallets = $this->db->table('wallets')->get()->getResultArray();
        foreach ($wallets as $wallet) {
            $totalIncome = $this->db->table('transactions')
                ->selectSum('amount')
                ->where('wallet_id', $wallet['id'])
                ->where('type', 'income')
                ->get()
                ->getRowArray()['amount'] ?? 0;
            
            $totalExpense = $this->db->table('transactions')
                ->selectSum('amount')
                ->where('wallet_id', $wallet['id'])
                ->where('type', 'expense')
                ->get()
                ->getRowArray()['amount'] ?? 0;
            
            $balance = floatval($totalIncome) - floatval($totalExpense);
            
            $this->db->table('wallets')
                ->where('id', $wallet['id'])
                ->update(['balance' => $balance]);
        }
    }
 
    public function down()
    {
        $this->db->disableForeignKeyChecks();
 
        // Remove foreign keys first
        $this->db->query("ALTER TABLE transactions DROP FOREIGN KEY fk_transactions_wallet_id");
        $this->db->query("ALTER TABLE transactions DROP FOREIGN KEY fk_transactions_to_wallet_id");
 
        // Revert category_id to not null and type to original ENUM
        $this->db->query("ALTER TABLE transactions MODIFY category_id INT(11) NOT NULL");
        $this->db->query("ALTER TABLE transactions MODIFY type ENUM('income', 'expense') NOT NULL");
 
        // Drop added columns
        $this->forge->dropColumn('transactions', 'wallet_id');
        $this->forge->dropColumn('transactions', 'to_wallet_id');
 
        // Drop 'wallets' table
        $this->forge->dropTable('wallets', true);
 
        $this->db->enableForeignKeyChecks();
    }
}
