<?php

namespace App\Models;

use Database\Factories\ProfessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profession extends Model
{
    /** @use HasFactory<ProfessionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    protected $guarded = ['id'];

    public function instructors(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Instructor::class,
            'user_to_profession',
            'profession_id',
            'user_id',
        )->select([
            'users.id',
            'users.name',
            'users.email',
        ])->withTimestamps();
    }
}
