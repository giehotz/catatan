<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigratePendingAngsuranToSubmissions extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Find all pending records in kop_angsuran
        $pendingRecords = $db->table('kop_angsuran')
                             ->where('status', 'pending')
                             ->get()
                             ->getResultArray();

        foreach ($pendingRecords as $record) {
            // Insert into kop_angsuran_submissions
            $db->table('kop_angsuran_submissions')->insert([
                'pinjaman_id'       => $record['pinjaman_id'],
                'nominal_pengajuan' => $record['nominal_bayar'],
                'bukti_bayar'       => $record['bukti_bayar'],
                'status'            => 'pending',
                'created_at'        => $record['tanggal_bayar'],
                'updated_at'        => date('Y-m-d H:i:s')
            ]);
        }

        // Delete migrated pending records from kop_angsuran
        $db->table('kop_angsuran')->where('status', 'pending')->delete();
    }

    public function down()
    {
        // Reverting this perfectly is hard without keeping the old IDs, but we can do a best effort.
        // For development purposes, if we roll back, we just delete pending submissions.
        // Since we dropped the old pending rows, they are gone.
        $db = \Config\Database::connect();
        $db->table('kop_angsuran_submissions')->where('status', 'pending')->delete();
    }
}
