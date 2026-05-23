<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table            = 'audit_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'action',
        'details',
        'ip_address'
    ];

    protected $useTimestamps = false; // handled by MySQL CURRENT_TIMESTAMP automatically

    /**
     * Log a secure administrative/security action.
     */
    public static function log(string $action, string $details, ?int $userId = null)
    {
        $userId = $userId ?? (auth()->loggedIn() ? auth()->id() : null);
        $request = service('request');
        $ip = $request->getIPAddress();

        $model = new self();
        $model->insert([
            'user_id'    => $userId,
            'action'     => $action,
            'details'    => $details,
            'ip_address' => $ip,
        ]);
    }

    /**
     * DataTables Server-Side Processing
     */
    public function getDatatables(array $postData)
    {
        $builder = $this->builder();
        $builder->select('audit_logs.*, users.username');
        $builder->join('users', 'users.id = audit_logs.user_id', 'left');

        // Columns for sorting and searching
        $columns = ['audit_logs.created_at', 'users.username', 'audit_logs.action', 'audit_logs.details', 'audit_logs.ip_address'];

        // Searching
        if (!empty($postData['search']['value'])) {
            $searchValue = $postData['search']['value'];
            $builder->groupStart();
            foreach ($columns as $i => $item) {
                if ($i === 0) {
                    $builder->like($item, $searchValue);
                } else {
                    $builder->orLike($item, $searchValue);
                }
            }
            $builder->groupEnd();
        }

        // Sorting
        if (isset($postData['order'])) {
            $orderColIdx = $postData['order'][0]['column'];
            $orderDir = $postData['order'][0]['dir'];
            // Handle specific column indices mapped in the view
            $orderColName = $columns[$orderColIdx] ?? 'audit_logs.created_at';
            $builder->orderBy($orderColName, $orderDir);
        } else {
            $builder->orderBy('audit_logs.created_at', 'DESC');
        }

        // Pagination
        $start = $postData['start'] ?? 0;
        $length = $postData['length'] ?? 10;

        if ($length != -1) {
            $builder->limit($length, $start);
        }

        return $builder->get()->getResultArray();
    }

    public function countFiltered(array $postData)
    {
        $builder = $this->builder();
        $builder->join('users', 'users.id = audit_logs.user_id', 'left');

        $columns = ['audit_logs.created_at', 'users.username', 'audit_logs.action', 'audit_logs.details', 'audit_logs.ip_address'];

        if (!empty($postData['search']['value'])) {
            $searchValue = $postData['search']['value'];
            $builder->groupStart();
            foreach ($columns as $i => $item) {
                if ($i === 0) {
                    $builder->like($item, $searchValue);
                } else {
                    $builder->orLike($item, $searchValue);
                }
            }
            $builder->groupEnd();
        }

        return $builder->countAllResults();
    }

    public function countAllData()
    {
        return $this->builder()->countAllResults();
    }
}
