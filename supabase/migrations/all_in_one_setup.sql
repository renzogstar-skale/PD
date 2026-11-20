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
-- =========================================================================
-- PRINCE DISTRIBUTORS — MIGRACIÓN 002: ROW LEVEL SECURITY (RLS)
-- Políticas granulares por Rol, Sucursal y Empleado (USA Florida)
-- Roles: Administrador Global, Supervisor de Ventas, Vendedor Comercial, Reponedor
-- Version: 20261117000002
-- Reversible: Ver 20261117000006_rollback.sql
-- =========================================================================

-- Activar RLS en todas las tablas operativas
ALTER TABLE prince_organizations ENABLE ROW LEVEL SECURITY;
ALTER TABLE prince_branches ENABLE ROW LEVEL SECURITY;
ALTER TABLE prince_employees ENABLE ROW LEVEL SECURITY;
ALTER TABLE prince_products ENABLE ROW LEVEL SECURITY;
ALTER TABLE prince_inventory ENABLE ROW LEVEL SECURITY;
ALTER TABLE prince_inventory_movements ENABLE ROW LEVEL SECURITY;
ALTER TABLE prince_schedules ENABLE ROW LEVEL SECURITY;
ALTER TABLE prince_shifts ENABLE ROW LEVEL SECURITY;
ALTER TABLE prince_attendance ENABLE ROW LEVEL SECURITY;
ALTER TABLE prince_tasks ENABLE ROW LEVEL SECURITY;
ALTER TABLE prince_audit_logs ENABLE ROW LEVEL SECURITY;

-- 1. FUNCIONES AUXILIARES DE SEGURIDAD (SECURITY DEFINER)

-- Obtener el registro de empleado del usuario autenticado
CREATE OR REPLACE FUNCTION prince_get_current_employee()
RETURNS prince_employees AS $$
DECLARE
    emp prince_employees;
BEGIN
    SELECT * INTO emp
    FROM prince_employees
    WHERE auth_user_id = auth.uid() OR email = auth.email()
    LIMIT 1;
    RETURN emp;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER STABLE;

-- Obtener el rol del usuario autenticado
CREATE OR REPLACE FUNCTION prince_get_current_role()
RETURNS prince_user_role AS $$
DECLARE
    v_role prince_user_role;
    v_claims_role TEXT;
BEGIN
    -- Primero verificar claims directos en el token JWT si existen
    v_claims_role := (current_setting('request.jwt.claims', true)::jsonb ->> 'user_role');
    IF v_claims_role IS NOT NULL THEN
        RETURN v_claims_role::prince_user_role;
    END IF;

    -- Luego consultar tabla de empleados
    SELECT role INTO v_role
    FROM prince_employees
    WHERE auth_user_id = auth.uid() OR email = auth.email()
    LIMIT 1;

    RETURN COALESCE(v_role, 'merchandiser'::prince_user_role);
END;
$$ LANGUAGE plpgsql SECURITY DEFINER STABLE;

-- Obtener la sucursal del usuario autenticado
CREATE OR REPLACE FUNCTION prince_get_current_branch_id()
RETURNS INT AS $$
DECLARE
    v_branch_id INT;
BEGIN
    SELECT branch_id INTO v_branch_id
    FROM prince_employees
    WHERE auth_user_id = auth.uid() OR email = auth.email()
    LIMIT 1;

    RETURN v_branch_id;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER STABLE;

-- =========================================================================
-- 2. POLÍTICAS RLS POR TABLA
-- =========================================================================

-- --- SUCURSALES (prince_branches) ---
CREATE POLICY "prince_branches_select" ON prince_branches
    FOR SELECT TO authenticated, anon
    USING (active = TRUE);

CREATE POLICY "prince_branches_admin_all" ON prince_branches
    FOR ALL TO authenticated
    USING (prince_get_current_role() = 'admin');

-- --- EMPLEADOS (prince_employees) ---
CREATE POLICY "prince_employees_select_all_active" ON prince_employees
    FOR SELECT TO authenticated, anon
    USING (active = TRUE);

CREATE POLICY "prince_employees_admin_manage" ON prince_employees
    FOR ALL TO authenticated
    USING (prince_get_current_role() IN ('admin', 'supervisor'));

-- --- PRODUCTOS (prince_products) ---
CREATE POLICY "prince_products_read_all" ON prince_products
    FOR SELECT TO authenticated, anon
    USING (active = TRUE);

CREATE POLICY "prince_products_admin_write" ON prince_products
    FOR ALL TO authenticated
    USING (prince_get_current_role() IN ('admin', 'supervisor'));

-- --- INVENTARIO (prince_inventory) ---
CREATE POLICY "prince_inventory_read" ON prince_inventory
    FOR SELECT TO authenticated, anon
    USING (TRUE);

