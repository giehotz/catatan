<?php
 
namespace App\Controllers;
 
use App\Services\RecurringService;
use App\Models\IncomeCategoryModel;
use App\Models\ExpenseCategoryModel;
 
class Recurring extends BaseController
{
    protected RecurringService $recurringService;
    protected IncomeCategoryModel $incomeCategoryModel;
    protected ExpenseCategoryModel $expenseCategoryModel;
 
    public function __construct()
    {
        $this->recurringService = new RecurringService();
        $this->incomeCategoryModel = new IncomeCategoryModel();
        $this->expenseCategoryModel = new ExpenseCategoryModel();
    }
 
    /**
     * Display recurring transactions scheduling dashboard
     */
    public function index()
    {
        $userId = auth()->id();
 
        // Execute background catch-up processor dynamically
        $this->recurringService->processUserSchedules($userId);
 
        // Fetch schedules report
        $schedules = $this->recurringService->getSchedulesReport($userId);
 
        // Fetch user categories for dropdown forms
        $incomeCategories = $this->incomeCategoryModel->where('user_id', $userId)->findAll();
        $expenseCategories = $this->expenseCategoryModel->where('user_id', $userId)->findAll();
 
        return view('user/recurring/index', [
            'title'             => 'Transaksi Berulang',
            'schedules'         => $schedules,
            'incomeCategories'  => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    }
 
    /**
     * Set up a new recurring transaction schedule
     */
    public function create()
    {
        $rules = [
            'type'        => 'required|in_list[income,expense]',
            'category_id' => 'required|numeric',
            'amount'      => 'required|numeric|greater_than[0]',
            'description' => 'required|max_length[255]',
            'frequency'   => 'required|in_list[daily,weekly,monthly,yearly]',
            'start_date'  => 'required|valid_date[Y-m-d]',
        ];
 
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
 
        $userId = auth()->id();
        $type = $this->request->getPost('type');
        $categoryId = intval($this->request->getPost('category_id'));
 
        // Verify category belongs to this user based on type
        if ($type === 'income') {
            $category = $this->incomeCategoryModel->where('user_id', $userId)->find($categoryId);
        } else {
            $category = $this->expenseCategoryModel->where('user_id', $userId)->find($categoryId);
        }
 
        if (!$category) {
            return redirect()->back()->withInput()->with('error', 'Kategori yang dipilih tidak valid atau tidak ditemukan.');
        }
 
        $scheduleData = [
            'type'        => $type,
            'category_id' => $categoryId,
            'amount'      => floatval($this->request->getPost('amount')),
            'description' => trim((string) $this->request->getPost('description')),
            'frequency'   => $this->request->getPost('frequency'),
            'start_date'  => $this->request->getPost('start_date'),
        ];
 
        if ($this->recurringService->createSchedule($userId, $scheduleData)) {
            return redirect()->to('/recurring')->with('success', 'Jadwal transaksi berulang berhasil ditambahkan!');
        }
 
        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan jadwal transaksi berulang.');
    }
 
    /**
     * Pause or resume schedule
     */
    public function toggle(int $id)
    {
        $userId = auth()->id();
 
        if ($this->recurringService->toggleSchedule($userId, $id)) {
            return redirect()->to('/recurring')->with('success', 'Status jadwal transaksi berulang berhasil diperbarui!');
        }
 
        return redirect()->to('/recurring')->with('error', 'Gagal mengubah status jadwal.');
    }
 
    /**
     * Delete schedule
     */
    public function delete(int $id)
    {
        $userId = auth()->id();
 
        if ($this->recurringService->deleteSchedule($userId, $id)) {
            return redirect()->to('/recurring')->with('success', 'Jadwal transaksi berulang berhasil dihapus!');
        }
 
        return redirect()->to('/recurring')->with('error', 'Gagal menghapus jadwal transaksi berulang.');
    }
}
