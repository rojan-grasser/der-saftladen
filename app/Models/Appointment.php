<?php

namespace App\Models;

use App\Enums\AppointmentColor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    protected function casts(): array
    {
        return [
            'color' => AppointmentColor::class,
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(AppointmentReminder::class);
    }
}
