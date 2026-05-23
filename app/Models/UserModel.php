<?php

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;
use App\Entities\User;

class UserModel extends ShieldUserModel
{
    protected $returnType = User::class;

    protected function initialize(): void
    {
        parent::initialize();

        // Add avatar and theme_preference to the allowed fields list to allow updates in the database
        $this->allowedFields[] = 'avatar';
        $this->allowedFields[] = 'theme_preference';
    }
}
