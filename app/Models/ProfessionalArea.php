<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfessionalArea extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    protected $guarded = ['id'];
}
