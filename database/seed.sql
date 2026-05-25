-- ============================================
-- Panel Pracowniczy Firma KOT - Test Data Seeds
-- ============================================

\encoding UTF8

-- Wyczyść istniejące dane
TRUNCATE TABLE password_resets, error_logs, audit_logs, login_logs, incidents, applications, route_card_trips, route_cards, assignments, schedules, 
    dispatches, work_hours, route_stops, route_variants, brigades, platforms, stops, role_position_mapping,
    user_positions, user_roles, vehicles, lines, positions, roles, departments, users, settings, sessions CASCADE;

-- Reset sekwencji
ALTER SEQUENCE users_id_seq RESTART WITH 1;
ALTER SEQUENCE roles_id_seq RESTART WITH 1;
ALTER SEQUENCE departments_id_seq RESTART WITH 1;
ALTER SEQUENCE positions_id_seq RESTART WITH 1;
ALTER SEQUENCE lines_id_seq RESTART WITH 1;
ALTER SEQUENCE vehicles_id_seq RESTART WITH 1;
ALTER SEQUENCE schedules_id_seq RESTART WITH 1;
ALTER SEQUENCE assignments_id_seq RESTART WITH 1;
ALTER SEQUENCE route_cards_id_seq RESTART WITH 1;
ALTER SEQUENCE route_card_trips_id_seq RESTART WITH 1;
ALTER SEQUENCE incidents_id_seq RESTART WITH 1;
ALTER SEQUENCE applications_id_seq RESTART WITH 1;
ALTER SEQUENCE dispatches_id_seq RESTART WITH 1;
ALTER SEQUENCE work_hours_id_seq RESTART WITH 1;
ALTER SEQUENCE stops_id_seq RESTART WITH 1;
ALTER SEQUENCE platforms_id_seq RESTART WITH 1;
ALTER SEQUENCE brigades_id_seq RESTART WITH 1;
ALTER SEQUENCE route_variants_id_seq RESTART WITH 1;
ALTER SEQUENCE route_stops_id_seq RESTART WITH 1;
ALTER SEQUENCE error_logs_id_seq RESTART WITH 1;

-- ============================================
-- 0. USTAWIENIA SYSTEMOWE
-- ============================================
INSERT INTO settings (key, value, description) VALUES
('company_name', 'Firma KOT', 'Nazwa firmy widoczna w panelu'),
('base_url', 'http://localhost', 'Bazowy adres URL aplikacji'),
('support_email', 'admin@firmakot.pl', 'Adres kontaktowy do wsparcia'),
('session_timeout', '7200', 'Timeout sesji w sekundach');

-- ============================================
-- 1. DEPARTAMENTY
-- ============================================
INSERT INTO departments (name, description, active) VALUES
('Zarząd', 'Zarząd Firmy KOT', TRUE),
('Nadzór Ruchu', 'Nadzór Ruchu Firmy KOT', TRUE),
('Dyspozytornia', 'Dział Dyspozytorów', TRUE),
('Transport', 'Dział Transportu i Kierowców', TRUE),
('Zajezdnia', 'Dział Techniczny i Zajezdnia', TRUE);

-- ============================================
-- 2. ROLE (RBAC)
-- ============================================
INSERT INTO roles (name, description, permissions) VALUES
('Administrator', 'Pełny dostęp do systemu', 
    '{"users": ["read", "create", "update", "delete"], "vehicles": ["read", "create", "update", "delete"], "lines": ["read", "create", "update", "delete"], "positions": ["read", "create", "update", "delete"], "stops": ["read", "create", "update", "delete"], "platforms": ["read", "create", "update", "delete"], "brigades": ["read", "create", "update", "delete"], "route_variants": ["read", "create", "update", "delete"], "incidents": ["read", "create", "update", "delete", "resolve"], "schedules": ["read", "create", "update", "delete"], "reports": ["read", "create"]}'::jsonb),
('Dyspozytor', 'Zarządzanie grafikami i kontrola ruchu',
    '{"schedules": ["read", "create", "update", "delete"], "vehicles": ["read", "update"], "lines": ["read"], "brigades": ["read"], "stops": ["read"], "route_cards": ["read"], "incidents": ["read", "update"]}'::jsonb),
