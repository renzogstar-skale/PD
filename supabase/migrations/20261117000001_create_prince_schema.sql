-- =========================================================================
-- PRINCE DISTRIBUTORS — MIGRACIÓN 001: ESQUEMA CENTRAL POSTGRESQL (SUPABASE)
-- Multi-Tenancy, Estados Unidos (Florida), Moneda USD ($), Roles & Módulos WFM
-- Version: 20261117000001
-- Reversible: Ver 20261117000006_rollback.sql
-- =========================================================================

-- Extensiones requeridas
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- 1. TIPOS ENUMERADOS
DO $$ BEGIN
    CREATE TYPE prince_user_role AS ENUM ('admin', 'supervisor', 'sales_rep', 'merchandiser');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$ BEGIN
    CREATE TYPE prince_task_priority AS ENUM ('low', 'normal', 'high', 'urgent');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$ BEGIN
    CREATE TYPE prince_task_status AS ENUM ('pending', 'in_progress', 'completed', 'cancelled');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$ BEGIN
    CREATE TYPE prince_shift_type AS ENUM ('standard', 'opening', 'mid', 'closing', 'rest', 'vacation', 'absence');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$ BEGIN
    CREATE TYPE prince_attendance_status AS ENUM ('present', 'in_progress', 'late', 'completed', 'absent', 'excused');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$ BEGIN
    CREATE TYPE prince_movement_type AS ENUM ('restock_shelf', 'shrinkage', 'receipt', 'adjustment', 'transfer');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

