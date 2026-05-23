<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * @property \CodeIgniter\Database\BaseConnection|\CodeIgniter\Database\MySQLi\Connection $db
 */
class CreateKopDocumentSnapshotsTable extends Migration
{
    public function up()
    {
        $this->db->disableForeignKeyChecks();

        // 1. Create the polymorphic document snapshots table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'document_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'document_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'kop_snapshot' => [
                'type' => 'TEXT',
            ],
            'signer_snapshot' => [
                'type' => 'TEXT',
            ],
            'format_snapshot' => [
                'type' => 'TEXT',
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        
        // Ensure atomic uniqueness per document entity (Polymorphic Composite Index)
        $this->forge->addUniqueKey(['document_type', 'document_id']);
        
        $this->forge->createTable('kop_document_snapshots', true);

        // 2. Perform Tiered Data Backfill for existing approved resignations
        if ($this->db->tableExists('kop_pengunduran_diri')) {
            $approvedResigns = $this->db->table('kop_pengunduran_diri')
                                        ->where('status', 'approved')
                                        ->get()
                                        ->getResultArray();

            if (!empty($approvedResigns)) {
                // Tier 1: Read current active settings from database if available
                $settings = [];
                if ($this->db->tableExists('kop_settings')) {
                    $settingsQuery = $this->db->table('kop_settings')->get()->getResultArray();
                    foreach ($settingsQuery as $row) {
                        $settings[$row['key']] = $row['value'];
                    }
                }

                // Tier 2 Fallback values if DB settings are empty or missing
                $kopSnapshot = [
                    'schema_version'   => 1,
                    'cooperative_name' => $settings['kop_nama_koperasi'] ?? 'Koperasi Simpan Pinjam Catatan Keuangan',
                    'legal_id'         => $settings['kop_badan_hukum'] ?? 'Badan Hukum No. 00892/KSP/BH/2025',
                    'work_region'      => $settings['kop_wilayah_kerja'] ?? 'Wilayah Kerja Nasional DKI Jakarta',
                    'address'          => $settings['kop_alamat'] ?? 'Jl. Jend. Sudirman Kav. 21, Jakarta Selatan',
                    'phone'            => $settings['kop_telepon'] ?? '(021) 8089-9800',
                    'email'            => $settings['kop_email'] ?? 'ksp@catatankeuangan.com',
                    'website'          => $settings['kop_website'] ?? 'www.catatankeuangan.com',
                    'logo_path'        => $settings['kop_logo_path'] ?? 'assets/images/logo-ksp-default.png',
                ];

                $signerSnapshot = [
                    'schema_version' => 1,
                    'signer_id'      => 'signer_fallback',
                    'name'           => $settings['kop_penanda_tangan_nama'] ?? 'Pengurus Otoritatif Koperasi',
                    'role'           => $settings['kop_penanda_tangan_jabatan'] ?? 'Dewan Pengurus KSP',
                    'letter_type'    => 'resign'
                ];

                $formatSnapshot = [
                    'schema_version' => 1,
                    'format_string'  => $settings['kop_format_nomor_surat'] ?? '{nomor_urut}/KOP-SKP/{kode}/{year}',
                    'letter_code'    => 'RE',
                    'unit_code'      => ''
                ];

                // Safely perform the backfill inserts
                foreach ($approvedResigns as $resign) {
                    $exists = $this->db->table('kop_document_snapshots')
                                       ->where('document_type', 'resign')
                                       ->where('document_id', $resign['id'])
                                       ->countAllResults();

                    if ($exists === 0) {
                        $this->db->table('kop_document_snapshots')->insert([
                            'document_type'   => 'resign',
                            'document_id'     => $resign['id'],
                            'kop_snapshot'    => json_encode($kopSnapshot),
                            'signer_snapshot' => json_encode($signerSnapshot),
                            'format_snapshot' => json_encode($formatSnapshot),
                            'created_at'      => $resign['processed_at'] ?? $resign['created_at'] ?? date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }
        }

        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();
        $this->forge->dropTable('kop_document_snapshots', true);
        $this->db->enableForeignKeyChecks();
    }
}
