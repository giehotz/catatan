<?php

namespace App\Models;

use CodeIgniter\Model;

class KopInvitationModel extends Model
{
    protected $table            = 'kop_invitations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'code',
        'email',
        'target_user_id',
        'status',
        'created_by',
        'used_by',
        'used_at'
    ];

    protected $useTimestamps = false;
}
