<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $users = auth()->getProvider();

        // Create or find admin user
        $user = $users->findByCredentials(['email' => 'aanrozanahs1984@gmail.com']);

        if (!$user) {
            $user = new \CodeIgniter\Shield\Entities\User([
                'username' => 'aanrozanah',
                'email'    => 'aanrozanahs1984@gmail.com',
                'password' => 'aanrozanah1984',
            ]);
            $users->save($user);
            $user = $users->findByCredentials(['email' => 'aanrozanahs1984@gmail.com']);
            echo "  - User 'aanrozanah' created.\n";
        } else {
            echo "  - User 'aanrozanah' already exists.\n";
        }

        // Activate
        $user->activate();
        $users->save($user);

        // Assign admin groups
        $user->addGroup('superadmin');
        $user->addGroup('admin');
        $user->addGroup('manager');
        echo "  - Groups: superadmin, admin, manager assigned.\n";

        echo "  - Email: aanrozanahs1984@gmail.com\n";
        echo "  - Password: aanrozanah1984\n";
    }
}
