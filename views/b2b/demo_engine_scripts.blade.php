<script>
/**
 * =========================================================================
 * PRINCE DISTRIBUTORS — MOTOR CENTRAL DE DATOS DEMO & WORKFORCE MANAGEMENT
 * Arquitectura Desacoplada: Modelos de Dominio, Repositorio, Motor de Cálculos y UI
 * =========================================================================
 */

(function (window) {
	'use strict';

	const STORAGE_KEY = 'PRINCE_DISTRIBUTORS_WFM_DEMO_V3';

	// =====================================================================
	// 1. DATASET CENTRAL ESTÁNDAR (20 Empleados, 8 Sedes, 4 Semanas, 12 Sem Ventas)
	// =====================================================================

	const DEFAULT_ESTABLISHMENTS = [
		{ id: 1, code: 'EST-001', name: 'Almacén Central Norte', territory: 'Lima Norte', address: 'Av. Alfredo Mendiola 3420, Los Olivos', budget: 3200.00, minCoverage: 3, salesForecast: 48500.00 },
		{ id: 2, code: 'EST-002', name: 'Almacén Callao Port', territory: 'Callao', address: 'Av. Elmer Faucett 1850, Callao', budget: 2800.00, minCoverage: 2, salesForecast: 38200.00 },
		{ id: 3, code: 'EST-003', name: 'Distribuidora San Juan', territory: 'Lima Norte', address: 'Av. Próceres de la Independencia 1120, SJL', budget: 2400.00, minCoverage: 2, salesForecast: 29800.00 },
		{ id: 4, code: 'EST-004', name: 'Supermercados Los Andes (Sede Central)', territory: 'Lima Centro', address: 'Av. Javier Prado Este 4200, Surco', budget: 3500.00, minCoverage: 3, salesForecast: 54100.00 },
		{ id: 5, code: 'EST-005', name: 'Hipermercado Metro Sur', territory: 'Lima Sur', address: 'Av. Pedro Miotta 820, SJM', budget: 2600.00, minCoverage: 2, salesForecast: 34500.00 },
		{ id: 6, code: 'EST-006', name: 'Mercado Mayorista Santa Anita', territory: 'Lima Centro', address: 'Av. Metropolitana 105, Santa Anita', budget: 2900.00, minCoverage: 2, salesForecast: 42300.00 },
		{ id: 7, code: 'EST-007', name: 'Centro Logístico Faucett', territory: 'Callao', address: 'Av. Argentina 4100, Callao', budget: 2500.00, minCoverage: 2, salesForecast: 31200.00 },
		{ id: 8, code: 'EST-008', name: 'Mini Market Express Miraflores', territory: 'Lima Centro', address: 'Av. Larco 740, Miraflores', budget: 1800.00, minCoverage: 1, salesForecast: 19400.00 }
	];

	const DEFAULT_EMPLOYEES = [
		{ id: 1, code: 'EMP-001', name: 'Jorge Luis Quispe', role: 'sales_rep', role_label: 'Vendedor Senior B2B', department: 'Fuerza de Ventas B2B', email: 'jquispe@princedistributors.com', phone: '+51 984 111 201', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 8.50, supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 1, establishment_name: 'Almacén Central Norte', territory: 'Lima Norte', active: 1 },
		{ id: 2, code: 'EMP-002', name: 'Rosa María Huamán', role: 'merchandiser', role_label: 'Reponedora Líder', department: 'Reposición & Merchandising', email: 'rhuaman@princedistributors.com', phone: '+51 984 111 202', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 7.50, supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 1, establishment_name: 'Almacén Central Norte', territory: 'Lima Norte', active: 1 },
		{ id: 3, code: 'EMP-003', name: 'Carlos Mendoza', role: 'supervisor', role_label: 'Supervisor de Operaciones y Ventas', department: 'Supervisión & Control', email: 'cmendoza@princedistributors.com', phone: '+51 984 111 203', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 12.00, supervisor_id: 20, supervisor_name: 'Gonzalo Javier Núñez', establishment_id: 1, establishment_name: 'Almacén Central Norte', territory: 'Lima Norte', active: 1 },
		{ id: 4, code: 'EMP-004', name: 'Miguel Ángel Flores', role: 'merchandiser', role_label: 'Reponedor Almacén', department: 'Reposición & Merchandising', email: 'mflores@princedistributors.com', phone: '+51 984 111 204', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 7.50, supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 1, establishment_name: 'Almacén Central Norte', territory: 'Lima Norte', active: 1 },
		{ id: 5, code: 'EMP-005', name: 'Ana Sofía Rivas', role: 'sales_rep', role_label: 'Ejecutiva de Cuentas B2B', department: 'Fuerza de Ventas B2B', email: 'arivas@princedistributors.com', phone: '+51 984 111 205', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 9.00, supervisor_id: 7, supervisor_name: 'Patricia Vega', establishment_id: 4, establishment_name: 'Supermercados Los Andes (Sede Central)', territory: 'Lima Centro', active: 1 },
		{ id: 6, code: 'EMP-006', name: 'Diego Alonso Ramos', role: 'merchandiser', role_label: 'Reponedor Góndola', department: 'Reposición & Merchandising', email: 'dramos@princedistributors.com', phone: '+51 984 111 206', contract_type: 'part_time', contracted_hours: 40, hourly_cost: 7.50, supervisor_id: 7, supervisor_name: 'Patricia Vega', establishment_id: 4, establishment_name: 'Supermercados Los Andes (Sede Central)', territory: 'Lima Centro', active: 1 },
		{ id: 7, code: 'EMP-007', name: 'Patricia Vega', role: 'supervisor', role_label: 'Supervisora Comercial Centro', department: 'Supervisión & Control', email: 'pvega@princedistributors.com', phone: '+51 984 111 207', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 12.50, supervisor_id: 20, supervisor_name: 'Gonzalo Javier Núñez', establishment_id: 4, establishment_name: 'Supermercados Los Andes (Sede Central)', territory: 'Lima Centro', active: 1 },
		{ id: 8, code: 'EMP-008', name: 'Brenda Lucía Vargas', role: 'sales_rep', role_label: 'Preventista de Ruta Sur', department: 'Fuerza de Ventas B2B', email: 'bvargas@princedistributors.com', phone: '+51 984 111 208', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 8.50, supervisor_id: 11, supervisor_name: 'Roberto Salcedo', establishment_id: 5, establishment_name: 'Hipermercado Metro Sur', territory: 'Lima Sur', active: 1 },
		{ id: 9, code: 'EMP-009', name: 'Fernando José Castro', role: 'merchandiser', role_label: 'Montacarguista y Reponedor', department: 'Operaciones & Logística', email: 'fcastro@princedistributors.com', phone: '+51 984 111 209', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 8.00, supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 2, establishment_name: 'Almacén Callao Port', territory: 'Callao', active: 1 },
		{ id: 10, code: 'EMP-010', name: 'Lucía Carmen Espinoza', role: 'merchandiser', role_label: 'Despachadora & Picking', department: 'Operaciones & Logística', email: 'lespinoza@princedistributors.com', phone: '+51 984 111 210', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 7.50, supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 3, establishment_name: 'Distribuidora San Juan', territory: 'Lima Norte', active: 1 },
		{ id: 11, code: 'EMP-011', name: 'Roberto Salcedo', role: 'supervisor', role_label: 'Supervisor Territorial Sur', department: 'Supervisión & Control', email: 'rsalcedo@princedistributors.com', phone: '+51 984 111 211', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 12.00, supervisor_id: 20, supervisor_name: 'Gonzalo Javier Núñez', establishment_id: 5, establishment_name: 'Hipermercado Metro Sur', territory: 'Lima Sur', active: 1 },
		{ id: 12, code: 'EMP-012', name: 'Juan Carlos Benítez', role: 'sales_rep', role_label: 'Vendedor Mayorista Callao', department: 'Fuerza de Ventas B2B', email: 'jbenitez@princedistributors.com', phone: '+51 984 111 212', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 8.50, supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 7, establishment_name: 'Centro Logístico Faucett', territory: 'Callao', active: 1 },
		{ id: 13, code: 'EMP-013', name: 'Valeria Nicole Morales', role: 'merchandiser', role_label: 'Reponedora Nocturna', department: 'Reposición & Merchandising', email: 'vmorales@princedistributors.com', phone: '+51 984 111 213', contract_type: 'full_time', contracted_hours: 44, hourly_cost: 8.00, supervisor_id: 7, supervisor_name: 'Patricia Vega', establishment_id: 4, establishment_name: 'Supermercados Los Andes (Sede Central)', territory: 'Lima Centro', active: 1 },
		{ id: 14, code: 'EMP-014', name: 'Kevin Alexander Gómez', role: 'merchandiser', role_label: 'Chofer & Despacho Mayorista', department: 'Operaciones & Logística', email: 'kgomez@princedistributors.com', phone: '+51 984 111 214', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 8.50, supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 2, establishment_name: 'Almacén Callao Port', territory: 'Callao', active: 1 },
		{ id: 15, code: 'EMP-015', name: 'María Elena Gutiérrez', role: 'sales_rep', role_label: 'Atención Cuentas Clave', department: 'Fuerza de Ventas B2B', email: 'mgutierrez@princedistributors.com', phone: '+51 984 111 215', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 9.00, supervisor_id: 7, supervisor_name: 'Patricia Vega', establishment_id: 6, establishment_name: 'Mercado Mayorista Santa Anita', territory: 'Lima Centro', active: 1 },
		{ id: 16, code: 'EMP-016', name: 'Renzo Manuel Paucar', role: 'merchandiser', role_label: 'Reponedor de Carga', department: 'Reposición & Merchandising', email: 'rpaucar@princedistributors.com', phone: '+51 984 111 216', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 7.50, supervisor_id: 11, supervisor_name: 'Roberto Salcedo', establishment_id: 5, establishment_name: 'Hipermercado Metro Sur', territory: 'Lima Sur', active: 1 },
		{ id: 17, code: 'EMP-017', name: 'Diana Carolina Paredes', role: 'sales_rep', role_label: 'Preventista Tradicional', department: 'Fuerza de Ventas B2B', email: 'dparedes@princedistributors.com', phone: '+51 984 111 217', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 8.50, supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 3, establishment_name: 'Distribuidora San Juan', territory: 'Lima Norte', active: 1 },
		{ id: 18, code: 'EMP-018', name: 'Andrés Felipe Soto', role: 'merchandiser', role_label: 'Operador de Racks WMS', department: 'Operaciones & Logística', email: 'asoto@princedistributors.com', phone: '+51 984 111 218', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 8.00, supervisor_id: 3, supervisor_name: 'Carlos Mendoza', establishment_id: 7, establishment_name: 'Centro Logístico Faucett', territory: 'Callao', active: 1 },
		{ id: 19, code: 'EMP-019', name: 'Camila Fernanda Ruiz', role: 'merchandiser', role_label: 'Reponedora Express', department: 'Reposición & Merchandising', email: 'cruiz@princedistributors.com', phone: '+51 984 111 219', contract_type: 'part_time', contracted_hours: 36, hourly_cost: 7.50, supervisor_id: 7, supervisor_name: 'Patricia Vega', establishment_id: 8, establishment_name: 'Mini Market Express Miraflores', territory: 'Lima Centro', active: 1 },
		{ id: 20, code: 'EMP-020', name: 'Gonzalo Javier Núñez', role: 'admin', role_label: 'Administrador General WFM', department: 'Dirección de Operaciones', email: 'gnunez@princedistributors.com', phone: '+51 984 111 220', contract_type: 'full_time', contracted_hours: 48, hourly_cost: 14.00, supervisor_id: null, supervisor_name: 'Gerencia General', establishment_id: 1, establishment_name: 'Almacén Central Norte', territory: 'Lima Centro', active: 1 }
	];

	// Generador de turnos deterministas para las 4 semanas
	function generateShiftsForWeek(weekCode, startDateStr, isHistorical) {
		const shifts = [];
		let shiftIdCounter = 1;
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
					// Domingo descanso para casi todos
					sType = 'rest';
					sLabel = 'Descanso';
					start = '00:00';
					end = '00:00';
					breakMin = 0;
					net = 0;
				} else if (emp.role === 'sales_rep') {
					if (dow === 6) {
						// Sábado medio día
						sType = 'standard';
						sLabel = 'Seguimiento';
						start = '09:00';
						end = '13:00';
						breakMin = 0;
						net = 4.0;
					} else {
						sType = 'standard';
						sLabel = 'Ruta Preventa';
						start = '08:00';
						end = '17:00';
						breakMin = 60;
						net = 8.0;
					}
				} else if (emp.role === 'merchandiser') {
					if (emp.id % 3 === 0) {
						sType = 'opening';
						sLabel = 'Apertura';
						start = '07:00';
						end = '15:30';
						breakMin = 30;
						net = 8.0;
					} else if (emp.id % 3 === 1) {
						sType = 'mid';
						sLabel = 'Intermedio';
						start = '09:00';
						end = '17:30';
						breakMin = 30;
						net = 8.0;
					} else {
						sType = 'closing';
						sLabel = 'Cierre';
						start = '13:30';
						end = '22:00';
						breakMin = 30;
						net = 8.0;
					}
				} else if (emp.role === 'supervisor') {
					sType = 'standard';
					sLabel = 'Supervisión';
					start = '08:00';
					end = '16:30';
					breakMin = 30;
					net = 8.0;
				} else {
					// Admin
					if (dow <= 5) {
						sType = 'standard';
						sLabel = 'Gestión WFM';
						start = '08:30';
						end = '17:30';
						breakMin = 60;
						net = 8.0;
					} else {
						sType = 'rest';
						sLabel = 'Descanso';
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
		{ id: 2, title: 'Alerta de Pico Mayorista', text: 'Demanda de $9,200 el Lunes 16 Noviembre requiere reforzar la apertura con 1 mercaderista adicional.', time: 'Hace 4 horas', read: false, type: 'alert' },
		{ id: 3, title: 'Solicitud de Turno Aprobada', text: 'Marcos Huamán ha intercambiado su turno de viernes con Jorge Luis Quispe con visto bueno de supervisión.', time: 'Hace 1 día', read: true, type: 'request' }
	];

	// =====================================================================
	// 2. MOTOR PRINCIPAL PRINCE DEMO ENGINE (SINGLETON)
	// =====================================================================

	class PrinceDemoEngine {
		constructor() {
			this.listeners = {};
			this.currentRole = 'admin'; // 'admin' | 'supervisor' | 'employee'
			this.currentEmployeeId = 1; // Jorge Luis Quispe por defecto para vista de colaborador
			this.activeWeek = '2026-W47';
			this.activeEstablishment = 'Todos';
			this.activeTerritory = 'Todos';
			this.activeDepartment = 'Todos';
			this.whatIfScenario = { salesDeltaPct: 0, absentCount: 0, prodFactor: 1.0 };
			this.lastOptimizationBackup = null;

			this.init();
		}

		init() {
			this.state = this.loadFromStorage();
			if (!this.state) {
				this.resetToDefaults();
			}
			console.log('>>> [PrinceDemoEngine] Inicializado con éxito. Empleados:', this.state.employees.length, '| Semanas:', Object.keys(this.state.schedules).length);
		}

		loadFromStorage() {
			try {
				const raw = localStorage.getItem(STORAGE_KEY);
				if (!raw) return null;
				const parsed = JSON.parse(raw);
				if (!parsed.employees || parsed.employees.length < 20 || !parsed.schedules) {
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
				notifications: JSON.parse(JSON.stringify(DEFAULT_NOTIFICATIONS)),
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
			// Evento global para toda la app
			if (typeof window !== 'undefined') {
				window.dispatchEvent(new CustomEvent('prince:state-change', { detail: { event, payload } }));
			}
		}

		// =================================================================
		// 3. CONSULTAS Y CÁLCULOS MATEMÁTICOS COHERENTES
		// =================================================================

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

			// 2. Horas Recomendadas (Ventas / $150/h estándar B2B ajustado por productividad)
			const salesPerHourTarget = 150.00 * (this.whatIfScenario.prodFactor || 1.0);
			const minCoverageHours = 120.0; // Cobertura de seguridad básica
			const recommendedHours = Math.max(minCoverageHours, Math.round((forecastedSales / salesPerHourTarget) * 10) / 10);

			// 3. Horas Programadas (Suma neta de turnos activos)
			let scheduledHours = 0;
			let estimatedCost = 0;

			// Mapa de costos por hora de colaboradores
			const empCostMap = {};
			this.state.employees.forEach(e => empCostMap[e.id] = Number(e.hourly_cost || 7.50));

			filteredShifts.forEach(shift => {
				scheduledHours += Number(shift.net_hours || 0);
				const rate = empCostMap[shift.employee_id] || 7.50;
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
		// 4. ACCIONES Y OPERACIONES SOBRE HORARIOS (REACTIVAS)
		// =================================================================

		addOrUpdateShift(shiftData) {
			const sched = this.state.schedules[this.activeWeek];
			if (!sched) return { success: false, error: 'Semana no encontrada' };

			const emp = this.state.employees.find(e => e.id === Number(shiftData.employee_id));
			if (!emp) return { success: false, error: 'Empleado no encontrado' };

			// Calcular horas netas
			const startParts = shiftData.start_time.split(':').map(Number);
			const endParts = shiftData.end_time.split(':').map(Number);
			const startMin = startParts[0] * 60 + startParts[1];
			const endMin = endParts[0] * 60 + endParts[1];
			let grossMin = endMin - startMin;
			if (grossMin < 0) grossMin += 1440; // Turno nocturno
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
				// Convertir a descanso en lugar de eliminar la celda
				sched.shifts[shiftIndex] = {
					...removed,
					start_time: '00:00',
					end_time: '00:00',
					break_minutes: 0,
					net_hours: 0,
					shift_type: 'rest',
					shift_label: 'Descanso'
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

			// Mover horario
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

			// Agregar notificación demo interna
			this.addNotification({
				title: `Programación ${weekCode} Publicada (v${sched.version})`,
				text: `Horarios oficiales publicados por ${sched.published_by}. 20 colaboradores han sido actualizados en la app.`,
				type: 'publish'
			});

			this.recordAudit('SCHEDULE_PUBLISHED', `Programación ${weekCode} publicada formalmente con versión v${sched.version}`);
			this.saveToStorage();
			this.emit('schedule:published', { schedule: sched });
			return { success: true, version: sched.version };
		}

		// =================================================================
		// 5. OPTIMIZACIÓN INTELIGENTE (REGLAS DETERMINISTAS)
		// =================================================================

		getOptimizationProposal(weekCode = this.activeWeek) {
			const sched = this.state.schedules[weekCode];
			const currentKpis = this.calculateKPIs(weekCode);

			// Regla: Detectar si el lunes (pico mayorista) tiene menos de 4 reponedores en apertura
			const mondayOpeningShifts = sched.shifts.filter(s => s.day_of_week === 1 && s.shift_type === 'opening');
			const suggestions = [];

			if (mondayOpeningShifts.length < 4) {
				suggestions.push({
					id: 'SUG-01',
					type: 'coverage_boost',
					title: 'Refuerzo en Pico Mayorista de Apertura (Lunes)',
					description: 'Asignar a Miguel Ángel Flores (EMP-004) en turno de Apertura (07:00-15:30) para absorber descarga de 1,420 cajas master.',
					impact_hours: 8.0,
					impact_cost: 60.00,
					target_emp_id: 4,
					target_dow: 1,
					proposed_shift: { start: '07:00', end: '15:30', breakMin: 30, type: 'opening', label: 'Apertura WMS' }
				});
			}

			// Regla: Compensar horas extras en viernes
			suggestions.push({
				id: 'SUG-02',
				type: 'efficiency_trim',
				title: 'Nivelación de Horas en Cierre (Viernes)',
				description: 'Ajustar el turno de cierre a 7.5h netas en Almacén Callao Port para evitar sobrecosto sin afectar despacho.',
				impact_hours: -0.5,
				impact_cost: -4.00,
				target_emp_id: 9,
				target_dow: 5,
				proposed_shift: { start: '14:00', end: '22:00', breakMin: 30, type: 'closing', label: 'Cierre Callao' }
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

			// Guardar backup para permitir revertir
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
					task_notes: 'Turno optimizado por algoritmo WFM'
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
		// 6. GESTIÓN DE ROLES & NOTIFICACIONES DEMO
		// =================================================================

		switchRole(newRole, employeeId = 1) {
			this.currentRole = newRole;
			this.currentEmployeeId = Number(employeeId);
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
			this.state.notifications.unshift(item);
			if (this.state.notifications.length > 20) this.state.notifications.pop();
			this.saveToStorage();
			this.emit('notification:added', { notification: item });
		}

		recordAudit(action, details) {
			const log = {
				id: Date.now(),
				time: new Date().toISOString().replace('T', ' ').substring(0, 19),
				user: this.currentRole === 'admin' ? 'Gonzalo Javier Núñez (Admin)' : 'Carlos Mendoza (Supervisor)',
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
				advice: cov >= 95 ? 'Dotación óptima para demanda proyectada.' : 'Se recomienda reforzar dotación para evitar quiebres OTIF.'
			};
		}

		exportDatasetJson() {
			const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(this.state, null, 2));
			const downloadAnchor = document.createElement('a');
			downloadAnchor.setAttribute("href", dataStr);
			downloadAnchor.setAttribute("download", `prince_wfm_demo_${new Date().toISOString().slice(0,10)}.json`);
			document.body.appendChild(downloadAnchor);
			downloadAnchor.click();
			downloadAnchor.remove();
		}
	}

	// Instanciación del Singleton
	window.PrinceDemoEngine = new PrinceDemoEngine();

})(window);
</script>
