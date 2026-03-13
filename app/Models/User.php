<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    public function hasVerifiedEmail(): bool
    {
        return !app()->isProduction() || $this->email_verified_at !== null;
    }

    /**
     * The attributes that are mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    public static string $deletedUserName = 'Gelöschter Benutzer';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'status' => UserStatus::class,
        ];
    }

    public function roles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'user_id', 'id');
    }

    /**
     * Return role payloads that work with both legacy and current schemas.
     *
     * @return array<int, array{id:int,user_id:int,role:string}>
     */
    public function rolePayload(): array
    {
        if (Schema::hasTable('user_role')) {
            if ($this->relationLoaded('roles')) {
                return $this->roles->values()->toArray();
            }

            return $this->roles()->get()->toArray();
        }

        if (Schema::hasColumn($this->getTable(), 'role') && $this->getAttribute('role')) {
            return [[
                'id' => 0,
                'user_id' => $this->id,
                'role' => $this->getAttribute('role'),
            ]];
        }

        return [];
    }

    /**
     * @return list<string>
     */
    public function roleValues(): array
    {
        return array_values(array_unique(array_map(
            fn(array $role) => $role['role'],
            $this->rolePayload(),
        )));
    }

    /**
     * Check if a user has at least one of the given roles.
     */
    public function hasRole(Role $role): bool
    {
        return $this->hasRoles([$role]);
    }

    /**
     * @param Role|Role[] $roles
     * @param bool $requireAll If true, the user must have ALL roles
     */
    public function hasRoles(Role|array $roles, bool $requireAll = false): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        $roleValues = array_map(
            fn(Role $role) => $role->value,
            $roles
        );

        $assignedRoles = $this->roleValues();

        if ($requireAll) {
            return count(array_diff($roleValues, $assignedRoles)) === 0;
        }

        return array_intersect($roleValues, $assignedRoles) !== [];
    }

    /**
     * Assign one or more roles to the user.
     *
     * @param Role|Role[] $roles
     */
    public function assignRole(Role|array $roles): void
    {
        $roles = is_array($roles) ? $roles : [$roles];

        if (! Schema::hasTable('user_role')) {
            if (Schema::hasColumn($this->getTable(), 'role') && $roles !== []) {
                $this->forceFill([
                    'role' => $roles[0]->value,
                ])->save();
            }

            return;
        }

        foreach ($roles as $role) {
            UserRole::firstOrCreate([
                'user_id' => $this->id,
                'role' => $role->value,
            ]);
        }
    }

    /**
     * Remove one or more roles from the user.
     *
     * @param Role|Role[] $roles
     */
    public function removeRole(Role|array $roles): void
    {
        $roles = is_array($roles) ? $roles : [$roles];

        if (! Schema::hasTable('user_role')) {
            if (
                Schema::hasColumn($this->getTable(), 'role') &&
                in_array($this->getAttribute('role'), array_map(fn($r) => $r->value, $roles), true)
            ) {
                $this->forceFill([
                    'role' => Role::USER->value,
                ])->save();
            }

            return;
        }

        $this->roles()
            ->whereIn('role', array_map(fn($r) => $r->value, $roles))
            ->delete();
    }

    public function hasStatus(UserStatus $status): bool
    {
        return $this->status === $status;
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ForumPost::class);
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }
}
