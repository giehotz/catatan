<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Controllers\Admin\ActiveUsers;

class TestJson extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:json';
    protected $description = 'Test active users json';

    public function run(array $params)
    {
        $c = new ActiveUsers();
        // Since getActiveUsersData is private, we will use reflection
        $ref = new \ReflectionMethod(ActiveUsers::class, 'getActiveUsersData');
        $ref->setAccessible(true);
        $data = $ref->invoke($c);
        
        $json = json_encode($data);
        if ($json === false) {
            CLI::error('JSON encode failed: ' . json_last_error_msg());
        } else {
            CLI::write($json);
        }
    }
}
