<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKopAngsuranSubmissionsTable extends Migration
{
    public function up()
    {
        // 1. Create kop_angsuran_submissions table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'pinjaman_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nominal_pengajuan' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'bukti_bayar' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected'],
                'default'    => 'pending',
            ],
            'catatan_tolak' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('pinjaman_id', 'kop_pinjaman', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kop_angsuran_submissions');

        // 2. Modify kop_angsuran table
        $this->forge->addColumn('kop_angsuran', [
            'submission_id_fk' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'pinjaman_id'
            ],
        ]);

        $this->db->query("ALTER TABLE `kop_angsuran` MODIFY `bukti_bayar` VARCHAR(255) NULL");

        $this->forge->addForeignKey('submission_id_fk', 'kop_angsuran_submissions', 'id', 'SET NULL', 'CASCADE', 'kop_angsuran_submission_id_fk_foreign');
        $this->db->query("ALTER TABLE `kop_angsuran` ADD CONSTRAINT `kop_angsuran_submission_id_fk_foreign` FOREIGN KEY (`submission_id_fk`) REFERENCES `kop_angsuran_submissions`(`id`) ON DELETE SET NULL ON UPDATE CASCADE");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `kop_angsuran` DROP FOREIGN KEY `kop_angsuran_submission_id_fk_foreign`");
        $this->forge->dropColumn('kop_angsuran', 'submission_id_fk');
        $this->forge->dropTable('kop_angsuran_submissions');
    }
}
