<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Terminerinnerung</title>
    <!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
    @include('mail.styles.appointment-reminder')
</head>
<body>
<div class="wrapper">
    <div class="card">

        <!-- Header -->
        <div class="header">
            <div class="header-icon">🗓️</div>
            <h1>Terminerinnerung</h1>
            <p>{{ config('app.name') }}</p>
        </div>

        <!-- Body -->
        <div class="body">

            <p class="label">Termin</p>
            <p class="appointment-title">{{ $appointment->title }}</p>

            <!-- Reminder Badge -->
            @php
                if ($offsetMinutes < 60) {
                    $label = $offsetMinutes . ' Minute' . ($offsetMinutes === 1 ? '' : 'n');
                } else {
                    $hours = intdiv($offsetMinutes, 60);
                    $remaining = $offsetMinutes % 60;
                    if ($remaining === 0) {
                        $label = $hours . ' Stunde' . ($hours === 1 ? '' : 'n');
                    } else {
                        $label = $hours . ' Std. ' . $remaining . ' Min.';
                    }
                }
            @endphp
            <div class="reminder-badge">
                ⏰ Beginnt in {{ $label }}
            </div>

            <!-- Details -->
            <div class="details">
                <div class="detail-row">
                    <span class="detail-icon">📅</span>
                    <div class="detail-content">
                        <div class="detail-key">Datum</div>
                        <div class="detail-value">{{ $appointment->start_time->locale('de')->isoFormat('dddd, D. MMMM YYYY') }}</div>
                    </div>
                </div>
                <div class="detail-row">
                    <span class="detail-icon">🕐</span>
                    <div class="detail-content">
                        <div class="detail-key">Uhrzeit</div>
                        <div class="detail-value">
                            {{ $appointment->start_time->format('H:i') }} – {{ $appointment->end_time->format('H:i') }} Uhr
                        </div>
                    </div>
                </div>
                @if($appointment->location)
                <div class="detail-row">
                    <span class="detail-icon">📍</span>
                    <div class="detail-content">
                        <div class="detail-key">Ort</div>
                        <div class="detail-value">{{ $appointment->location }}</div>
                    </div>
                </div>
                @endif
                @if($appointment->description)
                <div class="detail-row">
                    <span class="detail-icon">📝</span>
                    <div class="detail-content">
                        <div class="detail-key">Beschreibung</div>
                        <div class="detail-value">{{ $appointment->description }}</div>
                    </div>
                </div>
                @endif
            </div>

            <!-- CTA -->
            <div class="cta-wrapper">
                <a href="{{ url('/appointments') }}" class="cta-button">Kalender öffnen →</a>
            </div>

        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                Diese E-Mail wurde automatisch von <strong>{{ config('app.name') }}</strong> gesendet.<br>
                Du erhältst sie, weil du einen Termin mit Erinnerung erstellt hast.
            </p>
        </div>

    </div>
</div>
</body>
</html>
