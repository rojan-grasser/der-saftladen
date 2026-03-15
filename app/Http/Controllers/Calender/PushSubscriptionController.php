<?php

namespace App\Http\Controllers\Calender;

use App\Http\Controllers\Controller;
use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PushSubscriptionController extends Controller
{
    public function testEmail()
    {
        $user = Auth::user();

        // Nächsten Termin des Users als Beispiel verwenden
        $appointment = Appointment::with('creator')
            ->where('user_id', $user->id)
            ->where('start_time', '>', now())
            ->orderBy('start_time')
            ->first();

        // Fallback: Dummy-Termin für die Test-Mail
        if (! $appointment) {
            $appointment = new Appointment([
                'title' => 'Beispiel-Termin',
                'description' => 'Das ist eine Test-Benachrichtigung.',
                'location' => 'Besprechungsraum 1',
                'start_time' => now()->addHours(1),
                'end_time' => now()->addHours(2),
            ]);
        }

        Mail::to($user->email)->send(new AppointmentReminderMail($appointment, 15));

        return response()->json(['status' => 'ok', 'sent_to' => $user->email]);
    }
}
