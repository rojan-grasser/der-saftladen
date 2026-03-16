<?php

namespace App\Http\Controllers\Calender;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
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
        $appointments = Appointment::with('creator:id,first_name,last_name')
            ->get()
            ->map(function (Appointment $appointment) {
                return [
                    ...$appointment->toArray(),
                    'start_time' => $appointment->start_time?->timestamp,
                    'end_time' => $appointment->end_time?->timestamp,
                    'creator' => [
                        'id' => $appointment->creator?->id ?? 0,
                        'first_name' => $appointment->creator?->first_name ?? User::$deletedUserName,
                        'last_name' => $appointment->creator?->last_name ?? '',
                        'name' => $appointment->creator?->name ?? User::$deletedUserName,
                    ]
                ];
            });

        return Inertia::render('calender/CalenderDashboard', [
            'appointments' => $appointments,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->hasRole(Role::INSTRUCTOR)) {
            return back()->with('error', 'Sie dürfen keine termine erstellen');
        }

        $validated = $this->validateAppointment($request);

        Appointment::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Termin erfolgreich erstellt!');
    }

    public function update(Request $request, Appointment $appointment)
    {
        if ($request->user()->id !== $appointment->creator()->id) {
            return back()->with('error', 'Sie dürfen diesen termin nicht bearbeiten');
        }

        $validated = $this->validateAppointment($request);

        $appointment->update($validated);

        return back()->with('success', 'Termin erfolgreich aktualisiert!');
    }

    public function destroy(Request $request, Appointment $appointment)
    {
        if ($request->user()->id !== $appointment->creator()->id) {
            return back()->with('error', 'Sie dürfen diesen termin nicht löschen');
        }

        $appointment->delete();

        return back()->with('success', 'Termin erfolgreich gelöscht!');
    }
}
