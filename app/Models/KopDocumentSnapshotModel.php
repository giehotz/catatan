<?php

namespace App\Models;

use CodeIgniter\Model;

class KopDocumentSnapshotModel extends Model
{
    protected $table            = 'kop_document_snapshots';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'document_type',
        'document_id',
        'kop_snapshot',
        'signer_snapshot',
        'format_snapshot',
        'created_at'
    ];

    protected $useTimestamps = false;

    // Define model event hooks
    protected $afterInsert  = ['invalidateCache'];
    protected $afterUpdate  = ['invalidateCache'];
    protected $beforeDelete = ['handleBeforeDelete'];
    protected $afterDelete  = ['handleAfterDelete'];

    /**
     * Invalidate the cache for the inserted/updated snapshot.
     *
     * @param array $data
     * @return array
     */
    protected function invalidateCache(array $data)
    {
        $docType = null;
        $docId = null;

        if (isset($data['data']['document_type']) && isset($data['data']['document_id'])) {
            $docType = $data['data']['document_type'];
            $docId = $data['data']['document_id'];
        } elseif (isset($data['id'])) {
            $dbRow = $this->db->table($this->table)->where($this->primaryKey, $data['id'])->get()->getRowArray();
            if ($dbRow) {
                $docType = $dbRow['document_type'];
                $docId = $dbRow['document_id'];
            }
        }

        if ($docType && $docId) {
            $cacheKey = "kop_snapshot_{$docType}_{$docId}";
            cache()->delete($cacheKey);
        }

        return $data;
    }

    /**
     * Handle actions before a snapshot is deleted.
     * Captures the document type and ID to flush cache and log security audit details.
     *
     * @param array $data
     * @return array
     */
    protected function handleBeforeDelete(array $data)
    {
        if (isset($data['id'])) {
            // Find all matching rows before they are purged
            $builder = $this->db->table($this->table);
            if (is_array($data['id'])) {
                $builder->whereIn($this->primaryKey, $data['id']);
            } else {
                $builder->where($this->primaryKey, $data['id']);
            }

            $rows = $builder->get()->getResultArray();

            foreach ($rows as $row) {
                $docType = $row['document_type'];
                $docId = $row['document_id'];

                // Flush cache key
                $cacheKey = "kop_snapshot_{$docType}_{$docId}";
                cache()->delete($cacheKey);

                // Security log & Audit trail
                $warningDetails = "PERINGATAN KEAMANAN: Data snapshot untuk dokumen tipe '{$docType}' ID {$docId} telah DIHAPUS dari database! Sistem otomatis mengaktifkan fallback.";
                log_message('warning', $warningDetails);
                
                try {
                    AuditLogModel::log('coop_snapshot_deleted', $warningDetails);
                } catch (\Throwable $e) {
                    log_message('error', "Gagal mencatat audit log snapshot deleted: " . $e->getMessage());
                }
            }
        }

        return $data;
    }

    /**
     * Handle actions after a snapshot is deleted (Sanity cleanup).
     *
     * @param array $data
     * @return array
     */
    protected function handleAfterDelete(array $data)
    {
        // Extra cleanup if needed
        return $data;
    }
}
