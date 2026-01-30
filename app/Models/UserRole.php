<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserRoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    /** @use HasFactory<UserRoleFactory> */
    use HasFactory;

    protected $table = 'user_role';

    protected $fillable = [
        'user_id',
        'role',
    ];

    protected $casts = [
        'role' => Role::class,
    ];
}
