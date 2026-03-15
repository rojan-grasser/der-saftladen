<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Appointment $appointment,
        public readonly int $offsetMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🗓️ Erinnerung: ' . $this->appointment->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.appointment-reminder',
        );
    }
}
