<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'company' => ['nullable', 'string', 'max:255'],
            'password' => $this->passwordRules(),
            'roles' => ['required', 'array'],
            'roles.*' => ['required', new Enum(Role::class)],
        ])->validate();

        $user = User::create([
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'company' => $input['company'] ?? null,
            'password' => $input['password'],
            'status' => UserStatus::PENDING
        ]);

        $user->assignRole(array_map(fn($role) => Role::from($role), $input['roles']));

        return $user;
    }
}
