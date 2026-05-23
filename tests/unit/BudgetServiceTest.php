<?php

use App\Services\BudgetService;
use App\Models\BudgetModel;
use App\Models\TransactionModel;
use App\Models\ExpenseCategoryModel;
use CodeIgniter\Test\CIUnitTestCase;

final class BudgetServiceTest extends CIUnitTestCase
{
    private BudgetService $service;

    /**
     * Create BudgetService without calling its real constructor
     * (which tries to instantiate CI4 models that need a database).
     */
    private function createService(): BudgetService
    {
        $reflection = new ReflectionClass(BudgetService::class);
        return $reflection->newInstanceWithoutConstructor();
    }

    /**
     * Create a mock for a CI4 Model with fluent method chaining.
     *
     * CI4 Model uses __call() for ->where(), ->selectSum(), ->groupBy(), etc.
     * These must be mocked via addMethods(). Terminal methods (findAll, first,
     * insert, update, delete) use onlyMethods().
     */
    private function createModelMock(
        string $class,
        array $fluentMethods = ['where', 'selectSum', 'groupBy', 'orderBy', 'select'],
        ?array $findAllResult = null,
        mixed $firstResult = null,
    ): object {
        $builder = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->addMethods($fluentMethods)
            ->onlyMethods(['findAll', 'first', 'insert', 'update', 'delete', 'countAllResults'])
            ->getMock();

        foreach ($fluentMethods as $method) {
            $builder->method($method)->willReturnSelf();
        }

        if ($findAllResult !== null) {
            $builder->method('findAll')->willReturn($findAllResult);
        }

        if ($firstResult !== null) {
            $builder->method('first')->willReturn($firstResult);
        }

        return $builder;
    }

    /**
     * Inject a mock model into the service by property name.
     */
    private function injectMock(string $propertyName, object $mock): void
    {
        $reflection = new ReflectionClass(BudgetService::class);
        $prop = $reflection->getProperty($propertyName);
        $prop->setValue($this->service, $mock);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->createService();
    }

    public function testGetBudgetReportReturnsAllCategories(): void
    {
        $expenseCategoryModel = $this->createModelMock(
            ExpenseCategoryModel::class,
            findAllResult: [
                ['id' => 1, 'name' => 'Makanan', 'parent_id' => null],
                ['id' => 2, 'name' => 'Transportasi', 'parent_id' => null],
                ['id' => 3, 'name' => 'Makan Siang', 'parent_id' => 1],
                ['id' => 4, 'name' => 'Makan Malam', 'parent_id' => 1],
            ]
        );

        $budgetModel = $this->createModelMock(
            BudgetModel::class,
            findAllResult: [
                ['id' => 1, 'category_id' => 1, 'user_id' => 1, 'limit_amount' => 3000000],
                ['id' => 2, 'category_id' => 2, 'user_id' => 1, 'limit_amount' => 1000000],
            ]
        );

        $transactionModel = $this->createModelMock(
            TransactionModel::class,
            findAllResult: [
                ['category_id' => 1, 'amount' => 2500000],
                ['category_id' => 2, 'amount' => 500000],
                ['category_id' => 3, 'amount' => 800000],
            ]
        );

        $this->injectMock('expenseCategoryModel', $expenseCategoryModel);
        $this->injectMock('budgetModel', $budgetModel);
        $this->injectMock('transactionModel', $transactionModel);

        $report = $this->service->getBudgetReport(1);

        $this->assertCount(4, $report);
        $this->assertArrayHasKey(1, $report);
        $this->assertArrayHasKey(2, $report);
        $this->assertArrayHasKey(3, $report);
        $this->assertArrayHasKey(4, $report);
    }

