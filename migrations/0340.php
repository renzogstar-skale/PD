<?php

// Migration 0340: PASO 16 — GESTIÓN INTELIGENTE DE HORARIOS Y COBERTURA (WFM)
// Empleados, Horarios Semanales, Turnos, Plantillas, Reglas de Cobertura, Solicitudes y Auditoría

use Grocy\Services\DatabaseService;

if (!isset($db)) {
	$db = DatabaseService::GetInstance()->GetDbConnectionRaw();
}

// 1. EMPLEADOS OPERACIONALES & COMERCIALES
$db->exec("
CREATE TABLE IF NOT EXISTS wfm_employees (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	code TEXT NOT NULL UNIQUE,
	name TEXT NOT NULL,
	role TEXT NOT NULL, -- merchandiser, sales_rep, supervisor, warehouse, cashier
	role_label TEXT NOT NULL,
	department TEXT NOT NULL, -- Reposición, Ventas, Logística, Supervisión
	email TEXT,
	phone TEXT,
	contract_type TEXT DEFAULT 'full_time', -- full_time, part_time
	contracted_hours REAL DEFAULT 48.0,
	hourly_cost REAL DEFAULT 7.50,
	max_weekly_hours REAL DEFAULT 48.0,
	min_rest_hours REAL DEFAULT 12.0,
	supervisor_id INTEGER,
	supervisor_name TEXT,
	establishment_id INTEGER,
	establishment_name TEXT,
	territory TEXT DEFAULT 'Lima Norte',
	active INTEGER NOT NULL DEFAULT 1,
	notes TEXT,
	created_at DATETIME DEFAULT (datetime('now', 'localtime'))
);
");

// 2. PROGRAMACIONES SEMANALES (Master Schedule)
$db->exec("
CREATE TABLE IF NOT EXISTS wfm_schedules (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	week_code TEXT NOT NULL UNIQUE, -- e.g. 2026-W47
	start_date DATE NOT NULL,
	end_date DATE NOT NULL,
	establishment_id INTEGER DEFAULT 1,
	establishment_name TEXT DEFAULT 'Almacén Central Norte',
	territory TEXT DEFAULT 'Lima Norte',
	department TEXT DEFAULT 'Todos',
	status TEXT NOT NULL DEFAULT 'published', -- draft, published, archived
	version INTEGER DEFAULT 1,
	forecasted_sales REAL DEFAULT 48500.00,
	recommended_hours REAL DEFAULT 340.0,
	scheduled_hours REAL DEFAULT 336.0,
	estimated_labor_cost REAL DEFAULT 2520.00,
	labor_budget REAL DEFAULT 2800.00,
	coverage_score_pct REAL DEFAULT 98.8,
	published_at DATETIME,
	published_by TEXT,
	notes TEXT,
	created_at DATETIME DEFAULT (datetime('now', 'localtime')),
	updated_at DATETIME DEFAULT (datetime('now', 'localtime'))
);
");

// 3. TURNOS ASIGNADOS (Shifts)
$db->exec("
CREATE TABLE IF NOT EXISTS wfm_shifts (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	schedule_id INTEGER NOT NULL,
	employee_id INTEGER NOT NULL,
	employee_name TEXT NOT NULL,
	employee_role TEXT NOT NULL,
	shift_date DATE NOT NULL,
	day_of_week INTEGER NOT NULL, -- 1 = Lunes ... 7 = Domingo
	start_time TEXT NOT NULL, -- HH:MM (08:00)
	end_time TEXT NOT NULL, -- HH:MM (16:30)
	break_minutes INTEGER DEFAULT 30,
	net_hours REAL NOT NULL, -- 8.0
	shift_type TEXT DEFAULT 'standard', -- opening, mid, closing, rest, vacation, absence
	shift_label TEXT DEFAULT 'Turno Regular',
	assigned_store TEXT,
	task_notes TEXT,
	color_hex TEXT DEFAULT '#00A859',
	created_at DATETIME DEFAULT (datetime('now', 'localtime')),
	updated_at DATETIME DEFAULT (datetime('now', 'localtime')),
	FOREIGN KEY(schedule_id) REFERENCES wfm_schedules(id) ON DELETE CASCADE,
	FOREIGN KEY(employee_id) REFERENCES wfm_employees(id) ON DELETE CASCADE
);
");

// 4. PLANTILLAS DE TURNOS (Shift Templates)
$db->exec("
CREATE TABLE IF NOT EXISTS wfm_shift_templates (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	name TEXT NOT NULL,
	code TEXT NOT NULL UNIQUE,
	start_time TEXT NOT NULL,
	end_time TEXT NOT NULL,
	break_minutes INTEGER DEFAULT 30,
	net_hours REAL NOT NULL,
	role_target TEXT,
	color_hex TEXT DEFAULT '#00A859',
	description TEXT
);
");

// 5. SOLICITUDES DE PERSONAL (Shift Swaps, Permisos & Vacaciones)
$db->exec("
CREATE TABLE IF NOT EXISTS wfm_shift_requests (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	employee_id INTEGER NOT NULL,
	employee_name TEXT NOT NULL,
	request_type TEXT NOT NULL, -- swap, vacation, permission, absence
	shift_date DATE NOT NULL,
	target_employee_id INTEGER,
	target_employee_name TEXT,
	reason TEXT NOT NULL,
	status TEXT NOT NULL DEFAULT 'pending', -- pending, approved, rejected
	reviewed_by TEXT,
	reviewed_at DATETIME,
	created_at DATETIME DEFAULT (datetime('now', 'localtime')),
	FOREIGN KEY(employee_id) REFERENCES wfm_employees(id)
);
");

// 6. ASISTENCIA & PUNTUALIDAD REAL
$db->exec("
CREATE TABLE IF NOT EXISTS wfm_attendance (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	employee_id INTEGER NOT NULL,
	employee_name TEXT NOT NULL,
	work_date DATE NOT NULL,
	scheduled_in TEXT,
	scheduled_out TEXT,
	actual_in TEXT,
	actual_out TEXT,
	scheduled_hours REAL NOT NULL,
	worked_hours REAL NOT NULL,
	productive_hours REAL NOT NULL,
	paid_hours REAL NOT NULL,
	overtime_hours REAL DEFAULT 0,
	tardiness_minutes INTEGER DEFAULT 0,
	status TEXT DEFAULT 'present', -- present, late, absent, excused
	notes TEXT,
	created_at DATETIME DEFAULT (datetime('now', 'localtime'))
);
");

// 7. BITÁCORA DE AUDITORÍA WFM
$db->exec("
CREATE TABLE IF NOT EXISTS wfm_audit_log (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	schedule_id INTEGER,
	user_name TEXT NOT NULL DEFAULT 'Carlos Mendoza',
	action TEXT NOT NULL, -- SCHEDULE_CREATED, SHIFT_ADDED, SHIFT_EDITED, SHIFT_MOVED, SCHEDULE_PUBLISHED, SCHEDULE_REVERTED
	details TEXT,
	ip_address TEXT,
	created_at DATETIME DEFAULT (datetime('now', 'localtime'))
);
");

// SEMBRADO INICIAL DE PLANTILLAS DE TURNOS
$db->exec("
INSERT OR IGNORE INTO wfm_shift_templates (id, name, code, start_time, end_time, break_minutes, net_hours, role_target, color_hex, description) VALUES
(1, 'Apertura Mayorista', 'TMPL-APT', '07:00', '15:30', 30, 8.0, 'warehouse', '#059669', 'Recepción de camiones, conteo y preparación de pedidos matutinos'),
(2, 'Intermedio Reposición', 'TMPL-INT', '09:00', '17:30', 30, 8.0, 'merchandiser', '#3B82F6', 'Atención en góndolas, verificación de stock y rotación de fechas'),
(3, 'Cierre y Despacho', 'TMPL-CIE', '13:30', '22:00', 30, 8.0, 'warehouse', '#8B5CF6', 'Consolidación de carga, cuadre de mermas y cierre de almacén'),
(4, 'Ruta Comercial Preventa', 'TMPL-RUT', '08:00', '17:00', 60, 8.0, 'sales_rep', '#10B981', 'Visita presencial a clientes mayoristas según hoja de ruta semanal'),
(5, 'Medio Tiempo Apoyo', 'TMPL-PAR', '08:00', '12:00', 0, 4.0, 'merchandiser', '#F59E0B', 'Refuerzo de empaque y etiquetado en pico de demanda');
");

// SEMBRADO DE EMPLEADOS DE OPERACIÓN Y VENTAS
$db->exec("
INSERT OR IGNORE INTO wfm_employees (id, code, name, role, role_label, department, email, phone, contract_type, contracted_hours, hourly_cost, max_weekly_hours, supervisor_name, establishment_name, territory) VALUES
(1, 'EMP-REP-01', 'Jorge Luis Quispe', 'merchandiser', 'Reponedor Senior', 'Reposición & Merchandising', 'jquispe@princedistributors.com', '+51 984 111 201', 'full_time', 48.0, 7.50, 48.0, 'Carlos Mendoza', 'Almacén Central Norte', 'Lima Norte'),
(2, 'EMP-REP-02', 'Marcos Huamán', 'merchandiser', 'Mercaderista Retail', 'Reposición & Merchandising', 'mhuaman@princedistributors.com', '+51 984 111 202', 'full_time', 48.0, 7.50, 48.0, 'Carlos Mendoza', 'Supermercados Los Andes', 'Lima Norte'),
(3, 'EMP-REP-03', 'Paola Cárdenas', 'merchandiser', 'Mercaderista Retail', 'Reposición & Merchandising', 'pcardenas@princedistributors.com', '+51 984 111 203', 'full_time', 48.0, 7.50, 48.0, 'Carlos Mendoza', 'Distribuidora San Juan', 'Lima Norte'),
(4, 'EMP-REP-04', 'Renato Benítez', 'merchandiser', 'Reponedor Nocturno', 'Reposición & Merchandising', 'rbenitez@princedistributors.com', '+51 984 111 204', 'full_time', 48.0, 8.00, 48.0, 'Carlos Mendoza', 'Almacén Central Norte', 'Lima Norte'),
(5, 'EMP-VEN-01', 'Andrea Morales', 'sales_rep', 'Ejecutiva Mayorista B2B', 'Fuerza de Ventas B2B', 'amorales@princedistributors.com', '+51 992 444 888', 'full_time', 48.0, 9.00, 48.0, 'Carlos Mendoza', 'Sede Central San Borja', 'Lima Centro'),
(6, 'EMP-VEN-02', 'Luis Fernando Castro', 'sales_rep', 'Asesor Comercial Cuentas', 'Fuerza de Ventas B2B', 'lcastro@princedistributors.com', '+51 987 654 321', 'full_time', 48.0, 9.00, 48.0, 'Carlos Mendoza', 'Almacén Central Norte', 'Lima Norte'),
(7, 'EMP-VEN-03', 'Valeria Rojas', 'sales_rep', 'Asesora Mayorista Sur', 'Fuerza de Ventas B2B', 'vrojas@princedistributors.com', '+51 981 222 333', 'full_time', 48.0, 9.00, 48.0, 'Carlos Mendoza', 'Sede Lurín Mayorista', 'Lima Sur'),
(8, 'EMP-ALM-01', 'Manuel Flores', 'warehouse', 'Jefe de Picking & Despacho', 'Operaciones & Logística', 'mflores@princedistributors.com', '+51 993 333 444', 'full_time', 48.0, 8.50, 48.0, 'Carlos Mendoza', 'Almacén Central Norte', 'Lima Norte'),
(9, 'EMP-ALM-02', 'Christian Vega', 'warehouse', 'Operador de Montacargas', 'Operaciones & Logística', 'cvega@princedistributors.com', '+51 994 444 555', 'full_time', 48.0, 8.00, 48.0, 'Carlos Mendoza', 'Almacén Central Norte', 'Lima Norte'),
(10, 'EMP-ALM-03', 'Héctor Saldaña', 'warehouse', 'Auxiliar de Inventarios', 'Operaciones & Logística', 'hsaldana@princedistributors.com', '+51 995 555 666', 'part_time', 24.0, 7.50, 24.0, 'Carlos Mendoza', 'Almacén Central Norte', 'Lima Norte');
");

// SEMBRADO DE PROGRAMACIÓN SEMANAL ACTUAL (2026-W47: Lunes 16 Nov a Domingo 22 Nov)
$db->exec("
INSERT OR IGNORE INTO wfm_schedules (id, week_code, start_date, end_date, establishment_id, establishment_name, territory, status, version, forecasted_sales, recommended_hours, scheduled_hours, estimated_labor_cost, labor_budget, coverage_score_pct, published_at, published_by) VALUES
(1, '2026-W47', '2026-11-16', '2026-11-22', 1, 'Almacén Central Norte', 'Lima Norte', 'published', 1, 48500.00, 340.0, 336.0, 2520.00, 2800.00, 98.8, '2026-11-15 18:00:00', 'Carlos Mendoza'),
(2, '2026-W48', '2026-11-23', '2026-11-29', 1, 'Almacén Central Norte', 'Lima Norte', 'draft', 1, 52000.00, 365.0, 312.0, 2340.00, 3000.00, 85.5, NULL, NULL);
");

// SEMBRADO DE TURNOS SEMANALES PARA W47 (Lunes a Domingo)
$db->exec("
INSERT OR IGNORE INTO wfm_shifts (id, schedule_id, employee_id, employee_name, employee_role, shift_date, day_of_week, start_time, end_time, break_minutes, net_hours, shift_type, shift_label, color_hex) VALUES
-- Jorge Luis Quispe (Lun-Vie 07:00-15:30, Sáb 08:00-12:00, Dom Descanso)
(1, 1, 1, 'Jorge Luis Quispe', 'merchandiser', '2026-11-16', 1, '07:00', '15:30', 30, 8.0, 'opening', 'Apertura', '#059669'),
(2, 1, 1, 'Jorge Luis Quispe', 'merchandiser', '2026-11-17', 2, '07:00', '15:30', 30, 8.0, 'opening', 'Apertura', '#059669'),
(3, 1, 1, 'Jorge Luis Quispe', 'merchandiser', '2026-11-18', 3, '07:00', '15:30', 30, 8.0, 'opening', 'Apertura', '#059669'),
(4, 1, 1, 'Jorge Luis Quispe', 'merchandiser', '2026-11-19', 4, '07:00', '15:30', 30, 8.0, 'opening', 'Apertura', '#059669'),
(5, 1, 1, 'Jorge Luis Quispe', 'merchandiser', '2026-11-20', 5, '07:00', '15:30', 30, 8.0, 'opening', 'Apertura', '#059669'),
(6, 1, 1, 'Jorge Luis Quispe', 'merchandiser', '2026-11-21', 6, '08:00', '16:30', 30, 8.0, 'mid', 'Intermedio', '#3B82F6'),
(7, 1, 1, 'Jorge Luis Quispe', 'merchandiser', '2026-11-22', 7, '00:00', '00:00', 0, 0.0, 'rest', 'Descanso', '#94A3B8'),

-- Marcos Huamán (Lun-Vie 09:00-17:30, Sáb 09:00-17:30, Dom Descanso)
(8, 1, 2, 'Marcos Huamán', 'merchandiser', '2026-11-16', 1, '09:00', '17:30', 30, 8.0, 'mid', 'Intermedio', '#3B82F6'),
(9, 1, 2, 'Marcos Huamán', 'merchandiser', '2026-11-17', 2, '09:00', '17:30', 30, 8.0, 'mid', 'Intermedio', '#3B82F6'),
(10, 1, 2, 'Marcos Huamán', 'merchandiser', '2026-11-18', 3, '09:00', '17:30', 30, 8.0, 'mid', 'Intermedio', '#3B82F6'),
(11, 1, 2, 'Marcos Huamán', 'merchandiser', '2026-11-19', 4, '09:00', '17:30', 30, 8.0, 'mid', 'Intermedio', '#3B82F6'),
(12, 1, 2, 'Marcos Huamán', 'merchandiser', '2026-11-20', 5, '09:00', '17:30', 30, 8.0, 'mid', 'Intermedio', '#3B82F6'),
(13, 1, 2, 'Marcos Huamán', 'merchandiser', '2026-11-21', 6, '09:00', '17:30', 30, 8.0, 'mid', 'Intermedio', '#3B82F6'),
(14, 1, 2, 'Marcos Huamán', 'merchandiser', '2026-11-22', 7, '00:00', '00:00', 0, 0.0, 'rest', 'Descanso', '#94A3B8'),

-- Paola Cárdenas (Lun-Vie 13:30-22:00, Sáb Descanso, Dom 08:00-16:30)
(15, 1, 3, 'Paola Cárdenas', 'merchandiser', '2026-11-16', 1, '13:30', '22:00', 30, 8.0, 'closing', 'Cierre', '#8B5CF6'),
(16, 1, 3, 'Paola Cárdenas', 'merchandiser', '2026-11-17', 2, '13:30', '22:00', 30, 8.0, 'closing', 'Cierre', '#8B5CF6'),
(17, 1, 3, 'Paola Cárdenas', 'merchandiser', '2026-11-18', 3, '13:30', '22:00', 30, 8.0, 'closing', 'Cierre', '#8B5CF6'),
(18, 1, 3, 'Paola Cárdenas', 'merchandiser', '2026-11-19', 4, '13:30', '22:00', 30, 8.0, 'closing', 'Cierre', '#8B5CF6'),
(19, 1, 3, 'Paola Cárdenas', 'merchandiser', '2026-11-20', 5, '13:30', '22:00', 30, 8.0, 'closing', 'Cierre', '#8B5CF6'),
(20, 1, 3, 'Paola Cárdenas', 'merchandiser', '2026-11-21', 6, '00:00', '00:00', 0, 0.0, 'rest', 'Descanso', '#94A3B8'),
(21, 1, 3, 'Paola Cárdenas', 'merchandiser', '2026-11-22', 7, '08:00', '16:30', 30, 8.0, 'mid', 'Intermedio', '#3B82F6'),

-- Andrea Morales (Ventas B2B: Lun-Vie 08:00-17:00, Sáb 09:00-13:00, Dom Descanso)
(22, 1, 5, 'Andrea Morales', 'sales_rep', '2026-11-16', 1, '08:00', '17:00', 60, 8.0, 'standard', 'Ruta Comercial', '#10B981'),
(23, 1, 5, 'Andrea Morales', 'sales_rep', '2026-11-17', 2, '08:00', '17:00', 60, 8.0, 'standard', 'Ruta Comercial', '#10B981'),
(24, 1, 5, 'Andrea Morales', 'sales_rep', '2026-11-18', 3, '08:00', '17:00', 60, 8.0, 'standard', 'Ruta Comercial', '#10B981'),
(25, 1, 5, 'Andrea Morales', 'sales_rep', '2026-11-19', 4, '08:00', '17:00', 60, 8.0, 'standard', 'Ruta Comercial', '#10B981'),
(26, 1, 5, 'Andrea Morales', 'sales_rep', '2026-11-20', 5, '08:00', '17:00', 60, 8.0, 'standard', 'Ruta Comercial', '#10B981'),
(27, 1, 5, 'Andrea Morales', 'sales_rep', '2026-11-21', 6, '09:00', '13:00', 0, 4.0, 'standard', 'Seguimiento', '#10B981'),
(28, 1, 5, 'Andrea Morales', 'sales_rep', '2026-11-22', 7, '00:00', '00:00', 0, 0.0, 'rest', 'Descanso', '#94A3B8'),

-- Manuel Flores (Almacén: Lun-Sáb 07:00-15:30)
(29, 1, 8, 'Manuel Flores', 'warehouse', '2026-11-16', 1, '07:00', '15:30', 30, 8.0, 'opening', 'Apertura WMS', '#059669'),
(30, 1, 8, 'Manuel Flores', 'warehouse', '2026-11-17', 2, '07:00', '15:30', 30, 8.0, 'opening', 'Apertura WMS', '#059669'),
(31, 1, 8, 'Manuel Flores', 'warehouse', '2026-11-18', 3, '07:00', '15:30', 30, 8.0, 'opening', 'Apertura WMS', '#059669'),
(32, 1, 8, 'Manuel Flores', 'warehouse', '2026-11-19', 4, '07:00', '15:30', 30, 8.0, 'opening', 'Apertura WMS', '#059669'),
(33, 1, 8, 'Manuel Flores', 'warehouse', '2026-11-20', 5, '07:00', '15:30', 30, 8.0, 'opening', 'Apertura WMS', '#059669'),
(34, 1, 8, 'Manuel Flores', 'warehouse', '2026-11-21', 6, '07:00', '15:30', 30, 8.0, 'opening', 'Apertura WMS', '#059669'),
(35, 1, 8, 'Manuel Flores', 'warehouse', '2026-11-22', 7, '00:00', '00:00', 0, 0.0, 'rest', 'Descanso', '#94A3B8');
");

// SEMBRADO DE SOLICITUDES
$db->exec("
INSERT OR IGNORE INTO wfm_shift_requests (id, employee_id, employee_name, request_type, shift_date, target_employee_id, target_employee_name, reason, status) VALUES
(1, 2, 'Marcos Huamán', 'swap', '2026-11-20', 1, 'Jorge Luis Quispe', 'Intercambio de turno viernes tarde por cita médica familiar', 'pending'),
(2, 3, 'Paola Cárdenas', 'vacation', '2026-11-28', NULL, NULL, 'Solicitud anual de 5 días de vacaciones programadas', 'approved'),
(3, 4, 'Renato Benítez', 'permission', '2026-11-19', NULL, NULL, 'Permiso de estudios universitarios (salida 1 hora antes)', 'pending');
");

// REGISTRO DE AUDITORÍA INICIAL
$db->exec("
INSERT OR IGNORE INTO wfm_audit_log (id, schedule_id, user_name, action, details, ip_address) VALUES
(1, 1, 'Carlos Mendoza', 'SCHEDULE_PUBLISHED', 'Programación 2026-W47 publicada con 336.0 horas asignadas y 98.8% cobertura', '127.0.0.1');
");
