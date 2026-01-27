<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessionalArea extends Model
{
    /** @use HasFactory<\Database\Factories\ProfessionalAreaFactory> */
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
            'user_to_professional_area',
            'professional_area_id',
            'user_id',
        )->select([
            'users.id',
            'users.name',
            'users.email',
        ])->withTimestamps();
    }
}