    public function testGetBudgetReportParentCategoryCalculatesRollupSpending(): void
    {
        $expenseCategoryModel = $this->createModelMock(
            ExpenseCategoryModel::class,
            findAllResult: [
                ['id' => 1, 'name' => 'Makanan', 'parent_id' => null],
                ['id' => 2, 'name' => 'Transportasi', 'parent_id' => null],
                ['id' => 3, 'name' => 'Makan Siang', 'parent_id' => 1],
                ['id' => 4, 'name' => 'Makan Malam', 'parent_id' => 1],
            ]
        );

        $budgetModel = $this->createModelMock(
            BudgetModel::class,
            findAllResult: [
                ['id' => 1, 'category_id' => 1, 'user_id' => 1, 'limit_amount' => 3000000],
                ['id' => 2, 'category_id' => 2, 'user_id' => 1, 'limit_amount' => 1000000],
            ]
        );

        $transactionModel = $this->createModelMock(
            TransactionModel::class,
            findAllResult: [
                ['category_id' => 1, 'amount' => 2500000],
                ['category_id' => 2, 'amount' => 500000],
                ['category_id' => 3, 'amount' => 800000],
            ]
        );

        $this->injectMock('expenseCategoryModel', $expenseCategoryModel);
        $this->injectMock('budgetModel', $budgetModel);
        $this->injectMock('transactionModel', $transactionModel);

        $report = $this->service->getBudgetReport(1);

        $this->assertSame(3300000.0, $report[1]['spending']);
        $this->assertSame(3000000.0, $report[1]['limit_amount']);
        $this->assertSame(110.0, $report[1]['percent']);
        $this->assertSame('red', $report[1]['status_color']);
        $this->assertSame('Overbudget', $report[1]['status_text']);
        $this->assertTrue($report[1]['is_parent']);
        $this->assertSame(2, $report[1]['children_count']);
    }

    public function testGetBudgetReportChildCategoryNoLimit(): void
    {
        $expenseCategoryModel = $this->createModelMock(
            ExpenseCategoryModel::class,
            findAllResult: [
                ['id' => 3, 'name' => 'Makan Siang', 'parent_id' => null],
            ]
        );

        $budgetModel = $this->createModelMock(
            BudgetModel::class,
            findAllResult: []
        );

        $transactionModel = $this->createModelMock(
            TransactionModel::class,
            findAllResult: [
                ['category_id' => 3, 'amount' => 800000],
            ]
        );

        $this->injectMock('expenseCategoryModel', $expenseCategoryModel);
        $this->injectMock('budgetModel', $budgetModel);
        $this->injectMock('transactionModel', $transactionModel);

        $report = $this->service->getBudgetReport(1);

        $this->assertSame(800000.0, $report[3]['spending']);
        $this->assertSame(0.0, $report[3]['limit_amount']);
        $this->assertSame(0.0, $report[3]['percent']);
    }

    public function testGetBudgetReportCategoryWithinBudget(): void
    {
        $expenseCategoryModel = $this->createModelMock(
            ExpenseCategoryModel::class,
            findAllResult: [
                ['id' => 2, 'name' => 'Transportasi', 'parent_id' => null],
            ]
        );

        $budgetModel = $this->createModelMock(
            BudgetModel::class,
            findAllResult: [
                ['id' => 2, 'category_id' => 2, 'user_id' => 1, 'limit_amount' => 1000000],
            ]
        );

        $transactionModel = $this->createModelMock(
            TransactionModel::class,
            findAllResult: [
                ['category_id' => 2, 'amount' => 500000],
            ]
        );

        $this->injectMock('expenseCategoryModel', $expenseCategoryModel);
        $this->injectMock('budgetModel', $budgetModel);
        $this->injectMock('transactionModel', $transactionModel);

        $report = $this->service->getBudgetReport(1);

        $this->assertSame(500000.0, $report[2]['spending']);
        $this->assertSame(1000000.0, $report[2]['limit_amount']);
        $this->assertSame(50.0, $report[2]['percent']);
        $this->assertSame('green', $report[2]['status_color']);
        $this->assertSame('Aman', $report[2]['status_text']);
    }

