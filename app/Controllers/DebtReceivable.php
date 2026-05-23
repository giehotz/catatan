<?php

namespace App\Controllers;

use App\Models\DebtModel;
use App\Models\ReceivableModel;

class DebtReceivable extends BaseController
{
    protected DebtModel $debtModel;
    protected ReceivableModel $receivableModel;

    public function __construct()
    {
        $this->debtModel = new DebtModel();
        $this->receivableModel = new ReceivableModel();
    }

    public function index()
    {
        $userId = auth()->id();

        // 1. Process filters
        $search = $this->request->getGet('search');
        $status = $this->request->getGet('status');

        // 2. Fetch Debts (Utang)
        $debtQuery = $this->debtModel->where('user_id', $userId);
        if (!empty($search)) {
            $debtQuery->like('creditor_name', $search);
        }
        if (!empty($status)) {
            $debtQuery->where('status', $status);
        }
        $debts = $debtQuery->orderBy('due_date', 'ASC')->orderBy('id', 'DESC')->findAll();

        // 3. Fetch Receivables (Piutang)
        $receivableQuery = $this->receivableModel->where('user_id', $userId);
        if (!empty($search)) {
            $receivableQuery->like('borrower_name', $search);
        }
        if (!empty($status)) {
            $receivableQuery->where('status', $status);
        }
        $receivables = $receivableQuery->orderBy('due_date', 'ASC')->orderBy('id', 'DESC')->findAll();

        // 4. Calculate Summaries
        $totalDebt = 0;
        $totalReceivable = 0;

        foreach ($debts as $d) {
            if ($d['status'] !== 'paid') {
                $totalDebt += floatval($d['total_amount']);
            }
        }

        foreach ($receivables as $r) {
            if ($r['status'] !== 'paid') {
                $totalReceivable += floatval($r['total_amount']);
            }
        }

        $netExposure = $totalReceivable - $totalDebt;

        return view('user/debt_receivable/index', [
            'title'           => 'Utang Piutang',
            'debts'           => $debts,
            'receivables'     => $receivables,
            'totalDebt'       => $totalDebt,
            'totalReceivable' => $totalReceivable,
            'netExposure'     => $netExposure,
            'filterSearch'    => $search,
            'filterStatus'    => $status,
        ]);
    }

    public function createDebt()
    {
        $rules = [
            'creditor_name' => 'required|min_length[2]|max_length[150]',
            'total_amount'  => 'required|numeric|greater_than[0]',
            'due_date'      => 'permit_empty|valid_date',
            'description'   => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = auth()->id();
        $dueDate = $this->request->getPost('due_date') ?: null;

        $this->debtModel->insert([
            'user_id'       => $userId,
            'creditor_name' => esc($this->request->getPost('creditor_name')),
            'total_amount'  => $this->request->getPost('total_amount'),
            'due_date'      => $dueDate,
            'description'   => esc($this->request->getPost('description')),
            'status'        => 'unpaid',
        ]);

        return redirect()->to('/debt-receivable')->with('message', 'Catatan utang berhasil ditambahkan.');
    }

    public function createReceivable()
    {
        $rules = [
            'borrower_name' => 'required|min_length[2]|max_length[150]',
            'total_amount'  => 'required|numeric|greater_than[0]',
            'due_date'      => 'permit_empty|valid_date',
            'description'   => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = auth()->id();
        $dueDate = $this->request->getPost('due_date') ?: null;

        $this->receivableModel->insert([
            'user_id'       => $userId,
            'borrower_name' => esc($this->request->getPost('borrower_name')),
            'total_amount'  => $this->request->getPost('total_amount'),
            'due_date'      => $dueDate,
            'description'   => esc($this->request->getPost('description')),
            'status'        => 'unpaid',
        ]);

        return redirect()->to('/debt-receivable')->with('message', 'Catatan piutang berhasil ditambahkan.');
    }

    public function updateStatus(string $type, int $id)
    {
        $userId = auth()->id();
        $status = $this->request->getPost('status');

        if (!in_array($status, ['unpaid', 'partial', 'paid'])) {
            return redirect()->to('/debt-receivable')->with('error', 'Status pembayaran tidak valid.');
        }

        if ($type === 'debt') {
            $record = $this->debtModel->where('id', $id)->where('user_id', $userId)->first();
            if (!$record) {
                return redirect()->to('/debt-receivable')->with('error', 'Catatan utang tidak ditemukan.');
            }
            $this->debtModel->update($id, ['status' => $status]);
        } elseif ($type === 'receivable') {
            $record = $this->receivableModel->where('id', $id)->where('user_id', $userId)->first();
            if (!$record) {
                return redirect()->to('/debt-receivable')->with('error', 'Catatan piutang tidak ditemukan.');
            }
            $this->receivableModel->update($id, ['status' => $status]);
        } else {
            return redirect()->to('/debt-receivable')->with('error', 'Parameter tipe tidak valid.');
        }

        return redirect()->to('/debt-receivable')->with('message', 'Status pembayaran berhasil diperbarui.');
    }

    public function delete(string $type, int $id)
    {
        $userId = auth()->id();

        if ($type === 'debt') {
            $record = $this->debtModel->where('id', $id)->where('user_id', $userId)->first();
            if (!$record) {
                return redirect()->to('/debt-receivable')->with('error', 'Catatan utang tidak ditemukan.');
            }
            $this->debtModel->delete($id);
        } elseif ($type === 'receivable') {
            $record = $this->receivableModel->where('id', $id)->where('user_id', $userId)->first();
            if (!$record) {
                return redirect()->to('/debt-receivable')->with('error', 'Catatan piutang tidak ditemukan.');
            }
            $this->receivableModel->delete($id);
        } else {
            return redirect()->to('/debt-receivable')->with('error', 'Parameter tipe tidak valid.');
        }

        return redirect()->to('/debt-receivable')->with('message', 'Catatan berhasil dihapus.');
    }
}
