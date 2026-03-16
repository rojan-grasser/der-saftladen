<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';

    protected $description = 'Send notifications for upcoming appointment reminders';

    public function handle(): void
    {
        $now = Carbon::now();
        $windowStart = $now->copy()->subSeconds(30);
        $windowEnd = $now->copy()->addSeconds(30);

        $appointments = Appointment::whereNotNull('reminders')
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

                $label = $this->formatOffset($offsetMinutes);
                Log::info("Terminerinnerung: \"{$appointment->title}\" beginnt in {$label}", [
                    'appointment_id' => $appointment->id,
                    'offset_minutes' => $offsetMinutes,
                ]);

                DB::table('appointment_reminder_sent')->insert([
                    'appointment_id' => $appointment->id,
                    'offset_minutes' => $offsetMinutes,
                    'sent_at' => now(),
                ]);
            }
        }
    }

    private function formatOffset(int $minutes): string
    {
        if ($minutes < 60) {
            return "{$minutes} Minute" . ($minutes === 1 ? '' : 'n');
        }

        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        if ($remaining === 0) {
            return "{$hours} Stunde" . ($hours === 1 ? '' : 'n');
        }

        return "{$hours} Std. {$remaining} Min.";
    }
}
