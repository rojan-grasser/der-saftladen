<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {

        $user = User::firstOrCreate(
            ['email' => 'test@test.de'],
            [
                'first_name' => 'Louis',
                'email_verified_at' => now(),
                'password' => 'Passwort1234',
                'status' => UserStatus::ACTIVE,
            ],
        );
        $user->assignRole(Role::ADMIN);
    }
}
