<?php
 
namespace App\Commands;
 
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\RecurringTransactionModel;
use App\Services\RecurringService;
 
class ProcessRecurring extends BaseCommand
{
    protected $group       = 'Recurring';
    protected $name        = 'recurring:process';
    protected $description = 'Processes all due recurring transactions for all active users.';
 
    public function run(array $params)
    {
        CLI::write('Starting recurring transactions automated processor...', 'blue');
 
        $recurringModel = new RecurringTransactionModel();
        $recurringService = new RecurringService();
 
        // Find all unique user IDs having active schedules in system
        $users = $recurringModel->where('is_active', 1)
                                ->distinct()
                                ->select('user_id')
                                ->findAll();
 
        if (empty($users)) {
            CLI::write('No active schedules found in database.', 'yellow');
            return;
        }
 
        $totalProcessed = 0;
 
        foreach ($users as $user) {
            $userId = intval($user['user_id']);
            CLI::write("Processing user ID: {$userId}...", 'cyan');
 
            try {
                $count = $recurringService->processUserSchedules($userId);
                if ($count > 0) {
                    CLI::write("-> Created {$count} transaction(s) for user ID {$userId}.", 'green');
                    $totalProcessed += $count;
                } else {
                    CLI::write("-> No pending schedules for user ID {$userId}.", 'gray');
                }
            } catch (\Exception $e) {
                CLI::write("-> [ERROR] Failed processing user {$userId}: " . $e->getMessage(), 'red');
            }
        }
 
        CLI::write("Completed! Total transactions generated: {$totalProcessed}.", 'green');
    }
}
