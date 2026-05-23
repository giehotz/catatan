<?php

namespace App\Models;

use CodeIgniter\Model;

class UserMessageModel extends Model
{
    protected $table            = 'user_messages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'sender_id',
        'invitation_id',
        'subject',
        'message',
        'type',
        'is_read',
        'action_taken',
        'deleted_by_sender',
        'deleted_by_receiver'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
