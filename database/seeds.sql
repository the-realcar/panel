-- ============================================
-- Panel Pracowniczy Firma KOT - Test Data Seeds
-- ============================================

-- Wyczyść istniejące dane
TRUNCATE TABLE password_resets, audit_logs, login_logs, incidents, route_cards, schedules, 
    user_positions, user_roles, vehicles, lines, positions, roles, departments, users, sessions CASCADE;

-- Reset sekwencji
ALTER SEQUENCE users_id_seq RESTART WITH 1;
ALTER SEQUENCE roles_id_seq RESTART WITH 1;
ALTER SEQUENCE departments_id_seq RESTART WITH 1;
ALTER SEQUENCE positions_id_seq RESTART WITH 1;
ALTER SEQUENCE lines_id_seq RESTART WITH 1;
ALTER SEQUENCE vehicles_id_seq RESTART WITH 1;
ALTER SEQUENCE schedules_id_seq RESTART WITH 1;
ALTER SEQUENCE route_cards_id_seq RESTART WITH 1;
ALTER SEQUENCE incidents_id_seq RESTART WITH 1;

-- ============================================
-- 1. DEPARTAMENTY
-- ============================================
INSERT INTO departments (name, description, active) VALUES
('Zarząd', 'Zarząd Firmy KOT', TRUE),
('Administracja', 'Dział Administracyjny', TRUE),
('Transport', 'Dział Transportu i Kierowców', TRUE),
('Dyspozytornia', 'Dział Dyspozytorów', TRUE),
('Zajezdnia', 'Dział Techniczny i Zajezdnia', TRUE);

-- ============================================
-- 2. ROLE (RBAC)
-- ============================================
INSERT INTO roles (name, description, permissions) VALUES
('Administrator', 'Pełny dostęp do systemu', 
    '{"users": ["read", "create", "update", "delete"], "vehicles": ["read", "create", "update", "delete"], "lines": ["read", "create", "update", "delete"], "positions": ["read", "create", "update", "delete"], "incidents": ["read", "create", "update", "delete", "resolve"], "schedules": ["read", "create", "update", "delete"], "reports": ["read", "create"]}'::jsonb),
('Dyspozytor', 'Zarządzanie grafikami i kontrola ruchu',
    '{"schedules": ["read", "create", "update", "delete"], "vehicles": ["read", "update"], "lines": ["read"], "route_cards": ["read"], "incidents": ["read", "update"]}'::jsonb),
('Kierowca', 'Dostęp do własnego grafiku i kart drogowych',
    '{"schedules": ["read"], "route_cards": ["read", "create", "update"], "incidents": ["read", "create"], "vehicles": ["read"]}'::jsonb),
('Zarząd', 'Dostęp do raportów i zarządzania',
    '{"users": ["read"], "vehicles": ["read"], "lines": ["read"], "positions": ["read"], "incidents": ["read"], "schedules": ["read"], "reports": ["read", "create"]}'::jsonb);

-- ============================================
-- 3. UŻYTKOWNICY
-- Hasło dla wszystkich: "password123"
-- Hash bcrypt: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- ============================================
INSERT INTO users (username, email, password_hash, first_name, last_name, active) VALUES
('admin', 'admin@firmakot.pl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jan', 'Kowalski', TRUE),
('kierowca1', 'jan.nowak@firmakot.pl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jan', 'Nowak', TRUE),
('dyspozytor1', 'anna.wisniewska@firmakot.pl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Anna', 'Wiśniewska', TRUE);

-- ============================================
-- 4. PRZYPISANIE RÓL DO UŻYTKOWNIKÓW
-- ============================================
INSERT INTO user_roles (user_id, role_id) VALUES
(1, 1), -- admin -> Administrator
(2, 3), -- kierowca1 -> Kierowca
(3, 2); -- dyspozytor1 -> Dyspozytor

-- ============================================
-- 5. STANOWISKA
-- ============================================
INSERT INTO positions (name, department_id, max_count, description, active) VALUES
-- Zarząd
('Zarząd KOT', 1, 3, 'Członek Zarządu Firmy KOT', TRUE),
('Główny Inspektor', 1, 1, 'Główny Inspektor nadzorujący całość operacji', TRUE),

-- Administracja
('Główny Administrator', 2, 1, 'Główny Administrator Systemu', TRUE),
('Starszy Administrator', 2, 5, 'Starszy Administrator', TRUE),

-- Transport
('Starszy Kierowca', 3, 10, 'Doświadczony kierowca z uprawnieniami', TRUE),
('Kierowca', 3, NULL, 'Kierowca autobusów', TRUE),
('Motorniczy', 3, NULL, 'Motorniczy tramwajów', TRUE),

-- Dyspozytornia
('Dyspozytor Główny', 4, 1, 'Główny Dyspozytor', TRUE),
('Starszy Dyspozytor', 4, 5, 'Starszy Dyspozytor', TRUE),
('Dyspozytor', 4, 10, 'Dyspozytor', TRUE),

-- Zajezdnia
('Kierownik Zajezdni', 5, 1, 'Kierownik Zajezdni', TRUE),
('Mechanik', 5, NULL, 'Mechanik samochodowy', TRUE);

-- ============================================
-- 6. PRZYPISANIE STANOWISK DO UŻYTKOWNIKÓW
-- ============================================
INSERT INTO user_positions (user_id, position_id, active) VALUES
(1, 3, TRUE), -- admin -> Główny Administrator
(2, 6, TRUE), -- kierowca1 -> Kierowca
(3, 9, TRUE); -- dyspozytor1 -> Starszy Dyspozytor

