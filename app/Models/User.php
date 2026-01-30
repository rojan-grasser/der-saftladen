<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

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
        return $this->hasMany(UserRole::class);
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

        $query = $this->roles()
            ->whereIn('role', $roleValues);

        if ($requireAll) {
            return $query->count() === count($roleValues);
        }

        return $query->exists();
    }

    /**
     * Assign one or more roles to the user.
     *
     * @param Role|Role[] $roles
     */
    public function assignRole(Role|array $roles): void
    {
        $roles = is_array($roles) ? $roles : [$roles];

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
}