-- 2. ORGANIZACIÓN (TENANT)
CREATE TABLE IF NOT EXISTS prince_organizations (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    legal_name VARCHAR(255),
    tax_id VARCHAR(50) NOT NULL UNIQUE,
    country VARCHAR(100) DEFAULT 'United States',
    state VARCHAR(100) DEFAULT 'Florida',
    city VARCHAR(100) DEFAULT 'Hallandale Beach',
    currency VARCHAR(10) DEFAULT 'USD',
    timezone VARCHAR(100) DEFAULT 'America/New_York',
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 3. SUCURSALES / ESTABLECIMIENTOS (BRANCHES)
CREATE TABLE IF NOT EXISTS prince_branches (
    id SERIAL PRIMARY KEY,
    organization_id INT NOT NULL REFERENCES prince_organizations(id) ON DELETE CASCADE,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    territory VARCHAR(100) NOT NULL DEFAULT 'Broward South',
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL DEFAULT 'Hallandale Beach',
    state VARCHAR(50) NOT NULL DEFAULT 'FL',
    zip_code VARCHAR(20) DEFAULT '33009',
    weekly_labor_budget NUMERIC(12,2) DEFAULT 3200.00,
    min_coverage_staff INT DEFAULT 3,
    sales_forecast NUMERIC(12,2) DEFAULT 48500.00,
    is_demo BOOLEAN DEFAULT FALSE,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 4. EMPLEADOS OPERACIONALES & COMERCIALES
CREATE TABLE IF NOT EXISTS prince_employees (
    id SERIAL PRIMARY KEY,
    organization_id INT NOT NULL REFERENCES prince_organizations(id) ON DELETE CASCADE,
    auth_user_id UUID REFERENCES auth.users(id) ON DELETE SET NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    role prince_user_role NOT NULL DEFAULT 'merchandiser',
    role_label VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(50),
    contract_type VARCHAR(50) DEFAULT 'full_time',
    contracted_hours NUMERIC(6,2) DEFAULT 48.0,
    hourly_cost NUMERIC(10,2) DEFAULT 16.50,
    supervisor_id INT REFERENCES prince_employees(id) ON DELETE SET NULL,
    branch_id INT NOT NULL REFERENCES prince_branches(id) ON DELETE CASCADE,
    territory VARCHAR(100) DEFAULT 'Broward South',
    active BOOLEAN DEFAULT TRUE,
    is_demo BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 5. CATÁLOGO DE PRODUCTOS (CON CÓDIGOS UPC AUTÉNTICOS)
CREATE TABLE IF NOT EXISTS prince_products (
    id SERIAL PRIMARY KEY,
    organization_id INT NOT NULL REFERENCES prince_organizations(id) ON DELETE CASCADE,
    upc VARCHAR(50) NOT NULL UNIQUE,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    lot VARCHAR(50) NOT NULL,
    expiration_date DATE NOT NULL,
    unit_price NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    unit_cost NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    currency VARCHAR(10) NOT NULL DEFAULT 'USD',
    is_demo BOOLEAN DEFAULT FALSE,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 6. INVENTARIO POR SUCURSAL (BODEGA VS GÓNDOLA FEFO)
CREATE TABLE IF NOT EXISTS prince_inventory (
    id SERIAL PRIMARY KEY,
    branch_id INT NOT NULL REFERENCES prince_branches(id) ON DELETE CASCADE,
    product_id INT NOT NULL REFERENCES prince_products(id) ON DELETE CASCADE,
    warehouse_stock INT NOT NULL DEFAULT 0 CHECK (warehouse_stock >= 0),
    shelf_stock INT NOT NULL DEFAULT 0 CHECK (shelf_stock >= 0),
    shelf_capacity INT NOT NULL DEFAULT 50,
    min_stock_alert INT NOT NULL DEFAULT 15,
    is_demo BOOLEAN DEFAULT FALSE,
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    CONSTRAINT uq_branch_product UNIQUE (branch_id, product_id)
);

-- 7. REGISTRO INMUTABLE DE MOVIMIENTOS DE INVENTARIO
CREATE TABLE IF NOT EXISTS prince_inventory_movements (
    id BIGSERIAL PRIMARY KEY,
    uuid UUID DEFAULT uuid_generate_v4() UNIQUE,
    branch_id INT NOT NULL REFERENCES prince_branches(id) ON DELETE CASCADE,
    product_id INT NOT NULL REFERENCES prince_products(id) ON DELETE CASCADE,
    employee_id INT REFERENCES prince_employees(id) ON DELETE SET NULL,
    movement_type prince_movement_type NOT NULL,
    quantity INT NOT NULL,
    prev_warehouse_stock INT NOT NULL,
    new_warehouse_stock INT NOT NULL,
    prev_shelf_stock INT NOT NULL,
    new_shelf_stock INT NOT NULL,
    lot VARCHAR(50),
    reason TEXT,
    is_demo BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 8. PROGRAMACIÓN SEMANAL DE PERSONAL (MASTER SCHEDULE)
CREATE TABLE IF NOT EXISTS prince_schedules (
    id SERIAL PRIMARY KEY,
    branch_id INT NOT NULL REFERENCES prince_branches(id) ON DELETE CASCADE,
    week_code VARCHAR(20) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'published',
    version INT NOT NULL DEFAULT 1,
    forecasted_sales NUMERIC(12,2) DEFAULT 48500.00,
    recommended_hours NUMERIC(8,2) DEFAULT 340.0,
    scheduled_hours NUMERIC(8,2) DEFAULT 336.0,
    estimated_labor_cost NUMERIC(12,2) DEFAULT 2520.00,
    labor_budget NUMERIC(12,2) DEFAULT 3200.00,
    coverage_score_pct NUMERIC(5,2) DEFAULT 98.8,
    published_by VARCHAR(255) DEFAULT 'Carlos Mendoza',
    published_at TIMESTAMPTZ DEFAULT NOW(),
    is_demo BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    CONSTRAINT uq_branch_week UNIQUE (branch_id, week_code)
);

-- 9. TURNOS ASIGNADOS
CREATE TABLE IF NOT EXISTS prince_shifts (
    id SERIAL PRIMARY KEY,
    schedule_id INT NOT NULL REFERENCES prince_schedules(id) ON DELETE CASCADE,
    employee_id INT NOT NULL REFERENCES prince_employees(id) ON DELETE CASCADE,
    branch_id INT NOT NULL REFERENCES prince_branches(id) ON DELETE CASCADE,
    shift_date DATE NOT NULL,
    day_of_week INT NOT NULL CHECK (day_of_week BETWEEN 1 AND 7),
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    break_minutes INT NOT NULL DEFAULT 30,
    net_hours NUMERIC(5,2) NOT NULL DEFAULT 8.0,
    shift_type prince_shift_type NOT NULL DEFAULT 'standard',
    shift_label VARCHAR(100) DEFAULT 'Turno Regular',
    task_notes TEXT,
    color_hex VARCHAR(20) DEFAULT '#00A859',
    is_demo BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 10. REGISTRO REAL DE ASISTENCIA & JORNADA (CLOCK IN / CLOCK OUT)
CREATE TABLE IF NOT EXISTS prince_attendance (
    id BIGSERIAL PRIMARY KEY,
    uuid UUID DEFAULT uuid_generate_v4() UNIQUE,
    employee_id INT NOT NULL REFERENCES prince_employees(id) ON DELETE CASCADE,
    branch_id INT NOT NULL REFERENCES prince_branches(id) ON DELETE CASCADE,
    work_date DATE NOT NULL DEFAULT CURRENT_DATE,
    scheduled_in TIME,
    scheduled_out TIME,
    clock_in TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    clock_out TIMESTAMPTZ,
    break_start TIMESTAMPTZ,
    break_end TIMESTAMPTZ,
    scheduled_hours NUMERIC(5,2) DEFAULT 8.0,
    worked_hours NUMERIC(5,2) DEFAULT 0.0,
    status prince_attendance_status NOT NULL DEFAULT 'in_progress',
    source VARCHAR(50) NOT NULL DEFAULT 'expo_go', -- 'expo_go', 'web', 'kiosk'
    ip_address VARCHAR(50) DEFAULT '127.0.0.1',
    notes TEXT,
    is_demo BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 11. TAREAS OPERATIVAS Y REPOSICIÓN
CREATE TABLE IF NOT EXISTS prince_tasks (
    id SERIAL PRIMARY KEY,
    uuid UUID DEFAULT uuid_generate_v4() UNIQUE,
    branch_id INT NOT NULL REFERENCES prince_branches(id) ON DELETE CASCADE,
    employee_id INT NOT NULL REFERENCES prince_employees(id) ON DELETE CASCADE,
    supervisor_id INT REFERENCES prince_employees(id) ON DELETE SET NULL,
    product_id INT REFERENCES prince_products(id) ON DELETE SET NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    priority prince_task_priority NOT NULL DEFAULT 'normal',
    status prince_task_status NOT NULL DEFAULT 'pending',
    date DATE NOT NULL DEFAULT CURRENT_DATE,
    notes TEXT,
    evidence_url TEXT,
    is_demo BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 12. AUDITORÍA INMUTABLE DEL SISTEMA
CREATE TABLE IF NOT EXISTS prince_audit_logs (
    id BIGSERIAL PRIMARY KEY,
    uuid UUID DEFAULT uuid_generate_v4() UNIQUE,
    branch_id INT REFERENCES prince_branches(id) ON DELETE SET NULL,
    employee_id INT REFERENCES prince_employees(id) ON DELETE SET NULL,
    user_name VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id VARCHAR(100),
    old_values JSONB,
    new_values JSONB,
    source VARCHAR(50) DEFAULT 'system',
    ip_address VARCHAR(50) DEFAULT '127.0.0.1',
    is_demo BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Índices de alto rendimiento
CREATE INDEX IF NOT EXISTS idx_employees_role ON prince_employees(role);
CREATE INDEX IF NOT EXISTS idx_employees_branch ON prince_employees(branch_id);
CREATE INDEX IF NOT EXISTS idx_inventory_product_branch ON prince_inventory(branch_id, product_id);
CREATE INDEX IF NOT EXISTS idx_products_upc ON prince_products(upc);
CREATE INDEX IF NOT EXISTS idx_shifts_emp_date ON prince_shifts(employee_id, shift_date);
CREATE INDEX IF NOT EXISTS idx_attendance_emp_date ON prince_attendance(employee_id, work_date);
CREATE INDEX IF NOT EXISTS idx_tasks_emp_status ON prince_tasks(employee_id, status);
CREATE INDEX IF NOT EXISTS idx_audit_created ON prince_audit_logs(created_at DESC);
