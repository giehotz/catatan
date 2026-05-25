<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 
        'wallet_id',
        'to_wallet_id',
        'type', 
        'category_id', 
        'amount', 
        'description', 
        'transaction_date'
    ];

    protected $useTimestamps = false;

    public function getDatatables(array $postData, int $userId)
    {
        $builder = $this->builder();
        $builder->where('user_id', $userId);

        $this->_applyDatatablesFilters($builder, $postData);

        $columns = ['transaction_date', 'type', 'transaction_date', 'transaction_date', 'description', 'amount'];
        if (isset($postData['order'])) {
            $orderColIdx = $postData['order'][0]['column'];
            $orderDir = $postData['order'][0]['dir'];
            $orderColName = $columns[$orderColIdx] ?? 'transaction_date';
            $builder->orderBy($orderColName, $orderDir);
        } else {
            $builder->orderBy('transaction_date', 'DESC');
            $builder->orderBy('id', 'DESC');
        }

        $start = (int) ($postData['start'] ?? 0);
        $length = (int) ($postData['length'] ?? 10);
        if ($length != -1) {
            $builder->limit($length, $start);
        }

        return $builder->get()->getResultArray();
    }

    public function countFiltered(array $postData, int $userId)
    {
        $builder = $this->builder();
        $builder->where('user_id', $userId);
        $this->_applyDatatablesFilters($builder, $postData);
        return $builder->countAllResults();
    }

    public function countAll(int $userId)
    {
        return $this->builder()->where('user_id', $userId)->countAllResults();
    }

    public function getSummaryTotals(array $postData, int $userId)
    {
        $builder = $this->builder();
        $builder->select("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income, COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense");
        $builder->where('user_id', $userId);
        $this->_applyDatatablesFilters($builder, $postData);
        $result = $builder->get()->getRowArray();
        return [
            'total_income'  => (float) ($result['total_income'] ?? 0),
            'total_expense' => (float) ($result['total_expense'] ?? 0),
        ];
    }

    private function _applyDatatablesFilters($builder, array $postData)
    {
        if (!empty($postData['search']['value'])) {
            $searchValue = $postData['search']['value'];
            $builder->groupStart();
            $builder->like('description', $searchValue);
            $builder->orLike('type', $searchValue);
            $builder->groupEnd();
        }

        if (!empty($postData['filter_type'])) {
            $builder->where('type', $postData['filter_type']);
        }
        if (!empty($postData['filter_start_date'])) {
            $builder->where('transaction_date >=', $postData['filter_start_date']);
        }
        if (!empty($postData['filter_end_date'])) {
            $builder->where('transaction_date <=', $postData['filter_end_date']);
        }
        if (!empty($postData['filter_wallet_id'])) {
            $builder->groupStart()
                  ->where('wallet_id', $postData['filter_wallet_id'])
                  ->orWhere('to_wallet_id', $postData['filter_wallet_id'])
                  ->groupEnd();
        }
        if (!empty($postData['filter_search'])) {
            $builder->like('description', $postData['filter_search']);
        }
    }
}
