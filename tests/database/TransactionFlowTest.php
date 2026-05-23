<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\TransactionModel;
use App\Models\IncomeCategoryModel;
use App\Models\ExpenseCategoryModel;
use App\Models\WalletModel;
use Config\Database;

/**
 * Integration test for transaction CRUD flow.
 *
 * Prerequisites:
 *   1. Copy phpunit.xml.dist to phpunit.xml
 *   2. Configure a MySQL test database in phpunit.xml:
 *        <env name="database.tests.hostname" value="localhost"/>
 *        <env name="database.tests.database" value="catatan_test"/>
 *        <env name="database.tests.username" value="root"/>
 *        <env name="database.tests.password" value=""/>
 *        <env name="database.tests.DBDriver" value="MySQLi"/>
 *   3. Run: vendor/bin/phpunit tests/database/TransactionFlowTest.php
 *
 * The default test DB (SQLite3 :memory:) is incompatible with the
 * MySQL-specific migrations (ENUM types, CHECK constraints).
 *
 * @internal
 */
final class TransactionFlowTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    private TransactionModel $transactionModel;
    private IncomeCategoryModel $incomeCategoryModel;
    private ExpenseCategoryModel $expenseCategoryModel;
    private WalletModel $walletModel;

    protected $migrate = true;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();

        $db = Database::connect();
        if ($db->DBDriver === 'SQLite3') {
            $this->markTestSkipped(
                'Requires MySQL test database. Configure phpunit.xml with database.tests.* env vars pointing to a MySQL database.'
            );
        }

        $this->transactionModel = new TransactionModel();
        $this->incomeCategoryModel = new IncomeCategoryModel();
        $this->expenseCategoryModel = new ExpenseCategoryModel();
        $this->walletModel = new WalletModel();

        // Seed: create a test user
        $db->table('users')->insert([
            'username' => 'testuser',
            'email'    => 'test@example.com',
            'password' => password_hash('test123', PASSWORD_BCRYPT),
            'status'   => 'active',
        ]);

        // Seed: create a wallet for the user
        $this->walletModel->insert([
            'user_id' => 1,
            'name'    => 'Dompet Utama',
            'balance' => 5000000,
        ]);

        // Seed: income and expense categories
        $this->incomeCategoryModel->insert([
            'user_id' => 1,
            'name'    => 'Gaji',
        ]);

        $this->expenseCategoryModel->insert([
            'user_id'   => 1,
            'name'      => 'Makanan',
            'parent_id' => null,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // DatabaseTestTrait rolls back the transaction automatically,
        // so no explicit cleanup needed unless using non-transactional engines.
    }

    public function testCreateIncomeTransaction(): void
    {
        $txId = $this->transactionModel->insert([
            'user_id'          => 1,
            'wallet_id'        => 1,
            'type'             => 'income',
            'category_id'      => 1,
            'amount'           => 3000000,
            'description'      => 'Gaji bulan Mei 2026',
            'transaction_date' => '2026-05-01',
        ]);

        $this->assertNotFalse($txId);

        $tx = $this->transactionModel->find($txId);
        $this->assertNotNull($tx);
        $this->assertSame('income', $tx['type']);
        $this->assertSame(3000000.0, (float) $tx['amount']);
        $this->assertSame('Gaji bulan Mei 2026', $tx['description']);
    }

    public function testCreateExpenseTransaction(): void
    {
        $txId = $this->transactionModel->insert([
            'user_id'          => 1,
            'wallet_id'        => 1,
            'type'             => 'expense',
            'category_id'      => 1,
            'amount'           => 75000,
            'description'      => 'Makan siang',
            'transaction_date' => '2026-05-02',
        ]);

        $this->assertNotFalse($txId);

        $tx = $this->transactionModel->find($txId);
        $this->assertNotNull($tx);
        $this->assertSame('expense', $tx['type']);
        $this->assertSame(75000.0, (float) $tx['amount']);
    }

    public function testCannotInsertZeroAmountTransaction(): void
    {
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);

        $this->transactionModel->insert([
            'user_id'          => 1,
            'wallet_id'        => 1,
            'type'             => 'expense',
            'category_id'      => 1,
            'amount'           => 0,
            'description'      => 'Zero amount',
            'transaction_date' => '2026-05-03',
        ]);
    }

    public function testCannotInsertNegativeAmountTransaction(): void
    {
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);

        $this->transactionModel->insert([
            'user_id'          => 1,
            'wallet_id'        => 1,
            'type'             => 'expense',
            'category_id'      => 1,
            'amount'           => -50000,
            'description'      => 'Negative amount',
            'transaction_date' => '2026-05-03',
        ]);
    }

    public function testUpdateTransaction(): void
    {
        $txId = $this->transactionModel->insert([
            'user_id'          => 1,
            'wallet_id'        => 1,
            'type'             => 'expense',
            'category_id'      => 1,
            'amount'           => 50000,
            'description'      => 'Original description',
            'transaction_date' => '2026-05-04',
        ]);

        $updated = $this->transactionModel->update($txId, [
            'amount'      => 60000,
            'description' => 'Updated description',
        ]);

        $this->assertTrue($updated);

        $tx = $this->transactionModel->find($txId);
        $this->assertSame(60000.0, (float) $tx['amount']);
        $this->assertSame('Updated description', $tx['description']);
    }

    public function testDeleteTransaction(): void
    {
        $txId = $this->transactionModel->insert([
            'user_id'          => 1,
            'wallet_id'        => 1,
            'type'             => 'expense',
            'category_id'      => 1,
            'amount'           => 100000,
            'description'      => 'To be deleted',
            'transaction_date' => '2026-05-05',
        ]);

        $deleted = $this->transactionModel->delete($txId);
        $this->assertTrue($deleted);

        $tx = $this->transactionModel->find($txId);
        $this->assertNull($tx);
    }

    public function testFindTransactionsByDateRange(): void
    {
        // Insert multiple transactions
        $this->transactionModel->insert([
            'user_id' => 1, 'wallet_id' => 1, 'type' => 'expense',
            'category_id' => 1, 'amount' => 50000,
            'transaction_date' => '2026-05-01',
        ]);
        $this->transactionModel->insert([
            'user_id' => 1, 'wallet_id' => 1, 'type' => 'expense',
            'category_id' => 1, 'amount' => 75000,
            'transaction_date' => '2026-05-15',
        ]);
        $this->transactionModel->insert([
            'user_id' => 1, 'wallet_id' => 1, 'type' => 'expense',
            'category_id' => 1, 'amount' => 100000,
            'transaction_date' => '2026-06-01',
        ]);

        // Filter by May 2026
        $txs = $this->transactionModel
            ->where('user_id', 1)
            ->where('transaction_date >=', '2026-05-01')
            ->where('transaction_date <=', '2026-05-31')
            ->findAll();

        $this->assertCount(2, $txs);
    }

    public function testTransactionsScopedByUser(): void
    {
        $this->transactionModel->insert([
            'user_id' => 1, 'wallet_id' => 1, 'type' => 'expense',
            'category_id' => 1, 'amount' => 50000,
            'transaction_date' => '2026-05-01',
        ]);

        // Ensure another user cannot see this transaction directly
        $txs = $this->transactionModel
            ->where('user_id', 999)
            ->findAll();

        $this->assertCount(0, $txs);
    }
}
