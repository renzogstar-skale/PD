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