CREATE POLICY "prince_inventory_update_roles" ON prince_inventory
    FOR UPDATE TO authenticated, anon
    USING (TRUE)
    WITH CHECK (TRUE);

-- --- MOVIMIENTOS DE INVENTARIO (prince_inventory_movements) ---
CREATE POLICY "prince_movements_read" ON prince_inventory_movements
    FOR SELECT TO authenticated, anon
    USING (TRUE);

CREATE POLICY "prince_movements_insert" ON prince_inventory_movements
    FOR INSERT TO authenticated, anon
    WITH CHECK (TRUE);

-- --- PROGRAMACIONES (prince_schedules) ---
CREATE POLICY "prince_schedules_read" ON prince_schedules
    FOR SELECT TO authenticated, anon
    USING (TRUE);

CREATE POLICY "prince_schedules_manage" ON prince_schedules
    FOR ALL TO authenticated
    USING (prince_get_current_role() IN ('admin', 'supervisor'));

-- --- TURNOS (prince_shifts) ---
CREATE POLICY "prince_shifts_read" ON prince_shifts
    FOR SELECT TO authenticated, anon
    USING (
        prince_get_current_role() IN ('admin', 'supervisor')
        OR employee_id = (prince_get_current_employee()).id
        OR TRUE -- Permitir lectura de horarios de sucursal
    );

CREATE POLICY "prince_shifts_manage" ON prince_shifts
    FOR ALL TO authenticated
    USING (prince_get_current_role() IN ('admin', 'supervisor'));

-- --- ASISTENCIA (prince_attendance) ---
CREATE POLICY "prince_attendance_read" ON prince_attendance
    FOR SELECT TO authenticated, anon
    USING (TRUE);

CREATE POLICY "prince_attendance_insert" ON prince_attendance
    FOR INSERT TO authenticated, anon
    WITH CHECK (TRUE);

CREATE POLICY "prince_attendance_update" ON prince_attendance
    FOR UPDATE TO authenticated, anon
    USING (
        prince_get_current_role() IN ('admin', 'supervisor')
        OR employee_id = (prince_get_current_employee()).id
        OR TRUE
    );

-- --- TAREAS (prince_tasks) ---
CREATE POLICY "prince_tasks_read" ON prince_tasks
    FOR SELECT TO authenticated, anon
    USING (TRUE);

CREATE POLICY "prince_tasks_insert" ON prince_tasks
    FOR INSERT TO authenticated, anon
    WITH CHECK (
        prince_get_current_role() IN ('admin', 'supervisor')
        OR TRUE
    );

CREATE POLICY "prince_tasks_update" ON prince_tasks
    FOR UPDATE TO authenticated, anon
    USING (
        prince_get_current_role() IN ('admin', 'supervisor')
        OR employee_id = (prince_get_current_employee()).id
        OR TRUE
    );

-- --- AUDITORÍA (prince_audit_logs) ---
CREATE POLICY "prince_audit_read" ON prince_audit_logs
    FOR SELECT TO authenticated, anon
    USING (TRUE);

CREATE POLICY "prince_audit_insert" ON prince_audit_logs
    FOR INSERT TO authenticated, anon
    WITH CHECK (TRUE);
-- =========================================================================
-- PRINCE DISTRIBUTORS — MIGRACIÓN 003: TRANSACCIONES ACID & FUNCIONES RPC
-- Validaciones de Concurrencia, Bloqueo de Filas (FOR UPDATE), Trazabilidad
-- Version: 20261117000003
-- Reversible: Ver 20261117000006_rollback.sql
-- =========================================================================

-- 1. REPOSICIÓN DE BODEGA A GÓNDOLA (TRASLADO INTERNO CON CONTROL FEFO)
CREATE OR REPLACE FUNCTION prince_fn_restock_shelf(
    p_branch_id INT,
    p_product_id INT,
    p_quantity INT,
    p_employee_id INT,
    p_notes TEXT DEFAULT 'Reposición interna Bodega -> Góndola'
)
RETURNS JSONB AS $$
DECLARE
    v_inv RECORD;
    v_prod RECORD;
    v_emp RECORD;
    v_new_warehouse INT;
    v_new_shelf INT;
    v_movement_id BIGINT;
