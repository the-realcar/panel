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
    vehicle_number VARCHAR(20) UNIQUE NOT NULL,
    registration_plate VARCHAR(20) UNIQUE,
    vehicle_type VARCHAR(50) NOT NULL, -- bus, tram, metro
    model VARCHAR(100),
    manufacture_year INT,
    capacity INT,
    status VARCHAR(20) DEFAULT 'available', -- available, in_use, maintenance, broken
    last_inspection DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela grafików/rozkładów
CREATE TABLE schedules (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    vehicle_id INT REFERENCES vehicles(id) ON DELETE SET NULL,
    line_id INT REFERENCES lines(id) ON DELETE SET NULL,
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

-- ============================================
-- KONIEC SCHEMATU
-- ============================================
