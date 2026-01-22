# Der Saftladen

**Abschlussprojekt IFA 12 – Anwendungsentwicklung**

Ein Web-Portal für Ausbilder und Lehrkräfte mit Authentifizierung, Berufsbereichen, Forum und Terminverwaltung. Das Projekt wird Scrum-orientiert über 4 Sprints durchgeführt.

von Rojan Grasser, Justin Taube, Lukas Bauer  und Louis Dittmar

---

## 📋 Projektvorgaben & User Stories

### Überblick
- **Bewertung:** Softwareprodukt + Dokumentation + Präsentation + Fachgespräch
- **Methodik:** Scrum (Daily Scrums, Rollenwechsel, Reviews)
- **Zeitrahmen:** 4 Sprints, Abschluss in Blockwoche 10

### User Stories

#### Authentifizierung & Benutzerverwaltung
- **US-01:** Registrierung als Ausbilder (Freischaltung nötig)
- **US-02:** Registrierung als Lehrkraft (Freischaltung nötig)
- **US-03:** Admin: Benutzer freigeben/ablehnen
- **US-04:** Anmeldung am Portal
- **US-05:** Abmeldung vom Portal

#### Berufsbereiche & Zuordnung
- **US-06:** Admin: Berufsbereiche verwalten (CRUD)
- **US-07:** Admin: Ausbilder zu Berufsbereichen zuordnen
- **US-08:** Ausbilder: nur eigene Berufsbereiche sehen

#### Forum
- **US-09:** Thema in Berufsbereich erstellen
- **US-10:** Themen eines Berufsbereichs anzeigen
- **US-11:** Beitrag zu Thema schreiben
- **US-12:** Reaktionen auf Beiträge (Daumen hoch/runter)

#### Terminverwaltung
- **US-13:** Lehrkraft: Termine erstellen
- **US-14:** Ausbilder: anstehende Termine sehen
- **US-15:** Lehrkraft: eigene Termine bearbeiten/löschen

## 🛠️ Tech-Stack

- **Backend:** PHP 8.2+ (Laravel)
- **Frontend:** Vue.js (via Inertia.js)
- **Datenbank:** PostgreSQL  (Standard) / SQLite (Local)
- **Tools:** Composer, Node.js, Git/GitHub

---

## 🚀 Installation & Entwicklung

### Voraussetzungen
- PHP 8.2+
- Composer
- Node.js & npm

### Setup
Projekt klonen und Abhängigkeiten installieren:

```bash
composer run setup
```

### Entwicklungsserver starten
Startet Laravel Backend, Vite und Queue Worker:

```bash
composer run dev
```

### Weitere Befehle

- **Tests:** `composer run test`
- **Linting/Formatierung:** `composer run lint` / `npm run format`
- **Build (Production):** `npm run build`

### Projektstruktur
- `app/` - Backend Logik (Models, Controllers)
- `resources/` - Frontend (Vue Components, Pages)
- `database/` - Migrationen & SQLite DB
- `routes/` - Definition der Web-Routen
