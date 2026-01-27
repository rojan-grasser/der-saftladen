<?php

namespace App\Http\Controllers\Calender;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    private function parseAppointmentTime(mixed $value): ?Carbon
    {
        if (is_numeric($value)) {
            $timestamp = (int) $value;
            if ($timestamp > 9999999999) {
                $timestamp = (int) floor($timestamp / 1000);
            }
            return Carbon::createFromTimestamp($timestamp);
        }

        if (is_string($value)) {
            try {
                return Carbon::parse($value);
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    private function validateAppointment(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $start = $this->parseAppointmentTime($validated['start_time']);
        $end = $this->parseAppointmentTime($validated['end_time']);

        $errors = [];
        if (!$start) {
            $errors['start_time'] = 'Ungültiges Startdatum.';
        }
        if (!$end) {
            $errors['end_time'] = 'Ungültiges Enddatum.';
        }
        if ($start && $end && $end->lessThanOrEqualTo($start)) {
            $errors['end_time'] = 'Ende muss nach dem Beginn liegen.';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $validated['start_time'] = $start;
        $validated['end_time'] = $end;

        return $validated;
    }

    public function index()
    {
        $appointments = Appointment::with('creator:id,name')
            ->get()
            ->map(function (Appointment $appointment) {
                return [
                    ...$appointment->toArray(),
                    'start_time' => $appointment->start_time?->timestamp,
                    'end_time' => $appointment->end_time?->timestamp,
                ];
            });

        return Inertia::render('calender/CalenderDashboard', [
            'appointments' => $appointments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateAppointment($request);

        Appointment::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $this->validateAppointment($request);

        $appointment->update($validated);
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
    }
}
