<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCatatanTolakToKopAngsuran extends Migration
{
    public function up()
    {
        $this->forge->addColumn('kop_angsuran', [
            'catatan_tolak' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'status',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('kop_angsuran', 'catatan_tolak');
    }
}
