-- =========================================================================
-- PRINCE DISTRIBUTORS — MIGRACIÓN 006: ROLLBACK COMPLETO Y REVERSIBLE
-- Deshace ordenadamente todas las tablas, vistas, funciones, triggers y políticas
-- Version: 20261117000006 (Rollback)
-- =========================================================================

-- 1. Eliminar tablas de publicación Realtime
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM pg_publication WHERE pubname = 'supabase_realtime') THEN
        ALTER PUBLICATION supabase_realtime DROP TABLE IF EXISTS prince_tasks;
        ALTER PUBLICATION supabase_realtime DROP TABLE IF EXISTS prince_attendance;
        ALTER PUBLICATION supabase_realtime DROP TABLE IF EXISTS prince_inventory;
        ALTER PUBLICATION supabase_realtime DROP TABLE IF EXISTS prince_inventory_movements;
        ALTER PUBLICATION supabase_realtime DROP TABLE IF EXISTS prince_shifts;
        ALTER PUBLICATION supabase_realtime DROP TABLE IF EXISTS prince_audit_logs;
    END IF;
EXCEPTION
    WHEN OTHERS THEN NULL;
END $$;

-- 2. Eliminar funciones RPC
DROP FUNCTION IF EXISTS prince_fn_create_task(INT, INT, INT, VARCHAR, TEXT, prince_task_priority, DATE, TEXT);
DROP FUNCTION IF EXISTS prince_fn_complete_task(INT, INT, TEXT, TEXT);
DROP FUNCTION IF EXISTS prince_fn_clock_out(INT, BIGINT, TEXT);
DROP FUNCTION IF EXISTS prince_fn_clock_in(INT, INT, TEXT, TEXT);
DROP FUNCTION IF EXISTS prince_fn_record_shrinkage(INT, INT, INT, INT, TEXT);
DROP FUNCTION IF EXISTS prince_fn_restock_shelf(INT, INT, INT, INT, TEXT);
DROP FUNCTION IF EXISTS prince_get_current_branch_id();
DROP FUNCTION IF EXISTS prince_get_current_role();
DROP FUNCTION IF EXISTS prince_get_current_employee();

-- 3. Eliminar tablas en orden inverso de dependencias
DROP TABLE IF EXISTS prince_audit_logs CASCADE;
DROP TABLE IF EXISTS prince_tasks CASCADE;
DROP TABLE IF EXISTS prince_attendance CASCADE;
DROP TABLE IF EXISTS prince_shifts CASCADE;
DROP TABLE IF EXISTS prince_schedules CASCADE;
DROP TABLE IF EXISTS prince_inventory_movements CASCADE;
DROP TABLE IF EXISTS prince_inventory CASCADE;
DROP TABLE IF EXISTS prince_products CASCADE;
DROP TABLE IF EXISTS prince_employees CASCADE;
DROP TABLE IF EXISTS prince_branches CASCADE;
DROP TABLE IF EXISTS prince_organizations CASCADE;

-- 4. Eliminar tipos enumerados
DROP TYPE IF EXISTS prince_movement_type;
DROP TYPE IF EXISTS prince_attendance_status;
DROP TYPE IF EXISTS prince_shift_type;
DROP TYPE IF EXISTS prince_task_status;
DROP TYPE IF EXISTS prince_task_priority;
DROP TYPE IF EXISTS prince_user_role;
