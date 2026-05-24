<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSourceToKopAngsuranSubmissions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('kop_angsuran_submissions', [
            'source' => [
                'type'       => 'ENUM',
                'constraint' => ['user', 'admin'],
                'default'    => 'user',
                'after'      => 'bukti_bayar'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('kop_angsuran_submissions', 'source');
    }
}