-- ============================================
-- 7. LINIE KOMUNIKACYJNE
-- ============================================
INSERT INTO lines (line_number, name, route_description, line_type, active) VALUES
('1', 'Linia 1 - Centrum', 'Dworzec Główny - Plac Wolności - Osiedle Północne', 'bus', TRUE),
('5', 'Linia 5 - Lotnisko', 'Dworzec PKS - Rondo Niepodległości - Lotnisko', 'bus', TRUE),
('7', 'Linia 7 - Tramwaj', 'Nowy Świat - Stare Miasto - Politechnika', 'tram', TRUE);

-- ============================================
-- 8. POJAZDY
-- ============================================
INSERT INTO vehicles (vehicle_number, registration_plate, vehicle_type, model, manufacture_year, capacity, status, last_inspection) VALUES
('BUS-001', 'KR 12345', 'bus', 'Solaris Urbino 12', 2020, 90, 'available', '2025-12-01'),
('BUS-002', 'KR 23456', 'bus', 'MAN Lion''s City', 2019, 85, 'available', '2025-11-15'),
('TRAM-001', 'KR 98765', 'tram', 'Pesa Swing', 2021, 180, 'available', '2025-12-10'),
('BUS-003', 'KR 34567', 'bus', 'Mercedes-Benz Citaro', 2018, 80, 'maintenance', '2025-10-20');

-- ============================================
-- 9. GRAFIKI (SCHEDULES)
-- ============================================
INSERT INTO schedules (user_id, vehicle_id, line_id, schedule_date, start_time, end_time, status, notes) VALUES
-- Dzisiejsze grafiki dla kierowcy1
(2, 1, 1, CURRENT_DATE, '06:00:00', '14:00:00', 'scheduled', 'Poranna zmiana na linii 1'),
(2, 2, 2, CURRENT_DATE, '14:30:00', '22:30:00', 'scheduled', 'Popołudniowa zmiana na linii 5'),

-- Przyszłe grafiki
(2, 1, 1, CURRENT_DATE + 1, '06:00:00', '14:00:00', 'scheduled', 'Poranna zmiana'),
(2, 3, 3, CURRENT_DATE + 2, '07:00:00', '15:00:00', 'scheduled', 'Zmiana tramwajowa'),

-- Wczorajsze wykonane
(2, 1, 1, CURRENT_DATE - 1, '06:00:00', '14:00:00', 'completed', 'Wykonano zgodnie z planem');

-- ============================================
-- 10. KARTY DROGOWE (przykładowe)
-- ============================================
INSERT INTO route_cards (user_id, vehicle_id, line_id, route_date, start_time, end_time, start_km, end_km, fuel_start, fuel_end, passengers_count, status, notes) VALUES
(2, 1, 1, CURRENT_DATE - 1, '06:00:00', '14:00:00', 125000, 125280, 45.5, 21.3, 450, 'completed', 'Przejazd bez problemów'),
(2, 2, 2, CURRENT_DATE - 2, '14:30:00', '22:30:00', 98500, 98820, 38.2, 15.7, 380, 'completed', 'Zwiększony ruch w godzinach szczytu');

-- ============================================
-- 11. INCYDENTY/AWARIE
-- ============================================
INSERT INTO incidents (reported_by, vehicle_id, incident_type, severity, title, description, incident_date, status) VALUES
(2, 4, 'breakdown', 'high', 'Awaria klimatyzacji', 'Przestała działać klimatyzacja w autobusie BUS-003 podczas kursu na linii 1', CURRENT_TIMESTAMP - INTERVAL '2 days', 'resolved'),
(2, 1, 'complaint', 'low', 'Reklamacja pasażera', 'Pasażer złożył reklamację dotyczącą spóźnienia o 5 minut', CURRENT_TIMESTAMP - INTERVAL '1 day', 'in_progress'),
(2, 2, 'other', 'medium', 'Brak paliwa na stacji', 'Stacja paliwa była chwilowo niedostępna, tankowanie z opóźnieniem', CURRENT_TIMESTAMP - INTERVAL '3 hours', 'open');

-- ============================================
-- 12. LOGI LOGOWANIA (przykładowe)
-- ============================================
INSERT INTO login_logs (user_id, login_time, ip_address, user_agent, success) VALUES
(1, CURRENT_TIMESTAMP - INTERVAL '1 hour', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', TRUE),
(2, CURRENT_TIMESTAMP - INTERVAL '2 hours', '192.168.1.101', 'Mozilla/5.0 (Linux; Android 10)', TRUE),
(3, CURRENT_TIMESTAMP - INTERVAL '30 minutes', '192.168.1.102', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', TRUE),
(2, CURRENT_TIMESTAMP - INTERVAL '1 day', '192.168.1.101', 'Mozilla/5.0 (Linux; Android 10)', FALSE);

-- ============================================
-- PODSUMOWANIE DANYCH TESTOWYCH
-- ============================================
-- Użytkownicy:
--   admin / password123 (Administrator)
--   kierowca1 / password123 (Kierowca)
--   dyspozytor1 / password123 (Dyspozytor)
--
-- Pojazdy: 4 (3 dostępne, 1 w serwisie)
-- Linie: 3 (2 autobusowe, 1 tramwajowa)
-- Stanowiska: 12 (z różnymi limitami)
-- ============================================
