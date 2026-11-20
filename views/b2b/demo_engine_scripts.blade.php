<script>
/**
 * =========================================================================
 * PRINCE DISTRIBUTORS — MOTOR CENTRAL DE DATOS DEMO & WORKFORCE MANAGEMENT
 * Configuración: Estados Unidos (Hallandale Beach, FL) | USD ($) | en-US | America/New_York
 * Roles: Vendedor Comercial, Supervisor de Ventas, Administrador Global, Reponedor / Merchandiser
 * Módulos Integrados: Horarios, Tareas, Inventario FEFO, UPC Barcode Scanner, Asistencia
 * =========================================================================
 */

(function (window) {
	'use strict';

	const STORAGE_KEY = 'PRINCE_DISTRIBUTORS_WFM_DEMO_USA_V1';

	// =====================================================================
	// 1. DATASET CENTRAL — ESTADOS UNIDOS / FLORIDA / SOUTH FLORIDA
	// =====================================================================

	const DEFAULT_ESTABLISHMENTS = [
		{ id: 1, code: 'EST-001', name: 'Prince Distributors — Hallandale HQ', territory: 'Broward South', address: '1250 E Hallandale Beach Blvd, Hallandale Beach, FL 33009', budget: 3200.00, minCoverage: 3, salesForecast: 48500.00 },
		{ id: 2, code: 'EST-002', name: 'Aventura Mall Retail Hub', territory: 'Miami-Dade North', address: '19501 Biscayne Blvd, Aventura, FL 33180', budget: 2800.00, minCoverage: 2, salesForecast: 38200.00 },
		{ id: 3, code: 'EST-003', name: 'Hollywood Boulevard Supercenter', territory: 'Broward South', address: '3800 Hollywood Blvd, Hollywood, FL 33021', budget: 2400.00, minCoverage: 2, salesForecast: 29800.00 },
		{ id: 4, code: 'EST-004', name: 'Fort Lauderdale Logistics Hub', territory: 'Broward Central', address: '2400 SE 10th Ave, Fort Lauderdale, FL 33316', budget: 3500.00, minCoverage: 3, salesForecast: 54100.00 },
		{ id: 5, code: 'EST-005', name: 'Miami Gardens Wholesale', territory: 'Miami-Dade North', address: '18300 NW 27th Ave, Miami Gardens, FL 33056', budget: 2600.00, minCoverage: 2, salesForecast: 34500.00 },
		{ id: 6, code: 'EST-006', name: 'Sunny Isles Gourmet Mart', territory: 'Miami-Dade North', address: '17100 Collins Ave, Sunny Isles Beach, FL 33160', budget: 2900.00, minCoverage: 2, salesForecast: 42300.00 },
		{ id: 7, code: 'EST-007', name: 'Pembroke Pines Distribution', territory: 'Broward South', address: '11401 Pines Blvd, Pembroke Pines, FL 33026', budget: 2500.00, minCoverage: 2, salesForecast: 31200.00 },
		{ id: 8, code: 'EST-008', name: 'Pompano Beach Central Depot', territory: 'Broward Central', address: '2201 W Atlantic Blvd, Pompano Beach, FL 33069', budget: 1800.00, minCoverage: 1, salesForecast: 19400.00 }
	];

	const DEFAULT_EMPLOYEES = [
		{ id: 1, code: 'EMP-001', name: 'Jorge Luis Quispe', role: 'sales_rep', role_label: 'Senior B2B Sales Representative', department: 'Fuerza de Ventas B2B', email: 'jquispe@princedistributors.com', phone: '+1 (954) 555-0101', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 18.50, supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 1, establishment_name: 'Prince Distributors — Hallandale HQ', territory: 'Broward South', active: 1 },
		{ id: 2, code: 'EMP-002', name: 'Rosa María Huamán', role: 'merchandiser', role_label: 'Lead Merchandiser', department: 'Reposición & Merchandising', email: 'rhuaman@princedistributors.com', phone: '+1 (954) 555-0102', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 16.50, supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 1, establishment_name: 'Prince Distributors — Hallandale HQ', territory: 'Broward South', active: 1 },
		{ id: 3, code: 'EMP-003', name: 'Carlos Mendoza', role: 'supervisor', role_label: 'Operations & Sales Supervisor', department: 'Supervisión & Control', email: 'cmendoza@princedistributors.com', phone: '+1 (954) 555-0103', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 26.00, supervisor_id: 20, supervisor_name: 'Gonzalo Javier Núñez', establishment_id: 1, establishment_name: 'Prince Distributors — Hallandale HQ', territory: 'Broward South', active: 1 },
		{ id: 4, code: 'EMP-004', name: 'Miguel Ángel Flores', role: 'merchandiser', role_label: 'Warehouse & Shelf Stocker', department: 'Reposición & Merchandising', email: 'mflores@princedistributors.com', phone: '+1 (954) 555-0104', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 16.00, supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 1, establishment_name: 'Prince Distributors — Hallandale HQ', territory: 'Broward South', active: 1 },
		{ id: 5, code: 'EMP-005', name: 'Ana Sofía Rivas', role: 'sales_rep', role_label: 'Key Account Executive', department: 'Fuerza de Ventas B2B', email: 'arivas@princedistributors.com', phone: '+1 (305) 555-0105', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 19.50, supervisor_id: 7, supervisor_name: 'Patricia Vega', establishment_id: 2, establishment_name: 'Aventura Mall Retail Hub', territory: 'Miami-Dade North', active: 1 },
		{ id: 6, code: 'EMP-006', name: 'Diego Alonso Ramos', role: 'merchandiser', role_label: 'Retail Merchandiser', department: 'Reposición & Merchandising', email: 'dramos@princedistributors.com', phone: '+1 (305) 555-0106', contract_type: 'part_time', contracted_hours: 40, hourly_cost: 16.00, supervisor_id: 7, supervisor_name: 'Patricia Vega', establishment_id: 2, establishment_name: 'Aventura Mall Retail Hub', territory: 'Miami-Dade North', active: 1 },
		{ id: 7, code: 'EMP-007', name: 'Patricia Vega', role: 'supervisor', role_label: 'Commercial Supervisor Miami-Dade', department: 'Supervisión & Control', email: 'pvega@princedistributors.com', phone: '+1 (305) 555-0107', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 27.00, supervisor_id: 20, supervisor_name: 'Gonzalo Javier Núñez', establishment_id: 2, establishment_name: 'Aventura Mall Retail Hub', territory: 'Miami-Dade North', active: 1 },
		{ id: 8, code: 'EMP-008', name: 'Brenda Lucía Vargas', role: 'sales_rep', role_label: 'Route Sales Representative', department: 'Fuerza de Ventas B2B', email: 'bvargas@princedistributors.com', phone: '+1 (954) 555-0108', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 18.50, supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 3, establishment_name: 'Hollywood Boulevard Supercenter', territory: 'Broward South', active: 1 },
		{ id: 9, code: 'EMP-009', name: 'Fernando José Castro', role: 'merchandiser', role_label: 'Forklift & Inventory Specialist', department: 'Operaciones & Logística', email: 'fcastro@princedistributors.com', phone: '+1 (954) 555-0109', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 17.50, supervisor_id: 11, supervisor_name: 'Roberto Salcedo', establishment_id: 4, establishment_name: 'Fort Lauderdale Logistics Hub', territory: 'Broward Central', active: 1 },
		{ id: 10, code: 'EMP-010', name: 'Lucía Carmen Espinoza', role: 'merchandiser', role_label: 'Order Picker & Restocker', department: 'Operaciones & Logística', email: 'lespinoza@princedistributors.com', phone: '+1 (305) 555-0110', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 16.00, supervisor_id: 7, supervisor_name: 'Patricia Vega', establishment_id: 5, establishment_name: 'Miami Gardens Wholesale', territory: 'Miami-Dade North', active: 1 },
		{ id: 11, code: 'EMP-011', name: 'Roberto Salcedo', role: 'supervisor', role_label: 'Territorial Supervisor Broward', department: 'Supervisión & Control', email: 'rsalcedo@princedistributors.com', phone: '+1 (954) 555-0111', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 26.50, supervisor_id: 20, supervisor_name: 'Gonzalo Javier Núñez', establishment_id: 4, establishment_name: 'Fort Lauderdale Logistics Hub', territory: 'Broward Central', active: 1 },
		{ id: 12, code: 'EMP-012', name: 'Juan Carlos Benítez', role: 'sales_rep', role_label: 'Wholesale Account Rep', department: 'Fuerza de Ventas B2B', email: 'jbenitez@princedistributors.com', phone: '+1 (954) 555-0112', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 18.50, supervisor_id: 11, supervisor_name: 'Roberto Salcedo', establishment_id: 4, establishment_name: 'Fort Lauderdale Logistics Hub', territory: 'Broward Central', active: 1 },
		{ id: 13, code: 'EMP-013', name: 'Valeria Nicole Morales', role: 'merchandiser', role_label: 'Night Restocker & FEFO Specialist', department: 'Reposición & Merchandising', email: 'vmorales@princedistributors.com', phone: '+1 (305) 555-0113', contract_type: 'full_time', contracted_hours: 44, hourly_cost: 17.00, supervisor_id: 7, supervisor_name: 'Patricia Vega', establishment_id: 6, establishment_name: 'Sunny Isles Gourmet Mart', territory: 'Miami-Dade North', active: 1 },
		{ id: 14, code: 'EMP-014', name: 'Kevin Alexander Gómez', role: 'merchandiser', role_label: 'Dispatch Driver & Merchandiser', department: 'Operaciones & Logística', email: 'kgomez@princedistributors.com', phone: '+1 (954) 555-0114', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 17.50, supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 7, establishment_name: 'Pembroke Pines Distribution', territory: 'Broward South', active: 1 },
		{ id: 15, code: 'EMP-015', name: 'María Elena Gutiérrez', role: 'sales_rep', role_label: 'Specialty Accounts Rep', department: 'Fuerza de Ventas B2B', email: 'mgutierrez@princedistributors.com', phone: '+1 (305) 555-0115', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 19.50, supervisor_id: 7, supervisor_name: 'Patricia Vega', establishment_id: 6, establishment_name: 'Sunny Isles Gourmet Mart', territory: 'Miami-Dade North', active: 1 },
		{ id: 16, code: 'EMP-016', name: 'Renzo Manuel Paucar', role: 'merchandiser', role_label: 'Heavy Stocker & Receiving', department: 'Reposición & Merchandising', email: 'rpaucar@princedistributors.com', phone: '+1 (954) 555-0116', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 16.00, supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 7, establishment_name: 'Pembroke Pines Distribution', territory: 'Broward South', active: 1 },
		{ id: 17, code: 'EMP-017', name: 'Diana Carolina Paredes', role: 'sales_rep', role_label: 'Territory Sales Rep', department: 'Fuerza de Ventas B2B', email: 'dparedes@princedistributors.com', phone: '+1 (954) 555-0117', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 18.50, supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 3, establishment_name: 'Hollywood Boulevard Supercenter', territory: 'Broward South', active: 1 },
		{ id: 18, code: 'EMP-018', name: 'Andrés Felipe Soto', role: 'merchandiser', role_label: 'WMS Inventory Specialist', department: 'Operaciones & Logística', email: 'asoto@princedistributors.com', phone: '+1 (954) 555-0118', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 17.00, supervisor_id: 11, supervisor_name: 'Roberto Salcedo', establishment_id: 8, establishment_name: 'Pompano Beach Central Depot', territory: 'Broward Central', active: 1 },
		{ id: 19, code: 'EMP-019', name: 'Camila Fernanda Ruiz', role: 'merchandiser', role_label: 'Express Merchandiser', department: 'Reposición & Merchandising', email: 'cruiz@princedistributors.com', phone: '+1 (954) 555-0119', contract_type: 'part_time', contracted_hours: 36, hourly_cost: 16.00, supervisor_id: 11, supervisor_name: 'Roberto Salcedo', establishment_id: 8, establishment_name: 'Pompano Beach Central Depot', territory: 'Broward Central', active: 1 },
		{ id: 20, code: 'EMP-020', name: 'Gonzalo Javier Núñez', role: 'admin', role_label: 'Global Workforce Director', department: 'Dirección de Operaciones', email: 'gnunez@princedistributors.com', phone: '+1 (954) 555-0120', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 32.00, supervisor_id: null, supervisor_name: 'Executive Board', establishment_id: 1, establishment_name: 'Prince Distributors — Hallandale HQ', territory: 'Broward South', active: 1 }
	];

	// Tareas operacionales de demostración
	const DEFAULT_TASKS = [
		{ id: 1, title: 'Reposición Góndola Goya & Bebidas', description: 'Trasladar 15 cajas de Frijoles Goya y 20 packs de Pepsi de bodega a góndola frontal.', employee_id: 2, employee_name: 'Rosa María Huamán', supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 1, establishment_name: 'Prince Distributors — Hallandale HQ', date: '2026-11-16', priority: 'high', status: 'in_progress', evidence_url: null, notes: 'Aplicar regla FEFO con lote LOT-2026-E15' },
		{ id: 2, title: 'Auditoría FEFO y Vencimientos Lote 2026', description: 'Revisar fechas de expiración en estantes de snack y lácteos. Separar mermas menores a 15 días.', employee_id: 4, employee_name: 'Miguel Ángel Flores', supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 1, establishment_name: 'Prince Distributors — Hallandale HQ', date: '2026-11-16', priority: 'urgent', status: 'pending', evidence_url: null, notes: 'Reportar cualquier producto vencido a Carlos Mendoza' },
		{ id: 3, title: 'Conteo Cíclico con Escáner UPC', description: 'Escanear códigos UPC en pasillo 4 (Monster Energy, Arizona Tea y Doritos) y cuadrar contra stock bodega.', employee_id: 6, employee_name: 'Diego Alonso Ramos', supervisor_id: 7, supervisor_name: 'Patricia Vega', establishment_id: 2, establishment_name: 'Aventura Mall Retail Hub', date: '2026-11-16', priority: 'normal', status: 'completed', evidence_url: 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=400', notes: 'Conteo verificado 100% cuadre' },
		{ id: 4, title: 'Alineación de Precios y Planograma POS', description: 'Verificar etiquetas electrónicas y frentes de Inca Kola 2L y Coca-Cola 20oz según planograma aprobado.', employee_id: 9, employee_name: 'Fernando José Castro', supervisor_id: 11, supervisor_name: 'Roberto Salcedo', establishment_id: 4, establishment_name: 'Fort Lauderdale Logistics Hub', date: '2026-11-16', priority: 'normal', status: 'pending', evidence_url: null, notes: 'Actualizar tags de promoción de fin de semana' },
		{ id: 5, title: 'Recepción y Descarga Pallet PepsiCo', description: 'Recepcionar camión de despacho mayorista con 4 pallets de bebidas. Registrar en WMS.', employee_id: 10, employee_name: 'Lucía Carmen Espinoza', supervisor_id: 7, supervisor_name: 'Patricia Vega', establishment_id: 5, establishment_name: 'Miami Gardens Wholesale', date: '2026-11-16', priority: 'high', status: 'pending', evidence_url: null, notes: 'Confirmar remisión con firma de chofer' },
		{ id: 6, title: 'Inspección de Mermas y Devoluciones', description: 'Separar latas abolladas de Monster Energy y registrar merma operacional.', employee_id: 13, employee_name: 'Valeria Nicole Morales', supervisor_id: 7, supervisor_name: 'Patricia Vega', establishment_id: 6, establishment_name: 'Sunny Isles Gourmet Mart', date: '2026-11-16', priority: 'low', status: 'pending', evidence_url: null, notes: 'Colocar en área de cuarentena' }
	];

	// Catálogo de Productos con UPC autenticados y control FEFO
	const DEFAULT_UPC_PRODUCTS = [
		{ id: 1, upc: '012000000133', sku: 'BEV-PEP-12', name: 'Pepsi Cola 12oz Pack (12pk)', category: 'Bebidas & Refrescos', lot: 'LOT-2026-A12', expiration_date: '2027-04-15', warehouse_stock: 140, shelf_stock: 35, shelf_capacity: 50, price: 6.99, cost: 4.20 },
		{ id: 2, upc: '049000000443', sku: 'BEV-COK-20', name: 'Coca-Cola Classic 20oz Bottle', category: 'Bebidas & Refrescos', lot: 'LOT-2026-B04', expiration_date: '2027-02-28', warehouse_stock: 220, shelf_stock: 45, shelf_capacity: 60, price: 2.29, cost: 1.15 },
		{ id: 3, upc: '028400064088', sku: 'SNK-DOR-92', name: 'Doritos Nacho Cheese 9.25oz', category: 'Snacks & Golosinas', lot: 'LOT-2026-C09', expiration_date: '2026-12-10', warehouse_stock: 85, shelf_stock: 18, shelf_capacity: 30, price: 4.49, cost: 2.60 },
		{ id: 4, upc: '070847811169', sku: 'BEV-MON-16', name: 'Monster Energy Drink Original 16oz', category: 'Bebidas Energéticas', lot: 'LOT-2026-D16', expiration_date: '2027-08-30', warehouse_stock: 160, shelf_stock: 28, shelf_capacity: 40, price: 3.29, cost: 1.85 },
		{ id: 5, upc: '041789001429', sku: 'GRO-GOY-15', name: 'Goya Black Beans (Frijoles Negros) 15.5oz', category: 'Abarrotes & Enlatados', lot: 'LOT-2026-E15', expiration_date: '2028-01-20', warehouse_stock: 310, shelf_stock: 60, shelf_capacity: 80, price: 1.79, cost: 0.95 },
		{ id: 6, upc: '775015100102', sku: 'BEV-INC-2L', name: 'Inca Kola Botella 2L Importada', category: 'Bebidas Hispanas', lot: 'LOT-2026-F02', expiration_date: '2027-03-15', warehouse_stock: 95, shelf_stock: 22, shelf_capacity: 35, price: 3.89, cost: 2.10 },
		{ id: 7, upc: '026400001221', sku: 'BEV-ARI-23', name: 'Arizona Green Tea Ginseng 23oz Can', category: 'Tés & Bebidas', lot: 'LOT-2026-G23', expiration_date: '2027-06-18', warehouse_stock: 190, shelf_stock: 40, shelf_capacity: 50, price: 1.29, cost: 0.65 },
		{ id: 8, upc: '016000264601', sku: 'SNK-NAT-12', name: 'Nature Valley Oats & Honey Bars 12ct', category: 'Snacks & Barras', lot: 'LOT-2026-H12', expiration_date: '2026-11-28', warehouse_stock: 60, shelf_stock: 12, shelf_capacity: 25, price: 4.99, cost: 2.80 }
	];

	// Generador de turnos deterministas para las 4 semanas
	function generateShiftsForWeek(weekCode, startDateStr, isHistorical) {
		const shifts = [];
		const startDate = new Date(startDateStr);

		DEFAULT_EMPLOYEES.forEach((emp) => {
			for (let dow = 1; dow <= 7; dow++) {
				const dayDate = new Date(startDate);
				dayDate.setDate(startDate.getDate() + (dow - 1));
				const dateStr = dayDate.toISOString().split('T')[0];

				let sType = 'mid';
				let sLabel = 'Intermedio';
				let start = '09:00';
				let end = '17:30';
				let breakMin = 30;
				let net = 8.0;

				if (dow === 7) {
					// Domingo libre
					sType = 'rest';
					sLabel = 'Descanso Semanal';
					start = '00:00';
					end = '00:00';
					breakMin = 0;
					net = 0;
				} else if (emp.role === 'sales_rep') {
					if (dow === 6) {
						// Sábado ruta corta
						sType = 'standard';
						sLabel = 'Ruta Seguimiento';
						start = '09:00';
						end = '13:00';
						breakMin = 0;
						net = 4.0;
					} else {
						sType = 'standard';
						sLabel = 'Ruta Preventa B2B';
						start = '08:00';
						end = '17:00';
						breakMin = 60;
						net = 8.0;
					}
				} else if (emp.role === 'merchandiser') {
					if (emp.id % 3 === 0) {
						sType = 'opening';
						sLabel = 'Apertura & Recepción';
						start = '07:00';
						end = '15:30';
						breakMin = 30;
						net = 8.0;
					} else if (emp.id % 3 === 1) {
						sType = 'mid';
						sLabel = 'Reposición Góndola';
						start = '09:00';
						end = '17:30';
						breakMin = 30;
						net = 8.0;
					} else {
						sType = 'closing';
						sLabel = 'Cierre & FEFO';
						start = '13:30';
						end = '22:00';
						breakMin = 30;
						net = 8.0;
					}
				} else if (emp.role === 'supervisor') {
					sType = 'standard';
					sLabel = 'Supervisión de Ruta';
					start = '08:00';
					end = '16:30';
					breakMin = 30;
					net = 8.0;
				} else {
					// Admin
					if (dow <= 5) {
						sType = 'standard';
						sLabel = 'Gestión WFM Global';
						start = '08:30';
						end = '17:30';
						breakMin = 60;
						net = 8.0;
					} else {
						sType = 'rest';
						sLabel = 'Descanso Semanal';
						start = '00:00';
						end = '00:00';
						breakMin = 0;
						net = 0;
					}
				}

				shifts.push({
					id: `${weekCode}-${emp.id}-${dow}`,
					week_code: weekCode,
					employee_id: emp.id,
					employee_name: emp.name,
					employee_role: emp.role,
					shift_date: dateStr,
					day_of_week: dow,
					start_time: start,
					end_time: end,
					break_minutes: breakMin,
					net_hours: net,
					shift_type: sType,
					shift_label: sLabel,
					establishment_id: emp.establishment_id,
					establishment_name: emp.establishment_name,
					task_notes: sType !== 'rest' ? `Operación asignada en ${emp.establishment_name}` : ''
				});
			}
		});

		return shifts;
	}

	const DEFAULT_SCHEDULES = {
		'2026-W46': {
			id: 1,
			week_code: '2026-W46',
			label: 'Semana 46: 09 Nov – 15 Nov (Anterior)',
			start_date: '2026-11-09',
			end_date: '2026-11-15',
			status: 'published',
			version: 2,
			published_at: '2026-11-08 19:00',
			published_by: 'Carlos Mendoza',
			forecasted_sales: 44800.00,
			labor_budget: 2700.00,
			shifts: generateShiftsForWeek('2026-W46', '2026-11-09', true)
		},
		'2026-W47': {
			id: 2,
			week_code: '2026-W47',
			label: 'Semana 47: 16 Nov – 22 Nov (Actual)',
			start_date: '2026-11-16',
			end_date: '2026-11-22',
			status: 'published',
			version: 1,
			published_at: '2026-11-15 18:00',
			published_by: 'Carlos Mendoza',
			forecasted_sales: 48500.00,
			labor_budget: 2800.00,
			shifts: generateShiftsForWeek('2026-W47', '2026-11-16', false)
		},
		'2026-W48': {
			id: 3,
			week_code: '2026-W48',
			label: 'Semana 48: 23 Nov – 29 Nov (Próxima)',
			start_date: '2026-11-23',
			end_date: '2026-11-29',
			status: 'draft',
			version: 1,
			published_at: null,
			published_by: null,
			forecasted_sales: 52000.00,
			labor_budget: 3000.00,
			shifts: generateShiftsForWeek('2026-W48', '2026-11-23', false)
		},
		'2026-W49': {
			id: 4,
			week_code: '2026-W49',
			label: 'Semana 49: 30 Nov – 06 Dic (Siguiente)',
			start_date: '2026-11-30',
			end_date: '2026-12-06',
			status: 'draft',
			version: 1,
			published_at: null,
			published_by: null,
			forecasted_sales: 54600.00,
			labor_budget: 3100.00,
			shifts: generateShiftsForWeek('2026-W49', '2026-11-30', false)
		}
	};

	const DEFAULT_NOTIFICATIONS = [
		{ id: 1, title: 'Programación Semana 47 Publicada', text: 'El horario para la semana del 16 al 22 de Noviembre ha sido publicado por Carlos Mendoza (v1).', time: 'Hace 2 horas', read: false, type: 'publish' },
		{ id: 2, title: 'Alerta de Pico en Hallandale HQ', text: 'Demanda de $9,200 el Lunes 16 Noviembre requiere reforzar la apertura con 1 reponedor adicional.', time: 'Hace 4 horas', read: false, type: 'alert' },
		{ id: 3, title: 'Turno Intercambiado en Aventura', text: 'Diego Alonso Ramos ha intercambiado su turno de viernes con Rosa Huamán con visto bueno de supervisión.', time: 'Hace 1 día', read: true, type: 'request' }
	];

	// =====================================================================
	// 2. MOTOR PRINCIPAL PRINCE DEMO ENGINE (SINGLETON)
	// =====================================================================

	class PrinceDemoEngine {
		constructor() {
			this.listeners = {};
			this.currentRole = 'admin'; // 'admin' | 'supervisor' | 'sales_rep' | 'merchandiser'
			this.currentEmployeeId = 2; // Rosa María Huamán por defecto para reponedor / jornada
			this.activeWeek = '2026-W47';
			this.activeEstablishment = 'Todos';
			this.activeTerritory = 'Todos';
			this.activeDepartment = 'Todos';
			this.whatIfScenario = { salesDeltaPct: 0, absentCount: 0, prodFactor: 1.0 };
			this.lastOptimizationBackup = null;

			// Sistema de Asistencia / Clock-In / Jornada Activa
			this.activeSessions = {};

			this.init();
		}

		init() {
			this.state = this.loadFromStorage();
			if (!this.state) {
				this.resetToDefaults();
			}
			console.log('>>> [PrinceDemoEngine] USA Florida WFM Engine inicializado. Empleados:', this.state.employees.length, '| Tareas:', (this.state.tasks || []).length);
			this.pollSupabaseUpdates();
			setInterval(() => this.pollSupabaseUpdates(), 7000);
		}

		pollSupabaseUpdates() {
			const self = this;
			fetch('/api/b2b/supabase/tasks')
				.then(r => r.json())
				.then(data => {
					if (data && data.success && data.source === 'supabase' && Array.isArray(data.tasks)) {
						let changed = false;
						data.tasks.forEach(remoteTask => {
							const local = self.state.tasks.find(t => t.id === remoteTask.id);
							if (local && local.status !== remoteTask.status) {
								local.status = remoteTask.status;
								if (remoteTask.notes) local.notes = remoteTask.notes;
								changed = true;
							} else if (!local) {
								self.state.tasks.unshift({
									id: remoteTask.id,
									title: remoteTask.title,
									description: remoteTask.description || '',
									employee_id: remoteTask.employee_id,
									employee_name: remoteTask.prince_employees?.name || 'Rosa María Huamán',
									supervisor_id: remoteTask.supervisor_id || 3,
									supervisor_name: 'Carlos Mendoza',
									establishment_id: remoteTask.branch_id || 1,
									establishment_name: 'Prince Distributors — Hallandale HQ',
									date: remoteTask.date || new Date().toISOString().slice(0, 10),
									priority: remoteTask.priority || 'normal',
									status: remoteTask.status || 'pending',
									notes: remoteTask.notes || ''
								});
								changed = true;
							}
						});
						if (changed) {
							self.saveToStorage();
							self.emit('task:updated', {});
							if (typeof renderWfmTasksTable === 'function') {
								renderWfmTasksTable();
							}
						}
					}
				})
				.catch(() => {});
		}

		loadFromStorage() {
			try {
				const raw = localStorage.getItem(STORAGE_KEY);
				if (!raw) return null;
				const parsed = JSON.parse(raw);
				if (!parsed.employees || parsed.employees.length < 20 || !parsed.schedules || !parsed.tasks || !parsed.inventory) {
					return null;
				}
				return parsed;
			} catch (e) {
				console.warn('[PrinceDemoEngine] Error al leer localStorage, regenerando dataset:', e);
				return null;
			}
		}

		saveToStorage() {
			try {
				localStorage.setItem(STORAGE_KEY, JSON.stringify(this.state));
			} catch (e) {
				console.warn('[PrinceDemoEngine] Error al guardar en localStorage:', e);
			}
		}

		resetToDefaults() {
			this.state = {
				establishments: JSON.parse(JSON.stringify(DEFAULT_ESTABLISHMENTS)),
				employees: JSON.parse(JSON.stringify(DEFAULT_EMPLOYEES)),
				schedules: JSON.parse(JSON.stringify(DEFAULT_SCHEDULES)),
				tasks: JSON.parse(JSON.stringify(DEFAULT_TASKS)),
				inventory: JSON.parse(JSON.stringify(DEFAULT_UPC_PRODUCTS)),
				notifications: JSON.parse(JSON.stringify(DEFAULT_NOTIFICATIONS)),
				attendance: [
					{ id: 1, employee_id: 2, establishment_id: 1, type: 'clock_in', timestamp: '2026-11-16 08:58:12', notes: 'Turno iniciado a tiempo' }
				],
				auditLog: [
					{ id: 1, time: '2026-11-15 18:00', user: 'Carlos Mendoza', action: 'SCHEDULE_PUBLISHED', details: 'Programación Semana 47 publicada (v1)' }
				]
			};
			this.lastOptimizationBackup = null;
			this.saveToStorage();
			this.emit('demo:reset', {});
		}

		// EVENT BUS
		on(event, callback) {
			if (!this.listeners[event]) this.listeners[event] = [];
			this.listeners[event].push(callback);
		}

		emit(event, payload) {
			if (this.listeners[event]) {
				this.listeners[event].forEach(cb => {
					try { cb(payload); } catch (err) { console.error('Error in listener:', err); }
				});
			}
			if (typeof window !== 'undefined') {
				window.dispatchEvent(new CustomEvent('prince:state-change', { detail: { event, payload } }));
			}
		}

		// =================================================================
		// 3. CONSULTAS Y CÁLCULOS MATEMÁTICOS COHERENTES (USD / EN-US)
		// =================================================================

		formatCurrency(amount) {
			return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(Number(amount) || 0);
		}

		format12h(timeStr) {
			if (!timeStr || timeStr === '00:00') return 'Descanso';
			const parts = timeStr.split(':');
			let hours = parseInt(parts[0], 10);
			const minutes = parts[1] || '00';
			const ampm = hours >= 12 ? 'PM' : 'AM';
			hours = hours % 12;
			hours = hours ? hours : 12;
			return `${hours}:${minutes} ${ampm}`;
		}

		getEmployees(filterDept = 'Todos', filterEst = 'Todos') {
			return this.state.employees.filter(emp => {
				const matchDept = filterDept === 'Todos' || emp.department === filterDept;
				const matchEst = filterEst === 'Todos' || emp.establishment_name === filterEst || String(emp.establishment_id) === String(filterEst);
				return matchDept && matchEst;
			});
		}

		getEstablishments() {
			return this.state.establishments;
		}

		getCurrentSchedule() {
			return this.state.schedules[this.activeWeek] || this.state.schedules['2026-W47'];
		}

		calculateKPIs(weekCode = this.activeWeek, estFilter = this.activeEstablishment, deptFilter = this.activeDepartment) {
			const sched = this.state.schedules[weekCode] || this.state.schedules['2026-W47'];
			let filteredShifts = sched.shifts.filter(s => s.shift_type !== 'rest');

			if (estFilter !== 'Todos') {
				filteredShifts = filteredShifts.filter(s => s.establishment_name === estFilter || String(s.establishment_id) === String(estFilter));
			}

			if (deptFilter !== 'Todos') {
				const deptEmps = new Set(this.state.employees.filter(e => e.department === deptFilter).map(e => e.id));
				filteredShifts = filteredShifts.filter(s => deptEmps.has(s.employee_id));
			}

			// 1. Ventas Previstas
			let baseSales = sched.forecasted_sales || 48500.00;
			if (estFilter !== 'Todos') {
				const est = this.state.establishments.find(e => e.name === estFilter || String(e.id) === String(estFilter));
				if (est) baseSales = est.salesForecast;
			}
			const forecastedSales = baseSales * (1 + (this.whatIfScenario.salesDeltaPct / 100));

			// 2. Horas Recomendadas ($150/h objetivo de ventas por hora laboral estándar Retail B2B USA)
			const salesPerHourTarget = 150.00 * (this.whatIfScenario.prodFactor || 1.0);
			const minCoverageHours = 120.0;
			const recommendedHours = Math.max(minCoverageHours, Math.round((forecastedSales / salesPerHourTarget) * 10) / 10);

			// 3. Horas Programadas & Costo Laboral Estimado
			let scheduledHours = 0;
			let estimatedCost = 0;

			const empCostMap = {};
			this.state.employees.forEach(e => empCostMap[e.id] = Number(e.hourly_cost || 16.50));

			filteredShifts.forEach(shift => {
				scheduledHours += Number(shift.net_hours || 0);
				const rate = empCostMap[shift.employee_id] || 16.50;
				estimatedCost += (Number(shift.net_hours || 0) * rate);
			});

			scheduledHours = Math.round(scheduledHours * 10) / 10;
			estimatedCost = Math.round(estimatedCost * 100) / 100;

			// 4. Presupuesto y Varianza
			let budget = sched.labor_budget || 2800.00;
			if (estFilter !== 'Todos') {
				const est = this.state.establishments.find(e => e.name === estFilter || String(e.id) === String(estFilter));
				if (est) budget = est.budget;
			}
			const budgetVariance = Math.round((budget - estimatedCost) * 100) / 100;
			const hoursVariance = Math.round((scheduledHours - recommendedHours) * 10) / 10;

			// 5. Cobertura de Personal
			const coverageScore = recommendedHours > 0 ? Math.min(100, Math.round((scheduledHours / recommendedHours) * 1000) / 10) : 100;

			return {
				forecasted_sales: forecastedSales,
				recommended_hours: recommendedHours,
				scheduled_hours: scheduledHours,
				estimated_labor_cost: estimatedCost,
				labor_budget: budget,
				budget_variance: budgetVariance,
				hours_variance: hoursVariance,
				coverage_score_pct: coverageScore,
				status: sched.status,
				version: sched.version,
				active_employees: new Set(filteredShifts.map(s => s.employee_id)).size
			};
		}

		getScheduleMatrix(weekCode = this.activeWeek, estFilter = this.activeEstablishment, deptFilter = this.activeDepartment) {
			const sched = this.state.schedules[weekCode] || this.state.schedules['2026-W47'];
			const emps = this.getEmployees(deptFilter, estFilter);

			const matrix = emps.map(emp => {
				const empShifts = sched.shifts.filter(s => s.employee_id === emp.id);
				const days = { 1: null, 2: null, 3: null, 4: null, 5: null, 6: null, 7: null };
				let totalHours = 0;

				empShifts.forEach(shift => {
					days[shift.day_of_week] = shift;
					if (shift.shift_type !== 'rest') {
						totalHours += Number(shift.net_hours || 0);
					}
				});

				return {
					employee: emp,
					days: days,
					total_scheduled_hours: Math.round(totalHours * 10) / 10
				};
			});

			return matrix;
		}

		// =================================================================
		// 4. ACCIONES SOBRE HORARIOS
		// =================================================================

		addOrUpdateShift(shiftData) {
			const sched = this.state.schedules[this.activeWeek];
			if (!sched) return { success: false, error: 'Semana no encontrada' };

			const emp = this.state.employees.find(e => e.id === Number(shiftData.employee_id));
			if (!emp) return { success: false, error: 'Empleado no encontrado' };

			const startParts = shiftData.start_time.split(':').map(Number);
			const endParts = shiftData.end_time.split(':').map(Number);
			const startMin = startParts[0] * 60 + startParts[1];
			const endMin = endParts[0] * 60 + endParts[1];
			let grossMin = endMin - startMin;
			if (grossMin < 0) grossMin += 1440;
			const breakMin = Number(shiftData.break_minutes || 0);
			const netMin = Math.max(0, grossMin - breakMin);
			const netHours = shiftData.shift_type === 'rest' ? 0 : Math.round((netMin / 60) * 10) / 10;

			const existingIndex = sched.shifts.findIndex(s => s.employee_id === emp.id && s.day_of_week === Number(shiftData.day_of_week));

			const newShift = {
				id: shiftData.id || `${this.activeWeek}-${emp.id}-${shiftData.day_of_week}`,
				week_code: this.activeWeek,
				employee_id: emp.id,
				employee_name: emp.name,
				employee_role: emp.role,
				shift_date: shiftData.shift_date || '2026-11-16',
				day_of_week: Number(shiftData.day_of_week),
				start_time: shiftData.start_time,
				end_time: shiftData.end_time,
				break_minutes: breakMin,
				net_hours: netHours,
				shift_type: shiftData.shift_type || 'mid',
				shift_label: shiftData.shift_label || 'Turno',
				establishment_id: emp.establishment_id,
				establishment_name: emp.establishment_name,
				task_notes: shiftData.task_notes || ''
			};

			if (existingIndex >= 0) {
				sched.shifts[existingIndex] = newShift;
			} else {
				sched.shifts.push(newShift);
			}

			this.recordAudit('SHIFT_UPDATED', `Turno actualizado para ${emp.name} (Día ${shiftData.day_of_week}: ${shiftData.start_time}-${shiftData.end_time})`);
			this.saveToStorage();
			this.emit('shift:updated', { shift: newShift });
			return { success: true, shift: newShift };
		}

		deleteShift(shiftId) {
			const sched = this.state.schedules[this.activeWeek];
			if (!sched) return { success: false };

			const shiftIndex = sched.shifts.findIndex(s => String(s.id) === String(shiftId));
			if (shiftIndex >= 0) {
				const removed = sched.shifts[shiftIndex];
				sched.shifts[shiftIndex] = {
					...removed,
					start_time: '00:00',
					end_time: '00:00',
					break_minutes: 0,
					net_hours: 0,
					shift_type: 'rest',
					shift_label: 'Descanso Semanal'
				};
				this.recordAudit('SHIFT_DELETED', `Turno removido para ${removed.employee_name}`);
				this.saveToStorage();
				this.emit('shift:updated', { shift: sched.shifts[shiftIndex] });
				return { success: true };
			}
			return { success: false };
		}

		moveShift(shiftId, targetEmpId, targetDow) {
			const sched = this.state.schedules[this.activeWeek];
			if (!sched) return { success: false };

			const shift = sched.shifts.find(s => String(s.id) === String(shiftId));
			const targetEmp = this.state.employees.find(e => e.id === Number(targetEmpId));
			if (!shift || !targetEmp) return { success: false };

			const oldEmpName = shift.employee_name;
			shift.employee_id = targetEmp.id;
			shift.employee_name = targetEmp.name;
			shift.employee_role = targetEmp.role;
			shift.day_of_week = Number(targetDow);
			shift.id = `${this.activeWeek}-${targetEmp.id}-${targetDow}`;

			this.recordAudit('SHIFT_MOVED', `Turno de ${oldEmpName} movido a ${targetEmp.name} (Día ${targetDow})`);
			this.saveToStorage();
			this.emit('shift:updated', { shift });
			return { success: true };
		}

		duplicateShift(shiftId) {
			const sched = this.state.schedules[this.activeWeek];
			if (!sched) return { success: false };

			const shift = sched.shifts.find(s => String(s.id) === String(shiftId));
			if (!shift) return { success: false };

			const nextDow = shift.day_of_week < 7 ? shift.day_of_week + 1 : 1;
			return this.addOrUpdateShift({
				employee_id: shift.employee_id,
				day_of_week: nextDow,
				start_time: shift.start_time,
				end_time: shift.end_time,
				break_minutes: shift.break_minutes,
				shift_type: shift.shift_type,
				shift_label: shift.shift_label,
				task_notes: shift.task_notes
			});
		}

		publishSchedule(weekCode = this.activeWeek) {
			const sched = this.state.schedules[weekCode];
			if (!sched) return { success: false };

			sched.status = 'published';
			sched.version = (sched.version || 1) + 1;
			sched.published_at = new Date().toISOString().replace('T', ' ').substring(0, 19);
			sched.published_by = this.currentRole === 'admin' ? 'Gonzalo Javier Núñez (Admin)' : 'Carlos Mendoza (Supervisor)';

			this.addNotification({
				title: `Programación ${weekCode} Publicada (v${sched.version})`,
				text: `Horarios oficiales publicados por ${sched.published_by}. 20 colaboradores sincronizados en Web & Mobile.`,
				type: 'publish'
			});

			this.recordAudit('SCHEDULE_PUBLISHED', `Programación ${weekCode} publicada formalmente con versión v${sched.version}`);
			this.saveToStorage();
			this.emit('schedule:published', { schedule: sched });
			return { success: true, version: sched.version };
		}

		// =================================================================
		// 5. MOTOR DE TAREAS & OPERACIONES EN TIENDA (SHARED WEB & MOBILE)
		// =================================================================

		getTasks(filter = {}) {
			let tasks = this.state.tasks || [];
			if (filter.employee_id) {
				tasks = tasks.filter(t => t.employee_id === Number(filter.employee_id));
			}
			if (filter.establishment_id) {
				tasks = tasks.filter(t => t.establishment_id === Number(filter.establishment_id));
			}
			if (filter.status && filter.status !== 'all') {
				tasks = tasks.filter(t => t.status === filter.status);
			}
			return tasks;
		}

		createTask(taskData) {
			const newTask = {
				id: Date.now(),
				title: taskData.title,
				description: taskData.description || '',
				employee_id: Number(taskData.employee_id),
				employee_name: taskData.employee_name || 'Colaborador',
				supervisor_id: Number(taskData.supervisor_id || 3),
				supervisor_name: taskData.supervisor_name || 'Carlos Mendoza',
				establishment_id: Number(taskData.establishment_id || 1),
				establishment_name: taskData.establishment_name || 'Prince Distributors — Hallandale HQ',
				date: taskData.date || new Date().toISOString().slice(0, 10),
				priority: taskData.priority || 'normal',
				status: 'pending',
				evidence_url: null,
				notes: taskData.notes || ''
			};

			if (!this.state.tasks) this.state.tasks = [];
			this.state.tasks.unshift(newTask);
			this.saveToStorage();
			this.emit('task:created', { task: newTask });

			// Sincronización transparente con Supabase PostgreSQL
			try {
				fetch('/api/b2b/supabase/tasks', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(newTask)
				}).catch(function () {});
			} catch (e) {}

			return { success: true, task: newTask };
		}

		updateTaskStatus(taskId, status, notes = '', evidenceUrl = null) {
			if (!this.state.tasks) return { success: false };
			const task = this.state.tasks.find(t => t.id === Number(taskId));
			if (!task) return { success: false, error: 'Tarea no encontrada' };

			task.status = status;
			if (notes) task.notes = notes;
			if (evidenceUrl) task.evidence_url = evidenceUrl;

			this.recordAudit('TASK_STATUS_UPDATED', `Tarea #${taskId} (${task.title}) cambiada a ${status}`);
			this.saveToStorage();
			this.emit('task:updated', { task });

			// Sincronización transparente con Supabase PostgreSQL
			try {
				if (status === 'completed') {
					fetch(`/api/b2b/supabase/tasks/${taskId}/complete`, {
						method: 'POST',
						headers: { 'Content-Type': 'application/json' },
						body: JSON.stringify({ notes, evidenceUrl })
					}).catch(function () {});
				}
			} catch (e) {}

			return { success: true, task };
		}

		deleteTask(taskId) {
			if (!this.state.tasks) return { success: false };
			const idx = this.state.tasks.findIndex(t => t.id === Number(taskId));
			if (idx >= 0) {
				this.state.tasks.splice(idx, 1);
				this.saveToStorage();
				this.emit('task:deleted', { taskId });
				return { success: true };
			}
			return { success: false };
		}

		// =================================================================
		// 6. CONTROL FEFO, ESCÁNER UPC & REPOSICIÓN BODEGA -> GÓNDOLA
		// =================================================================

		getInventory() {
			return this.state.inventory || DEFAULT_UPC_PRODUCTS;
		}

		findProductByUpc(upc) {
			const cleanUpc = String(upc).trim();
			return (this.state.inventory || []).find(p => p.upc === cleanUpc || p.sku.toLowerCase() === cleanUpc.toLowerCase());
		}

		restockShelf(productId, quantity, lot = null) {
			const prod = (this.state.inventory || []).find(p => p.id === Number(productId));
			if (!prod) return { success: false, error: 'Producto no encontrado' };

			const qty = Number(quantity) || 0;
			if (qty <= 0) return { success: false, error: 'Cantidad inválida' };

			// Validación de stock en bodega
			if (prod.warehouse_stock < qty) {
				return { success: false, error: `Stock insuficiente en bodega (${prod.warehouse_stock} disponibles)` };
			}

			// Traslado interno bodega a góndola (No cambia el stock total de la tienda)
			prod.warehouse_stock -= qty;
			prod.shelf_stock += qty;

			this.recordAudit('SHELF_RESTOCKED', `Traslado interno: ${qty} unidades de ${prod.name} de bodega a góndola (FEFO: ${prod.lot})`);
			this.saveToStorage();
			this.emit('inventory:updated', { product: prod });

			// Sincronización transparente con Supabase PostgreSQL
			try {
				fetch('/api/b2b/supabase/inventory/restock', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({ product_id: productId, quantity: qty })
				}).catch(function () {});
			} catch (e) {}

			return { success: true, product: prod };
		}

		recordShrinkage(productId, quantity, reason = 'Merma operacional / daño') {
			const prod = (this.state.inventory || []).find(p => p.id === Number(productId));
			if (!prod) return { success: false };

			const qty = Number(quantity) || 0;
			prod.shelf_stock = Math.max(0, prod.shelf_stock - qty);

			this.recordAudit('SHRINKAGE_RECORDED', `Merma registrada: ${qty} un. de ${prod.name}. Causa: ${reason}`);
			this.saveToStorage();
			this.emit('inventory:updated', { product: prod });
			return { success: true, product: prod };
		}

		recordStockCount(productId, actualShelfStock, actualWarehouseStock = null) {
			const prod = (this.state.inventory || []).find(p => p.id === Number(productId));
			if (!prod) return { success: false };

			const oldShelf = prod.shelf_stock;
			prod.shelf_stock = Number(actualShelfStock);
			if (actualWarehouseStock !== null) {
				prod.warehouse_stock = Number(actualWarehouseStock);
			}

			this.recordAudit('STOCK_COUNTED', `Conteo cíclico ${prod.name}: Góndola antes=${oldShelf}, conteo=${prod.shelf_stock}`);
			this.saveToStorage();
			this.emit('inventory:updated', { product: prod });
			return { success: true, product: prod };
		}

		// =================================================================
		// 7. ASISTENCIA & SESIONES OPERATIVAS (MI JORNADA)
		// =================================================================

		clockIn(employeeId, establishmentId) {
			const emp = this.state.employees.find(e => e.id === Number(employeeId));
			const est = this.state.establishments.find(e => e.id === Number(establishmentId));
			const nowStr = new Date().toISOString().replace('T', ' ').substring(0, 19);

			const session = {
				id: Date.now(),
				employee_id: emp ? emp.id : employeeId,
				employee_name: emp ? emp.name : 'Colaborador',
				establishment_id: est ? est.id : establishmentId,
				establishment_name: est ? est.name : 'Prince Distributors — Hallandale HQ',
				clock_in: nowStr,
				clock_out: null,
				status: 'in_progress',
				tasks_completed: 0,
				restocked_units: 0
			};

			this.activeSessions[employeeId] = session;
			if (!this.state.attendance) this.state.attendance = [];
			this.state.attendance.unshift(session);

			this.recordAudit('CLOCK_IN', `Inicio de jornada para ${session.employee_name} en ${session.establishment_name}`);
			this.saveToStorage();
			this.emit('attendance:clock_in', { session });
			return { success: true, session };
		}

		clockOut(employeeId, notes = '') {
			const session = this.activeSessions[employeeId];
			const nowStr = new Date().toISOString().replace('T', ' ').substring(0, 19);

			if (session) {
				session.clock_out = nowStr;
				session.status = 'completed';
				session.notes = notes;
				delete this.activeSessions[employeeId];
			}

			this.recordAudit('CLOCK_OUT', `Fin de jornada para colaborador #${employeeId}`);
			this.saveToStorage();
			this.emit('attendance:clock_out', { employeeId });
			return { success: true };
		}

		getActiveSession(employeeId) {
			return this.activeSessions[employeeId] || null;
		}

		// =================================================================
		// 8. OPTIMIZACIÓN INTELIGENTE (REGLAS DETERMINISTAS FLORIDA)
		// =================================================================

		getOptimizationProposal(weekCode = this.activeWeek) {
			const sched = this.state.schedules[weekCode];
			const currentKpis = this.calculateKPIs(weekCode);

			const mondayOpeningShifts = sched.shifts.filter(s => s.day_of_week === 1 && s.shift_type === 'opening');
			const suggestions = [];

			if (mondayOpeningShifts.length < 4) {
				suggestions.push({
					id: 'SUG-01',
					type: 'coverage_boost',
					title: 'Refuerzo en Pico Mayorista de Apertura (Lunes)',
					description: 'Asignar a Miguel Ángel Flores (EMP-004) en turno de Apertura (07:00-15:30) en Hallandale HQ para absorber descarga de 1,420 cajas master.',
					impact_hours: 8.0,
					impact_cost: 128.00,
					target_emp_id: 4,
					target_dow: 1,
					proposed_shift: { start: '07:00', end: '15:30', breakMin: 30, type: 'opening', label: 'Apertura WMS' }
				});
			}

			suggestions.push({
				id: 'SUG-02',
				type: 'efficiency_trim',
				title: 'Nivelación de Horas en Cierre (Viernes)',
				description: 'Ajustar el turno de cierre a 7.5h netas en Fort Lauderdale Hub para evitar sobrecosto sin afectar despacho.',
				impact_hours: -0.5,
				impact_cost: -8.75,
				target_emp_id: 9,
				target_dow: 5,
				proposed_shift: { start: '14:00', end: '22:00', breakMin: 30, type: 'closing', label: 'Cierre Logístico' }
			});

			const projectedHours = currentKpis.scheduled_hours + suggestions.reduce((acc, s) => acc + s.impact_hours, 0);
			const projectedCost = currentKpis.estimated_labor_cost + suggestions.reduce((acc, s) => acc + s.impact_cost, 0);
			const projectedCoverage = currentKpis.recommended_hours > 0 ? Math.min(100, Math.round((projectedHours / currentKpis.recommended_hours) * 1000) / 10) : 100;

			return {
				currentKpis,
				suggestions,
				projectedKpis: {
					scheduled_hours: Math.round(projectedHours * 10) / 10,
					estimated_labor_cost: Math.round(projectedCost * 100) / 100,
					coverage_score_pct: projectedCoverage,
					budget_variance: Math.round((currentKpis.labor_budget - projectedCost) * 100) / 100
				}
			};
		}

		applyOptimization(weekCode = this.activeWeek) {
			const sched = this.state.schedules[weekCode];
			if (!sched) return { success: false };

			this.lastOptimizationBackup = JSON.parse(JSON.stringify(sched.shifts));

			const proposal = this.getOptimizationProposal(weekCode);
			proposal.suggestions.forEach(s => {
				this.addOrUpdateShift({
					employee_id: s.target_emp_id,
					day_of_week: s.target_dow,
					start_time: s.proposed_shift.start,
					end_time: s.proposed_shift.end,
					break_minutes: s.proposed_shift.breakMin,
					shift_type: s.proposed_shift.type,
					shift_label: s.proposed_shift.label,
					task_notes: 'Turno optimizado por algoritmo WFM Florida'
				});
			});

			this.recordAudit('SCHEDULE_OPTIMIZED', `Semana ${weekCode} optimizada automáticamente con ${proposal.suggestions.length} mejoras`);
			this.saveToStorage();
			this.emit('schedule:optimized', { weekCode });
			return { success: true, count: proposal.suggestions.length };
		}

		revertOptimization(weekCode = this.activeWeek) {
			const sched = this.state.schedules[weekCode];
			if (!sched || !this.lastOptimizationBackup) return { success: false };

			sched.shifts = JSON.parse(JSON.stringify(this.lastOptimizationBackup));
			this.lastOptimizationBackup = null;

			this.recordAudit('SCHEDULE_REVERTED', `Optimización de semana ${weekCode} revertida al estado previo`);
			this.saveToStorage();
			this.emit('shift:updated', {});
			return { success: true };
		}

		// =================================================================
		// 9. ROLES, NOTIFICACIONES Y AUDITORÍA
		// =================================================================

		switchRole(newRole, employeeId = null) {
			this.currentRole = newRole;
			if (employeeId !== null) {
				this.currentEmployeeId = Number(employeeId);
			} else {
				if (newRole === 'merchandiser' || newRole === 'reponedor') this.currentEmployeeId = 2; // Rosa Huamán
				else if (newRole === 'supervisor') this.currentEmployeeId = 3; // Carlos Mendoza
				else if (newRole === 'sales_rep' || newRole === 'employee') this.currentEmployeeId = 1; // Jorge Quispe
				else this.currentEmployeeId = 20; // Gonzalo Núñez
			}
			this.emit('role:changed', { role: newRole, employeeId: this.currentEmployeeId });
		}

		addNotification(notif) {
			const item = {
				id: Date.now(),
				title: notif.title,
				text: notif.text,
				time: 'Hace un momento',
				read: false,
				type: notif.type || 'info'
			};
			if (!this.state.notifications) this.state.notifications = [];
			this.state.notifications.unshift(item);
			if (this.state.notifications.length > 20) this.state.notifications.pop();
			this.saveToStorage();
			this.emit('notification:added', { notification: item });
		}

		recordAudit(action, details) {
			const log = {
				id: Date.now(),
				time: new Date().toISOString().replace('T', ' ').substring(0, 19),
				user: this.currentRole === 'admin' ? 'Gonzalo Javier Núñez (Admin)' : (this.currentRole === 'supervisor' ? 'Carlos Mendoza (Supervisor)' : 'Rosa Huamán (Merchandiser)'),
				action: action,
				details: details
			};
			if (!this.state.auditLog) this.state.auditLog = [];
			this.state.auditLog.unshift(log);
		}

		getKpis(week, estab, terr, dept, sup) {
			return this.calculateKPIs(week, estab, dept);
		}

		getSchedule(week) {
			return this.state.schedules[week] || this.state.schedules[this.activeWeek] || this.state.schedules['2026-W47'];
		}

		updateShift(shiftId, updates) {
			const sched = this.getSchedule(this.activeWeek);
			const shift = sched.shifts.find(s => String(s.id) === String(shiftId));
			if (!shift) return { success: false };
			return this.addOrUpdateShift({ ...shift, ...updates });
		}

		addShift(shiftData) {
			return this.addOrUpdateShift(shiftData);
		}

		runSmartOptimization(weekCode) {
			const prop = this.getOptimizationProposal(weekCode);
			return {
				before: {
					scheduled_hours: prop.currentKpis.scheduled_hours,
					coverage_pct: prop.currentKpis.coverage_score_pct,
					cost: prop.currentKpis.estimated_labor_cost
				},
				after: {
					scheduled_hours: prop.projectedKpis.scheduled_hours,
					coverage_pct: prop.projectedKpis.coverage_score_pct,
					cost: prop.projectedKpis.estimated_labor_cost
				},
				suggestions: prop.suggestions.map(s => ({
					action: s.title,
					impact: `${s.impact_hours >= 0 ? '+' : ''}${s.impact_hours}h | ${s.impact_cost >= 0 ? '+$' : '-$'}${Math.abs(s.impact_cost)}`
				}))
			};
		}

		getNotifications() {
			return this.state.notifications || [];
		}

		markAllNotificationsRead() {
			if (this.state.notifications) {
				this.state.notifications.forEach(n => n.read = true);
				this.saveToStorage();
			}
		}

		setActiveRole(role) {
			this.switchRole(role);
		}

		simulateDemand(baseSales, deltaPct, absences) {
			const simSales = baseSales * (1 + (deltaPct / 100));
			const recHours = Math.round((simSales / 150.0) * 10) / 10;
			const schedHours = Math.max(0, 336.0 - (absences * 48.0));
			const cov = recHours > 0 ? Math.min(100, Math.round((schedHours / recHours) * 1000) / 10) : 100;
			return {
				success: true,
				simulated_sales: simSales,
				recommended_hours: recHours,
				projected_coverage_pct: cov,
				advice: cov >= 95 ? 'Dotación óptima para demanda proyectada en Florida.' : 'Se recomienda reforzar dotación para evitar quiebres de servicio.'
			};
		}

		exportDatasetJson() {
			const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(this.state, null, 2));
			const downloadAnchor = document.createElement('a');
			downloadAnchor.setAttribute("href", dataStr);
			downloadAnchor.setAttribute("download", `prince_wfm_demo_usa_${new Date().toISOString().slice(0,10)}.json`);
			document.body.appendChild(downloadAnchor);
			downloadAnchor.click();
			downloadAnchor.remove();
		}
	}

	// Instanciación del Singleton
	window.PrinceDemoEngine = new PrinceDemoEngine();

})(window);
</script>
