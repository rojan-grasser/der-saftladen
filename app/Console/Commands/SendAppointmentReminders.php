<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\PushSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';

    protected $description = 'Send push notifications for upcoming appointment reminders';

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

                $this->sendPushNotifications($appointment, $offsetMinutes);

                DB::table('appointment_reminder_sent')->insert([
                    'appointment_id' => $appointment->id,
                    'offset_minutes' => $offsetMinutes,
                    'sent_at' => now(),
                ]);
            }
        }
    }

    private function sendPushNotifications(Appointment $appointment, int $offsetMinutes): void
    {
        $subscriptions = PushSubscription::all();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $vapidPublicKey = config('webpush.vapid.public_key');
        $vapidPrivateKey = config('webpush.vapid.private_key');
        $vapidSubject = config('webpush.vapid.subject', 'mailto:admin@example.com');

        if (! $vapidPublicKey || ! $vapidPrivateKey) {
            Log::warning('VAPID keys not configured. Skipping push notifications.');
            return;
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => $vapidSubject,
                'publicKey' => $vapidPublicKey,
                'privateKey' => $vapidPrivateKey,
            ],
        ]);

        $label = $this->formatOffset($offsetMinutes);
        $payload = json_encode([
            'title' => 'Terminerinnerung',
            'body' => "\"{$appointment->title}\" beginnt in {$label}",
            'appointmentId' => $appointment->id,
        ]);

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub->endpoint,
                'keys' => [
                    'p256dh' => $sub->public_key,
                    'auth' => $sub->auth_token,
                ],
            ]);

            $webPush->queueNotification($subscription, $payload);
        }

        foreach ($webPush->flush() as $report) {
            if (! $report->isSuccess()) {
                // Remove expired/invalid subscriptions
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint', $report->getEndpoint())->delete();
                }
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
