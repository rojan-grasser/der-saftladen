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
    private function canCreateAppointment(User $user): bool
    {
        return $user->hasRole(Role::ADMIN) || $user->hasRole(Role::TEACHER);
    }

    private function canUpdateAppointment(User $user, Appointment $appointment): bool
    {
        if ($user->hasRole(Role::ADMIN)) {
            return true;
        }

        return $user->hasRole(Role::TEACHER) && $appointment->user_id === $user->id;
    }

    private function canDeleteAppointment(User $user): bool
    {
        return $user->hasRole(Role::ADMIN);
    }

    private function abortIfCannotCreate(?User $user): void
    {
        if (! $user || ! $this->canCreateAppointment($user)) {
            abort(403, 'Sie dürfen keine Termine erstellen.');
        }
    }

    private function abortIfCannotUpdate(?User $user, Appointment $appointment): void
    {
        if (! $user || ! $this->canUpdateAppointment($user, $appointment)) {
            abort(403, 'Sie dürfen diesen Termin nicht bearbeiten.');
        }
    }

    private function abortIfCannotDelete(?User $user): void
    {
        if (! $user || ! $this->canDeleteAppointment($user)) {
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

        if (! $start) {
            $errors['start_time'] = 'Ungültiges Startdatum.';
        }

        if (! $end) {
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

        $appointments = Appointment::with('creator:id,name')
            ->get()
            ->map(function (Appointment $appointment) {
                return [
                    ...$appointment->toArray(),
                    'color' => $appointment->color?->value ?? AppointmentColor::PEACOCK->value,
                    'start_time' => $appointment->start_time?->timestamp,
                    'end_time' => $appointment->end_time?->timestamp,
                    'creator' => [
                        'id' => $appointment->creator?->id ?? 0,
                        'name' => $appointment->creator?->name ?? User::$deletedUserName,
                    ]
                ];
            });

        return Inertia::render('calender/CalenderDashboard', [
            'appointments' => $appointments,
            'permissions' => [
                'canCreate' => $user ? $this->canCreateAppointment($user) : false,
                'canEditOwn' => $user ? $user->hasRole(Role::TEACHER) : false,
                'canEditAll' => $user ? $user->hasRole(Role::ADMIN) : false,
                'canDeleteAll' => $user ? $this->canDeleteAppointment($user) : false,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->abortIfCannotCreate($request->user());

        $validated = $this->validateAppointment($request);

        Appointment::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Termin erfolgreich erstellt!');
    }

    public function update(Request $request, Appointment $appointment)
    {
        $this->abortIfCannotUpdate($request->user(), $appointment);

        $validated = $this->validateAppointment($request);

        $appointment->update($validated);

        // Reset sent reminders if start_time or reminders changed
        if ($appointment->wasChanged(['start_time', 'reminders'])) {
            \DB::table('appointment_reminder_sent')
                ->where('appointment_id', $appointment->id)
                ->delete();
        }

        return back()->with('success', 'Termin erfolgreich aktualisiert!');
    }

    public function destroy(Appointment $appointment)
    {
        $this->abortIfCannotDelete(request()->user());

        $appointment->delete();

        return back()->with('success', 'Termin erfolgreich gelöscht!');
    }
}
