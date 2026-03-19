<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TestUserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::query()->firstOrNew([
            'email' => 'test@test',
        ]);

        $attributes = [
            'name' => 'Louis',
            'email' => 'test@test',
            'email_verified_at' => now(),
            'password' => 'Passwort1234',
            'remember_token' => $user->remember_token ?: Str::random(10),
            'status' => UserStatus::ACTIVE,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];

        if (Schema::hasColumn('users', 'role')) {
            $attributes['role'] = Role::ADMIN->value;
        }

        $user->forceFill($attributes)->save();
    }
}
