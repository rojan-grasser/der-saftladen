<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';

    protected $description = 'Send email reminders for upcoming appointments';

    public function handle(): void
    {
        $now = Carbon::now();
        $windowStart = $now->copy()->subSeconds(30);
        $windowEnd = $now->copy()->addSeconds(30);

        $appointments = Appointment::with('creator')
            ->whereNotNull('reminders')
            ->where('start_time', '>', $now)
            ->get();

        foreach ($appointments as $appointment) {
            $reminders = $appointment->reminders ?? [];

            foreach ($reminders as $offsetMinutes) {
                $reminderTime = $appointment->start_time->copy()->subMinutes($offsetMinutes);

                if (! $reminderTime->between($windowStart, $windowEnd)) {
                    continue;
                }

                $alreadySent = DB::table('appointment_reminder_sent')
                    ->where('appointment_id', $appointment->id)
                    ->where('offset_minutes', $offsetMinutes)
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $this->sendReminderEmail($appointment, $offsetMinutes);

                DB::table('appointment_reminder_sent')->insert([
                    'appointment_id' => $appointment->id,
                    'offset_minutes' => $offsetMinutes,
                    'sent_at' => now(),
                ]);
            }
        }
    }

    private function sendReminderEmail(Appointment $appointment, int $offsetMinutes): void
    {
        if (! $appointment->creator || ! $appointment->creator->email) {
            return;
        }

        Mail::to($appointment->creator->email)
            ->send(new AppointmentReminderMail($appointment, $offsetMinutes));
    }
}
