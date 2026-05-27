<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Super Admin',
                'email'    => 'super.admin@demo.local',
                'password' => Hash::make('password'),
                'status'   => 'active',
                'is_demo'  => true,
                'role'     => 'Super Admin',
            ],
            [
                'name'     => 'City Admin',
                'email'    => 'city.admin@demo.local',
                'password' => Hash::make('password'),
                'status'   => 'active',
                'is_demo'  => true,
                'role'     => 'City Admin',
            ],
            [
                'name'     => 'HR Officer',
                'email'    => 'hr.officer@demo.local',
                'password' => Hash::make('password'),
                'status'   => 'active',
                'is_demo'  => true,
                'role'     => 'HR Officer',
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                $data,
            );

            $user->assignRole($role);
        }

        $this->command->info('Users seeded successfully.');
    }
}
