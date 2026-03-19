<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentReminder extends Model
{
    protected $fillable = [
        'appointment_id',
        'offset_minutes',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
