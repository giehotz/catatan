<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterKopInvitationsAddTargetUser extends Migration
{
    public function up()
    {
        $this->forge->addColumn('kop_invitations', [
            'target_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'email'
            ]
        ]);
        
        // Let's also modify status enum to include 'rejected'
        // Since sqlite doesn't easily alter enum, we'll just ignore enum restriction in code if SQLite, but for MySQL we can alter.
        // The project might be using MySQL (laragon). Let's do raw query for enum if it's MySQL.
        if ($this->db->DBDriver === 'MySQLi') {
            $this->db->query("ALTER TABLE kop_invitations MODIFY COLUMN status ENUM('unused', 'used', 'expired', 'rejected') NOT NULL DEFAULT 'unused'");
        }
    }

    public function down()
    {
        $this->forge->dropColumn('kop_invitations', 'target_user_id');
        if ($this->db->DBDriver === 'MySQLi') {
            $this->db->query("ALTER TABLE kop_invitations MODIFY COLUMN status ENUM('unused', 'used', 'expired') NOT NULL DEFAULT 'unused'");
        }
    }
}
