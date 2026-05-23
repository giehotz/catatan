<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserMessagesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'sender_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true, // Null for system messages
            ],
            'invitation_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['invitation', 'billing', 'system'],
                'default'    => 'system',
            ],
            'is_read' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'action_taken' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'accepted', 'rejected'],
                'null'       => true,
            ],
            'deleted_by_sender' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'deleted_by_receiver' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
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
        $this->forge->addKey('user_id');
        $this->forge->addKey('sender_id');
        $this->forge->addKey('invitation_id');
        
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        // If sender_id refers to users
        $this->forge->addForeignKey('sender_id', 'users', 'id', 'SET NULL', 'CASCADE');
        // If invitation_id refers to kop_invitations
        $this->forge->addForeignKey('invitation_id', 'kop_invitations', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('user_messages');
    }

    public function down()
    {
        $this->forge->dropTable('user_messages');
    }
}
