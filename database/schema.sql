-- ============================================
-- Panel Pracowniczy Firma KOT - Database Schema
-- PostgreSQL 14+
-- ============================================

-- Wyczyść istniejące obiekty
DROP TABLE IF EXISTS password_resets CASCADE;
DROP TABLE IF EXISTS audit_logs CASCADE;
DROP TABLE IF EXISTS login_logs CASCADE;
DROP TABLE IF EXISTS incidents CASCADE;
DROP TABLE IF EXISTS route_cards CASCADE;
DROP TABLE IF EXISTS schedules CASCADE;
DROP TABLE IF EXISTS route_stops CASCADE;
DROP TABLE IF EXISTS route_variants CASCADE;
DROP TABLE IF EXISTS brigades CASCADE;
DROP TABLE IF EXISTS platforms CASCADE;
DROP TABLE IF EXISTS stops CASCADE;
DROP TABLE IF EXISTS role_position_mapping CASCADE;
DROP TABLE IF EXISTS user_positions CASCADE;
DROP TABLE IF EXISTS user_roles CASCADE;
DROP TABLE IF EXISTS vehicles CASCADE;
DROP TABLE IF EXISTS lines CASCADE;
DROP TABLE IF EXISTS positions CASCADE;
DROP TABLE IF EXISTS roles CASCADE;
DROP TABLE IF EXISTS departments CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS sessions CASCADE;

DROP FUNCTION IF EXISTS check_position_limit() CASCADE;
DROP FUNCTION IF EXISTS update_updated_at() CASCADE;
DROP VIEW IF EXISTS v_user_roles CASCADE;
DROP VIEW IF EXISTS v_user_permissions CASCADE;

-- ============================================
-- 1. TABELE PODSTAWOWE
-- ============================================

-- Tabela użytkowników
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    discord_id VARCHAR(32) UNIQUE,
    roblox_id VARCHAR(64) UNIQUE,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela ról (RBAC)
CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    permissions JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela działów/departamentów
CREATE TABLE departments (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela stanowisk z limitami
CREATE TABLE positions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    department_id INT REFERENCES departments(id) ON DELETE SET NULL,
    max_count INT DEFAULT NULL,
    description TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Przypisanie użytkowników do ról
CREATE TABLE user_roles (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    role_id INT REFERENCES roles(id) ON DELETE CASCADE,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, role_id)
);

-- Przypisanie użytkowników do stanowisk
CREATE TABLE user_positions (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    position_id INT REFERENCES positions(id) ON DELETE CASCADE,
    assigned_date DATE DEFAULT CURRENT_DATE,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, position_id)
);