('Kierowca', 'Dostęp do własnego grafiku i kart drogowych',
    '{"schedules": ["read"], "route_cards": ["read", "create", "update"], "incidents": ["read", "create"], "vehicles": ["read"], "lines": ["read"], "stops": ["read"]}'::jsonb),
('Zarząd', 'Dostęp do raportów i zarządzania',
    '{"users": ["read"], "vehicles": ["read", "create", "update", "delete"], "lines": ["read", "create", "update", "delete"], "positions": ["read", "create", "update", "delete"], "stops": ["read", "create", "update", "delete"], "platforms": ["read", "create", "update", "delete"], "brigades": ["read", "create", "update", "delete"], "route_variants": ["read", "create", "update", "delete"], "incidents": ["read"], "schedules": ["read"], "reports": ["read", "create"]}'::jsonb),
('Nadzór Ruchu', 'Planowanie tras i rozkładów',
    '{"lines": ["read", "create", "update"], "stops": ["read", "create", "update"], "platforms": ["read", "create", "update"], "brigades": ["read", "create", "update"], "route_variants": ["read", "create", "update", "delete"], "schedules": ["read", "create", "update"], "reports": ["read"]}'::jsonb),
('Kontrole', 'Nadzór zgłoszeń i kontroli',
    '{"incidents": ["read", "update"], "vehicles": ["read"], "reports": ["read"]}'::jsonb),
('Kadry', 'Obsługa personelu',
    '{"users": ["read", "update"], "positions": ["read"], "reports": ["read"]}'::jsonb),
('Transport', 'Realizacja kursów przez kierowców',
    '{"schedules": ["read"], "route_cards": ["read", "create", "update"], "incidents": ["read", "create"], "vehicles": ["read"], "lines": ["read"], "stops": ["read"]}'::jsonb),
('Zajezdnia', 'Obsługa techniczna taboru',
    '{"vehicles": ["read", "update"], "incidents": ["read", "create", "update"], "reports": ["read"]}'::jsonb);

-- ============================================
-- 3. UŻYTKOWNICY
-- Hasło: "Gliwice2010!Zajezdnia123"
-- Hash bcrypt: $2y$12$FbJSRcI7LRRl2xQY7p/4EOqdiAM2nWetldWxvO5n2ysYy.5GsFqcO
-- ============================================
INSERT INTO users (username, email, password_hash, first_name, last_name, hired_date, archived, active) VALUES
('realcar', 're4lcar@gmail.com', '$2y$12$FbJSRcI7LRRl2xQY7p/4EOqdiAM2nWetldWxvO5n2ysYy.5GsFqcO', 'Dawid', 'Volve', CURRENT_DATE - INTERVAL '365 days', FALSE, TRUE);

-- ============================================
-- 4. PRZYPISANIE RÓL DO UŻYTKOWNIKÓW
-- ============================================
-- Ręczne przypisanie zostanie wygenerowane automatycznie na podstawie stanowisk i mapowania.

-- ============================================
-- 5. STANOWISKA - FIRMA KOT I SPÓŁKI
-- ============================================
INSERT INTO positions (name, department_id, max_count, description, active) VALUES
-- FIRMA KOT - Zarząd
('Zarząd KOT', 1, 3, 'Członek Zarządu Firmy KOT - nadzoruje prace każdego wydziału', TRUE),
('Dyspozytor Główny', 1, 1, 'Powołany przez Zarząd KOT, podlega tylko Zarządowi. Nadzoruje pracę dyspozytorów i organizuje rekrutacje', TRUE),

-- FIRMA KOT - Administracja
('Główny Administrator', 2, 1, 'Najwyższy administrator Firmy KOT, kontroluje pracę administracji', TRUE),
('Zastępca Głównego Administratora', 2, 2, 'Zastępuje Głównego Administratora, uprawnienia identyczne', TRUE),
('Starszy Administrator', 2, 5, 'Doświadczony Administrator z możliwością moderacji serwera KOT', TRUE),

-- FIRMA KOT - Nadzór Ruchu
('Koordynator rozkładów jazdy', 2, 3, 'Zarządza częstotliwościami odjazdów linii, rozpatruje nowe rozkłady z zarządem', TRUE),
('Planer rozkładów jazdy', 2, 8, 'Planuje i układa rozkład jazdy dla tras zatwierdzonych przez zarząd', TRUE),
('Planer tras linii', 2, 7, 'Planuje i układa trasę linii, podlega Koordynatorowi rozkładów jazdy', TRUE),
('Nadzorca ruchu', 2, NULL, 'Nadzoruje pracowników wydziału transportu, stanowisko bez limitu', TRUE),