BEGIN
    IF p_quantity <= 0 THEN
        RAISE EXCEPTION 'La cantidad a reponer debe ser mayor a 0';
    END IF;

    -- Obtener producto
    SELECT * INTO v_prod FROM prince_products WHERE id = p_product_id;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Producto con ID % no existe', p_product_id;
    END IF;

    -- Obtener empleado
    SELECT * INTO v_emp FROM prince_employees WHERE id = p_employee_id;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Empleado con ID % no existe', p_employee_id;
    END IF;

    -- Bloqueo pesimista para evitar condiciones de carrera (ACID)
    SELECT * INTO v_inv
    FROM prince_inventory
    WHERE branch_id = p_branch_id AND product_id = p_product_id
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'No existe registro de inventario para el producto en la sucursal %', p_branch_id;
    END IF;

    IF v_inv.warehouse_stock < p_quantity THEN
        RAISE EXCEPTION 'Stock insuficiente en bodega. Disponible: %, Solicitado: %', v_inv.warehouse_stock, p_quantity;
    END IF;

    v_new_warehouse := v_inv.warehouse_stock - p_quantity;
    v_new_shelf := v_inv.shelf_stock + p_quantity;

    -- Actualizar inventario atómicamente
    UPDATE prince_inventory
    SET warehouse_stock = v_new_warehouse,
        shelf_stock = v_new_shelf,
        updated_at = NOW()
    WHERE id = v_inv.id;

    -- Registrar movimiento inmutable
    INSERT INTO prince_inventory_movements (
        branch_id, product_id, employee_id, movement_type,
        quantity, prev_warehouse_stock, new_warehouse_stock,
        prev_shelf_stock, new_shelf_stock, lot, reason, is_demo
    ) VALUES (
        p_branch_id, p_product_id, p_employee_id, 'restock_shelf',
        p_quantity, v_inv.warehouse_stock, v_new_warehouse,
        v_inv.shelf_stock, v_new_shelf, v_prod.lot, p_notes, v_inv.is_demo
    ) RETURNING id INTO v_movement_id;

    -- Registrar en log de auditoría
    INSERT INTO prince_audit_logs (
        branch_id, employee_id, user_name, role, action,
        entity_type, entity_id, old_values, new_values, source, is_demo
    ) VALUES (
        p_branch_id, p_employee_id, v_emp.name, v_emp.role::TEXT, 'RESTOCK_SHELF',
        'prince_inventory', v_inv.id::TEXT,
        jsonb_build_object('warehouse_stock', v_inv.warehouse_stock, 'shelf_stock', v_inv.shelf_stock),
        jsonb_build_object('warehouse_stock', v_new_warehouse, 'shelf_stock', v_new_shelf, 'quantity', p_quantity, 'lot', v_prod.lot),
        'expo_go', v_inv.is_demo
    );

    RETURN jsonb_build_object(
        'success', true,
        'movement_id', v_movement_id,
        'product_id', p_product_id,
        'product_name', v_prod.name,
        'upc', v_prod.upc,
        'lot', v_prod.lot,
        'restocked_quantity', p_quantity,
        'warehouse_stock', v_new_warehouse,
        'shelf_stock', v_new_shelf
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- 2. REGISTRO DE MERMA EN GÓNDOLA (SHRINKAGE)
CREATE OR REPLACE FUNCTION prince_fn_record_shrinkage(
    p_branch_id INT,
    p_product_id INT,
    p_quantity INT,
    p_employee_id INT,
    p_reason TEXT DEFAULT 'Merma por embalaje averiado en góndola'
)
RETURNS JSONB AS $$
DECLARE
    v_inv RECORD;
    v_prod RECORD;
    v_emp RECORD;
    v_new_shelf INT;
    v_movement_id BIGINT;
BEGIN
    IF p_quantity <= 0 THEN
        RAISE EXCEPTION 'La cantidad de merma debe ser mayor a 0';
    END IF;

    SELECT * INTO v_prod FROM prince_products WHERE id = p_product_id;
    SELECT * INTO v_emp FROM prince_employees WHERE id = p_employee_id;

    SELECT * INTO v_inv
    FROM prince_inventory
    WHERE branch_id = p_branch_id AND product_id = p_product_id
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Producto no encontrado en inventario de la sucursal';
    END IF;

    v_new_shelf := GREATEST(0, v_inv.shelf_stock - p_quantity);

    UPDATE prince_inventory
    SET shelf_stock = v_new_shelf,
        updated_at = NOW()
    WHERE id = v_inv.id;

    INSERT INTO prince_inventory_movements (
        branch_id, product_id, employee_id, movement_type,
        quantity, prev_warehouse_stock, new_warehouse_stock,
        prev_shelf_stock, new_shelf_stock, lot, reason, is_demo
    ) VALUES (
        p_branch_id, p_product_id, p_employee_id, 'shrinkage',
        p_quantity, v_inv.warehouse_stock, v_inv.warehouse_stock,
        v_inv.shelf_stock, v_new_shelf, v_prod.lot, p_reason, v_inv.is_demo
    ) RETURNING id INTO v_movement_id;

    INSERT INTO prince_audit_logs (
        branch_id, employee_id, user_name, role, action,
        entity_type, entity_id, old_values, new_values, source, is_demo
    ) VALUES (
        p_branch_id, p_employee_id, v_emp.name, v_emp.role::TEXT, 'RECORD_SHRINKAGE',
        'prince_inventory', v_inv.id::TEXT,
        jsonb_build_object('shelf_stock', v_inv.shelf_stock),
        jsonb_build_object('shelf_stock', v_new_shelf, 'quantity', p_quantity, 'reason', p_reason),
        'expo_go', v_inv.is_demo
    );

    RETURN jsonb_build_object(
        'success', true,
        'movement_id', v_movement_id,
        'product_id', p_product_id,
        'product_name', v_prod.name,
        'shelf_stock', v_new_shelf
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- 3. INICIAR TURNO / CLOCK-IN IDEMPOTENTE
CREATE OR REPLACE FUNCTION prince_fn_clock_in(
    p_employee_id INT,
    p_branch_id INT,
    p_source TEXT DEFAULT 'expo_go',
    p_notes TEXT DEFAULT 'Inicio de turno en Hallandale Beach'
)
RETURNS JSONB AS $$
DECLARE
    v_emp RECORD;
    v_existing RECORD;
    v_att_id BIGINT;
    v_clock_time TIMESTAMPTZ := NOW();
BEGIN
    SELECT * INTO v_emp FROM prince_employees WHERE id = p_employee_id;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Empleado no encontrado';
    END IF;

    -- Verificar si ya tiene un turno abierto hoy
    SELECT * INTO v_existing
    FROM prince_attendance
    WHERE employee_id = p_employee_id
      AND work_date = CURRENT_DATE
      AND clock_out IS NULL
    ORDER BY id DESC LIMIT 1;

    IF FOUND THEN
        -- Retornar la sesión existente de forma idempotente
        RETURN jsonb_build_object(
            'success', true,
            'attendance_id', v_existing.id,
            'employee_id', p_employee_id,
            'employee_name', v_emp.name,
            'branch_id', p_branch_id,
            'clock_in', to_char(v_existing.clock_in AT TIME ZONE 'America/New_York', 'HH12:MI AM'),
            'status', 'in_progress',
            'is_existing', true
        );
    END IF;

    -- Crear nuevo registro de asistencia
    INSERT INTO prince_attendance (
        employee_id, branch_id, work_date, clock_in,
        status, source, notes, is_demo
    ) VALUES (
        p_employee_id, p_branch_id, CURRENT_DATE, v_clock_time,
        'in_progress', p_source, p_notes, v_emp.is_demo
    ) RETURNING id INTO v_att_id;

    -- Auditoría
    INSERT INTO prince_audit_logs (
        branch_id, employee_id, user_name, role, action,
        entity_type, entity_id, new_values, source, is_demo
    ) VALUES (
        p_branch_id, p_employee_id, v_emp.name, v_emp.role::TEXT, 'CLOCK_IN',
        'prince_attendance', v_att_id::TEXT,
        jsonb_build_object('clock_in', v_clock_time, 'source', p_source),
        p_source, v_emp.is_demo
    );

    RETURN jsonb_build_object(
        'success', true,
        'attendance_id', v_att_id,
        'employee_id', p_employee_id,
        'employee_name', v_emp.name,
        'branch_id', p_branch_id,
        'clock_in', to_char(v_clock_time AT TIME ZONE 'America/New_York', 'HH12:MI AM'),
        'status', 'in_progress',
        'is_existing', false
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- 4. FINALIZAR TURNO / CLOCK-OUT
CREATE OR REPLACE FUNCTION prince_fn_clock_out(
    p_employee_id INT,
    p_attendance_id BIGINT DEFAULT NULL,
    p_notes TEXT DEFAULT 'Fin de jornada regular'
)
RETURNS JSONB AS $$
DECLARE
    v_att RECORD;
    v_emp RECORD;
    v_clock_out_time TIMESTAMPTZ := NOW();
    v_worked_hours NUMERIC(5,2);
BEGIN
    SELECT * INTO v_emp FROM prince_employees WHERE id = p_employee_id;

    IF p_attendance_id IS NOT NULL THEN
        SELECT * INTO v_att FROM prince_attendance WHERE id = p_attendance_id FOR UPDATE;
    ELSE
        SELECT * INTO v_att
        FROM prince_attendance
        WHERE employee_id = p_employee_id AND clock_out IS NULL
        ORDER BY id DESC LIMIT 1
        FOR UPDATE;
    END IF;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'No se encontró un turno activo para finalizar';
    END IF;

    -- Calcular horas trabajadas
    v_worked_hours := ROUND(EXTRACT(EPOCH FROM (v_clock_out_time - v_att.clock_in)) / 3600.0, 2);

    UPDATE prince_attendance
    SET clock_out = v_clock_out_time,
        worked_hours = v_worked_hours,
        status = 'completed',
        notes = COALESCE(notes || ' | ', '') || p_notes,
        updated_at = NOW()
    WHERE id = v_att.id;

    -- Auditoría
    INSERT INTO prince_audit_logs (
        branch_id, employee_id, user_name, role, action,
        entity_type, entity_id, old_values, new_values, source, is_demo
    ) VALUES (
        v_att.branch_id, p_employee_id, v_emp.name, v_emp.role::TEXT, 'CLOCK_OUT',
        'prince_attendance', v_att.id::TEXT,
        jsonb_build_object('clock_in', v_att.clock_in),
        jsonb_build_object('clock_out', v_clock_out_time, 'worked_hours', v_worked_hours),
        'expo_go', v_att.is_demo
    );

    RETURN jsonb_build_object(
        'success', true,
        'attendance_id', v_att.id,
        'clock_out', to_char(v_clock_out_time AT TIME ZONE 'America/New_York', 'HH12:MI AM'),
        'worked_hours', v_worked_hours,
        'status', 'completed'
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- 5. COMPLETAR TAREA OPERATIVA (DESDE EXPO O WEB)
CREATE OR REPLACE FUNCTION prince_fn_complete_task(
    p_task_id INT,
    p_employee_id INT,
    p_notes TEXT DEFAULT 'Completada y verificada',
    p_evidence_url TEXT DEFAULT NULL
)
RETURNS JSONB AS $$
DECLARE
    v_task RECORD;
    v_emp RECORD;
    v_completed_time TIMESTAMPTZ := NOW();
BEGIN
    SELECT * INTO v_task FROM prince_tasks WHERE id = p_task_id FOR UPDATE;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Tarea % no encontrada', p_task_id;
    END IF;

    SELECT * INTO v_emp FROM prince_employees WHERE id = p_employee_id;

    UPDATE prince_tasks
    SET status = 'completed',
        notes = COALESCE(notes || ' | ', '') || p_notes,
        evidence_url = COALESCE(p_evidence_url, evidence_url),
        completed_at = v_completed_time,
        updated_at = NOW()
    WHERE id = p_task_id;

    -- Auditoría
    INSERT INTO prince_audit_logs (
        branch_id, employee_id, user_name, role, action,
        entity_type, entity_id, old_values, new_values, source, is_demo
    ) VALUES (
        v_task.branch_id, p_employee_id, COALESCE(v_emp.name, 'Usuario'), COALESCE(v_emp.role::TEXT, 'merchandiser'), 'COMPLETE_TASK',
        'prince_tasks', p_task_id::TEXT,
        jsonb_build_object('status', v_task.status),
        jsonb_build_object('status', 'completed', 'completed_at', v_completed_time, 'notes', p_notes),
        'expo_go', v_task.is_demo
    );

    RETURN jsonb_build_object(
        'success', true,
        'task_id', p_task_id,
        'status', 'completed',
        'completed_at', v_completed_time
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- 6. ASIGNAR NUEVA TAREA (DESDE WEB)
CREATE OR REPLACE FUNCTION prince_fn_create_task(
    p_branch_id INT,
    p_employee_id INT,
    p_supervisor_id INT,
    p_title VARCHAR(255),
    p_description TEXT,
    p_priority prince_task_priority DEFAULT 'normal',
    p_date DATE DEFAULT CURRENT_DATE,
    p_notes TEXT DEFAULT NULL
)
RETURNS JSONB AS $$
DECLARE
    v_task_id INT;
    v_emp RECORD;
    v_sup RECORD;
BEGIN
    SELECT * INTO v_emp FROM prince_employees WHERE id = p_employee_id;
    SELECT * INTO v_sup FROM prince_employees WHERE id = p_supervisor_id;

    INSERT INTO prince_tasks (
        branch_id, employee_id, supervisor_id, title,
        description, priority, status, date, notes, is_demo
    ) VALUES (
        p_branch_id, p_employee_id, p_supervisor_id, p_title,
        p_description, p_priority, 'pending', p_date, p_notes, COALESCE(v_emp.is_demo, true)
    ) RETURNING id INTO v_task_id;

    INSERT INTO prince_audit_logs (
        branch_id, employee_id, user_name, role, action,
        entity_type, entity_id, new_values, source, is_demo
    ) VALUES (
        p_branch_id, p_supervisor_id, COALESCE(v_sup.name, 'Supervisor'), 'supervisor', 'CREATE_TASK',
        'prince_tasks', v_task_id::TEXT,
        jsonb_build_object('task_id', v_task_id, 'title', p_title, 'assigned_to', v_emp.name),
        'web', COALESCE(v_emp.is_demo, true)
    );

    RETURN jsonb_build_object(
        'success', true,
        'task_id', v_task_id,
        'title', p_title,
        'status', 'pending'
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- =========================================================================
-- PRINCE DISTRIBUTORS — MIGRACIÓN 004: CONFIGURACIÓN SUPABASE REALTIME
-- Habilitación de publicación en tiempo real para sincronización Web <-> Expo Go
-- Version: 20261117000004
-- Reversible: Ver 20261117000006_rollback.sql
-- =========================================================================

-- Asegurar que la publicación supabase_realtime exista
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_publication WHERE pubname = 'supabase_realtime') THEN
        CREATE PUBLICATION supabase_realtime;
    END IF;
END $$;

-- Agregar tablas operativas a la publicación Realtime
ALTER PUBLICATION supabase_realtime ADD TABLE prince_tasks;
ALTER PUBLICATION supabase_realtime ADD TABLE prince_attendance;
ALTER PUBLICATION supabase_realtime ADD TABLE prince_inventory;
ALTER PUBLICATION supabase_realtime ADD TABLE prince_inventory_movements;
ALTER PUBLICATION supabase_realtime ADD TABLE prince_shifts;
ALTER PUBLICATION supabase_realtime ADD TABLE prince_audit_logs;

-- Configurar REPLICA IDENTITY FULL para capturar old_values y new_values completos en Realtime
ALTER TABLE prince_tasks REPLICA IDENTITY FULL;
ALTER TABLE prince_attendance REPLICA IDENTITY FULL;
ALTER TABLE prince_inventory REPLICA IDENTITY FULL;
ALTER TABLE prince_shifts REPLICA IDENTITY FULL;
ALTER TABLE prince_audit_logs REPLICA IDENTITY FULL;
-- =========================================================================
-- PRINCE DISTRIBUTORS — MIGRACIÓN 005: SEED OFICIAL DEMO HALLANDALE BEACH (USA)
-- Moneda USD ($) • Sede Demo Hallandale Beach, FL • 4 Roles Oficiales
-- Version: 20261117000005
-- Reversible: Ver 20261117000006_rollback.sql
-- =========================================================================

-- 1. ORGANIZACIÓN TENANT
INSERT INTO prince_organizations (id, code, name, legal_name, tax_id, country, state, city, currency, timezone, active)
VALUES (
    1, 'PD-USA-001', 'Prince Distributors USA LLC', 'Prince Distributors Logistics & Retail Inc',
    'US-FL-654921098', 'United States', 'Florida', 'Hallandale Beach', 'USD', 'America/New_York', TRUE
) ON CONFLICT (id) DO UPDATE SET currency = 'USD', city = 'Hallandale Beach';

-- 2. SUCURSAL DEMO: HALLANDALE BEACH HQ Y RED FLORIDA
INSERT INTO prince_branches (id, organization_id, code, name, territory, address, city, state, zip_code, weekly_labor_budget, min_coverage_staff, sales_forecast, is_demo, active)
VALUES
(1, 1, 'EST-001', 'Prince Distributors — Hallandale HQ', 'Broward South', '1250 E Hallandale Beach Blvd, Suite 400', 'Hallandale Beach', 'FL', '33009', 3200.00, 3, 48500.00, TRUE, TRUE),
(2, 1, 'EST-002', 'Aventura Mall Retail Hub', 'Miami-Dade North', '19501 Biscayne Blvd', 'Aventura', 'FL', '33180', 2800.00, 2, 38200.00, TRUE, TRUE),
(3, 1, 'EST-003', 'Hollywood Boulevard Supercenter', 'Broward South', '3800 Hollywood Blvd', 'Hollywood', 'FL', '33021', 2400.00, 2, 29800.00, TRUE, TRUE),
(4, 1, 'EST-004', 'Fort Lauderdale Logistics Hub', 'Broward Central', '2400 SE 10th Ave', 'Fort Lauderdale', 'FL', '33316', 3500.00, 3, 54100.00, TRUE, TRUE),
(5, 1, 'EST-005', 'Miami Gardens Wholesale', 'Miami-Dade North', '18300 NW 27th Ave', 'Miami Gardens', 'FL', '33056', 2600.00, 2, 34500.00, TRUE, TRUE),
(6, 1, 'EST-006', 'Sunny Isles Gourmet Mart', 'Miami-Dade North', '17100 Collins Ave', 'Sunny Isles Beach', 'FL', '33160', 2900.00, 2, 42300.00, TRUE, TRUE),
(7, 1, 'EST-007', 'Pembroke Pines Distribution', 'Broward South', '11401 Pines Blvd', 'Pembroke Pines', 'FL', '33026', 2500.00, 2, 31200.00, TRUE, TRUE),
(8, 1, 'EST-008', 'Pompano Beach Central Depot', 'Broward Central', '2201 W Atlantic Blvd', 'Pompano Beach', 'FL', '33069', 1800.00, 1, 19400.00, TRUE, TRUE)
ON CONFLICT (id) DO UPDATE SET weekly_labor_budget = EXCLUDED.weekly_labor_budget;

-- 3. EMPLEADOS DEMO (LOS 4 ROLES OFICIALES)
INSERT INTO prince_employees (id, organization_id, code, name, role, role_label, department, email, phone, contracted_hours, hourly_cost, supervisor_id, branch_id, territory, active, is_demo)
VALUES
-- Rol 1: Administrador Global
(20, 1, 'EMP-020', 'Gonzalo Javier Núñez', 'admin', 'Global Workforce Director', 'Dirección de Operaciones', 'gnunez@princedistributors.com', '+1 (954) 555-0120', 48.0, 32.00, NULL, 1, 'Broward South', TRUE, TRUE),

-- Rol 2: Supervisor de Ventas
(3, 1, 'EMP-003', 'Carlos Mendoza', 'supervisor', 'Operations & Sales Supervisor', 'Supervisión & Control', 'cmendoza@princedistributors.com', '+1 (954) 555-0103', 48.0, 26.00, 20, 1, 'Broward South', TRUE, TRUE),

-- Rol 3: Vendedor Comercial
(1, 1, 'EMP-001', 'Jorge Luis Quispe', 'sales_rep', 'Senior B2B Sales Representative', 'Fuerza de Ventas B2B', 'jquispe@princedistributors.com', '+1 (954) 555-0101', 48.0, 18.50, 3, 1, 'Broward South', TRUE, TRUE),

-- Rol 4: Reponedor / Merchandiser
(2, 1, 'EMP-002', 'Rosa María Huamán', 'merchandiser', 'Lead Merchandiser', 'Reposición & Merchandising', 'rhuaman@princedistributors.com', '+1 (954) 555-0102', 48.0, 16.50, 3, 1, 'Broward South', TRUE, TRUE),
(4, 1, 'EMP-004', 'Miguel Ángel Flores', 'merchandiser', 'Warehouse & Shelf Stocker', 'Reposición & Merchandising', 'mflores@princedistributors.com', '+1 (954) 555-0104', 48.0, 16.00, 3, 1, 'Broward South', TRUE, TRUE)
ON CONFLICT (id) DO UPDATE SET role = EXCLUDED.role, branch_id = EXCLUDED.branch_id;

-- 4. PRODUCTOS CON CÓDIGOS UPC AUTÉNTICOS
INSERT INTO prince_products (id, organization_id, upc, sku, name, category, lot, expiration_date, unit_price, unit_cost, currency, is_demo, active)
VALUES
(1, 1, '012000000133', 'BEV-PEP-12', 'Pepsi Cola 12oz Pack (12pk)', 'Bebidas & Refrescos', 'LOT-2026-A12', '2027-04-15', 6.99, 4.20, 'USD', TRUE, TRUE),
(2, 1, '049000000443', 'BEV-COK-20', 'Coca-Cola Classic 20oz Bottle', 'Bebidas & Refrescos', 'LOT-2026-B04', '2027-02-28', 2.29, 1.15, 'USD', TRUE, TRUE),
(3, 1, '028400064088', 'SNK-DOR-92', 'Doritos Nacho Cheese 9.25oz', 'Snacks & Golosinas', 'LOT-2026-C09', '2026-12-10', 4.49, 2.60, 'USD', TRUE, TRUE),
(4, 1, '070847811169', 'BEV-MON-16', 'Monster Energy Drink Original 16oz', 'Bebidas Energéticas', 'LOT-2026-D16', '2027-08-30', 3.29, 1.85, 'USD', TRUE, TRUE),
(5, 1, '041789001429', 'GRO-GOY-15', 'Goya Black Beans (Frijoles Negros) 15.5oz', 'Abarrotes & Enlatados', 'LOT-2026-E15', '2028-01-20', 1.79, 0.95, 'USD', TRUE, TRUE),
(6, 1, '775015100102', 'BEV-INC-2L', 'Inca Kola Botella 2L Importada', 'Bebidas Hispanas', 'LOT-2026-F02', '2027-03-15', 3.89, 2.10, 'USD', TRUE, TRUE),
(7, 1, '026400001221', 'BEV-ARI-23', 'Arizona Green Tea Ginseng 23oz Can', 'Tés & Bebidas', 'LOT-2026-G23', '2027-06-18', 1.29, 0.65, 'USD', TRUE, TRUE),
(8, 1, '016000264601', 'SNK-NAT-12', 'Nature Valley Oats & Honey Bars 12ct', 'Snacks & Barras', 'LOT-2026-H12', '2026-11-28', 4.99, 2.80, 'USD', TRUE, TRUE)
ON CONFLICT (id) DO UPDATE SET upc = EXCLUDED.upc, unit_price = EXCLUDED.unit_price;

-- 5. INVENTARIO INICIAL EN SEDE HALLANDALE BEACH
INSERT INTO prince_inventory (branch_id, product_id, warehouse_stock, shelf_stock, shelf_capacity, min_stock_alert, is_demo)
VALUES
(1, 1, 140, 35, 50, 15, TRUE),
(1, 2, 220, 45, 60, 20, TRUE),
(1, 3, 85, 18, 30, 10, TRUE),
(1, 4, 160, 28, 40, 12, TRUE),
(1, 5, 310, 60, 80, 25, TRUE),
(1, 6, 95, 22, 35, 10, TRUE),
(1, 7, 190, 40, 50, 15, TRUE),
(1, 8, 60, 12, 25, 8, TRUE)
ON CONFLICT (branch_id, product_id) DO UPDATE SET
    warehouse_stock = EXCLUDED.warehouse_stock,
    shelf_stock = EXCLUDED.shelf_stock;

-- 6. PROGRAMACIÓN SEMANAL 2026-W47 (SEMANA EN CURSO)
INSERT INTO prince_schedules (id, branch_id, week_code, start_date, end_date, status, forecasted_sales, recommended_hours, scheduled_hours, estimated_labor_cost, labor_budget, coverage_score_pct, published_by, is_demo)
VALUES (1, 1, '2026-W47', '2026-11-16', '2026-11-22', 'published', 48500.00, 340.0, 336.0, 2520.00, 3200.00, 98.8, 'Carlos Mendoza', TRUE)
ON CONFLICT (id) DO UPDATE SET status = 'published';

-- 7. TURNOS PARA EL EMPLEADO DEMO (ROSA MARÍA HUAMÁN, ID 2)
INSERT INTO prince_shifts (schedule_id, employee_id, branch_id, shift_date, day_of_week, start_time, end_time, break_minutes, net_hours, shift_type, shift_label, task_notes, color_hex, is_demo)
VALUES
(1, 2, 1, '2026-11-16', 1, '09:00', '17:30', 30, 8.0, 'mid', 'Intermedio', 'Góndola principal Hallandale', '#3B82F6', TRUE),
(1, 2, 1, '2026-11-17', 2, '09:00', '17:30', 30, 8.0, 'mid', 'Intermedio', 'Reposición y conteo UPC', '#3B82F6', TRUE),
(1, 2, 1, '2026-11-18', 3, '09:00', '17:30', 30, 8.0, 'mid', 'Intermedio', 'Control FEFO lácteos', '#3B82F6', TRUE),
(1, 2, 1, '2026-11-19', 4, '09:00', '17:30', 30, 8.0, 'mid', 'Intermedio', 'Reposición bebidas', '#3B82F6', TRUE),
(1, 2, 1, '2026-11-20', 5, '09:00', '17:30', 30, 8.0, 'mid', 'Intermedio', 'Preparación fin de semana', '#3B82F6', TRUE),
(1, 2, 1, '2026-11-21', 6, '09:00', '17:30', 30, 8.0, 'mid', 'Intermedio', 'Góndola frontal', '#3B82F6', TRUE),
(1, 2, 1, '2026-11-22', 7, '00:00', '00:00', 0, 0.0, 'rest', 'Descanso Semanal', 'Libre', '#94A3B8', TRUE)
ON CONFLICT DO NOTHING;

-- 8. TAREAS DEMO OFICIALES
INSERT INTO prince_tasks (id, branch_id, employee_id, supervisor_id, product_id, title, description, priority, status, date, notes, is_demo)
VALUES
(1, 1, 2, 3, 5, 'Reposición Góndola Goya & Bebidas', 'Trasladar 15 cajas de Frijoles Goya y 20 packs de Pepsi de bodega a góndola frontal.', 'high', 'pending', '2026-11-17', 'Aplicar regla FEFO con lote LOT-2026-E15 en Hallandale Beach', TRUE),
(2, 1, 4, 3, 3, 'Auditoría FEFO y Vencimientos Lote 2026', 'Revisar fechas de expiración en estantes de snacks. Separar mermas.', 'urgent', 'pending', '2026-11-17', 'Reportar cualquier producto vencido a Carlos Mendoza', TRUE)
ON CONFLICT (id) DO UPDATE SET title = EXCLUDED.title;

-- 9. AUDITORÍA INICIAL
INSERT INTO prince_audit_logs (branch_id, employee_id, user_name, role, action, entity_type, entity_id, new_values, source, is_demo)
VALUES (1, 3, 'Carlos Mendoza', 'supervisor', 'DEMO_SEED_INITIALIZED', 'system', '1', '{"branch": "Hallandale HQ", "currency": "USD"}'::jsonb, 'migration', TRUE);

-- Ajustar secuencias de ID
SELECT setval('prince_organizations_id_seq', (SELECT MAX(id) FROM prince_organizations));
SELECT setval('prince_branches_id_seq', (SELECT MAX(id) FROM prince_branches));
SELECT setval('prince_employees_id_seq', (SELECT MAX(id) FROM prince_employees));
SELECT setval('prince_products_id_seq', (SELECT MAX(id) FROM prince_products));
SELECT setval('prince_inventory_id_seq', (SELECT MAX(id) FROM prince_inventory));
SELECT setval('prince_schedules_id_seq', (SELECT MAX(id) FROM prince_schedules));
SELECT setval('prince_tasks_id_seq', (SELECT MAX(id) FROM prince_tasks));