-- Mapowanie stanowisk do ról RBAC
CREATE TABLE role_position_mapping (
    id SERIAL PRIMARY KEY,
    role_id INT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    position_id INT NOT NULL REFERENCES positions(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(role_id, position_id)
);

-- ============================================
-- 2. TABELE TRANSPORTOWE
-- ============================================

-- Tabela linii komunikacyjnych
CREATE TABLE lines (
    id SERIAL PRIMARY KEY,
    line_number VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    route_description TEXT,
    line_type VARCHAR(20) DEFAULT 'bus', -- bus, tram, metro
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela pojazdów
CREATE TABLE vehicles (
    id SERIAL PRIMARY KEY,
    nr_poj VARCHAR(20) UNIQUE NOT NULL,
    reg_plate VARCHAR(20) UNIQUE,
    vehicle_type VARCHAR(50) NOT NULL CHECK (vehicle_type IN ('bus', 'tram', 'metro', 'tbus')),
    model VARCHAR(100),
    rok_prod INT,
    pojemnosc VARCHAR(10) CHECK (pojemnosc IN ('MINI', 'MIDI', 'MAXI', 'MAXI+', 'MEGA', 'MEGA+', 'GIGA')),
    status VARCHAR(20) DEFAULT 'sprawny' CHECK (status IN ('sprawny', 'w naprawie', 'odstawiony', 'zawieszony')),
    marka VARCHAR(25),
    pulpit VARCHAR(25),
    engine VARCHAR(50),
    gearbox VARCHAR(50),
    typ_napedu VARCHAR(20) CHECK (typ_napedu IN ('Diesel', 'CNG', 'Hybrydowy', 'Elektryczny', 'Wodorowy')),
    norma_spalania VARCHAR(10),
    klimatyzacja BOOLEAN DEFAULT FALSE,
    zajezdnia VARCHAR(10) CHECK (zajezdnia IN ('KM', 'KW', 'MC')),
    przewoznik VARCHAR(20) CHECK (przewoznik IN ('Ostrans', 'KujaTrans', 'Ostromunikacja')),
    opiekun_1 VARCHAR(20),
    opiekun_2 VARCHAR(20),
    dodatkowe_informacje VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela przystanków (fizycznych lokalizacji)
CREATE TABLE stops (
    id SERIAL PRIMARY KEY,
    stop_id VARCHAR(20) UNIQUE NOT NULL, -- Unikalny identyfikator (zgodny z SIL)
    name VARCHAR(100) NOT NULL,
    location_description TEXT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela stanowisk/słupków (konkretne miejsca postoju na przystanku)
CREATE TABLE platforms (
    id SERIAL PRIMARY KEY,
    stop_id INT NOT NULL REFERENCES stops(id) ON DELETE CASCADE,
    platform_number VARCHAR(10) NOT NULL, -- np. "01", "02", "A", "B"
    platform_type VARCHAR(20) DEFAULT 'regular', -- regular, loop, technical, on_demand
    description TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(stop_id, platform_number)
);

-- Tabela brygad (konkretne kursy dla linii)
CREATE TABLE brigades (
    id SERIAL PRIMARY KEY,
    line_id INT NOT NULL REFERENCES lines(id) ON DELETE CASCADE,
    brigade_number VARCHAR(20) NOT NULL, -- np. "105/1", "105/02"
    default_vehicle_type VARCHAR(50), -- preferowany typ pojazdu
    description TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(line_id, brigade_number)
);

-- Tabela wariantów tras (kierunki i odmiany tras dla linii)
CREATE TABLE route_variants (
    id SERIAL PRIMARY KEY,
    line_id INT NOT NULL REFERENCES lines(id) ON DELETE CASCADE,
    variant_name VARCHAR(100) NOT NULL, -- np. "Kierunek A->B", "Zjazd do zajezdni"
    variant_type VARCHAR(20) DEFAULT 'normal', -- normal, short, depot_entry, depot_exit
    direction VARCHAR(50), -- A->B, B->A
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela sekwencji przystanków na trasie (uporządkowana lista stanowisk dla wariantu)
CREATE TABLE route_stops (
    id SERIAL PRIMARY KEY,
    route_variant_id INT NOT NULL REFERENCES route_variants(id) ON DELETE CASCADE,
    platform_id INT NOT NULL REFERENCES platforms(id) ON DELETE CASCADE,
    stop_sequence INT NOT NULL, -- kolejność przystanku na trasie (1, 2, 3...)
    travel_time_minutes INT, -- czas przejazdu do tego przystanku od poprzedniego
    is_timing_point BOOLEAN DEFAULT FALSE, -- czy to punkt czasowy
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(route_variant_id, stop_sequence),
    UNIQUE(route_variant_id, platform_id) -- jeden słupek nie może występować dwa razy na tej samej trasie
);

-- Tabela grafików/rozkładów
CREATE TABLE schedules (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    vehicle_id INT REFERENCES vehicles(id) ON DELETE SET NULL,
    line_id INT REFERENCES lines(id) ON DELETE SET NULL,
    brigade_id INT REFERENCES brigades(id) ON DELETE SET NULL,
    schedule_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status VARCHAR(20) DEFAULT 'scheduled', -- scheduled, completed, cancelled
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela kart drogowych
CREATE TABLE route_cards (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    vehicle_id INT REFERENCES vehicles(id) ON DELETE SET NULL,
    line_id INT REFERENCES lines(id) ON DELETE SET NULL,
    route_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME,
    start_km INT NOT NULL,
    end_km INT,
    fuel_start DECIMAL(10,2),
    fuel_end DECIMAL(10,2),
    passengers_count INT DEFAULT 0,
    notes TEXT,
    status VARCHAR(20) DEFAULT 'in_progress', -- in_progress, completed
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 3. TABELE ZARZĄDZANIA I LOGOWANIA
-- ============================================

-- Tabela awarii/incydentów
CREATE TABLE incidents (
    id SERIAL PRIMARY KEY,
    reported_by INT REFERENCES users(id) ON DELETE SET NULL,
    vehicle_id INT REFERENCES vehicles(id) ON DELETE SET NULL,
    incident_type VARCHAR(50) NOT NULL, -- breakdown, accident, complaint, other
    severity VARCHAR(20) DEFAULT 'low', -- low, medium, high, critical
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    incident_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'open', -- open, in_progress, resolved, closed
    resolved_by INT REFERENCES users(id) ON DELETE SET NULL,
    resolved_at TIMESTAMP,
    resolution_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela logów logowania
CREATE TABLE login_logs (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    success BOOLEAN DEFAULT TRUE
);

-- Tabela logów audytowych
CREATE TABLE audit_logs (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSONB,
    new_values JSONB,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela resetów haseł
CREATE TABLE password_resets (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela sesji
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    data TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 4. FUNKCJE I TRIGGERY
-- ============================================

-- Funkcja automatycznej aktualizacji updated_at
CREATE OR REPLACE FUNCTION update_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Funkcja kontroli limitu stanowisk
CREATE OR REPLACE FUNCTION check_position_limit()
RETURNS TRIGGER AS $$
DECLARE
    current_count INT;
    max_allowed INT;
BEGIN
    -- Pobierz maksymalny limit dla stanowiska
    SELECT max_count INTO max_allowed 
    FROM positions 
    WHERE id = NEW.position_id;
    
    -- Jeśli brak limitu, pozwól na dodanie
    IF max_allowed IS NULL THEN 
        RETURN NEW; 
    END IF;
    
    -- Policz aktywne przypisania
    SELECT COUNT(*) INTO current_count 
    FROM user_positions 
    WHERE position_id = NEW.position_id 
    AND active = TRUE;
    
    -- Sprawdź czy limit został wyczerpany
    IF current_count >= max_allowed THEN
        RAISE EXCEPTION 'Limit stanowisk wyczerpany dla pozycji ID %', NEW.position_id;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 5. PRZYPISANIE TRIGGERÓW
-- ============================================

-- Trigger dla updated_at w tabelach
CREATE TRIGGER trigger_update_users_updated_at
    BEFORE UPDATE ON users
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER trigger_update_positions_updated_at
    BEFORE UPDATE ON positions
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER trigger_update_vehicles_updated_at
    BEFORE UPDATE ON vehicles
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER trigger_update_lines_updated_at
    BEFORE UPDATE ON lines
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER trigger_update_schedules_updated_at
    BEFORE UPDATE ON schedules
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER trigger_update_route_cards_updated_at
    BEFORE UPDATE ON route_cards
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER trigger_update_incidents_updated_at
    BEFORE UPDATE ON incidents
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER trigger_update_stops_updated_at
    BEFORE UPDATE ON stops
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER trigger_update_platforms_updated_at
    BEFORE UPDATE ON platforms
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER trigger_update_brigades_updated_at
    BEFORE UPDATE ON brigades
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER trigger_update_route_variants_updated_at
    BEFORE UPDATE ON route_variants
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at();

-- Trigger kontroli limitu stanowisk
CREATE TRIGGER trigger_check_position_limit
    BEFORE INSERT ON user_positions
    FOR EACH ROW
    EXECUTE FUNCTION check_position_limit();

-- ============================================
-- 6. WIDOKI SQL
-- ============================================

-- Widok ról użytkowników z szczegółami
CREATE OR REPLACE VIEW v_user_roles AS
SELECT 
    u.id as user_id,
    u.username,
    u.email,
    u.first_name,
    u.last_name,
    r.id as role_id,
    r.name as role_name,
    r.description as role_description,
    r.permissions,
    ur.assigned_date
FROM users u
INNER JOIN user_roles ur ON u.id = ur.user_id
INNER JOIN roles r ON ur.role_id = r.id
WHERE u.active = TRUE;

-- Widok uprawnień użytkowników z stanowiskami
CREATE OR REPLACE VIEW v_user_permissions AS
SELECT 
    u.id as user_id,
    u.username,
    u.email,
    u.first_name,
    u.last_name,
    u.active,
    COALESCE(
        json_agg(
            json_build_object(
                'role_id', r.id,
                'role_name', r.name,
                'permissions', r.permissions
            )
        ) FILTER (WHERE r.id IS NOT NULL),
        '[]'::json
    ) as roles,
    COALESCE(
        json_agg(
            DISTINCT json_build_object(
                'position_id', p.id,
                'position_name', p.name,
                'department_id', d.id,
                'department_name', d.name
            )
        ) FILTER (WHERE p.id IS NOT NULL),
        '[]'::json
    ) as positions
FROM users u
LEFT JOIN user_roles ur ON u.id = ur.user_id
LEFT JOIN roles r ON ur.role_id = r.id
LEFT JOIN user_positions up ON u.id = up.user_id AND up.active = TRUE
LEFT JOIN positions p ON up.position_id = p.id AND p.active = TRUE
LEFT JOIN departments d ON p.department_id = d.id
GROUP BY u.id, u.username, u.email, u.first_name, u.last_name, u.active;

-- ============================================
-- 7. INDEKSY DLA WYDAJNOŚCI
-- ============================================

-- Indeksy dla users
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_active ON users(active);

-- Indeksy dla user_roles i user_positions
CREATE INDEX idx_user_roles_user_id ON user_roles(user_id);
CREATE INDEX idx_user_roles_role_id ON user_roles(role_id);
CREATE INDEX idx_user_positions_user_id ON user_positions(user_id);
CREATE INDEX idx_user_positions_position_id ON user_positions(position_id);
CREATE INDEX idx_user_positions_active ON user_positions(active);

-- Indeksy dla schedules
CREATE INDEX idx_schedules_user_id ON schedules(user_id);
CREATE INDEX idx_schedules_date ON schedules(schedule_date);
CREATE INDEX idx_schedules_status ON schedules(status);

-- Indeksy dla route_cards
CREATE INDEX idx_route_cards_user_id ON route_cards(user_id);
CREATE INDEX idx_route_cards_date ON route_cards(route_date);
CREATE INDEX idx_route_cards_vehicle_id ON route_cards(vehicle_id);

-- Indeksy dla vehicles
CREATE INDEX idx_vehicles_status ON vehicles(status);
CREATE INDEX idx_vehicles_type ON vehicles(vehicle_type);

-- Indeksy dla lines
CREATE INDEX idx_lines_active ON lines(active);
CREATE INDEX idx_lines_type ON lines(line_type);

-- Indeksy dla incidents
CREATE INDEX idx_incidents_reported_by ON incidents(reported_by);
CREATE INDEX idx_incidents_vehicle_id ON incidents(vehicle_id);
CREATE INDEX idx_incidents_status ON incidents(status);
CREATE INDEX idx_incidents_date ON incidents(incident_date);

-- Indeksy dla login_logs
CREATE INDEX idx_login_logs_user_id ON login_logs(user_id);
CREATE INDEX idx_login_logs_login_time ON login_logs(login_time);

-- Indeksy dla audit_logs
CREATE INDEX idx_audit_logs_user_id ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_table_name ON audit_logs(table_name);
CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at);

-- Indeksy dla role_position_mapping
CREATE INDEX idx_rpm_role_id ON role_position_mapping(role_id);
CREATE INDEX idx_rpm_position_id ON role_position_mapping(position_id);

-- Indeksy dla stops
CREATE INDEX idx_stops_stop_id ON stops(stop_id);
CREATE INDEX idx_stops_active ON stops(active);

-- Indeksy dla platforms
CREATE INDEX idx_platforms_stop_id ON platforms(stop_id);
CREATE INDEX idx_platforms_active ON platforms(active);

-- Indeksy dla brigades
CREATE INDEX idx_brigades_line_id ON brigades(line_id);
CREATE INDEX idx_brigades_active ON brigades(active);

-- Indeksy dla route_variants
CREATE INDEX idx_route_variants_line_id ON route_variants(line_id);
CREATE INDEX idx_route_variants_active ON route_variants(is_active);

-- Indeksy dla route_stops
CREATE INDEX idx_route_stops_variant_id ON route_stops(route_variant_id);
CREATE INDEX idx_route_stops_platform_id ON route_stops(platform_id);
CREATE INDEX idx_route_stops_sequence ON route_stops(route_variant_id, stop_sequence);

-- ============================================
-- KONIEC SCHEMATU
-- ============================================