-- SPÓŁKI - Zarząd
('Dyrektor Spółki', 1, 1, 'Nadzoruje każdego pracownika w spółce, jest częścią zarządu KOT', TRUE),
('Zastępca Dyrektora Spółki', 1, 2, 'Zastępuje Dyrektora Spółki, uprawnienia identyczne, brak członkowstwa w zarządzie KOT', TRUE),

-- SPÓŁKI - Administracja
('Główny Administrator (Spółka)', 2, 1, 'Główny Administrator spółki, kontroluje pracę administracji spółki', TRUE),
('Zastępca Głównego Administratora (Spółka)', 2, 2, 'Zastępuje Głównego Administratora spółki', TRUE),
('Starszy Administrator (Spółka)', 2, 5, 'Doświadczony Administrator spółki', TRUE),
('Doświadczony Administrator (Spółka)', 2, 10, 'Doświadczony administrator spółki z uprawnieniami do banowania', TRUE),
('Administrator (Spółka)', 2, NULL, 'Administrator spółki z uprawnieniami do wyrzucania członków, bez limitu', TRUE),
('Moderator (Spółka)', 2, 15, 'Początkujący administrator spółki z uprawnieniami do przerw', TRUE),
('Młodszy Moderator (Spółka)', 2, 10, 'Najmłodszy członek administracji spółki, uprawnienia do usuwania wiadomości', TRUE),

-- SPÓŁKI - Dyspozytornia
('Dyspozytor Główny (Spółka)', 4, 1, 'Powołany przez Zarząd Spółki, podlega Zarządowi. Nadzoruje pracę dyspozytorów spółki', TRUE),
('Zastępca Dyspozytora Głównego (Spółka)', 4, 3, 'Zastępuje Dyspozytora Głównego, opiekuje się przydzieloną spółką', TRUE),
('Starszy Dyspozytor (Spółka)', 4, 5, 'Doświadczony dyspozytor, uprawnienia do egzaminów praktycznych', TRUE),
('Dyspozytor (Spółka)', 4, 10, 'Pełnoprawny dyspozytor, zdał egzamin praktyczny', TRUE),
('Młodszy Dyspozytor (Spółka)', 4, 5, 'Najmłodszy członek dyspozytorni, uczy się od wyższych stanowisk', TRUE),

-- SPÓŁKI - Transport
('Koordynator Przewozów', 3, 1, 'Nadzoruje pracowników wydziału transportu, wydaje rozporządzenia', TRUE),
('Zastępca Koordynatora Przewozów', 3, 2, 'Zastępuje Koordynatora, wspiera nadzór nad transportem', TRUE),
('Egzaminator', 3, 5, 'Przeprowadza egzaminy praktyczne dla kierowców i motorniczych', TRUE),
('Kontroler biletów', 3, NULL, 'Inspekcja biletów pasażerów w pojazdach spółek, bez limitu', TRUE),
('Starszy Kierowca/Motorniczy', 3, 10, 'Doświadczony kierowca/motorniczy, nadzoruje młodszych', TRUE),
('Kierowca/Motorniczy', 3, NULL, 'Pełnoprawny pracownik transportu, zdał egzamin praktyczny, bez limitu', TRUE),
('Młodszy Kierowca/Motorniczy', 3, 15, 'Początkujący pracownik transportu, uczy się pod opieką starszych', TRUE),

-- SPÓŁKI - Zajezdnia
('Kierownik Zajezdni', 5, 1, 'Głowa zajezdni, odpowiada za stan pojazdów', TRUE),
('Zastępca Kierownika Zajezdni', 5, 1, 'Zastępuje Kierownika Zajezdni, wspomaga nadzór', TRUE),
('Lakiernik', 5, NULL, 'Odpowiada za prace lakiernicze, utrzymanie estetyki pojazdów, bez limitu', TRUE);

