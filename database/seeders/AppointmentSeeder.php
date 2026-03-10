<?php

namespace Database\Seeders;

use App\Enums\AppointmentColor;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AppointmentSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $owner = User::query()->where('email', 'test@test')->first();

        if (! $owner) {
            return;
        }

        $today = Carbon::today();

        $appointments = [
            ['title' => 'Wochenstart und Prioritaeten', 'location' => 'Buero', 'description' => 'Offene Aufgaben sortieren und Prioritaeten fuer die Woche festlegen.', 'day_offset' => 0, 'hour' => 9, 'minute' => 0, 'duration' => 60, 'color' => AppointmentColor::PEACOCK],
            ['title' => 'Lieferantenabstimmung', 'location' => 'Telefonat', 'description' => 'Verfuegbarkeit und Lieferfenster fuer kommende Bestellungen abstimmen.', 'day_offset' => 3, 'hour' => 11, 'minute' => 30, 'duration' => 45, 'color' => AppointmentColor::SAGE],
            ['title' => 'Team-Retrospektive', 'location' => 'Meetingraum', 'description' => 'Rueckblick auf die laufenden Prozesse und offene Verbesserungen.', 'day_offset' => 6, 'hour' => 15, 'minute' => 0, 'duration' => 75, 'color' => AppointmentColor::GRAPE],
            ['title' => 'Social-Media-Planung', 'location' => 'Marketing Ecke', 'description' => 'Themen fuer Aktionen, Stories und Wochenangebote planen.', 'day_offset' => 10, 'hour' => 10, 'minute' => 0, 'duration' => 60, 'color' => AppointmentColor::FLAMINGO],
            ['title' => 'Saisonale Sortimentsrunde', 'location' => 'Showroom', 'description' => 'Neue saisonale Produkte pruefen und Sortiment anpassen.', 'day_offset' => 14, 'hour' => 13, 'minute' => 15, 'duration' => 90, 'color' => AppointmentColor::BANANA],
            ['title' => 'Kassen- und Lagercheck', 'location' => 'Filiale', 'description' => 'Bestand, Kasse und Rueckstellungen fuer den Monat abstimmen.', 'day_offset' => 18, 'hour' => 8, 'minute' => 30, 'duration' => 60, 'color' => AppointmentColor::TANGERINE],
            ['title' => 'Workshop Kundenservice', 'location' => 'Seminarraum', 'description' => 'Praxisbeispiele fuer serviceorientierte Kommunikation durchgehen.', 'day_offset' => 23, 'hour' => 14, 'minute' => 0, 'duration' => 120, 'color' => AppointmentColor::LAVENDER],
            ['title' => 'Monatsabschluss Vorbereitung', 'location' => 'Backoffice', 'description' => 'Belege, Ausgaben und Umsatzdaten fuer den Abschluss sortieren.', 'day_offset' => 29, 'hour' => 16, 'minute' => 0, 'duration' => 90, 'color' => AppointmentColor::GRAPHITE],
            ['title' => 'Produktshooting', 'location' => 'Studio', 'description' => 'Neue Produkte fuer Website und Kampagnen fotografieren.', 'day_offset' => 35, 'hour' => 9, 'minute' => 30, 'duration' => 150, 'color' => AppointmentColor::BLUEBERRY],
            ['title' => 'Rezeptentwicklung', 'location' => 'Testkueche', 'description' => 'Neue Saftmischungen pruefen und dokumentieren.', 'day_offset' => 42, 'hour' => 12, 'minute' => 0, 'duration' => 90, 'color' => AppointmentColor::BASIL],
            ['title' => 'Verkaufsflaechen-Check', 'location' => 'Verkaufsraum', 'description' => 'Flaechenwirkung, Beschilderung und Produktplatzierung evaluieren.', 'day_offset' => 48, 'hour' => 10, 'minute' => 15, 'duration' => 60, 'color' => AppointmentColor::TOMATO],
            ['title' => 'Sommeraktion Planung', 'location' => 'Meetingraum Nord', 'description' => 'Aktionen fuer die naechste Saison gemeinsam finalisieren.', 'day_offset' => 56, 'hour' => 13, 'minute' => 0, 'duration' => 90, 'color' => AppointmentColor::PEACOCK],
            ['title' => 'Schichtplanung Review', 'location' => 'Backoffice', 'description' => 'Einsatzplaene pruefen und Engpaesse fuer die naechsten Wochen auffangen.', 'day_offset' => 64, 'hour' => 11, 'minute' => 0, 'duration' => 60, 'color' => AppointmentColor::SAGE],
            ['title' => 'Partnergespraech Catering', 'location' => 'Videocall', 'description' => 'Kooperationen fuer Veranstaltungen und Cateringtermine abstimmen.', 'day_offset' => 73, 'hour' => 15, 'minute' => 30, 'duration' => 75, 'color' => AppointmentColor::GRAPE],
            ['title' => 'Quartalsausblick', 'location' => 'Konferenzraum', 'description' => 'Entwicklung der naechsten drei Monate mit Fokus auf Umsatz und Aktionen planen.', 'day_offset' => 84, 'hour' => 9, 'minute' => 0, 'duration' => 120, 'color' => AppointmentColor::FLAMINGO],
        ];

        foreach ($appointments as $plan) {
            $start = $today->copy()->addDays($plan['day_offset'])->setTime($plan['hour'], $plan['minute']);

            Appointment::updateOrCreate(
                [
                    'user_id' => $owner->id,
                    'title' => $plan['title'],
                ],
                [
                    'description' => $plan['description'],
                    'location' => $plan['location'],
                    'color' => $plan['color']->value,
                    'start_time' => $start,
                    'end_time' => $start->copy()->addMinutes($plan['duration']),
                ],
            );
        }
    }
}
