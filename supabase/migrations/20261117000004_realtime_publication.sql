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
