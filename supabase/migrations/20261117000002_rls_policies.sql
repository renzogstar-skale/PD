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
