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

        $attributes = [
            'first_name' => 'Louis',
            'email' => 'test@test.de',
            'email_verified_at' => now(),
            'password' => 'Passwort1234',
            'status' => UserStatus::ACTIVE,
        ];
        $user = User::create($attributes);
        $user->assignRole(Role::ADMIN);
    }
}
