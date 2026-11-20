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
