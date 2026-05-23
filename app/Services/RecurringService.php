<?php
 
namespace App\Services;
 
use App\Models\RecurringTransactionModel;
use App\Models\TransactionModel;
use App\Models\IncomeCategoryModel;
use App\Models\ExpenseCategoryModel;
 
class RecurringService
{
    /**
     * Processes all active recurring schedules of a user that are due.
     * Inserts standard transactions and updates schedules next run dates.
     *
     * @param int $userId
     * @return int Number of transactions generated
     */
    public function processUserSchedules(int $userId): int
    {
        $recurringModel = new RecurringTransactionModel();
        $transactionModel = new TransactionModel();
 
        $currentDateStr = date('Y-m-d');
 
        // Find all active schedules for this user that are due (next_run <= current_date)
        $schedules = $recurringModel->where('user_id', $userId)
                                    ->where('is_active', 1)
                                    ->where('next_run <=', $currentDateStr)
                                    ->findAll();
 
        $generatedCount = 0;
 
        foreach ($schedules as $schedule) {
            $nextRun = $schedule['next_run'];
            $lastRun = $schedule['last_run'];
 
            // Sequential execution for catching up missed transaction dates
            while ($nextRun <= $currentDateStr) {
                $transactionModel->insert([
                    'user_id'          => $schedule['user_id'],
                    'type'             => $schedule['type'],
                    'category_id'      => $schedule['category_id'],
                    'amount'             => $schedule['amount'],
                    'description'      => $schedule['description'] . ' (Otomatis Berulang)',
                    'transaction_date' => $nextRun,
                ]);
 
                $generatedCount++;
 
                // Set the run date that just executed as last_run
                $lastRun = $nextRun;
                
                // Calculate next run date
                $nextRun = $this->calculateNextRun($nextRun, $schedule['frequency']);
            }
 
            // Update the recurring record in database
            $recurringModel->update($schedule['id'], [
                'last_run' => $lastRun,
                'next_run' => $nextRun,
            ]);
        }
 
        return $generatedCount;
    }
 
    /**
     * Calculates the next date based on frequency.
     *
     * @param string $startDate Y-m-d format
     * @param string $frequency daily|weekly|monthly|yearly
     * @return string Next run date in Y-m-d format
     */
    public function calculateNextRun(string $startDate, string $frequency): string
    {
        $date = new \DateTime($startDate);
        switch ($frequency) {
            case 'daily':
                $date->modify('+1 day');
                break;
            case 'weekly':
                $date->modify('+1 week');
                break;
            case 'monthly':
                $date->modify('+1 month');
                break;
            case 'yearly':
                $date->modify('+1 year');
                break;
        }
        return $date->format('Y-m-d');
    }
 
    /**
     * Gets all recurring schedules with detailed category names and countdown text.
     *
     * @param int $userId
     * @return array
     */
    public function getSchedulesReport(int $userId): array
    {
        $recurringModel = new RecurringTransactionModel();
        $schedules = $recurringModel->where('user_id', $userId)
                                    ->orderBy('next_run', 'ASC')
                                    ->findAll();
 
        $incomeCatModel = new IncomeCategoryModel();
        $expenseCatModel = new ExpenseCategoryModel();
 
        // Build map for fast category lookup
        $incomeCats = [];
        foreach ($incomeCatModel->findAll() as $c) {
            $incomeCats[$c['id']] = $c['name'];
        }
 
        $expenseCats = [];
        foreach ($expenseCatModel->findAll() as $c) {
            $expenseCats[$c['id']] = $c['name'];
        }
 
        $report = [];
        $today = new \DateTime(date('Y-m-d'));
 
        foreach ($schedules as $s) {
            $catName = '-';
            if ($s['type'] === 'income') {
                $catName = $incomeCats[$s['category_id']] ?? '-';
            } else {
                $catName = $expenseCats[$s['category_id']] ?? '-';
            }
 
            // Calculate days left count
            $nextRunDate = new \DateTime($s['next_run']);
            $diff = $today->diff($nextRunDate);
            $daysLeft = (int)$diff->format('%r%a');
 
            $countdownText = '';
            if ($daysLeft < 0) {
                $countdownText = 'Terlewat ' . abs($daysLeft) . ' hari';
            } elseif ($daysLeft === 0) {
                $countdownText = 'Hari ini';
            } elseif ($daysLeft === 1) {
                $countdownText = 'Besok';
            } else {
                $countdownText = 'Dalam ' . $daysLeft . ' hari';
            }
 
            $report[] = array_merge($s, [
                'category_name'  => $catName,
                'days_left'      => $daysLeft,
                'countdown_text' => $countdownText,
            ]);
        }
 
        return $report;
    }
 
    /**
     * Creates a new recurring schedule.
     *
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function createSchedule(int $userId, array $data): bool
    {
        $recurringModel = new RecurringTransactionModel();
 
        $startDate = $data['start_date'];
        $nextRun = $startDate; // Set next run as start date initially so the catch-up processor handles it
 
        return $recurringModel->insert([
            'user_id'     => $userId,
            'type'        => $data['type'],
            'category_id' => $data['category_id'],
            'amount'      => $data['amount'],
            'description' => $data['description'],
            'frequency'   => $data['frequency'],
            'start_date'  => $startDate,
            'next_run'    => $nextRun,
            'is_active'   => 1,
        ]) !== false;
    }
 
    /**
     * Toggles schedule status (active <-> suspended).
     *
     * @param int $userId
     * @param int $scheduleId
     * @return bool
     */
    public function toggleSchedule(int $userId, int $scheduleId): bool
    {
        $recurringModel = new RecurringTransactionModel();
        $schedule = $recurringModel->where('user_id', $userId)->find($scheduleId);
 
        if (!$schedule) {
            return false;
        }
 
        $newStatus = ($schedule['is_active'] == 1) ? 0 : 1;
        return $recurringModel->update($scheduleId, [
            'is_active' => $newStatus
        ]) !== false;
    }
 
    /**
     * Deletes a schedule.
     *
     * @param int $userId
     * @param int $scheduleId
     * @return bool
     */
    public function deleteSchedule(int $userId, int $scheduleId): bool
    {
        $recurringModel = new RecurringTransactionModel();
        $schedule = $recurringModel->where('user_id', $userId)->find($scheduleId);
 
        if (!$schedule) {
            return false;
        }
 
        return $recurringModel->delete($scheduleId) !== false;
    }
}
