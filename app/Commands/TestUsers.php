<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestUsers extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:users';
    protected $description = 'Test active users controller output';

    public function run(array $params)
    {
        $userModel = auth()->getProvider();
        $users = $userModel->orderBy('last_active', 'DESC')->findAll();
        CLI::write('Total users found: ' . count($users));
        
        foreach ($users as $u) {
            CLI::write('ID: ' . $u->id . ', Username: ' . $u->username . ', Email: ' . $u->email . ', Last Active: ' . $u->last_active);
        }
    }
}
