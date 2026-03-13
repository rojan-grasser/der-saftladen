<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Instructor extends User
{
    protected $table = 'users';

    protected static function booted(): void
    {
        static::addGlobalScope('instructor', function (Builder $query) {
            $query->whereHas('roles', function ($q) {
                $q->where('role', 'instructor');
            });
        });
    }

    public function professions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Profession::class,
            'user_to_profession',
            'user_id',
            'profession_id'
        )->withTimestamps();
    }

    public function hasAccess(string $professionId)
    {
        return $this->professions()
            ->where('profession_id', $professionId)
            ->exists();
    }
}
