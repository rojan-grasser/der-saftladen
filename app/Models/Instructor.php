<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Instructor extends User
{
    protected $table = 'users';

    protected static function booted()
    {
        static::addGlobalScope('instructor', function (Builder $query) {
            $query->where('role', 'instructor');
        });
    }

    public function professionalAreas()
    {
        return $this->belongsToMany(
            ProfessionalArea::class,
            'user_to_professional_area',
            'user_id',
            'professional_area_id'
        )->withTimestamps();
    }
}
