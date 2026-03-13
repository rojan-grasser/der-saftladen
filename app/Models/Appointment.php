<?php

namespace App\Models;

use App\Enums\AppointmentColor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'location',
        'color',
        'start_time',
        'end_time',
        'user_id',
        'reminders',
    ];

    protected function casts(): array
    {
        return [
            'color' => AppointmentColor::class,
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'reminders' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
