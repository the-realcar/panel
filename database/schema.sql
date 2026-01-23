-- =============================================================================
-- PANEL PRACOWNICZY PPUT OSTRANS - SCHEMA BAZY DANYCH (PostgreSQL)
-- =============================================================================
-- Wersja: 1.0
-- Data: 2026-01-23
-- Opis: Struktura bazy danych dla systemu zarządzania wirtualnym 
--       przedsiębiorstwem transportowym (zgodnie z wymaganiami README.md)
-- =============================================================================

-- Wyłączenie istniejących obiektów (dla czystego wdrożenia)
DROP TABLE IF EXISTS trasa_przystanki CASCADE;
DROP TABLE IF EXISTS warianty_tras CASCADE;
DROP TABLE IF EXISTS brygady CASCADE;
DROP TABLE IF EXISTS linie CASCADE;
DROP TABLE IF EXISTS stanowiska CASCADE;
DROP TABLE IF EXISTS przystanki CASCADE;
DROP TABLE IF EXISTS pojazdy CASCADE;
DROP TABLE IF EXISTS konta_uzytkownikow CASCADE;
DROP TABLE IF EXISTS role CASCADE;

-- =============================================================================
-- 1. UŻYTKOWNICY I ROLE (3.1 i Sekcja 2)
-- =============================================================================

