# Panel Pracowniczy Firma KOT

Wewnetrzny system webowy do obslugi pracy w firmie transportowej (role, grafiki, flota, incydenty, dokumentacja, raportowanie operacyjne).

## Aktualny status (maj 2026)

- Domyslne srodowisko aplikacji: production.
- Logowanie testowymi danymi nie jest eksponowane w UI.
- Endpoint health nie ujawnia szczegolow bledow bazy.
- OAuth (Discord/Roblox) jest aktualnie wylaczony na poziomie kontrolera logowania.

## Najwazniejsze funkcje

### Autoryzacja i bezpieczenstwo

- Logowanie haslem (bcrypt).
- RBAC + mapowanie stanowisk do rol.
- CSRF tokeny dla formularzy.
- Timeout sesji i bezpieczne cookies sesyjne.
- Logowanie zdarzen (audit_logs, login_logs, error_logs).

### Panele operacyjne

- Kierowca: grafik, karta drogowa, zglaszanie incydentow, dokumentacja.
- Dyspozytor: przydzialy, komunikaty, podglad floty i grafikow.
- HR: raporty godzin pracy.
- Admin/Zarzad: slowniki, uzytkownicy, role, pojazdy, linie, brygady, trasy, przystanki, ustawienia.

## Stos technologiczny

- PHP 8.0+
- PostgreSQL 14+
- Apache lub Nginx
- Frontend: klasyczny SSR (PHP + HTML + CSS + JS)

Wymagane rozszerzenia PHP:

- pdo_pgsql
- pgsql
- mbstring
- session

## Architektura

Projekt dziala w ukladzie MVC:

- app/Controllers - logika endpointow
- app/Models - dostep do danych
- app/Views - widoki
- core - klasy bazowe i uslugi wspolne
- config - konfiguracja aplikacji, bazy i sesji

Autoload i ladowanie .env odbywa sie w pliku app/bootstrap.php.

## Szybki start (lokalnie)

1. Sklonuj repozytorium i przejdz do katalogu projektu.
2. Utworz baze PostgreSQL.
3. Zaimportuj schemat:

```bash
psql -U <db_user> -d <db_name> -f database/schema.sql
```

4. Opcjonalnie (DEV): zaladuj dane testowe:

```bash
psql -U <db_user> -d <db_name> -f database/seed.sql
```

5. Skonfiguruj zmienne srodowiskowe (patrz sekcja Konfiguracja).
6. Skieruj VirtualHost/root serwera WWW na katalog projektu.
7. Otworz /login.php.

## Konfiguracja

### Priorytet konfiguracji

1. Zmienne srodowiskowe
2. Wartosc domyslna z plikow config/*.php

### Plik wzorcowy production

Szablon zmiennych produkcyjnych znajduje sie w:

- .env.production.example

Mozesz skopiowac jego zawartosc do lokalnego pliku .env (DEV) albo ustawic wartosci przez system/sekret manager (PROD).

### Kluczowe zmienne

- APP_ENV
- BASE_URL
- OAUTH_REDIRECT_BASE
- DB_HOST
- DB_PORT
- DB_NAME
- DB_USER
- DB_PASSWORD
- MAIL_HOST
- MAIL_PORT
- MAIL_USERNAME
- MAIL_PASSWORD
- MAIL_FROM
- MAIL_FROM_NAME
- MAIL_ENCRYPTION
- SHOW_TEST_CREDENTIALS

## Produkcja - wymagania minimalne

- APP_ENV=production
- SHOW_TEST_CREDENTIALS=false
- HTTPS + poprawny certyfikat TLS
- Sekrety i hasla z managera sekretow
- Brak seedowania danych testowych na produkcji

Szczegolowa checklista wdrozenia:

- docs/deployment-checklist.md

## Uwaga o domyslnych wartosciach DB

Plik config/database.php zawiera fallbacki dla DB_*.

W srodowisku produkcyjnym zawsze nadpisz je zmiennymi srodowiskowymi i nie opieraj sie na wartosciach domyslnych z repozytorium.

## Endpointy techniczne

- /health.php - status aplikacji i podstawowe checki
- /ping.php - prosty endpoint kontrolny

## Struktura katalogow (skrot)

```text
panel/
|- admin/                  # endpointy panelu administracyjnego
|- app/
|  |- Controllers/
|  |- Models/
|  |- Views/
|  |- bootstrap.php
|- assets/                 # css/js
|- config/                 # config.php, database.php, session.php
|- core/                   # Auth, Database, RBAC, Validator, AuditLog
|- database/               # schema.sql, seed.sql
|- dispatcher/             # endpointy panelu dyspozytora
|- driver/                 # endpointy panelu kierowcy
|- docs/                   # dokumentacja projektu i wdrozenia
|- errors/                 # strony bledow HTTP
|- hr/                     # endpointy panelu kadr
|- management/             # dokumentacja zarzadu
|- includes/               # funkcje i layouty wspolne
|- oauth/                  # endpointy OAuth
|- storage/                # sesje i dane runtime
|- login.php
|- logout.php
|- reset-password.php
|- settings.php
`- index.php
```

## Rozwiazywanie problemow

### Brak polaczenia z baza

- Sprawdz DB_HOST/DB_PORT/DB_NAME/DB_USER/DB_PASSWORD.
- Upewnij sie, ze PostgreSQL przyjmuje polaczenia z hosta aplikacji.

### Sesja wygasa zbyt szybko

- Sprawdz SESSION_TIMEOUT oraz konfiguracje cookies w config/session.php.

### Brak maili resetu hasla

- Sprawdz MAIL_* i logi SMTP.

### Dostep zabroniony (403)

- Sprawdz przypisane role i stanowiska uzytkownika.

## Licencja

Projekt wewnetrzny Firmy KOT. Wszelkie prawa zastrzezone.