-- ============================================
-- 6. MAPOWANIE STANOWISK -> RÓL
-- ============================================
INSERT INTO role_position_mapping (role_id, position_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN positions p ON p.name IN (
    'Zarząd KOT', 'Dyspozytor Główny',
    'Dyrektor Spółki', 'Zastępca Dyrektora Spółki'
)
WHERE r.name = 'Zarząd';

INSERT INTO role_position_mapping (role_id, position_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN positions p ON p.name IN (
    'Główny Administrator', 'Zastępca Głównego Administratora', 'Starszy Administrator',
    'Główny Administrator (Spółka)', 'Zastępca Głównego Administratora (Spółka)',
    'Starszy Administrator (Spółka)', 'Doświadczony Administrator (Spółka)',
    'Administrator (Spółka)', 'Moderator (Spółka)', 'Młodszy Moderator (Spółka)'
)
WHERE r.name IN ('Administrator', 'Administrator IT');

INSERT INTO role_position_mapping (role_id, position_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN positions p ON p.name IN (
    'Koordynator rozkładów jazdy', 'Planer rozkładów jazdy', 'Planer tras linii', 'Nadzorca ruchu'
)
WHERE r.name = 'Nadzór Ruchu';

INSERT INTO role_position_mapping (role_id, position_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN positions p ON p.name IN (
    'Dyspozytor Główny (Spółka)', 'Zastępca Dyspozytora Głównego (Spółka)',
    'Starszy Dyspozytor (Spółka)', 'Dyspozytor (Spółka)', 'Młodszy Dyspozytor (Spółka)'
)
WHERE r.name = 'Dyspozytor';

INSERT INTO role_position_mapping (role_id, position_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN positions p ON p.name IN (
    'Koordynator Przewozów', 'Zastępca Koordynatora Przewozów', 'Egzaminator',
    'Starszy Kierowca/Motorniczy', 'Kierowca/Motorniczy', 'Młodszy Kierowca/Motorniczy'
)
WHERE r.name IN ('Transport', 'Kierowca');

INSERT INTO role_position_mapping (role_id, position_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN positions p ON p.name = 'Kontroler biletów'
WHERE r.name = 'Kontrole';

INSERT INTO role_position_mapping (role_id, position_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN positions p ON p.name IN ('Kierownik Zajezdni', 'Zastępca Kierownika Zajezdni', 'Lakiernik')
WHERE r.name = 'Zajezdnia';

-- ============================================
-- 7. PRZYPISANIE STANOWISK DO UŻYTKOWNIKÓW
-- ============================================
INSERT INTO user_positions (user_id, position_id, active)
SELECT 1, id, TRUE FROM positions WHERE name = 'Główny Administrator';

-- Synchronizacja ról użytkowników na podstawie przypisanych stanowisk
INSERT INTO user_roles (user_id, role_id, assigned_date)
SELECT DISTINCT up.user_id, rpm.role_id, CURRENT_TIMESTAMP
FROM user_positions up
INNER JOIN role_position_mapping rpm ON rpm.position_id = up.position_id;

-- ============================================
-- 8. LINIE KOMUNIKACYJNE
-- ============================================
INSERT INTO lines (line_number, name, route_description, line_type, active) VALUES
('1', 'Linia 1 - Centrum', 'Dworzec Główny - Plac Wolności - Osiedle Północne', 'bus', TRUE),
('5', 'Linia 5 - Lotnisko', 'Dworzec PKS - Rondo Niepodległości - Lotnisko', 'bus', TRUE),
('7', 'Linia 7 - Tramwaj', 'Nowy Świat - Stare Miasto - Politechnika', 'tram', TRUE);

-- ============================================
-- 8. POJAZDY
-- ============================================
INSERT INTO vehicles (nr_poj, reg_plate, vehicle_type, model, rok_prod, pojemnosc, status, marka, engine, gearbox, typ_napedu, klimatyzacja, zajezdnia, przewoznik) VALUES
('BUS-001', 'KR 12345', 'bus', 'Urbino 12', 2020, 'MAXI', 'sprawny', 'Solaris', 'Cummins ISB6.7', 'ZF Ecolife', 'Diesel', true, 'KM', 'Ostrans'),
('BUS-002', 'KR 23456', 'bus', 'Lion''s City', 2019, 'MAXI', 'sprawny', 'MAN', 'MAN D2066', 'Voith DIWA', 'Diesel', true, 'KW', 'KujaTrans'),
('TRAM-001', 'KR 98765', 'tram', 'Swing', 2021, 'MEGA', 'sprawny', 'Pesa', NULL, NULL, 'Elektryczny', true, 'MC', 'Ostromunikacja'),
('BUS-003', 'KR 34567', 'bus', 'Citaro', 2018, 'MIDI', 'w naprawie', 'Mercedes-Benz', 'Mercedes OM936', 'Mercedes GO190', 'Diesel', true, 'KM', 'Ostrans');

-- ============================================
-- 9. GRAFIKI (SCHEDULES)
-- ============================================
INSERT INTO schedules (user_id, vehicle_id, line_id, brigade_id, schedule_date, start_time, end_time, status, notes) VALUES
(1, 1, 1, NULL, CURRENT_DATE, '06:00:00', '14:00:00', 'scheduled', 'Poranna zmiana na linii 1, brygada 1/1'),
(1, 2, 2, NULL, CURRENT_DATE, '14:30:00', '22:30:00', 'scheduled', 'Popołudniowa zmiana na linii 5, brygada 5/1'),
(1, 1, 1, NULL, CURRENT_DATE + 1, '06:00:00', '14:00:00', 'scheduled', 'Poranna zmiana, brygada 1/2'),
(1, 3, 3, NULL, CURRENT_DATE + 2, '07:00:00', '15:00:00', 'scheduled', 'Zmiana tramwajowa, brygada 7/1'),
(1, 1, 1, NULL, CURRENT_DATE - 1, '06:00:00', '14:00:00', 'completed', 'Wykonano zgodnie z planem');

-- ============================================
-- 9b. PRZYDZIALY DYSPozytora (ASSIGNMENTS)
-- ============================================
INSERT INTO assignments (dispatcher_id, user_id, vehicle_id, line_id, brigade_id, schedule_id, assignment_date, start_time, end_time, status, notes)
SELECT
    1,
    s.user_id,
    s.vehicle_id,
    s.line_id,
    s.brigade_id,
    s.id,
    s.schedule_date,
    s.start_time,
    s.end_time,
    CASE WHEN s.status = 'completed' THEN 'completed' ELSE 'active' END,
    COALESCE(s.notes, 'Przydzial wygenerowany z seedow')
FROM schedules s;

-- ============================================
-- 10. KARTY DROGOWE (przykładowe)
-- ============================================
INSERT INTO route_cards (user_id, vehicle_id, line_id, route_date, start_time, end_time, passengers_count, status, notes) VALUES
(1, 1, 1, CURRENT_DATE - 1, '06:00:00', '14:00:00', 450, 'completed', 'Przejazd bez problemów'),
(1, 2, 2, CURRENT_DATE - 2, '14:30:00', '22:30:00', 380, 'completed', 'Zwiekszony ruch w godzinach szczytu');

-- ============================================
-- 11. INCYDENTY/AWARIE
-- ============================================
INSERT INTO incidents (reported_by, vehicle_id, incident_type, severity, title, description, incident_date, status) VALUES
(1, 4, 'breakdown', 'high', 'Awaria klimatyzacji', 'Przestała działać klimatyzacja w autobusie BUS-003 podczas kursu na linii 1', CURRENT_TIMESTAMP - INTERVAL '2 days', 'resolved'),
(1, 1, 'complaint', 'low', 'Reklamacja pasażera', 'Pasażer złożył reklamację dotyczącą spóźnienia o 5 minut', CURRENT_TIMESTAMP - INTERVAL '1 day', 'in_progress'),
(1, 2, 'other', 'medium', 'Brak paliwa na stacji', 'Stacja paliwa była chwilowo niedostępna, tankowanie z opóźnieniem', CURRENT_TIMESTAMP - INTERVAL '3 hours', 'open');

-- ============================================
-- 11b. KOMUNIKATY DYSPOZYTORA
-- ============================================
INSERT INTO dispatches (sender_id, recipient_id, message, created_at) VALUES
(1, 1, 'Zmiana trasy na linii 1: objazd przez Plac Wolnosci do odwolania.', CURRENT_TIMESTAMP - INTERVAL '30 minutes');

-- ============================================
-- 11c. EWIDENCJA CZASU PRACY
-- ============================================
INSERT INTO work_hours (user_id, work_date, hours_worked, notes, source, updated_by) VALUES
(1, CURRENT_DATE - INTERVAL '2 days', 8.00, 'Zmiana poranna', 'manual', 1),
(1, CURRENT_DATE - INTERVAL '1 day', 7.50, 'Skrocona zmiana', 'manual', 1),
(1, CURRENT_DATE, 8.00, 'Zmiana dzienna', 'manual', 1);

-- ============================================
-- 12. PRZYSTANKI
-- ============================================
INSERT INTO stops (city_id, name, opis, status_nz, active) VALUES
(NULL, 'Dworzec Główny', 'Przy dworcu kolejowym PKP', FALSE, TRUE),
(NULL, 'Plac Wolności', 'Centrum miasta, rondo', FALSE, TRUE),
(NULL, 'Osiedle Północne', 'Przy blokach mieszkalnych', FALSE, TRUE),
(NULL, 'Dworzec PKS', 'Dworzec autobusowy', FALSE, TRUE),
(NULL, 'Lotnisko', 'Terminal pasażerski', TRUE, TRUE),
(NULL, 'Nowy Świat', 'Ulica handlowa', FALSE, TRUE),
(NULL, 'Stare Miasto', 'Rynek staromiejski', FALSE, TRUE),
(NULL, 'Politechnika', 'Kampus uniwersytecki', FALSE, TRUE);

-- ============================================
-- 13. STANOWISKA (SŁUPKI)
-- ============================================
INSERT INTO platforms (stop_id, platform_number, platform_type, description, active) VALUES
-- Dworzec Główny
(1, '01', 'regular', 'Perón 1 - linie miejskie', TRUE),
(1, '02', 'regular', 'Perón 2 - linie podmiejskie', TRUE),
-- Plac Wolności
(2, 'A', 'loop', 'Pętla autobusowa', TRUE),
-- Osiedle Północne
(3, '01', 'regular', 'Przystanek przystankowy', TRUE),
-- Dworzec PKS
(4, '01', 'regular', 'Przy wejściu głównym', TRUE),
(4, '02', 'regular', 'Zatoka autobusowa', TRUE),
-- Lotnisko
(5, 'T1', 'regular', 'Terminal 1', TRUE),
-- Nowy Świat
(6, '01', 'regular', 'Kierunek północ', TRUE),
(6, '02', 'regular', 'Kierunek południe', TRUE),
-- Stare Miasto
(7, '01', 'regular', 'Przy rynku', TRUE),
-- Politechnika
(8, '01', 'loop', 'Pętla tramwajowa', TRUE);

-- ============================================
-- 14. BRYGADY
-- ============================================
INSERT INTO brigades (line_id, brigade_number, shift_a_start, shift_a_end, default_vehicle_type, description, active) VALUES
(1, '1', '04:10', '13:53', 'bus', 'Pierwsza brygada linii 1 - poranna zmiana', TRUE),
(1, '2', '14:10', '23:57', 'bus', 'Druga brygada linii 1 - popoÅudniowa zmiana', TRUE),
(2, '1', '05:00', '14:30', 'bus', 'Pierwsza brygada linii 5', TRUE),
(2, '2', '14:30', '23:00', 'articulated_bus', 'Druga brygada linii 5 - autobus przegubowy', TRUE),
(3, '1', '05:15', '14:45', 'tram', 'Pierwsza brygada tramwajowa linii 7', TRUE),
(3, '2', '14:45', '23:15', 'tram', 'Druga brygada tramwajowa linii 7', TRUE);

-- ============================================
-- 15. LOGI LOGOWANIA (przykładowe)
-- ============================================
INSERT INTO login_logs (user_id, login_time, ip_address, user_agent, success) VALUES
(1, CURRENT_TIMESTAMP - INTERVAL '1 hour', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', TRUE),
(1, CURRENT_TIMESTAMP - INTERVAL '2 hours', '192.168.1.101', 'Mozilla/5.0 (Linux; Android 10)', TRUE),
(1, CURRENT_TIMESTAMP - INTERVAL '30 minutes', '192.168.1.102', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', TRUE),
(1, CURRENT_TIMESTAMP - INTERVAL '1 day', '192.168.1.101', 'Mozilla/5.0 (Linux; Android 10)', FALSE);

-- ============================================
-- PODSUMOWANIE DANYCH TESTOWYCH
-- ============================================
-- Użytkownicy:
--   realcar / Gliwice2010!Zajezdnia123 (Administrator)
--
-- Pojazdy: 4 (3 dostępne, 1 w serwisie)
-- Linie: 3 (2 autobusowe, 1 tramwajowa)
-- Stanowiska: 12 (z różnymi limitami)
-- ============================================