-- Tabela ról użytkowników
CREATE TABLE role (
    id_roli SERIAL PRIMARY KEY,
    nazwa_roli VARCHAR(50) NOT NULL UNIQUE,
    opis TEXT,
    utworzona_data TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Wstawienie domyślnych ról zgodnie z wymaganiami (Sekcja 2)
INSERT INTO role (nazwa_roli, opis) VALUES
    ('Administrator', 'Administrator IT - zarządzanie systemem i kontami'),
    ('Zarząd', 'Zarząd (Management) - pełna kontrola nad strukturą transportową'),
    ('Kadry', 'Pracownik Administracyjny / Kadry - obsługa wniosków, ewidencja czasu'),
    ('Dyspozytor', 'Dyspozytor - zarządzanie bieżącym ruchem i przydziałami'),
    ('Kierowca', 'Kierowca - realizacja kursów w grze');

-- Tabela użytkowników
CREATE TABLE konta_uzytkownikow (
    id_uzytkownika SERIAL PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    haslo_hash VARCHAR(255) NOT NULL, -- bcrypt/Argon2
    email VARCHAR(100) UNIQUE,
    imie VARCHAR(50),
    nazwisko VARCHAR(100),
    id_roli INT NOT NULL REFERENCES role(id_roli),
    aktywny BOOLEAN DEFAULT TRUE,
    data_utworzenia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ostatnie_logowanie TIMESTAMP,
    token_resetowania VARCHAR(255),
    token_wazny_do TIMESTAMP
);

-- Indeksy dla wydajności
CREATE INDEX idx_konta_login ON konta_uzytkownikow(login);
CREATE INDEX idx_konta_rola ON konta_uzytkownikow(id_roli);

-- =============================================================================
-- 2. TABOR (POJAZDY) - Wymaganie 3.5.A
-- =============================================================================

CREATE TABLE pojazdy (
    id_pojazdu SERIAL PRIMARY KEY,
    numer_taborowy VARCHAR(20) NOT NULL UNIQUE, -- np. "1001", "T-502"
    typ_pojazdu VARCHAR(20) NOT NULL CHECK (typ_pojazdu IN ('Autobus', 'Tramwaj')),
    marka VARCHAR(100), -- np. "Solaris"
    model VARCHAR(100), -- np. "Urbino 12"
    rok_produkcji INT,
    malowanie VARCHAR(100), -- Livery/schemat kolorystyczny
    status VARCHAR(20) DEFAULT 'Aktywny' CHECK (status IN ('Aktywny', 'Wycofany', 'Serwis', 'Rezerwa')),
    uwagi TEXT,
    data_dodania TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modyfikacji TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indeks dla szybkiego wyszukiwania
CREATE INDEX idx_pojazdy_status ON pojazdy(status);
CREATE INDEX idx_pojazdy_typ ON pojazdy(typ_pojazdu);

-- =============================================================================
-- 3. INFRASTRUKTURA (PRZYSTANKI I STANOWISKA) - Wymagania 3.5.B i 3.5.C
-- =============================================================================

-- Tabela przystanków fizycznych (3.5.B)
CREATE TABLE przystanki (
    id_przystanku SERIAL PRIMARY KEY,
    nazwa_przystanku VARCHAR(200) NOT NULL,
    identyfikator_unikatowy VARCHAR(50) UNIQUE, -- zgodność z logiką SIL
    lokalizacja_opisowa TEXT, -- np. "Przy dworcu"
    aktywny BOOLEAN DEFAULT TRUE,
    data_dodania TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela stanowisk/słupków (3.5.C)
CREATE TABLE stanowiska (
    id_stanowiska SERIAL PRIMARY KEY,
    numer_stanowiska VARCHAR(10) NOT NULL, -- np. "01", "02"
    id_przystanku INT NOT NULL REFERENCES przystanki(id_przystanku) ON DELETE CASCADE,
    typ_stanowiska VARCHAR(50) CHECK (typ_stanowiska IN ('Przelotowy', 'Pętla', 'Techniczny', 'Na żądanie')),
    uwagi TEXT,
    aktywny BOOLEAN DEFAULT TRUE,
    UNIQUE(id_przystanku, numer_stanowiska)
);

CREATE INDEX idx_stanowiska_przystanek ON stanowiska(id_przystanku);

-- =============================================================================
-- 4. ORGANIZACJA PRZEWOZÓW - Wymagania 3.5.D - 3.5.G
-- =============================================================================

-- Tabela linii (3.5.D)
CREATE TABLE linie (
    id_linii SERIAL PRIMARY KEY,
    numer_linii VARCHAR(20) NOT NULL UNIQUE, -- np. "105", "N12"
    typ_linii VARCHAR(30) CHECK (typ_linii IN ('Dzienna', 'Nocna', 'Podmiejska', 'Zastępcza')),
    kolorystyka VARCHAR(50), -- Hex lub nazwa koloru (zgodność z SIL)
    opis TEXT,
    aktywna BOOLEAN DEFAULT TRUE,
    data_dodania TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela brygad (3.5.E)
CREATE TABLE brygady (
    id_brygady SERIAL PRIMARY KEY,
    numer_brygady VARCHAR(30) NOT NULL, -- np. "105/1", "105/02"
    id_linii INT NOT NULL REFERENCES linie(id_linii) ON DELETE CASCADE,
    domyslny_typ_taboru VARCHAR(50), -- np. "Przegubowy", "Standard"
    uwagi TEXT,
    aktywna BOOLEAN DEFAULT TRUE,
    UNIQUE(id_linii, numer_brygady)
);

CREATE INDEX idx_brygady_linia ON brygady(id_linii);

-- Tabela wariantów tras (3.5.F)
CREATE TABLE warianty_tras (
    id_wariantu SERIAL PRIMARY KEY,
    id_linii INT NOT NULL REFERENCES linie(id_linii) ON DELETE CASCADE,
    nazwa_wariantu VARCHAR(200) NOT NULL, -- np. "Kierunek: Dworzec -> Osiedle Północne"
    typ_wariantu VARCHAR(50) CHECK (typ_wariantu IN ('Kierunek A->B', 'Kierunek B->A', 'Zjazd do zajezdni', 'Skrócony')),
    nazwa_kierunku VARCHAR(200), -- Wyświetlana na tablicach
    opis TEXT,
    aktywny BOOLEAN DEFAULT TRUE
);

CREATE INDEX idx_warianty_linia ON warianty_tras(id_linii);

-- Tabela sekwencji przystanków na trasie (3.5.G)
CREATE TABLE trasa_przystanki (
    id_trasa_przystanek SERIAL PRIMARY KEY,
    id_wariantu INT NOT NULL REFERENCES warianty_tras(id_wariantu) ON DELETE CASCADE,
    id_stanowiska INT NOT NULL REFERENCES stanowiska(id_stanowiska),
    kolejnosc INT NOT NULL, -- Uporządkowanie przystanków na trasie
    czas_przejazdu_min INT, -- Czas do następnego przystanku (minuty)
    dystans_km DECIMAL(5,2), -- Odległość do następnego przystanku
    UNIQUE(id_wariantu, kolejnosc)
);

CREATE INDEX idx_trasa_wariant ON trasa_przystanki(id_wariantu);
CREATE INDEX idx_trasa_kolejnosc ON trasa_przystanki(id_wariantu, kolejnosc);

-- =============================================================================
-- DODATKOWE TABELE WSPIERAJĄCE (dla rozbudowy systemu)
-- =============================================================================

-- Tabela sesji logowania (3.1 - bezpieczeństwo)
CREATE TABLE sesje_uzytkownikow (
    id_sesji SERIAL PRIMARY KEY,
    id_uzytkownika INT NOT NULL REFERENCES konta_uzytkownikow(id_uzytkownika) ON DELETE CASCADE,
    token_sesji VARCHAR(255) NOT NULL UNIQUE,
    ip_adres VARCHAR(45),
    user_agent TEXT,
    data_rozpoczecia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_ostatniej_aktywnosci TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    aktywna BOOLEAN DEFAULT TRUE
);

CREATE INDEX idx_sesje_token ON sesje_uzytkownikow(token_sesji);
CREATE INDEX idx_sesje_uzytkownik ON sesje_uzytkownikow(id_uzytkownika);

-- Tabela do logowania zdarzeń systemowych (audyt)
CREATE TABLE logi_systemowe (
    id_logu SERIAL PRIMARY KEY,
    id_uzytkownika INT REFERENCES konta_uzytkownikow(id_uzytkownika),
    akcja VARCHAR(100) NOT NULL,
    modul VARCHAR(50),
    opis TEXT,
    ip_adres VARCHAR(45),
    data_zdarzenia TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_logi_uzytkownik ON logi_systemowe(id_uzytkownika);
CREATE INDEX idx_logi_data ON logi_systemowe(data_zdarzenia);

-- =============================================================================
-- DANE PRZYKŁADOWE (opcjonalnie - dla testów)
-- =============================================================================

-- Przykładowy użytkownik administratora (hasło: admin123 - ZMIENIĆ W PRODUKCJI!)
-- Hash wygenerowany dla 'admin123' używając bcrypt
-- UWAGA BEZPIECZEŃSTWA: To konto należy usunąć lub zmienić hasło po pierwszym logowaniu!
INSERT INTO konta_uzytkownikow (login, haslo_hash, email, imie, nazwisko, id_roli) VALUES
    ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@ostrans.local', 'Administrator', 'Systemu', 1);

-- Przykładowe przystanki
INSERT INTO przystanki (nazwa_przystanku, identyfikator_unikatowy, lokalizacja_opisowa) VALUES
    ('Dworzec Główny', 'DG001', 'Przed głównym wejściem do dworca'),
    ('Osiedle Północne', 'OP001', 'Przy bloku 15A'),
    ('Zajezdnia Ostrans', 'ZO001', 'Wjazd główny do zajezdni');

-- Przykładowe stanowiska
INSERT INTO stanowiska (numer_stanowiska, id_przystanku, typ_stanowiska) VALUES
    ('01', 1, 'Przelotowy'),
    ('02', 1, 'Przelotowy'),
    ('01', 2, 'Pętla'),
    ('01', 3, 'Techniczny');

-- Przykładowe pojazdy
INSERT INTO pojazdy (numer_taborowy, typ_pojazdu, marka, model, rok_produkcji, malowanie, status) VALUES
    ('1001', 'Autobus', 'Solaris', 'Urbino 12', 2020, 'Livery Ostrans Standard', 'Aktywny'),
    ('1002', 'Autobus', 'Mercedes-Benz', 'Citaro', 2019, 'Livery Ostrans Standard', 'Aktywny'),
    ('T-501', 'Tramwaj', 'Pesa', 'Swing', 2021, 'Livery Ostrans Tramwaj', 'Aktywny');

-- Przykładowa linia
INSERT INTO linie (numer_linii, typ_linii, kolorystyka, opis) VALUES
    ('105', 'Dzienna', '#FF6B6B', 'Linia główna: Dworzec - Osiedle Północne');

-- Przykładowa brygada
INSERT INTO brygady (numer_brygady, id_linii, domyslny_typ_taboru) VALUES
    ('105/1', 1, 'Standard 12m');

-- Przykładowy wariant trasy
INSERT INTO warianty_tras (id_linii, nazwa_wariantu, typ_wariantu, nazwa_kierunku) VALUES
    (1, 'Kierunek: Dworzec Główny -> Osiedle Północne', 'Kierunek A->B', 'Osiedle Północne');

-- Przykładowa sekwencja przystanków
INSERT INTO trasa_przystanki (id_wariantu, id_stanowiska, kolejnosc, czas_przejazdu_min, dystans_km) VALUES
    (1, 1, 1, 15, 5.2),  -- Dworzec Główny 01
    (1, 3, 2, 0, 0);      -- Osiedle Północne 01 (pętla końcowa)

-- =============================================================================
-- KOŃCOWE KOMENTARZE
-- =============================================================================
-- Schema zgodna z wymaganiami README.md (sekcje 3.5.A - 3.5.G)
-- Struktura wspiera rozbudowę o dodatkowe funkcjonalności:
-- - Panel Kierowcy (3.2)
-- - Panel Dyspozytora (3.3)
-- - Panel Kadrowo-Administracyjny (3.4)
-- - Panel Zarządu (3.5)
-- 
-- UWAGA: Hasła w danych przykładowych należy ZMIENIĆ w środowisku produkcyjnym!
-- =============================================================================
