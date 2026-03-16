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
            // Vergangene Termine
            ['title' => 'Jahresplanung 2025', 'location' => 'Konferenzraum', 'description' => 'Ziele und Meilensteine fuer das Jahr 2025 festlegen.', 'day_offset' => -84, 'hour' => 9, 'minute' => 0, 'duration' => 120, 'color' => AppointmentColor::BLUEBERRY],
            ['title' => 'Lieferantenbesuch Bioland', 'location' => 'Lager', 'description' => 'Neue Produkte und Konditionen fuer die Frischesaison besprechen.', 'day_offset' => -60, 'hour' => 10, 'minute' => 0, 'duration' => 90, 'color' => AppointmentColor::BASIL],
            ['title' => 'Inventur Februar', 'location' => 'Filiale', 'description' => 'Vollstaendige Bestandsaufnahme aller Lager- und Verkaufsware.', 'day_offset' => -45, 'hour' => 7, 'minute' => 0, 'duration' => 180, 'color' => AppointmentColor::GRAPHITE],
            ['title' => 'Teammeeting Q1', 'location' => 'Meetingraum', 'description' => 'Quartalsrueckblick und Ausblick auf Q2.', 'day_offset' => -30, 'hour' => 14, 'minute' => 0, 'duration' => 60, 'color' => AppointmentColor::PEACOCK],
            ['title' => 'Schulung Kassensystem', 'location' => 'Buero', 'description' => 'Neue Funktionen des Kassensystems kennenlernen.', 'day_offset' => -20, 'hour' => 11, 'minute' => 0, 'duration' => 90, 'color' => AppointmentColor::TANGERINE],
            ['title' => 'Kundenevent Fruehlingsmarkt', 'location' => 'Verkaufsraum', 'description' => 'Verkostung und Praesentationen neuer Fruehlingsprodukte.', 'day_offset' => -14, 'hour' => 10, 'minute' => 0, 'duration' => 240, 'color' => AppointmentColor::FLAMINGO],
            ['title' => 'Social-Media-Rueckblick', 'location' => 'Marketing Ecke', 'description' => 'Performance der letzten Kampagnen auswerten.', 'day_offset' => -7, 'hour' => 13, 'minute' => 0, 'duration' => 60, 'color' => AppointmentColor::SAGE],
            ['title' => 'Wochenabschluss', 'location' => 'Buero', 'description' => 'Offene Punkte der Woche abschliessen und naechste Woche vorbereiten.', 'day_offset' => -2, 'hour' => 16, 'minute' => 0, 'duration' => 45, 'color' => AppointmentColor::LAVENDER],

            // Aktuelle Woche
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

        // Mehrtaegige Termine (eigene end_time)
        $multiDayAppointments = [
            ['title' => 'Branchenmesse BioNord', 'location' => 'Messezentrum Hamburg', 'description' => 'Zwei Tage auf der Fachmesse fuer Bioprodukte und Naturkost.', 'day_offset' => -50, 'hour' => 9, 'minute' => 0, 'end_offset' => -49, 'end_hour' => 18, 'color' => AppointmentColor::BLUEBERRY],
            ['title' => 'Betriebsausflug Team', 'location' => 'Schwarzwald', 'description' => 'Dreitaegiger Teamausflug zur Staerkung des Zusammenhalts.', 'day_offset' => -21, 'hour' => 8, 'minute' => 0, 'end_offset' => -19, 'end_hour' => 17, 'color' => AppointmentColor::FLAMINGO],
            ['title' => 'Umbau Verkaufsflaeche', 'location' => 'Filiale', 'description' => 'Neugestaltung der Verkaufsflaeche ueber zwei Tage.', 'day_offset' => 5, 'hour' => 7, 'minute' => 0, 'end_offset' => 6, 'end_hour' => 20, 'color' => AppointmentColor::TANGERINE],
            ['title' => 'Foodfestival Teilnahme', 'location' => 'Stadtpark', 'description' => 'Drei Tage als Aussteller beim regionalen Foodfestival.', 'day_offset' => 20, 'hour' => 10, 'minute' => 0, 'end_offset' => 22, 'end_hour' => 21, 'color' => AppointmentColor::TOMATO],
            ['title' => 'Schulung neue Mitarbeiter', 'location' => 'Seminarraum', 'description' => 'Zweitaegige Einarbeitung und Schulung neuer Teammitglieder.', 'day_offset' => 40, 'hour' => 9, 'minute' => 0, 'end_offset' => 41, 'end_hour' => 17, 'color' => AppointmentColor::LAVENDER],
            ['title' => 'Strategieworkshop Herbst', 'location' => 'Tagungshotel', 'description' => 'Dreitaegiger Workshop zur Herbst- und Winterstrategie.', 'day_offset' => 70, 'hour' => 9, 'minute' => 0, 'end_offset' => 72, 'end_hour' => 16, 'color' => AppointmentColor::GRAPE],
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

        foreach ($multiDayAppointments as $plan) {
            $start = $today->copy()->addDays($plan['day_offset'])->setTime($plan['hour'], $plan['minute']);
            $end = $today->copy()->addDays($plan['end_offset'])->setTime($plan['end_hour'], 0);

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
                    'end_time' => $end,
                ],
            );
        }
    }
}