    public function testGetSmartAlertsReturnsOnlyCategoriesAbove80Percent(): void
    {
        $expenseCategoryModel = $this->createModelMock(
            ExpenseCategoryModel::class,
            findAllResult: [
                ['id' => 1, 'name' => 'Makanan', 'parent_id' => null],
            ]
        );

        $budgetModel = $this->createModelMock(
            BudgetModel::class,
            findAllResult: [
                ['id' => 1, 'category_id' => 1, 'user_id' => 1, 'limit_amount' => 1000000],
            ]
        );

        $transactionModel = $this->createModelMock(
            TransactionModel::class,
            findAllResult: [
                ['category_id' => 1, 'amount' => 900000],  // 90%
            ]
        );

        $this->injectMock('expenseCategoryModel', $expenseCategoryModel);
        $this->injectMock('budgetModel', $budgetModel);
        $this->injectMock('transactionModel', $transactionModel);

        $alerts = $this->service->getSmartAlerts(1);

        $this->assertCount(1, $alerts);
        $this->assertSame(90.0, $alerts[0]['percent']);
    }

    public function testGetSmartAlertsExcludesCategoriesUnder80Percent(): void
    {
        $expenseCategoryModel = $this->createModelMock(
            ExpenseCategoryModel::class,
            findAllResult: [
                ['id' => 1, 'name' => 'Makanan', 'parent_id' => null],
            ]
        );

        $budgetModel = $this->createModelMock(
            BudgetModel::class,
            findAllResult: [
                ['id' => 1, 'category_id' => 1, 'user_id' => 1, 'limit_amount' => 1000000],
            ]
        );

        $transactionModel = $this->createModelMock(
            TransactionModel::class,
            findAllResult: [
                ['category_id' => 1, 'amount' => 500000],  // 50%
            ]
        );

        $this->injectMock('expenseCategoryModel', $expenseCategoryModel);
        $this->injectMock('budgetModel', $budgetModel);
        $this->injectMock('transactionModel', $transactionModel);

        $alerts = $this->service->getSmartAlerts(1);
        $this->assertCount(0, $alerts);
    }

    public function testSetLimitInsertsWhenNoExistingBudget(): void
    {
        $model = $this->createModelMock(BudgetModel::class);
        $model->method('first')->willReturn(null);
        $model->method('insert')->with([
            'user_id' => 1, 'category_id' => 5, 'limit_amount' => 2000000
        ])->willReturn(1);

        $this->injectMock('budgetModel', $model);
        $this->injectMock('transactionModel', $this->createModelMock(TransactionModel::class));
        $this->injectMock('expenseCategoryModel', $this->createModelMock(ExpenseCategoryModel::class));

        $result = $this->service->setLimit(1, 5, 2000000);
        $this->assertTrue($result);
    }

    public function testSetLimitUpdatesWhenExistingBudget(): void
    {
        $model = $this->createModelMock(BudgetModel::class);
        $model->method('first')->willReturn(['id' => 1, 'user_id' => 1, 'category_id' => 1, 'limit_amount' => 1000000]);
        $model->method('update')->with(1, [
            'user_id' => 1, 'category_id' => 1, 'limit_amount' => 3000000
        ])->willReturn(true);

        $this->injectMock('budgetModel', $model);
        $this->injectMock('transactionModel', $this->createModelMock(TransactionModel::class));
        $this->injectMock('expenseCategoryModel', $this->createModelMock(ExpenseCategoryModel::class));

        $result = $this->service->setLimit(1, 1, 3000000);
        $this->assertTrue($result);
    }

    public function testDeleteLimit(): void
    {
        $model = $this->createModelMock(BudgetModel::class);
        $model->method('delete')->willReturn(true);

        $this->injectMock('budgetModel', $model);
        $this->injectMock('transactionModel', $this->createModelMock(TransactionModel::class));
        $this->injectMock('expenseCategoryModel', $this->createModelMock(ExpenseCategoryModel::class));

        $result = $this->service->deleteLimit(1, 1);
        $this->assertTrue($result);
    }
}
