<?php

namespace App\Http\Controllers\Calender;

use App\Enums\AppointmentColor;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    // US-13: Nur Teacher und Admin dürfen Termine erstellen.
    private function canCreateAppointment(User $user): bool
    {
        return $user->hasRole(Role::TEACHER) || $user->hasRole(Role::ADMIN);
    }

    // US-15: Teacher darf nur eigene Termine bearbeiten. Admin darf alle.
    private function canUpdateAppointment(User $user, Appointment $appointment): bool
    {
        if ($user->hasRole(Role::ADMIN)) {
            return true;
        }

        return $user->hasRole(Role::TEACHER) && $appointment->user_id === $user->id;
    }

    // US-15: Teacher darf nur eigene Termine löschen. Admin darf alle.
    private function canDeleteAppointment(User $user, Appointment $appointment): bool
    {
        if ($user->hasRole(Role::ADMIN)) {
            return true;
        }

        return $user->hasRole(Role::TEACHER) && $appointment->user_id === $user->id;
    }

    private function abortIfCannotCreate(?User $user): void
    {
        if (!$user || !$this->canCreateAppointment($user)) {
            abort(403, 'Sie dürfen keine Termine erstellen.');
        }
    }

    private function abortIfCannotUpdate(?User $user, Appointment $appointment): void
    {
        if (!$user || !$this->canUpdateAppointment($user, $appointment)) {
            abort(403, 'Sie dürfen diesen Termin nicht bearbeiten.');
        }
    }

    private function abortIfCannotDelete(?User $user, Appointment $appointment): void
    {
        if (!$user || !$this->canDeleteAppointment($user, $appointment)) {
            abort(403, 'Sie dürfen diesen Termin nicht löschen.');
        }
    }

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
            'color' => ['nullable', new Enum(AppointmentColor::class)],
            'start_time' => 'required',
            'end_time' => 'required',
            'reminders' => 'nullable|array',
            'reminders.*' => 'integer|min:0|max:10080',
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

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $validated['color'] = $validated['color'] ?? AppointmentColor::PEACOCK->value;
        $validated['start_time'] = $start;
        $validated['end_time'] = $end;

        return $validated;
    }

    public function index()
    {
        $user = Auth::user();
        $appointments = Appointment::with(['creator:id,first_name,last_name', 'reminders'])
            ->get()
            ->map(function (Appointment $appointment) {
                return [
                    ...$appointment->toArray(),
                    'color' => $appointment->color?->value ?? AppointmentColor::PEACOCK->value,
                    'start_time' => $appointment->start_time?->timestamp,
                    'end_time' => $appointment->end_time?->timestamp,
                    'reminders' => $appointment->reminders->pluck('offset_minutes')->values()->toArray(),
                    'creator' => [
                        'id' => $appointment->creator?->id ?? 0,
                        'first_name' => $appointment->creator?->first_name ?? User::$deletedUserName,
                        'last_name' => $appointment->creator?->last_name ?? '',
                        'name' => $appointment->creator?->name ?? User::$deletedUserName,
                    ],
                ];
            });

        return Inertia::render('calender/CalenderDashboard', [
            'appointments' => $appointments,
            'permissions' => [
                'canCreate' => $user ? $this->canCreateAppointment($user) : false,
                'canEditOwn' => $user ? ($user->hasRole(Role::TEACHER) || $user->hasRole(Role::ADMIN)) : false,
                'canEditAll' => $user ? $user->hasRole(Role::ADMIN) : false,
                'canDeleteAll' => $user ? $user->hasRole(Role::ADMIN) : false,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->abortIfCannotCreate($request->user());

        $validated = $this->validateAppointment($request);

        $reminders = $validated['reminders'] ?? [];
        unset($validated['reminders']);

        $appointment = Appointment::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        $this->syncReminders($appointment, $reminders);

        return back()->with('success', 'Termin erfolgreich erstellt!');
    }

    public function update(Request $request, Appointment $appointment)
    {
        $this->abortIfCannotUpdate($request->user(), $appointment);

        $validated = $this->validateAppointment($request);

        $reminders = $validated['reminders'] ?? [];
        unset($validated['reminders']);

        $oldReminders = $appointment->reminders()->pluck('offset_minutes')->sort()->values()->toArray();

        $appointment->update($validated);

        $this->syncReminders($appointment, $reminders);

        $newReminders = collect($reminders)->sort()->values()->toArray();

        // Reset sent reminders if start_time or reminders changed
        if ($appointment->wasChanged('start_time') || $oldReminders !== $newReminders) {
            \DB::table('appointment_reminder_sent')
                ->where('appointment_id', $appointment->id)
                ->delete();
        }

        return back()->with('success', 'Termin erfolgreich aktualisiert!');
    }

    private function syncReminders(Appointment $appointment, array $reminders): void
    {
        $appointment->reminders()->delete();

        foreach ($reminders as $offsetMinutes) {
            $appointment->reminders()->create([
                'offset_minutes' => (int) $offsetMinutes,
            ]);
        }
    }

    public function destroy(Appointment $appointment)
    {
        $this->abortIfCannotDelete(request()->user(), $appointment);

        $appointment->delete();

        return back()->with('success', 'Termin erfolgreich gelöscht!');
    }
}
