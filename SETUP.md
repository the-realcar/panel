# Panel Ostrans - Setup Guide

## Szybki Start

### 1. Konfiguracja Bazy Danych PostgreSQL

```bash
# Utwórz bazę danych
createdb ostrans_panel

# Zaaplikuj schemat
psql -U postgres -d ostrans_panel -f database/schema.sql
```

### 2. Konfiguracja Połączenia

Ustaw zmienne środowiskowe lub edytuj `config/database.php`:

```bash
export DB_HOST=localhost
export DB_PORT=5432
export DB_NAME=ostrans_panel
export DB_USER=ostrans_user
export DB_PASSWORD=your_password
```

### 3. Uruchomienie Serwera Deweloperskiego

```bash
# PHP Built-in Server
php -S localhost:8000
```

Otwórz przeglądarkę: http://localhost:8000

### 4. Dane Testowe

Schema zawiera przykładowe dane testowe:
- **Login:** `admin`
- **Hasło:** `admin123` (ZMIEŃ W PRODUKCJI!)

## Struktura Projektu

```
panel/
├── assets/
│   └── css/
│       └── style.css          # Style (Mobile First + Dark Mode)
├── config/
│   └── database.php           # Konfiguracja połączenia z PostgreSQL
├── database/
│   └── schema.sql             # Schema bazy danych
├── index.php                  # Strona główna z formularzem logowania
└── README.md                  # Dokumentacja wymagań
```

## Dalszy Rozwój

Zgodnie z wymaganiami README.md, do implementacji:

- [ ] `auth/login.php` - Obsługa logowania
- [ ] `auth/forgot-password.php` - Reset hasła
- [ ] `dashboard.php` - Panel główny
- [ ] `panels/driver/` - Panel Kierowcy (Priorytet Mobile)
- [ ] `panels/dispatcher/` - Panel Dyspozytora
- [ ] `panels/hr/` - Panel Kadrowo-Administracyjny
- [ ] `panels/management/` - Panel Zarządu (CRUD)

## Wymagania Techniczne

- PHP 7.4+ (z obsługą PDO PostgreSQL)
- PostgreSQL 12+
- Serwer WWW (Apache/Nginx) z mod_rewrite

## Bezpieczeństwo

- Hasła hashowane przy użyciu bcrypt
- Tokeny CSRF w formularzach
- Prepared statements (PDO)
- Sesje z timeout'em
- RBAC (Role-Based Access Control)

## Wsparcie

System zgodny z wymaganiami opisanymi w `README.md` (Sekcje 1-6).
