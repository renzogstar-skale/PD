<script>
/**
 * Prince Distributors B2B — Workforce Management (WFM) Controller
 * Horarios y Cobertura Inteligente & Motor Central de Demostración
 */

let wfmCurrentSchedule = null;
let wfmMatrixData = [];
let wfmEmployeesData = [];
let wfmWizardCurrentStep = 1;
let wfmSimSalesDelta = 0;
let wfmDraggedShiftId = null;
let wfmChartInstance = null;
let wfmActiveTab = 'schedule';
let wfmActiveDemoRole = 'admin';

// Vistas del Calendario Profesional: Día / Semana / Mes
let wfmActiveCalendarView = 'weekly'; // 'daily' | 'weekly' | 'monthly' (Semana predeterminada)
let wfmSelectedDate = '2026-11-20'; // Hoy por defecto (Viernes 20 Nov 2026)
let wfmSelectedMonth = '2026-11';
let wfmShiftPendingDeleteId = null;

// Inicialización del Módulo WFM
document.addEventListener('DOMContentLoaded', () => {
	if (typeof currentSection !== 'undefined' && currentSection === 'schedules') {
		initWorkforceModule();
	}

	// Escuchar cambios de eventos reactivos del Motor Central de Demo
	if (window.PrinceDemoEngine) {
		window.PrinceDemoEngine.on('shift:updated', () => refreshWfmSchedule());
		window.PrinceDemoEngine.on('shift:added', () => refreshWfmSchedule());
		window.PrinceDemoEngine.on('shift:deleted', () => refreshWfmSchedule());
		window.PrinceDemoEngine.on('schedule:optimized', () => refreshWfmSchedule());
		window.PrinceDemoEngine.on('role:changed', (data) => handleDemoRoleSwitch(data.role, false));
		window.PrinceDemoEngine.on('demo:reset', () => refreshWfmSchedule());
	}
});

async function initWorkforceModule() {
	updateDemoUnreadBadge();
	if (window.PrinceDemoEngine) {
		wfmEmployeesData = window.PrinceDemoEngine.getEmployees();
	}

	// En dispositivos móviles (<768px), priorizar la vista diaria
	if (window.innerWidth < 768) {
		wfmActiveCalendarView = 'daily';
	}
	switchCalendarView(wfmActiveCalendarView, false);

	await Promise.all([
		loadWfmDashboard(),
		refreshActiveCalendarView(),
		loadWfmEmployees(),
		loadWfmRequests()
	]);
	renderWfmSalesVsHoursChart();
	if (typeof lucide !== 'undefined') lucide.createIcons();
}

// Actualizar badge de notificaciones no leídas
function updateDemoUnreadBadge() {
	const badge = document.getElementById('demo-unread-badge');
	if (!badge) return;
	if (window.PrinceDemoEngine) {
		const unread = window.PrinceDemoEngine.getNotifications().filter(n => !n.read).length;
		badge.textContent = unread;
		badge.classList.toggle('hidden', unread === 0);
	}
}

// 0. CAMBIAR PESTAÑA INTERNA (7 PESTAÑAS)
function switchWfmTab(tabKey) {
	wfmActiveTab = tabKey;
	const tabs = ['summary', 'schedule', 'employees', 'productivity', 'simulator', 'settings', 'tasks'];

	tabs.forEach(t => {
		const btn = document.getElementById(`wfm-navtab-${t}`);
		const content = document.getElementById(`wfm-tab-content-${t}`);
		if (btn) {
			if (t === tabKey) {
				btn.className = 'wfm-tab-btn px-3 py-2.5 border-b-2 border-brand-700 text-brand-700 dark:text-brand-400 flex items-center gap-2 whitespace-nowrap font-bold transition-all';
			} else {
				btn.className = 'wfm-tab-btn px-3 py-2.5 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white flex items-center gap-2 whitespace-nowrap font-medium transition-all';
			}
		}
		if (content) {
			content.classList.toggle('hidden', t !== tabKey);
		}
	});

	if (tabKey === 'summary') {
		setTimeout(() => renderWfmSalesVsHoursChart(), 50);
	} else if (tabKey === 'schedule') {
		if (!wfmMatrixData || wfmMatrixData.length === 0) {
			loadWfmSchedule();
		} else {
			renderWfmScheduleTable(wfmMatrixData);
		}
	} else if (tabKey === 'tasks') {
		renderWfmTasksAndInventory();
	} else if (tabKey === 'employees') {
		if (!wfmEmployeesData || wfmEmployeesData.length === 0) {
			if (window.PrinceDemoEngine) {
				wfmEmployeesData = window.PrinceDemoEngine.getEmployees();
			}
		}
		renderEmployeesTable(wfmEmployeesData);
	} else if (tabKey === 'simulator') {
		runInlineSimulation();
	}

	if (typeof lucide !== 'undefined') lucide.createIcons();
}

// 1. CARGAR KPIS Y DASHBOARD (CONECTADO AL MOTOR CENTRAL Y REACTIVO A LA VISTA ACTIVA)
async function loadWfmDashboard() {
	try {
		const week = document.getElementById('wfm-filter-week')?.value || '2026-W47';
		const dept = document.getElementById('wfm-filter-department')?.value || 'Todos';
		const terr = document.getElementById('wfm-filter-territory')?.value || 'Todos';
		const estab = document.getElementById('wfm-filter-establishment')?.value || 'Todos';
		const sup = document.getElementById('wfm-filter-supervisor')?.value || 'Todos';

		let k = null;
		if (window.PrinceDemoEngine) {
			k = window.PrinceDemoEngine.calculateKPIs(week, estab, dept, wfmActiveCalendarView, wfmSelectedDate);
		} else {
			const res = await fetch(`/api/b2b/wfm/dashboard?week=${encodeURIComponent(week)}&view=${wfmActiveCalendarView}&date=${wfmSelectedDate}&department=${encodeURIComponent(dept)}&territory=${encodeURIComponent(terr)}`);
			if (res.ok) {
				const data = await res.json();
				if (data.success) k = data.kpis;
			}
		}

		if (!k) return;

		// 8 KPIS SUPERIORES ESPECIFICADOS
		const elTopStaff = document.getElementById('wfm-top-kpi-staff');
		const elTopStaffSub = document.getElementById('wfm-top-kpi-staff-sub');
		const elTopCov = document.getElementById('wfm-top-kpi-coverage');
		const elTopCovSub = document.getElementById('wfm-top-kpi-coverage-sub');
		const elTopUncov = document.getElementById('wfm-top-kpi-uncovered');
		const elTopUncovSub = document.getElementById('wfm-top-kpi-uncovered-sub');
		const elTopHours = document.getElementById('wfm-top-kpi-hours');
		const elTopHoursSub = document.getElementById('wfm-top-kpi-hours-sub');
		const elTopOvertime = document.getElementById('wfm-top-kpi-overtime');
		const elTopOvertimeSub = document.getElementById('wfm-top-kpi-overtime-sub');
		const elTopAbsences = document.getElementById('wfm-top-kpi-absences');
		const elTopAbsencesSub = document.getElementById('wfm-top-kpi-absences-sub');
		const elTopRequests = document.getElementById('wfm-top-kpi-requests');
		const elTopRequestsSub = document.getElementById('wfm-top-kpi-requests-sub');
		const elTopCost = document.getElementById('wfm-top-kpi-cost');
		const elTopCostSub = document.getElementById('wfm-top-kpi-cost-sub');

		if (elTopStaff) elTopStaff.textContent = k.scheduled_staff || 0;
		if (elTopStaffSub) elTopStaffSub.textContent = `${k.scheduled_staff || 0} colaboradores`;

		if (elTopCov) {
			const cov = Number(k.coverage_score_pct || 0);
			elTopCov.textContent = cov.toFixed(1) + '%';
			elTopCov.className = `text-xl font-bold font-mono tracking-tight ${cov >= 95 ? 'text-emerald-600 dark:text-emerald-400' : (cov >= 80 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400')}`;
		}
		if (elTopCovSub) {
			const cov = Number(k.coverage_score_pct || 0);
			elTopCovSub.textContent = cov >= 95 ? 'Nivel óptimo' : (cov >= 80 ? 'Requiere atención' : 'Déficit crítico');
		}

		if (elTopUncov) elTopUncov.textContent = k.uncovered_shifts || 0;
		if (elTopUncovSub) elTopUncovSub.textContent = `${k.uncovered_shifts || 0} puestos vacíos`;

		if (elTopHours) elTopHours.textContent = Number(k.scheduled_hours || 0).toFixed(1) + 'h';
		if (elTopHoursSub) {
			const diff = Number(k.hours_variance || 0);
			elTopHoursSub.textContent = diff >= 0 ? `+${diff.toFixed(1)}h vs demanda` : `${diff.toFixed(1)}h brecha`;
		}

		if (elTopOvertime) elTopOvertime.textContent = Number(k.overtime_hours || 0).toFixed(1) + 'h';
		if (elTopOvertimeSub) elTopOvertimeSub.textContent = (Number(k.overtime_hours || 0) > 0) ? 'Horas adicionales' : 'Dentro de límite';

		if (elTopAbsences) elTopAbsences.textContent = k.absences_count || 0;
		if (elTopAbsencesSub) elTopAbsencesSub.textContent = `${k.absences_count || 0} autorizadas`;

		if (elTopRequests) elTopRequests.textContent = k.pending_requests || 0;
		if (elTopRequestsSub) elTopRequestsSub.textContent = 'Por revisar';

		if (elTopCost) elTopCost.textContent = '$' + Number(k.estimated_labor_cost || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
		if (elTopCostSub) elTopCostSub.textContent = `Ppto: $${Number(k.labor_budget || 2800).toFixed(2)}`;

		// ETIQUETA DINÁMICA DEL PERIODO ACTUAL
		const rangeLabel = document.getElementById('wfm-current-range-label');
		if (rangeLabel) {
			if (wfmActiveCalendarView === 'daily') {
				const dayNames = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
				const monthNames = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
				const [y, m, d] = wfmSelectedDate.split('-');
				const dow = new Date(wfmSelectedDate + 'T12:00:00').getDay() || 7;
				rangeLabel.textContent = `${dayNames[dow - 1]}, ${d} de ${monthNames[parseInt(m, 10)]} ${y}`;
			} else if (wfmActiveCalendarView === 'weekly') {
				const weekNum = week.replace(/.*W/, '');
				rangeLabel.textContent = `Semana ${weekNum}: 16 Nov – 22 Nov 2026`;
			} else if (wfmActiveCalendarView === 'monthly') {
				rangeLabel.textContent = `Mes de Noviembre de 2026`;
			}
		}

		// ACTUALIZACIÓN DE BADGES DE ESTADO (Publicado / Borrador)
		const calStatusBadge = document.getElementById('wfm-calendar-status-badge');
		const badge = document.getElementById('wfm-status-badge');
		if (window.PrinceDemoEngine) {
			const sched = window.PrinceDemoEngine.getSchedule(week);
			const label = sched.status === 'published' ? `Publicado v${sched.version || 1}` : (sched.status === 'in_review' ? 'En Revisión' : 'Borrador');
			const badgeClass = sched.status === 'published'
				? 'px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300'
				: 'px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300';
			
			if (calStatusBadge) {
				calStatusBadge.textContent = label;
				calStatusBadge.className = badgeClass;
			}
			if (badge) {
				badge.textContent = label;
				badge.className = badgeClass;
			}
		}

		// Compatibilidad con Pestaña 1 (Resumen)
		const elSales = document.getElementById('wfm-kpi-sales');
		const elRec = document.getElementById('wfm-kpi-rec-hours');
		const elSched = document.getElementById('wfm-kpi-sched-hours');
		const elCost = document.getElementById('wfm-kpi-cost');
		const elCov = document.getElementById('wfm-kpi-coverage');
		if (elSales) elSales.textContent = '$' + Number(k.forecasted_sales || 48500).toLocaleString('en-US', { minimumFractionDigits: 2 });
		if (elRec) elRec.textContent = Number(k.recommended_hours || 340).toFixed(1) + ' hrs';
		if (elSched) elSched.textContent = Number(k.scheduled_hours || 336).toFixed(1) + ' hrs';
		if (elCost) elCost.textContent = '$' + Number(k.estimated_labor_cost || 2520).toLocaleString('en-US', { minimumFractionDigits: 2 });
		if (elCov) elCov.textContent = Number(k.coverage_score_pct || 98.8).toFixed(1) + '%';

		updateDemoUnreadBadge();
	} catch (e) {
		console.warn('Error al cargar KPIs de WFM:', e);
	}
}

// 2. GRÁFICO PRINCIPAL: VENTAS PREVISTAS VS HORAS LABORALES
function renderWfmSalesVsHoursChart() {
	const canvas = document.getElementById('wfmSalesVsHoursChart');
	if (!canvas) return;

	if (typeof Chart === 'undefined') {
		console.warn('Chart.js no está cargado');
		return;
	}

	if (wfmChartInstance) {
		wfmChartInstance.destroy();
	}

	const ctx = canvas.getContext('2d');
	const labels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
	const week = document.getElementById('wfm-filter-week')?.value || '2026-W47';

	let salesData = [9200, 7800, 8400, 8100, 8900, 4200, 1900];
	let recHoursData = [64.0, 54.5, 58.8, 56.7, 62.3, 29.4, 14.3];
	let schedHoursData = [45.0, 42.0, 40.0, 38.0, 42.0, 20.0, 9.0];

	if (window.PrinceDemoEngine) {
		const sched = window.PrinceDemoEngine.getSchedule(week);
		const baseSales = sched.forecasted_sales || 48500;
		const dailyFractions = [0.19, 0.16, 0.17, 0.17, 0.18, 0.09, 0.04];
		salesData = dailyFractions.map(f => Math.round(baseSales * f));
		recHoursData = salesData.map(s => Number((s / 142.6).toFixed(1)));

		// Calcular horas programadas por día desde turnos
		const dayHours = [0, 0, 0, 0, 0, 0, 0];
		sched.shifts.forEach(s => {
			if (s.day_of_week >= 1 && s.day_of_week <= 7) {
				dayHours[s.day_of_week - 1] += Number(s.net_hours || 0);
			}
		});
		schedHoursData = dayHours;
	}

	wfmChartInstance = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: labels,
			datasets: [
				{
					label: 'Ventas Previstas ($ USD)',
					data: salesData,
					backgroundColor: 'rgba(0, 168, 89, 0.25)',
					borderColor: '#00A859',
					borderWidth: 1.5,
					borderRadius: 4,
					yAxisID: 'y',
					order: 2
				},
				{
					label: 'Horas Recomendadas',
					data: recHoursData,
					type: 'line',
					borderColor: '#4F46E5',
					backgroundColor: '#4F46E5',
					borderWidth: 2.5,
					pointRadius: 4,
					tension: 0.25,
					yAxisID: 'y1',
					order: 1
				},
				{
					label: 'Horas Programadas',
					data: schedHoursData,
					type: 'line',
					borderColor: '#059669',
					backgroundColor: '#059669',
					borderWidth: 2,
					borderDash: [4, 4],
					pointRadius: 3.5,
					tension: 0.25,
					yAxisID: 'y1',
					order: 0
				}
			]
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			interaction: {
				mode: 'index',
				intersect: false
			},
			plugins: {
				legend: {
					display: false
				},
				tooltip: {
					callbacks: {
						label: function(ctx) {
							if (ctx.dataset.yAxisID === 'y') {
								return `${ctx.dataset.label}: $${ctx.raw.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
							}
							return `${ctx.dataset.label}: ${ctx.raw} hrs`;
						}
					}
				}
			},
			scales: {
				x: {
					grid: { display: false }
				},
				y: {
					type: 'linear',
					display: true,
					position: 'left',
					title: { display: true, text: 'Ventas ($ USD)', font: { size: 10 } },
					ticks: {
						callback: function(v) { return '$' + (v / 1000) + 'k'; }
					},
					grid: { color: 'rgba(226, 232, 240, 0.6)' }
				},
				y1: {
					type: 'linear',
					display: true,
					position: 'right',
					title: { display: true, text: 'Horas Laborales', font: { size: 10 } },
					ticks: {
						callback: function(v) { return v + ' h'; }
					},
					grid: { drawOnChartArea: false }
				}
			}
		}
	});
}

// ========================================================
// CONTROLADOR DE VISTAS DE CALENDARIO (DÍA / SEMANA / MES)
// ========================================================

function switchCalendarView(viewKey, doRefresh = true) {
	wfmActiveCalendarView = viewKey;

	const btnDaily = document.getElementById('btn-view-daily');
	const btnWeekly = document.getElementById('btn-view-weekly');
	const btnMonthly = document.getElementById('btn-view-monthly');

	const activeClass = 'wfm-view-btn px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all bg-white dark:bg-slate-700 text-brand-700 dark:text-brand-300 shadow-xs flex items-center gap-1.5';
	const inactiveClass = 'wfm-view-btn px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white flex items-center gap-1.5';

	if (btnDaily) btnDaily.className = viewKey === 'daily' ? activeClass : inactiveClass;
	if (btnWeekly) btnWeekly.className = viewKey === 'weekly' ? activeClass : inactiveClass;
	if (btnMonthly) btnMonthly.className = viewKey === 'monthly' ? activeClass : inactiveClass;

	const viewDaily = document.getElementById('wfm-view-daily');
	const viewWeekly = document.getElementById('wfm-view-weekly');
	const viewMonthly = document.getElementById('wfm-view-monthly');

	if (viewDaily) viewDaily.classList.toggle('hidden', viewKey !== 'daily');
	if (viewWeekly) viewWeekly.classList.toggle('hidden', viewKey !== 'weekly');
	if (viewMonthly) viewMonthly.classList.toggle('hidden', viewKey !== 'monthly');

	const weekSelect = document.getElementById('wfm-filter-week');
	const dayInput = document.getElementById('wfm-filter-day-input');
	const periodLabel = document.getElementById('wfm-filter-period-label');

	if (viewKey === 'daily') {
		if (weekSelect) weekSelect.classList.add('hidden');
		if (dayInput) {
			dayInput.classList.remove('hidden');
			dayInput.value = wfmSelectedDate;
		}
		if (periodLabel) periodLabel.textContent = 'Fecha Seleccionada';
	} else if (viewKey === 'weekly') {
		if (weekSelect) weekSelect.classList.remove('hidden');
		if (dayInput) dayInput.classList.add('hidden');
		if (periodLabel) periodLabel.textContent = 'Semana Activa';
	} else {
		if (weekSelect) weekSelect.classList.remove('hidden');
		if (dayInput) dayInput.classList.add('hidden');
		if (periodLabel) periodLabel.textContent = 'Semana / Mes';
	}

	if (doRefresh) {
		refreshWfmSchedule();
	}
	if (typeof lucide !== 'undefined') lucide.createIcons();
}

function navigateCalendarDate(delta) {
	if (wfmActiveCalendarView === 'daily') {
		const cur = new Date(wfmSelectedDate + 'T12:00:00');
		cur.setDate(cur.getDate() + delta);
		wfmSelectedDate = cur.toISOString().split('T')[0];
		const input = document.getElementById('wfm-filter-day-input');
		if (input) input.value = wfmSelectedDate;
	} else if (wfmActiveCalendarView === 'weekly') {
		const weekSelect = document.getElementById('wfm-filter-week');
		if (weekSelect) {
			const weeks = ['2026-W46', '2026-W47', '2026-W48', '2026-W49'];
			let idx = weeks.indexOf(weekSelect.value);
			if (idx === -1) idx = 1;
			idx = Math.max(0, Math.min(weeks.length - 1, idx + delta));
			weekSelect.value = weeks[idx];
		}
	} else if (wfmActiveCalendarView === 'monthly') {
		const [y, m] = wfmSelectedMonth.split('-').map(Number);
		const d = new Date(y, m - 1 + delta, 1);
		wfmSelectedMonth = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
	}
	refreshWfmSchedule();
}

function goToToday() {
	wfmSelectedDate = '2026-11-20';
	wfmSelectedMonth = '2026-11';
	const weekSelect = document.getElementById('wfm-filter-week');
	if (weekSelect) weekSelect.value = '2026-W47';
	const dayInput = document.getElementById('wfm-filter-day-input');
	if (dayInput) dayInput.value = '2026-11-20';
	refreshWfmSchedule();
}

function openDailyViewFromDow(dow) {
	const mapDowToDate = {
		1: '2026-11-16',
		2: '2026-11-17',
		3: '2026-11-18',
		4: '2026-11-19',
		5: '2026-11-20',
		6: '2026-11-21',
		7: '2026-11-22'
	};
	wfmSelectedDate = mapDowToDate[dow] || '2026-11-20';
	const dayInput = document.getElementById('wfm-filter-day-input');
	if (dayInput) dayInput.value = wfmSelectedDate;
	switchCalendarView('daily');
}

function openDayFromCalendar(dateStr) {
	wfmSelectedDate = dateStr;
	const dayInput = document.getElementById('wfm-filter-day-input');
	if (dayInput) dayInput.value = wfmSelectedDate;
	switchCalendarView('daily');
}

function handleWeekFilterChange(weekVal) {
	refreshWfmSchedule();
}

function handleDayInputChange(dayVal) {
	if (dayVal) {
		wfmSelectedDate = dayVal;
		refreshWfmSchedule();
	}
}

function openWfmKpiInfoModal(kpiKey) {
	const modal = document.getElementById('modal-wfm-kpi-info');
	if (modal) modal.classList.remove('hidden');
}

async function refreshActiveCalendarView() {
	if (wfmActiveCalendarView === 'daily') {
		await loadWfmDailySchedule();
	} else if (wfmActiveCalendarView === 'monthly') {
		await loadWfmMonthlySchedule();
	} else {
		await loadWfmSchedule();
	}
}

// 3. CARGAR MATRIZ SEMANAL DE TURNOS (VISTA SEMANAL)
async function loadWfmSchedule() {
	try {
		const week = document.getElementById('wfm-filter-week')?.value || '2026-W47';
		const dept = document.getElementById('wfm-filter-department')?.value || 'Todos';
		const terr = document.getElementById('wfm-filter-territory')?.value || 'Todos';
		const estab = document.getElementById('wfm-filter-establishment')?.value || 'Todos';
		const sup = document.getElementById('wfm-filter-supervisor')?.value || 'Todos';

		if (window.PrinceDemoEngine) {
			wfmCurrentSchedule = window.PrinceDemoEngine.getSchedule(week);
			wfmMatrixData = window.PrinceDemoEngine.getScheduleMatrix(week, estab, terr, dept, sup);
			renderWfmScheduleTable(wfmMatrixData);
			if (wfmActiveDemoRole === 'employee' || wfmActiveDemoRole === 'reponedor') {
				renderEmployeePersonalSchedule(wfmActiveDemoRole);
			}
			return;
		}

		const res = await fetch(`/api/b2b/wfm/schedule?week=${encodeURIComponent(week)}&department=${encodeURIComponent(dept)}&territory=${encodeURIComponent(terr)}`);
		if (!res.ok) return;
		const data = await res.json();
		if (!data.success) return;

		wfmCurrentSchedule = data.schedule;
		wfmMatrixData = data.schedule_matrix || [];

		renderWfmScheduleTable(wfmMatrixData);
	} catch (e) {
		console.warn('Error al cargar calendario WFM:', e);
	}
}

// RENDERIZAR TABLA DE CALENDARIO INTERACTIVO SEMANAL CON PIE DE RESUMEN
function renderWfmScheduleTable(matrix) {
	const tbody = document.getElementById('wfm-schedule-tbody');
	if (!tbody) return;

	if (!matrix || matrix.length === 0) {
		tbody.innerHTML = `
			<tr>
				<td colspan="9" class="p-8 text-center text-slate-400">
					No se encontraron colaboradores bajo los filtros seleccionados.
				</td>
			</tr>
		`;
		return;
	}

	tbody.innerHTML = matrix.map(row => {
		const emp = row.employee;
		const hoursScheduled = Number(row.total_scheduled_hours || 0);
		const hoursContracted = Number(emp.contracted_hours || 48);
		const pct = Math.min(100, Math.round((hoursScheduled / hoursContracted) * 100));
		const isFull = hoursScheduled >= hoursContracted;

		let daysHtml = '';
		for (let dow = 1; dow <= 7; dow++) {
			const shift = row.days[dow];
			daysHtml += renderShiftCell(emp, dow, shift);
		}

		return `
			<tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
				<td class="p-3 sticky left-0 bg-white dark:bg-surface-dark border-r border-slate-200/60 dark:border-slate-800 z-10">
					<div class="flex items-center gap-2.5">
						<div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-brand-700 dark:text-brand-300 flex items-center justify-center font-bold text-xs shrink-0">
							${emp.name.split(' ').map(n => n[0]).slice(0, 2).join('')}
						</div>
						<div class="truncate">
							<div class="font-bold text-slate-800 dark:text-slate-100 truncate">${emp.name}</div>
							<div class="text-[10px] text-slate-400 truncate">${emp.role_label} • ${emp.establishment_name}</div>
						</div>
					</div>
					<div class="mt-2 flex items-center justify-between text-[10px] text-slate-500">
						<span>Horas: <strong>${hoursScheduled.toFixed(1)}</strong> / ${hoursContracted}h</span>
						<span class="${isFull ? 'text-emerald-600 font-bold' : 'text-slate-400'}">${pct}%</span>
					</div>
					<div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full mt-0.5 overflow-hidden">
						<div class="h-full ${isFull ? 'bg-emerald-500' : 'bg-brand-600'}" style="width: ${pct}%"></div>
					</div>
				</td>
				${daysHtml}
				<td class="p-3 text-center font-bold text-slate-800 dark:text-white">
					${hoursScheduled.toFixed(1)}h
				</td>
			</tr>
		`;
	}).join('');

	// Rellenar Fila Resumen Semanal en TFOOT
	const tfoot = document.getElementById('wfm-schedule-tfoot');
	if (tfoot) {
		const dayTotals = { 1: { staff: 0, hours: 0 }, 2: { staff: 0, hours: 0 }, 3: { staff: 0, hours: 0 }, 4: { staff: 0, hours: 0 }, 5: { staff: 0, hours: 0 }, 6: { staff: 0, hours: 0 }, 7: { staff: 0, hours: 0 } };
		let grandTotalHours = 0;

		matrix.forEach(row => {
			for (let dow = 1; dow <= 7; dow++) {
				const s = row.days[dow];
				if (s && s.shift_type !== 'rest') {
					dayTotals[dow].staff++;
					const h = Number(s.net_hours || 0);
					dayTotals[dow].hours += h;
					grandTotalHours += h;
				}
			}
		});

		let footCols = '';
		for (let dow = 1; dow <= 7; dow++) {
			const st = dayTotals[dow].staff;
			const hr = dayTotals[dow].hours;
			const req = (dow === 7) ? 4 : ((dow === 6) ? 10 : 14);
			const isCov = st >= req;

			footCols += `
				<td class="p-2.5 text-center bg-slate-50/80 dark:bg-slate-900/40">
					<div class="text-[11px] font-bold text-slate-800 dark:text-white">${st} pers.</div>
					<div class="text-[10px] text-slate-500">${hr.toFixed(1)}h</div>
					<div class="mt-0.5"><span class="px-1.5 py-0.2 rounded text-[9px] font-bold ${isCov ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'}">${isCov ? 'Cubierto' : 'Atención'}</span></div>
				</td>
			`;
		}

		tfoot.innerHTML = `
			<td class="p-3 sticky left-0 bg-slate-100 dark:bg-slate-800 z-10 font-bold text-slate-800 dark:text-white text-xs">
				Total Diario
			</td>
			${footCols}
			<td class="p-3 text-center font-bold text-slate-900 dark:text-white text-sm">
				${grandTotalHours.toFixed(1)}h
			</td>
		`;
	}

	if (typeof lucide !== 'undefined') lucide.createIcons();
}

// 3.1 CARGAR VISTA DIARIA DETALLADA
async function loadWfmDailySchedule() {
	try {
		const dept = document.getElementById('wfm-filter-department')?.value || 'Todos';
		const terr = document.getElementById('wfm-filter-territory')?.value || 'Todos';
		const estab = document.getElementById('wfm-filter-establishment')?.value || 'Todos';
		const sup = document.getElementById('wfm-filter-supervisor')?.value || 'Todos';

		let data = null;
		if (window.PrinceDemoEngine) {
			data = window.PrinceDemoEngine.getDailySchedule(wfmSelectedDate, estab, terr, dept, sup);
		} else {
			const res = await fetch(`/api/b2b/wfm/schedule/daily?date=${encodeURIComponent(wfmSelectedDate)}&department=${encodeURIComponent(dept)}&territory=${encodeURIComponent(terr)}`);
			if (res.ok) {
				const json = await res.json();
				if (json.success) data = json;
			}
		}

		if (!data) return;

		// 1. Título y badges del banner diario
		const titleEl = document.getElementById('wfm-daily-date-title');
		const covBadgeEl = document.getElementById('wfm-daily-coverage-badge');
		const reqStaffEl = document.getElementById('wfm-daily-required-staff');
		const schedStaffEl = document.getElementById('wfm-daily-scheduled-staff');
		const schedCountEl = document.getElementById('wfm-daily-scheduled-count-badge');

		const dayNames = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
		const monthNames = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
		const [y, m, d] = wfmSelectedDate.split('-');
		const dow = new Date(wfmSelectedDate + 'T12:00:00').getDay() || 7;

		if (titleEl) titleEl.textContent = `${dayNames[dow - 1]}, ${d} de ${monthNames[parseInt(m, 10)]} de ${y}`;
		if (covBadgeEl) {
			const pct = data.coverage_pct || 100;
			covBadgeEl.textContent = `${pct}% Cobertura`;
			covBadgeEl.className = `px-2.5 py-0.5 text-xs font-bold rounded-full ${pct >= 95 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300' : (pct >= 80 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300')}`;
		}
		if (reqStaffEl) reqStaffEl.textContent = data.required_staff || 14;
		if (schedStaffEl) schedStaffEl.textContent = data.scheduled_staff || 0;
		if (schedCountEl) schedCountEl.textContent = `${data.scheduled_staff || 0} turnos activos`;

		// 2. Cobertura horaria por cada hora (06:00 a 22:00)
		const barsContainer = document.getElementById('wfm-daily-hourly-bars');
		if (barsContainer && data.hourly_coverage) {
			barsContainer.innerHTML = data.hourly_coverage.map(hc => {
				const statusColor = hc.status === 'covered' ? 'bg-emerald-500' : (hc.status === 'warning' ? 'bg-amber-500' : 'bg-rose-500');
				const barHeight = Math.max(15, Math.min(100, Math.round((hc.scheduled / Math.max(1, hc.required)) * 100)));
				return `
					<div class="flex flex-col items-center justify-end p-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60 group hover:border-brand-500 transition-colors">
						<span class="text-[9px] font-bold text-slate-700 dark:text-slate-300">${hc.scheduled}/${hc.required}</span>
						<div class="w-full bg-slate-200 dark:bg-slate-700 rounded-sm h-12 my-1 flex items-end p-0.5 overflow-hidden">
							<div class="${statusColor} w-full rounded-xs transition-all duration-300" style="height: ${barHeight}%;"></div>
						</div>
						<span class="text-[9px] font-semibold text-slate-500 dark:text-slate-400">${hc.hour}</span>
					</div>
				`;
			}).join('');
		}

		// 3. Tabla de empleados programados hoy
		const tbody = document.getElementById('wfm-daily-scheduled-tbody');
		if (tbody) {
			if (!data.scheduled_employees || data.scheduled_employees.length === 0) {
				tbody.innerHTML = `
					<tr>
						<td colspan="7" class="p-8 text-center text-slate-400">
							No hay empleados programados para esta fecha bajo los filtros actuales.
						</td>
					</tr>
				`;
			} else {
				const colorsByType = {
					opening: { bg: 'bg-emerald-50 dark:bg-emerald-950/60', text: 'text-emerald-800 dark:text-emerald-300', border: 'border-emerald-200 dark:border-emerald-800' },
					mid: { bg: 'bg-blue-50 dark:bg-blue-950/60', text: 'text-blue-800 dark:text-blue-300', border: 'border-blue-200 dark:border-blue-800' },
					closing: { bg: 'bg-purple-50 dark:bg-purple-950/60', text: 'text-purple-800 dark:text-purple-300', border: 'border-purple-200 dark:border-purple-800' },
					standard: { bg: 'bg-emerald-50 dark:bg-emerald-950/60', text: 'text-emerald-800 dark:text-emerald-300', border: 'border-emerald-200 dark:border-emerald-800' }
				};

				tbody.innerHTML = data.scheduled_employees.map(s => {
					const col = colorsByType[s.shift_type] || colorsByType.mid;
					const initials = (s.employee_name || 'EM').split(' ').map(n => n[0]).slice(0, 2).join('');
					return `
						<tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
							<td class="p-3">
								<div class="flex items-center gap-2.5">
									<div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-brand-700 dark:text-brand-300 flex items-center justify-center font-bold text-xs shrink-0">
										${initials}
									</div>
									<div class="truncate">
										<div class="font-bold text-slate-800 dark:text-slate-100 truncate">${s.employee_name}</div>
										<div class="text-[10px] text-slate-400 truncate">${s.role_label || s.employee_role} • ${s.employee_code || ''}</div>
									</div>
								</div>
							</td>
							<td class="p-3 text-slate-600 dark:text-slate-300">
								<div class="font-medium truncate">${s.department || 'Operaciones'}</div>
								<div class="text-[10px] text-slate-400 truncate">${s.establishment_name || 'Prince Distributors — Hallandale HQ'}</div>
							</td>
							<td class="p-3 text-center font-bold text-slate-800 dark:text-white">
								${s.start_time} – ${s.end_time}
							</td>
							<td class="p-3 text-center">
								<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border ${col.border} ${col.bg} ${col.text}">
									${s.shift_label || 'Turno'}
								</span>
							</td>
							<td class="p-3 text-center text-slate-500">
								${s.break_minutes || 30} min
							</td>
							<td class="p-3 text-center font-bold text-slate-900 dark:text-white">
								${Number(s.net_hours || 8.0).toFixed(1)}h
							</td>
							<td class="p-3 text-right">
								<div class="inline-flex items-center gap-1">
									<button onclick="duplicateShift('${s.id}')" title="Duplicar turno" class="p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded text-slate-600 dark:text-slate-300">
										<i data-lucide="copy" class="w-3.5 h-3.5"></i>
									</button>
									<button onclick="openShiftEditModal(${s.employee_id}, '${s.employee_name.replace(/'/g, "\\'")}', ${s.day_of_week || dow}, ${JSON.stringify(s).replace(/"/g, '&quot;')}, '${wfmSelectedDate}')" title="Editar turno" class="p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded text-brand-700 dark:text-brand-300">
										<i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
									</button>
									<button onclick="promptDeleteShift('${s.id}', '${s.employee_name.replace(/'/g, "\\'")}', '${s.start_time} - ${s.end_time}')" title="Quitar turno" class="p-1 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded text-rose-600">
										<i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
									</button>
								</div>
							</td>
						</tr>
					`;
				}).join('');
			}
		}

		// 4. Personal en descanso o ausencia hoy
		const offContainer = document.getElementById('wfm-daily-off-container');
		if (offContainer) {
			if (!data.off_employees || data.off_employees.length === 0) {
				offContainer.innerHTML = `<div class="p-3 text-slate-400 col-span-3 text-center">No hay personal libre o en descanso registrado.</div>`;
			} else {
				offContainer.innerHTML = data.off_employees.map(oe => {
					const isVacation = oe.shift_type === 'vacation';
					const isAbsence = oe.shift_type === 'absence';
					const badgeText = isVacation ? 'Vacaciones' : (isAbsence ? 'Ausencia' : 'Descanso Libre');
					const badgeClass = isVacation ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300' : (isAbsence ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400');
					return `
						<div class="p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-surface-dark flex items-center justify-between shadow-2xs">
							<div class="truncate mr-2">
								<div class="font-bold text-slate-800 dark:text-slate-100 truncate">${oe.employee_name}</div>
								<div class="text-[10px] text-slate-400 truncate">${oe.department || 'Operaciones'} • ${badgeText}</div>
							</div>
							<div class="flex items-center gap-1.5 shrink-0">
								<span class="px-2 py-0.5 rounded-full text-[9px] font-bold ${badgeClass}">${badgeText}</span>
								<button onclick="openShiftEditModal(${oe.employee_id}, '${oe.employee_name.replace(/'/g, "\\'")}', ${dow}, null, '${wfmSelectedDate}')" class="px-2 py-1 text-[10px] font-bold rounded-lg bg-brand-50 hover:bg-brand-100 dark:bg-brand-950/50 dark:hover:bg-brand-900/60 text-brand-700 dark:text-brand-300 transition-colors" title="Asignar turno extraordinario">
									+ Turno
								</button>
							</div>
						</div>
					`;
				}).join('');
			}
		}

		if (typeof lucide !== 'undefined') lucide.createIcons();
	} catch (e) {
		console.warn('Error al cargar vista diaria:', e);
	}
}

// 3.2 CARGAR VISTA MENSUAL COMPLETA
async function loadWfmMonthlySchedule() {
	try {
		const dept = document.getElementById('wfm-filter-department')?.value || 'Todos';
		const terr = document.getElementById('wfm-filter-territory')?.value || 'Todos';
		const estab = document.getElementById('wfm-filter-establishment')?.value || 'Todos';
		const sup = document.getElementById('wfm-filter-supervisor')?.value || 'Todos';

		let data = null;
		if (window.PrinceDemoEngine) {
			data = window.PrinceDemoEngine.getMonthlySchedule(wfmSelectedMonth, estab, terr, dept, sup);
		} else {
			const res = await fetch(`/api/b2b/wfm/schedule/monthly?month=${encodeURIComponent(wfmSelectedMonth)}&department=${encodeURIComponent(dept)}&territory=${encodeURIComponent(terr)}`);
			if (res.ok) {
				const json = await res.json();
				if (json.success) data = json;
			}
		}

		if (!data) return;

		// 1. Título
		const titleEl = document.getElementById('wfm-monthly-grid-title');
		if (titleEl) titleEl.textContent = `Calendario Mensual — ${data.month_label || 'Noviembre 2026'}`;

		// 2. Grilla de Días
		const grid = document.getElementById('wfm-monthly-days-grid');
		if (grid && data.days) {
			const firstDow = data.days[0].day_of_week; // 1 = Lun ... 7 = Dom
			let paddingCells = '';
			for (let p = 1; p < firstDow; p++) {
				paddingCells += `<div class="p-2 bg-slate-100/30 dark:bg-slate-900/40 min-h-[90px] border-r border-b border-slate-100 dark:border-slate-800/60"></div>`;
			}

			const dayCells = data.days.map(d => {
				const isToday = d.is_today;
				const statusBadge = d.status === 'green'
					? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800'
					: (d.status === 'amber'
						? 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border-amber-300 dark:border-amber-800'
						: 'bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300 border-rose-300 dark:border-rose-800');

				return `
					<div onclick="openDayFromCalendar('${d.date}')" class="p-2.5 min-h-[95px] flex flex-col justify-between hover:bg-brand-50/40 dark:hover:bg-brand-950/20 transition-all cursor-pointer group relative border-r border-b border-slate-100 dark:border-slate-800/60 ${isToday ? 'bg-brand-50/20 dark:bg-brand-950/10 ring-1 ring-inset ring-brand-500/40' : 'bg-white dark:bg-surface-dark'}">
						<div class="flex items-center justify-between">
							<span class="text-xs font-bold ${isToday ? 'w-5 h-5 rounded-full bg-brand-700 text-white flex items-center justify-center font-mono shadow-2xs' : 'text-slate-700 dark:text-slate-300'}">
								${d.day_number}
							</span>
							<span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold border ${statusBadge}">
								${d.coverage_pct}%
							</span>
						</div>

						<div class="my-1 space-y-1">
							<div class="text-[10px] font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1">
								<span class="w-1.5 h-1.5 rounded-full ${d.status === 'green' ? 'bg-emerald-500' : (d.status === 'amber' ? 'bg-amber-500' : 'bg-rose-500')}"></span>
								<span>${d.scheduled_staff} prog.</span>
							</div>
							${d.absences_count > 0 ? `
								<div class="text-[9px] text-amber-600 dark:text-amber-400 font-semibold flex items-center gap-0.5">
									<span>🌴</span> <span>${d.absences_count} ausencia</span>
								</div>
							` : ''}
							${d.pending_shifts > 0 ? `
								<div class="text-[9px] text-purple-600 dark:text-purple-400 font-semibold flex items-center gap-0.5">
									<span>⏳</span> <span>Pendiente</span>
								</div>
							` : ''}
						</div>

						<div class="flex items-center justify-between text-[9px] text-slate-400 pt-1 border-t border-slate-100 dark:border-slate-800/80">
							<span>${d.scheduled_hours.toFixed(0)}h</span>
							<span class="text-brand-700 dark:text-brand-400 font-bold opacity-0 group-hover:opacity-100 transition-opacity">Ver día →</span>
						</div>
					</div>
				`;
			}).join('');

			grid.innerHTML = paddingCells + dayCells;
		}

		// 3. Resumen Mensual
		if (data.summary) {
			const elH = document.getElementById('wfm-month-total-hours');
			const elC = document.getElementById('wfm-month-avg-coverage');
			const elR = document.getElementById('wfm-month-total-rests');
			const elV = document.getElementById('wfm-month-total-vacations');

			if (elH) elH.textContent = `${Number(data.summary.total_scheduled_hours || 0).toFixed(1)} hrs`;
			if (elC) elC.textContent = `${Number(data.summary.average_coverage_pct || 97.2).toFixed(1)}%`;
			if (elR) elR.textContent = `${data.summary.total_rest_days || 128} descansos`;
			if (elV) elV.textContent = `${data.summary.total_vacations || 4} registradas`;
		}

		if (typeof lucide !== 'undefined') lucide.createIcons();
	} catch (e) {
		console.warn('Error al cargar vista mensual:', e);
	}
}

function renderShiftCell(emp, dow, shift) {
	if (!shift || shift.shift_type === 'rest') {
		return `
			<td class="p-2 text-center align-top drop-zone" ondragover="handleDragOver(event)" ondrop="handleDrop(event, ${emp.id}, ${dow})">
				<div class="w-full min-h-[58px] rounded-lg border border-dashed border-slate-200 dark:border-slate-800 hover:border-brand-500 p-2 flex flex-col items-center justify-center group cursor-pointer transition-colors bg-slate-50/40 dark:bg-slate-900/20"
					 onclick="openShiftEditModal(${emp.id}, '${emp.name.replace(/'/g, "\\'")}', ${dow}, null)">
					<span class="text-[11px] text-slate-400 group-hover:text-brand-600 font-medium">+ Asignar</span>
					<span class="text-[10px] text-slate-300 dark:text-slate-600">Descanso</span>
				</div>
			</td>
		`;
	}

	const colorsByType = {
		opening: { bg: 'bg-emerald-50 dark:bg-emerald-950/60', text: 'text-emerald-800 dark:text-emerald-200', border: 'border-emerald-200 dark:border-emerald-800' },
		mid: { bg: 'bg-blue-50 dark:bg-blue-950/60', text: 'text-blue-800 dark:text-blue-200', border: 'border-blue-200 dark:border-blue-800' },
		closing: { bg: 'bg-purple-50 dark:bg-purple-950/60', text: 'text-purple-800 dark:text-purple-200', border: 'border-purple-200 dark:border-purple-800' },
		standard: { bg: 'bg-emerald-50 dark:bg-emerald-950/60', text: 'text-emerald-800 dark:text-emerald-200', border: 'border-emerald-200 dark:border-emerald-800' },
		vacation: { bg: 'bg-amber-50 dark:bg-amber-950/60', text: 'text-amber-800 dark:text-amber-200', border: 'border-amber-200 dark:border-amber-800' },
		absence: { bg: 'bg-rose-50 dark:bg-rose-950/60', text: 'text-rose-800 dark:text-rose-200', border: 'border-rose-200 dark:border-rose-800' },
	};
	const col = colorsByType[shift.shift_type] || colorsByType.mid;

	return `
		<td class="p-2 align-top drop-zone" ondragover="handleDragOver(event)" ondrop="handleDrop(event, ${emp.id}, ${dow})">
			<div draggable="true" ondragstart="handleDragStart(event, ${shift.id})"
				 class="w-full min-h-[58px] rounded-lg border ${col.border} ${col.bg} p-2 flex flex-col justify-between group shadow-xs cursor-grab active:cursor-grabbing transition-all hover:shadow-md relative">
				<div class="flex items-center justify-between">
					<span class="text-[10px] font-bold uppercase tracking-wider ${col.text}">${shift.shift_label || 'Turno'}</span>
					<span class="text-[10px] font-bold ${col.text}">${shift.net_hours}h</span>
				</div>
				<div class="text-[11px] font-bold text-slate-800 dark:text-slate-100 my-0.5">
					${shift.start_time} - ${shift.end_time}
				</div>
				<div class="flex items-center justify-end gap-1 pt-1 opacity-0 group-hover:opacity-100 transition-opacity">
					<button onclick="event.stopPropagation(); duplicateShift(${shift.id})" title="Duplicar a mañana" class="p-0.5 hover:bg-black/10 dark:hover:bg-white/10 rounded text-slate-600 dark:text-slate-300">
						<i data-lucide="copy" class="w-3 h-3"></i>
					</button>
					<button onclick="event.stopPropagation(); openShiftEditModal(${emp.id}, '${emp.name.replace(/'/g, "\\'")}', ${dow}, ${JSON.stringify(shift).replace(/"/g, '&quot;')})" title="Editar horario" class="p-0.5 hover:bg-black/10 dark:hover:bg-white/10 rounded text-slate-600 dark:text-slate-300">
						<i data-lucide="edit-2" class="w-3 h-3"></i>
					</button>
					<button onclick="event.stopPropagation(); promptDeleteShift('${shift.id}', '${emp.name.replace(/'/g, "\\'")}', '${shift.start_time} - ${shift.end_time}')" title="Quitar turno" class="p-0.5 hover:bg-rose-100 dark:hover:bg-rose-900/40 rounded text-rose-600">
						<i data-lucide="trash-2" class="w-3 h-3"></i>
					</button>
				</div>
			</div>
		</td>
	`;
}

// 4. DRAG AND DROP HANDLERS
function handleDragStart(event, shiftId) {
	wfmDraggedShiftId = shiftId;
	event.dataTransfer.setData('text/plain', String(shiftId));
	event.dataTransfer.effectAllowed = 'move';
}

function handleDragOver(event) {
	event.preventDefault();
	event.dataTransfer.dropEffect = 'move';
}

async function handleDrop(event, targetEmpId, targetDow) {
	event.preventDefault();
	if (!wfmDraggedShiftId) return;

	try {
		if (window.PrinceDemoEngine) {
			window.PrinceDemoEngine.moveShift(wfmDraggedShiftId, targetEmpId, targetDow);
			await refreshWfmSchedule();
			return;
		}

		const res = await fetch(`/api/b2b/wfm/shifts/${wfmDraggedShiftId}/move`, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({
				new_employee_id: targetEmpId,
				new_day_of_week: targetDow
			})
		});
		const data = await res.json();
		if (data.success) {
			await refreshWfmSchedule();
		}
	} catch (e) {
		console.warn('Error al mover turno:', e);
	} finally {
		wfmDraggedShiftId = null;
	}
}

// 5. ACCIONES DE TURNO (AGREGAR, DUPLICAR, ELIMINAR, GUARDAR BORRADOR)
function openShiftModal(shiftId, empId, dayOfWeek, explicitDate) {
	let emp = null;
	if (empId) {
		emp = (wfmEmployeesData || []).find(e => e.id === Number(empId));
	}
	let targetDow = dayOfWeek;
	let dateStr = explicitDate || wfmSelectedDate;
	if (!targetDow && dateStr) {
		const d = new Date(dateStr + 'T12:00:00');
		targetDow = d.getDay();
		if (targetDow === 0) targetDow = 7;
	}
	if (!targetDow) targetDow = 1;

	openShiftEditModal(emp ? emp.id : null, emp ? emp.name : null, targetDow, null, dateStr);
}

async function duplicateShift(shiftId) {
	try {
		if (window.PrinceDemoEngine) {
			window.PrinceDemoEngine.duplicateShift(shiftId);
			await refreshWfmSchedule();
			return;
		}

		const res = await fetch(`/api/b2b/wfm/shifts/${shiftId}/duplicate`, { method: 'POST' });
		const data = await res.json();
		if (data.success) {
			await refreshWfmSchedule();
		}
	} catch (e) {
		console.warn('Error al duplicar turno:', e);
	}
}

async function deleteShift(shiftId) {
	try {
		if (window.PrinceDemoEngine) {
			window.PrinceDemoEngine.deleteShift(shiftId);
			await refreshWfmSchedule();
			return;
		}

		const res = await fetch(`/api/b2b/wfm/shifts/${shiftId}`, { method: 'DELETE' });
		const data = await res.json();
		if (data.success) {
			await refreshWfmSchedule();
		}
	} catch (e) {
		console.warn('Error al eliminar turno:', e);
	}
}

function promptDeleteShift(shiftId, empName = 'Colaborador', timeRange = '') {
	if (!shiftId) return;
	document.getElementById('delete-confirm-shift-id').value = shiftId;
	const confirmText = document.getElementById('wfm-delete-confirm-text');
	if (confirmText) {
		confirmText.textContent = `¿Confirma que desea eliminar el turno ${empName ? 'de ' + empName : ''} ${timeRange ? '(' + timeRange + ')' : ''}? Esta acción convertirá el día en descanso semanal y se registrará en el registro inmutable de auditoría de WFM.`;
	}
	const modal = document.getElementById('modal-wfm-delete-confirm');
	if (modal) modal.classList.remove('hidden');
	if (typeof lucide !== 'undefined') lucide.createIcons();
}

function confirmDeleteShiftFromModal() {
	const shiftId = document.getElementById('shift-edit-id').value;
	if (!shiftId) {
		closeWfmModal('modal-wfm-shift-edit');
		return;
	}

	const empName = document.getElementById('shift-modal-emp-name')?.textContent || 'Colaborador';
	const startTime = document.getElementById('shift-edit-start')?.value || '08:00';
	const endTime = document.getElementById('shift-edit-end')?.value || '16:30';
	promptDeleteShift(shiftId, empName, `${startTime} - ${endTime}`);
}

async function executeConfirmedShiftDelete() {
	const shiftId = document.getElementById('delete-confirm-shift-id').value;
	if (shiftId) {
		await deleteShift(shiftId);
	}
	closeWfmModal('modal-wfm-delete-confirm');
	closeWfmModal('modal-wfm-shift-edit');
}

function saveScheduleDraft() {
	if (window.PrinceDemoEngine) {
		const week = document.getElementById('wfm-filter-week')?.value || '2026-W47';
		const sched = window.PrinceDemoEngine.getSchedule(week);
		if (sched) {
			sched.status = 'draft';
			window.PrinceDemoEngine.saveState();
		}
		const badge = document.getElementById('wfm-status-badge');
		if (badge) {
			badge.textContent = 'Borrador Guardado';
			badge.className = 'px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-700';
		}
	}
	alert('✓ Borrador de programación semanal guardado exitosamente. Los turnos permanecen en estado borrador y pueden continuar editándose.');
}

// 6. MODAL DE EDICIÓN / ASIGNACIÓN DE TURNO
function openShiftEditModal(empId, empName, dow, shift, explicitDate) {
	const dowNames = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
	
	const allEmps = (window.PrinceDemoEngine ? window.PrinceDemoEngine.getEmployees() : wfmEmployeesData) || [];
	const empSelectWrap = document.getElementById('shift-modal-emp-select-wrap');
	const empInfoWrap = document.getElementById('shift-modal-emp-info-wrap');
	const empSelect = document.getElementById('shift-edit-emp-select');

	let selectedEmpId = empId;
	let selectedEmpName = empName;

	if (!selectedEmpId) {
		// Modo creación libre: mostrar dropdown de colaboradores
		if (empSelectWrap) empSelectWrap.classList.remove('hidden');
		if (empInfoWrap) empInfoWrap.classList.add('hidden');
		if (empSelect) {
			empSelect.innerHTML = allEmps.map(e => `
				<option value="${e.id}">${e.name} — ${e.role} (${e.department})</option>
			`).join('');
			if (allEmps.length > 0) {
				selectedEmpId = allEmps[0].id;
				selectedEmpName = allEmps[0].name;
				empSelect.value = selectedEmpId;
			}
		}
	} else {
		// Colaborador específico
		if (empSelectWrap) empSelectWrap.classList.add('hidden');
		if (empInfoWrap) empInfoWrap.classList.remove('hidden');
		if (!selectedEmpName) {
			const found = allEmps.find(e => e.id === Number(selectedEmpId));
			selectedEmpName = found ? found.name : 'Colaborador';
		}
	}

	let targetDow = Number(dow) || 1;
	let dateStr = explicitDate || (shift ? shift.shift_date : wfmSelectedDate);
	if (dateStr && (!targetDow || targetDow < 1 || targetDow > 7)) {
		const d = new Date(dateStr + 'T12:00:00');
		targetDow = d.getDay();
		if (targetDow === 0) targetDow = 7;
	}

	document.getElementById('shift-edit-id').value = shift ? shift.id : '';
	document.getElementById('shift-edit-emp-id').value = selectedEmpId || 101;
	document.getElementById('shift-edit-dow').value = targetDow;
	document.getElementById('shift-edit-date').value = dateStr || '2026-11-20';

	const nameEl = document.getElementById('shift-modal-emp-name');
	if (nameEl) nameEl.textContent = selectedEmpName || 'Colaborador';

	const dowEl = document.getElementById('shift-modal-dow-name');
	if (dowEl) {
		dowEl.textContent = dateStr ? `${dowNames[targetDow] || 'Día ' + targetDow}, ${dateStr}` : (dowNames[targetDow] || `Día ${targetDow}`);
	}

	const repeatToggle = document.getElementById('shift-edit-repeat-toggle');
	if (repeatToggle) repeatToggle.checked = false;
	toggleRepeatDaysSection(false);

	// Preseleccionar checkbox del día correspondiente
	document.querySelectorAll('.repeat-dow-checkbox').forEach(cb => {
		cb.checked = (Number(cb.value) === targetDow);
	});

	if (shift) {
		document.getElementById('wfm-shift-modal-title').textContent = 'Editar Turno de Trabajo';
		document.getElementById('shift-edit-start').value = shift.start_time;
		document.getElementById('shift-edit-end').value = shift.end_time;
		document.getElementById('shift-edit-break').value = shift.break_minutes ?? 30;
		document.getElementById('shift-edit-type').value = shift.shift_type || 'mid';
		document.getElementById('shift-edit-notes').value = shift.task_notes || '';
	} else {
		document.getElementById('wfm-shift-modal-title').textContent = 'Asignar Turno de Trabajo';
		document.getElementById('shift-edit-start').value = '08:00';
		document.getElementById('shift-edit-end').value = '16:30';
		document.getElementById('shift-edit-break').value = 30;
		document.getElementById('shift-edit-type').value = 'mid';
		document.getElementById('shift-edit-notes').value = '';
	}

	triggerLiveShiftValidation();

	const modal = document.getElementById('modal-wfm-shift-edit');
	if (modal) modal.classList.remove('hidden');
	if (typeof lucide !== 'undefined') lucide.createIcons();
}

function handleModalEmployeeSelect(empId) {
	document.getElementById('shift-edit-emp-id').value = empId;
	const allEmps = (window.PrinceDemoEngine ? window.PrinceDemoEngine.getEmployees() : wfmEmployeesData) || [];
	const emp = allEmps.find(e => e.id === Number(empId));
	if (emp) {
		const nameEl = document.getElementById('shift-modal-emp-name');
		if (nameEl) nameEl.textContent = emp.name;
	}
	triggerLiveShiftValidation();
}

function toggleRepeatDaysSection(checked) {
	const container = document.getElementById('shift-repeat-days-container');
	if (container) {
		container.classList.toggle('hidden', !checked);
	}
	if (checked) {
		const curDow = Number(document.getElementById('shift-edit-dow').value) || 1;
		const anyChecked = Array.from(document.querySelectorAll('.repeat-dow-checkbox:checked')).length > 0;
		if (!anyChecked) {
			document.querySelectorAll('.repeat-dow-checkbox').forEach(cb => {
				cb.checked = (Number(cb.value) === curDow);
			});
		}
	}
}

function triggerLiveShiftValidation() {
	const alertContainer = document.getElementById('shift-validation-alerts');
	if (!alertContainer) return;

	const empId = Number(document.getElementById('shift-edit-emp-id').value);
	const dow = Number(document.getElementById('shift-edit-dow').value) || 1;
	const startTime = document.getElementById('shift-edit-start').value;
	const endTime = document.getElementById('shift-edit-end').value;
	const breakMin = Number(document.getElementById('shift-edit-break').value) || 0;
	const shiftType = document.getElementById('shift-edit-type').value;
	const shiftId = document.getElementById('shift-edit-id').value;

	if (!window.PrinceDemoEngine) {
		alertContainer.classList.add('hidden');
		alertContainer.innerHTML = '';
		return;
	}

	const valResult = window.PrinceDemoEngine.validateShift({
		id: shiftId,
		employee_id: empId,
		day_of_week: dow,
		start_time: startTime,
		end_time: endTime,
		break_minutes: breakMin,
		shift_type: shiftType
	});

	if (valResult.errors.length === 0 && valResult.warnings.length === 0) {
		alertContainer.classList.add('hidden');
		alertContainer.innerHTML = '';
		return;
	}

	let html = '';
	valResult.errors.forEach(err => {
		html += `
			<div class="p-2.5 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 font-medium flex items-center gap-2">
				<i data-lucide="alert-octagon" class="w-4 h-4 text-rose-600 shrink-0"></i>
				<span>${err}</span>
			</div>
		`;
	});

	valResult.warnings.forEach(warn => {
		html += `
			<div class="p-2.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 font-medium flex items-center gap-2">
				<i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0"></i>
				<span>${warn}</span>
			</div>
		`;
	});

	alertContainer.innerHTML = html;
	alertContainer.classList.remove('hidden');
	if (typeof lucide !== 'undefined') lucide.createIcons();
}

function applyShiftTemplateToForm() {
	const val = document.getElementById('shift-edit-template').value;
	if (!val) return;

	if (val === 'TMPL-APT') {
		document.getElementById('shift-edit-start').value = '07:00';
		document.getElementById('shift-edit-end').value = '15:30';
		document.getElementById('shift-edit-break').value = 30;
		document.getElementById('shift-edit-type').value = 'opening';
	} else if (val === 'TMPL-INT') {
		document.getElementById('shift-edit-start').value = '09:00';
		document.getElementById('shift-edit-end').value = '17:30';
		document.getElementById('shift-edit-break').value = 30;
		document.getElementById('shift-edit-type').value = 'mid';
	} else if (val === 'TMPL-CIE') {
		document.getElementById('shift-edit-start').value = '13:30';
		document.getElementById('shift-edit-end').value = '22:00';
		document.getElementById('shift-edit-break').value = 30;
		document.getElementById('shift-edit-type').value = 'closing';
	} else if (val === 'TMPL-RUT') {
		document.getElementById('shift-edit-start').value = '08:00';
		document.getElementById('shift-edit-end').value = '17:00';
		document.getElementById('shift-edit-break').value = 60;
		document.getElementById('shift-edit-type').value = 'standard';
	} else if (val === 'TMPL-PAR') {
		document.getElementById('shift-edit-start').value = '08:00';
		document.getElementById('shift-edit-end').value = '12:00';
		document.getElementById('shift-edit-break').value = 0;
		document.getElementById('shift-edit-type').value = 'standard';
	} else if (val === 'REST') {
		document.getElementById('shift-edit-start').value = '00:00';
		document.getElementById('shift-edit-end').value = '00:00';
		document.getElementById('shift-edit-break').value = 0;
		document.getElementById('shift-edit-type').value = 'rest';
	}

	triggerLiveShiftValidation();
}

async function handleSaveShift(event) {
	if (event && event.preventDefault) event.preventDefault();
	const shiftId = document.getElementById('shift-edit-id').value;
	const empId = Number(document.getElementById('shift-edit-emp-id').value);
	const dow = Number(document.getElementById('shift-edit-dow').value) || 1;
	const shiftDate = document.getElementById('shift-edit-date').value || wfmSelectedDate;
	const startTime = document.getElementById('shift-edit-start').value;
	const endTime = document.getElementById('shift-edit-end').value;
	const breakMin = Number(document.getElementById('shift-edit-break').value) || 0;
	const shiftType = document.getElementById('shift-edit-type').value;
	const notes = document.getElementById('shift-edit-notes').value;
	const week = document.getElementById('wfm-filter-week')?.value || '2026-W47';

	let repeatDays = null;
	const repeatToggle = document.getElementById('shift-edit-repeat-toggle');
	if (repeatToggle && repeatToggle.checked) {
		repeatDays = Array.from(document.querySelectorAll('.repeat-dow-checkbox:checked')).map(cb => Number(cb.value));
		if (repeatDays.length === 0) repeatDays = [dow];
	}

	const labelMap = {
		opening: 'Apertura',
		mid: 'Intermedio',
		closing: 'Cierre',
		standard: 'Ruta Preventa',
		rest: 'Descanso Semanal',
		vacation: 'Vacaciones Autorizadas',
		absence: 'Ausencia Justificada'
	};

	try {
		if (window.PrinceDemoEngine) {
			window.PrinceDemoEngine.addOrUpdateShift({
				id: shiftId || undefined,
				schedule_id: 1,
				employee_id: empId,
				day_of_week: dow,
				shift_date: shiftDate,
				start_time: startTime,
				end_time: endTime,
				break_minutes: breakMin,
				shift_type: shiftType,
				shift_label: labelMap[shiftType] || 'Turno',
				task_notes: notes,
				repeat_days: repeatDays
			});

			closeWfmModal('modal-wfm-shift-edit');
			await refreshWfmSchedule();
			return;
		}

		const res = await fetch('/api/b2b/wfm/shifts', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({
				schedule_id: wfmCurrentSchedule?.id || 1,
				employee_id: empId,
				day_of_week: dow,
				shift_date: shiftDate,
				start_time: startTime,
				end_time: endTime,
				break_minutes: breakMin,
				shift_type: shiftType,
				shift_label: labelMap[shiftType] || 'Turno',
				task_notes: notes,
				repeat_days: repeatDays
			})
		});
		const data = await res.json();
		if (data.success) {
			closeWfmModal('modal-wfm-shift-edit');
			await refreshWfmSchedule();
		}
	} catch (e) {
		console.warn('Error al guardar turno:', e);
	}
}

async function handleDeleteShiftFromModal() {
	confirmDeleteShiftFromModal();
}

// 7. ASISTENTE WIZARD (4 PASOS)
function openWizardStep(step) {
	wfmWizardCurrentStep = step;
	for (let i = 1; i <= 4; i++) {
		const elStep = document.getElementById(`wfm-wizard-step-${i}`);
		const elPill = document.getElementById(`wfm-step-pill-${i}`);
		if (elStep) elStep.classList.toggle('hidden', i !== step);
		if (elPill) {
			const badge = elPill.querySelector('span:first-child');
			if (badge) {
				badge.className = i <= step
					? 'w-6 h-6 rounded-full bg-brand-700 text-white flex items-center justify-center text-xs font-bold'
					: 'w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs font-bold';
			}
		}
	}

	const btnPrev = document.getElementById('wfm-btn-prev');
	const btnNext = document.getElementById('wfm-btn-next');
	const btnFinish = document.getElementById('wfm-btn-finish');

	if (btnPrev) btnPrev.classList.toggle('hidden', step === 1);
	if (btnNext) btnNext.classList.toggle('hidden', step === 4);
	if (btnFinish) btnFinish.classList.toggle('hidden', step !== 4);

	const modal = document.getElementById('modal-wfm-wizard');
	if (modal) modal.classList.remove('hidden');
	if (typeof lucide !== 'undefined') lucide.createIcons();
}

function navigateWizard(delta) {
	const next = Math.max(1, Math.min(4, wfmWizardCurrentStep + delta));
	openWizardStep(next);
}

async function finalizeWizardPublication() {
	await publishCurrentSchedule();
	closeWfmModal('modal-wfm-wizard');
}

// 8. PUBLICAR HORARIO
async function publishCurrentSchedule() {
	try {
		const week = document.getElementById('wfm-filter-week')?.value || '2026-W47';

		if (window.PrinceDemoEngine) {
			const pub = window.PrinceDemoEngine.publishSchedule(week);
			const badge = document.getElementById('wfm-status-badge');
			if (badge) {
				badge.textContent = `Publicado v${pub.version}`;
				badge.className = 'px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800';
			}
			alert(`✓ Programación semanal publicada con éxito (Versión ${pub.version}). Los turnos están activos para el personal.`);
			await refreshWfmSchedule();
			return;
		}

		const schedId = wfmCurrentSchedule?.id || 1;
		const res = await fetch(`/api/b2b/wfm/schedules/${schedId}/publish`, { method: 'POST' });
		const data = await res.json();
		if (data.success) {
			const badge = document.getElementById('wfm-status-badge');
			if (badge) badge.textContent = `Publicado v${data.version}`;
			alert(`✓ Programación semanal publicada con éxito (Versión ${data.version}). Los turnos están activos para el personal.`);
			await refreshWfmSchedule();
		}
	} catch (e) {
		console.warn('Error al publicar horario:', e);
	}
}

// 9. SIMULADOR INLINE Y MODAL
function setInlineSimulationSales(deltaPct) {
	wfmSimSalesDelta = deltaPct;
	['m10', '0', 'p10', 'p20'].forEach(id => {
		const btn = document.getElementById(`btn-sim-${id}`);
		if (!btn) return;
		const isMatch = (id === 'm10' && deltaPct === -10) ||
						(id === '0' && deltaPct === 0) ||
						(id === 'p10' && deltaPct === 10) ||
						(id === 'p20' && deltaPct === 20);
		if (isMatch) {
			btn.className = 'sim-sales-btn py-2 text-xs font-bold rounded-lg bg-brand-700 text-white shadow-xs';
		} else {
			btn.className = 'sim-sales-btn py-2 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300';
		}
	});
	runInlineSimulation();
}

async function runInlineSimulation() {
	try {
		const absent = Number(document.getElementById('sim-inline-absences')?.value || 0);
		const prodFactor = Number(document.getElementById('sim-inline-prod-factor')?.value || 1.0);
		const baseSales = 48500.00;

		let data = null;
		if (window.PrinceDemoEngine) {
			data = window.PrinceDemoEngine.simulateDemand(baseSales, wfmSimSalesDelta, absent);
		} else {
			const res = await fetch('/api/b2b/wfm/simulate', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					base_sales: baseSales,
					sales_delta_pct: wfmSimSalesDelta,
					absent_employees: absent
				})
			});
			const r = await res.json();
			if (r.success) data = r;
		}

		if (data) {
			const adjustedHours = (data.recommended_hours / prodFactor);
			const elSales = document.getElementById('sim-inline-sales');
			const elHours = document.getElementById('sim-inline-hours');
			const elCost = document.getElementById('sim-inline-cost');
			const elCov = document.getElementById('sim-inline-cov');
			const elAdvice = document.getElementById('sim-inline-recommendation');

			if (elSales) elSales.textContent = '$' + data.simulated_sales.toLocaleString('en-US', { minimumFractionDigits: 2 });
			if (elHours) elHours.textContent = adjustedHours.toFixed(1) + ' hrs';
			if (elCost) elCost.textContent = '$' + (adjustedHours * 7.50).toLocaleString('en-US', { minimumFractionDigits: 2 });
			if (elCov) elCov.textContent = data.projected_coverage_pct.toFixed(1) + '%';
			if (elAdvice) elAdvice.textContent = data.advice;
		}
	} catch (e) {
		console.warn('Error en simulación inline:', e);
	}
}

// 10. CONFIGURACIÓN WFM
function saveWfmSettingsForm() {
	alert('✓ Parámetros operativos y comerciales de Workforce Management guardados correctamente.');
}

// 11. COPIAR SEMANA
function openCopyWeekModal() {
	if (confirm('¿Desea copiar la plantilla de turnos de la semana anterior (Semana 46) a la semana actual?')) {
		if (window.PrinceDemoEngine) {
			const sched46 = window.PrinceDemoEngine.getSchedule('2026-W46');
			const sched47 = window.PrinceDemoEngine.getSchedule('2026-W47');
			if (sched46 && sched47) {
				sched47.shifts = JSON.parse(JSON.stringify(sched46.shifts)).map(s => {
					s.id = ++window.PrinceDemoEngine.nextShiftId;
					s.schedule_id = sched47.id;
					return s;
				});
				window.PrinceDemoEngine.saveState();
			}
		}
		alert('✓ Turnos de la semana anterior copiados y ajustados con éxito.');
		refreshWfmSchedule();
	}
}

// 12. REFRESCAR PANTALLA COMPLETA
async function refreshWfmSchedule() {
	await Promise.all([loadWfmDashboard(), loadWfmSchedule()]);
	if (wfmActiveCalendarView === 'daily') {
		await loadWfmDailySchedule();
	} else if (wfmActiveCalendarView === 'monthly') {
		await loadWfmMonthlySchedule();
	}
	renderWfmSalesVsHoursChart();
}

function closeWfmModal(id) {
	const el = document.getElementById(id);
	if (el) el.classList.add('hidden');
}

// 13. CARGAR DIRECTORIO DE EMPLEADOS
async function loadWfmEmployees() {
	try {
		if (window.PrinceDemoEngine) {
			wfmEmployeesData = window.PrinceDemoEngine.getEmployees();
			renderEmployeesTable(wfmEmployeesData);
			return;
		}

		const res = await fetch('/api/b2b/wfm/employees');
		if (!res.ok) return;
		const data = await res.json();
		if (!data.success) return;

		wfmEmployeesData = data.employees || [];
		renderEmployeesTable(wfmEmployeesData);
	} catch (e) {
		console.warn('Error al cargar empleados:', e);
	}
}

function renderEmployeesTable(employees) {
	const tbody1 = document.getElementById('wfm-employees-tbody');
	const tbody2 = document.getElementById('wfm-employees-tbody-2');

	const html = employees.map(emp => `
		<tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
			<td class="p-3">
				<div class="font-bold text-slate-800 dark:text-slate-100">${emp.name}</div>
				<div class="text-[10px] text-slate-400 font-mono">${emp.code}</div>
			</td>
			<td class="p-3 font-medium text-slate-700 dark:text-slate-300">${emp.role_label}</td>
			<td class="p-3 text-slate-600 dark:text-slate-400">${emp.department}</td>
			<td class="p-3 text-slate-600 dark:text-slate-400">${emp.establishment_name} <span class="text-[10px] text-slate-400">(${emp.territory})</span></td>
			<td class="p-3 text-center font-bold text-slate-800 dark:text-slate-200">${emp.contracted_hours}h / sem</td>
			<td class="p-3 text-right font-mono font-semibold text-slate-700 dark:text-slate-300">$${Number(emp.hourly_cost).toFixed(2)}</td>
			<td class="p-3 text-slate-600 dark:text-slate-400">${emp.supervisor_name}</td>
			<td class="p-3 text-center">
				<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300">Activo</span>
			</td>
		</tr>
	`).join('');

	if (tbody1) tbody1.innerHTML = html;
	if (tbody2) tbody2.innerHTML = html;
}

function filterEmployeesTable(customQ) {
	const q = (customQ != null ? customQ : document.getElementById('wfm-emp-search')?.value || '').toLowerCase();
	const filtered = wfmEmployeesData.filter(e => e.name.toLowerCase().includes(q) || e.code.toLowerCase().includes(q) || e.role_label.toLowerCase().includes(q));
	renderEmployeesTable(filtered);
}

// 14. CARGAR SOLICITUDES DE CAMBIO DE TURNO
async function loadWfmRequests() {
	try {
		if (window.PrinceDemoEngine) {
			const reqs = window.PrinceDemoEngine.getSwapRequests();
			renderRequestsTable(reqs);
			return;
		}

		const res = await fetch('/api/b2b/wfm/requests');
		if (!res.ok) return;
		const data = await res.json();
		if (!data.success) return;

		renderRequestsTable(data.requests || []);
	} catch (e) {
		console.warn('Error al cargar solicitudes:', e);
	}
}

function renderRequestsTable(reqs) {
	const tbody = document.getElementById('wfm-requests-tbody');
	if (!tbody) return;

	tbody.innerHTML = (reqs || []).map(r => `
		<tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
			<td class="p-3 font-bold text-slate-800 dark:text-slate-100">${r.employee_name}</td>
			<td class="p-3">
				<span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${r.request_type === 'swap' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700'}">
					${r.request_type === 'swap' ? 'Intercambio' : (r.request_type === 'vacation' ? 'Vacaciones' : 'Permiso')}
				</span>
			</td>
			<td class="p-3 font-mono text-slate-600 dark:text-slate-300">${r.shift_date}</td>
			<td class="p-3 text-slate-600 dark:text-slate-400">
				${r.reason} ${r.target_employee_name ? `<span class="block text-[10px] font-semibold text-slate-500">Reemplazo: ${r.target_employee_name}</span>` : ''}
			</td>
			<td class="p-3 text-center">
				<span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${r.status === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'}">
					${r.status === 'approved' ? 'Aprobado' : 'Pendiente'}
				</span>
			</td>
			<td class="p-3 text-right">
				${r.status === 'pending' ? `
					<button onclick="reviewRequest(${r.id}, 'approved')" class="px-2.5 py-1 text-[11px] font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-md transition-colors mr-1">
						Aprobar
					</button>
					<button onclick="reviewRequest(${r.id}, 'rejected')" class="px-2.5 py-1 text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-md transition-colors">
						Rechazar
					</button>
				` : `<span class="text-[11px] text-slate-400">Procesado</span>`}
			</td>
		</tr>
	`).join('');
}

async function reviewRequest(reqId, decision) {
	try {
		if (window.PrinceDemoEngine) {
			window.PrinceDemoEngine.reviewSwapRequest(reqId, decision);
			await loadWfmRequests();
			return;
		}

		const res = await fetch(`/api/b2b/wfm/requests/${reqId}/review`, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ decision })
		});
		const data = await res.json();
		if (data.success) {
			await loadWfmRequests();
		}
	} catch (e) {
		console.warn('Error al procesar solicitud:', e);
	}
}

// =========================================================================
// FUNCIONES ESPECÍFICAS DE CONTROL DEL MOTOR DE DEMOSTRACIÓN INTERACTIVA
// =========================================================================

// A. CAMBIO DE ROL DEMO
function handleDemoRoleSwitch(role, updateEngine = true) {
	wfmActiveDemoRole = role;
	if (updateEngine && window.PrinceDemoEngine) {
		window.PrinceDemoEngine.setActiveRole(role);
	}

	const roleSelect = document.getElementById('demo-active-role-select');
	if (roleSelect && roleSelect.value !== role) {
		roleSelect.value = role;
	}

	const adminView = document.getElementById('wfm-admin-schedule-view');
	const empView = document.getElementById('wfm-employee-personal-view');

	if (role === 'employee' || role === 'reponedor' || role === 'merchandiser' || role === 'sales_rep') {
		if (adminView) adminView.classList.add('hidden');
		if (empView) empView.classList.remove('hidden');
		renderEmployeePersonalSchedule(role);
	} else {
		if (adminView) adminView.classList.remove('hidden');
		if (empView) empView.classList.add('hidden');
	}

	if (typeof lucide !== 'undefined') lucide.createIcons();
}

// B. RENDERIZAR HORARIO PERSONAL DE UN COLABORADOR
function renderEmployeePersonalSchedule(role) {
	if (!window.PrinceDemoEngine) return;

	const employees = window.PrinceDemoEngine.getEmployees();
	// Seleccionar el empleado según rol
	const emp = (role === 'merchandiser' || role === 'reponedor')
		? (employees.find(e => e.role === 'merchandiser') || employees[1])
		: (employees.find(e => e.role === 'sales_rep') || employees[0]);

	if (!emp) return;

	const week = document.getElementById('wfm-filter-week')?.value || '2026-W47';
	const sched = window.PrinceDemoEngine.getSchedule(week);
	const empShifts = sched.shifts.filter(s => s.employee_id === emp.id);

	const elAvatar = document.getElementById('wfm-emp-avatar');
	const elName = document.getElementById('wfm-emp-name');
	const elDetail = document.getElementById('wfm-emp-detail');
	const elContracted = document.getElementById('wfm-emp-contracted-hours');
	const elScheduled = document.getElementById('wfm-emp-scheduled-hours');
	const elEstab = document.getElementById('wfm-emp-establishment');
	const elSup = document.getElementById('wfm-emp-supervisor');
	const elCards = document.getElementById('wfm-emp-schedule-cards');
	const elWeekLabel = document.getElementById('wfm-emp-week-label');

	if (elAvatar) elAvatar.textContent = emp.name.split(' ').map(n => n[0]).slice(0, 2).join('');
	if (elName) elName.textContent = emp.name;
	if (elDetail) elDetail.textContent = `${emp.role_label} • ${emp.territory} • ${emp.establishment_name}`;
	if (elContracted) elContracted.textContent = `${emp.contracted_hours}.0 hrs / sem`;

	let totalHours = 0;
	empShifts.forEach(s => totalHours += Number(s.net_hours || 0));
	if (elScheduled) elScheduled.textContent = `${totalHours.toFixed(1)} hrs`;
	if (elEstab) elEstab.textContent = emp.establishment_name;
	if (elSup) elSup.textContent = emp.supervisor_name;
	if (elWeekLabel) elWeekLabel.textContent = `${week === '2026-W47' ? 'Semana 47 (Actual)' : week}`;

	if (!elCards) return;

	const dowNames = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
	const dowDates = ['16 Nov', '17 Nov', '18 Nov', '19 Nov', '20 Nov', '21 Nov', '22 Nov'];

	elCards.innerHTML = dowNames.map((name, idx) => {
		const dow = idx + 1;
		const shift = empShifts.find(s => s.day_of_week === dow);
		const isRest = !shift || shift.shift_type === 'rest';

		const colorsByType = {
			opening: { bg: 'bg-emerald-50 dark:bg-emerald-950/60', text: 'text-emerald-800 dark:text-emerald-200', border: 'border-emerald-200 dark:border-emerald-800' },
			mid: { bg: 'bg-blue-50 dark:bg-blue-950/60', text: 'text-blue-800 dark:text-blue-200', border: 'border-blue-200 dark:border-blue-800' },
			closing: { bg: 'bg-purple-50 dark:bg-purple-950/60', text: 'text-purple-800 dark:text-purple-200', border: 'border-purple-200 dark:border-purple-800' },
			standard: { bg: 'bg-emerald-50 dark:bg-emerald-950/60', text: 'text-emerald-800 dark:text-emerald-200', border: 'border-emerald-200 dark:border-emerald-800' },
			vacation: { bg: 'bg-amber-50 dark:bg-amber-950/60', text: 'text-amber-800 dark:text-amber-200', border: 'border-amber-200 dark:border-amber-800' },
			absence: { bg: 'bg-rose-50 dark:bg-rose-950/60', text: 'text-rose-800 dark:text-rose-200', border: 'border-rose-200 dark:border-rose-800' },
		};
		const col = isRest ? { bg: 'bg-slate-50 dark:bg-slate-900', text: 'text-slate-500', border: 'border-slate-200 dark:border-slate-800' } : (colorsByType[shift.shift_type] || colorsByType.mid);

		return `
			<div class="rounded-xl border ${col.border} ${col.bg} p-3.5 flex flex-col justify-between shadow-2xs">
				<div>
					<div class="flex items-center justify-between text-xs font-semibold text-slate-500 dark:text-slate-400">
						<span>${name}</span>
						<span class="text-[11px] font-mono text-slate-400">${dowDates[idx]}</span>
					</div>
					<div class="mt-2.5">
						<span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded-md ${col.text} bg-white/70 dark:bg-black/20">
							${isRest ? 'Descanso Semanal' : (shift.shift_label || 'Turno')}
						</span>
					</div>
					<div class="mt-1.5 text-sm font-bold text-slate-900 dark:text-white">
						${isRest ? 'Libre' : `${shift.start_time} – ${shift.end_time}`}
					</div>
				</div>
				<div class="mt-3 pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px]">
					<span class="text-slate-500">${isRest ? '0 hrs' : `${shift.net_hours} hrs netas`}</span>
					<span class="font-medium ${isRest ? 'text-slate-400' : 'text-emerald-600'}">${isRest ? 'Refrigerio N/A' : '30 min break'}</span>
				</div>
			</div>
		`;
	}).join('');
}

// C. SOLICITAR CAMBIO DE TURNO PERSONAL
function requestPersonalShiftSwap() {
	const reason = prompt('Indique el motivo de la solicitud de cambio de turno:', 'Permiso por cita médica');
	if (!reason) return;

	if (window.PrinceDemoEngine) {
		const employees = window.PrinceDemoEngine.getEmployees();
		const emp = (wfmActiveDemoRole === 'reponedor')
			? (employees.find(e => e.role === 'reponedor') || employees[8])
			: (employees.find(e => e.role === 'vendedor') || employees[2]);

		window.PrinceDemoEngine.addSwapRequest({
			employee_id: emp.id,
			employee_name: emp.name,
			request_type: 'swap',
			shift_date: '2026-11-18',
			reason: reason,
			target_employee_id: 104,
			target_employee_name: 'Ana Vásquez'
		});

		updateDemoUnreadBadge();
		alert('✓ Solicitud de intercambio enviada exitosamente a supervisión para revisión.');
		loadWfmRequests();
	}
}

// D. MODAL DE OPTIMIZACIÓN INTELIGENTE
function openOptimizationModal() {
	if (!window.PrinceDemoEngine) return;
	const week = document.getElementById('wfm-filter-week')?.value || '2026-W47';
	const res = window.PrinceDemoEngine.runSmartOptimization(week);

	const setTxt = (id, txt) => {
		const el = document.getElementById(id);
		if (el) el.textContent = txt;
	};

	setTxt('opt-cur-hours', `${res.before.scheduled_hours.toFixed(1)} hrs`);
	setTxt('wfm-opt-hours-before', `${res.before.scheduled_hours.toFixed(1)} hrs`);
	setTxt('opt-prop-hours', `${res.after.scheduled_hours.toFixed(1)} hrs`);
	setTxt('wfm-opt-hours-after', `${res.after.scheduled_hours.toFixed(1)} hrs`);

	setTxt('opt-cur-cov', `${res.before.coverage_pct.toFixed(1)}%`);
	setTxt('wfm-opt-cov-before', `${res.before.coverage_pct.toFixed(1)}%`);
	setTxt('opt-prop-cov', `${res.after.coverage_pct.toFixed(1)}%`);
	setTxt('wfm-opt-cov-after', `${res.after.coverage_pct.toFixed(1)}%`);

	setTxt('opt-cur-cost', `$${res.before.cost.toFixed(2)}`);
	setTxt('wfm-opt-cost-before', `$${res.before.cost.toFixed(2)}`);
	setTxt('opt-prop-cost', `$${res.after.cost.toFixed(2)}`);
	setTxt('wfm-opt-cost-after', `$${res.after.cost.toFixed(2)}`);

	const listHtml = res.suggestions.map((s, idx) => `
		<div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs flex items-center justify-between">
			<span class="font-medium text-slate-800 dark:text-slate-200">${s.action}</span>
			<span class="font-mono text-emerald-600 dark:text-emerald-400 font-bold">${s.impact}</span>
		</div>
	`).join('');

	const elList1 = document.getElementById('opt-suggestions-list');
	const elList2 = document.getElementById('wfm-opt-suggestions-list');
	if (elList1) elList1.innerHTML = listHtml;
	if (elList2) elList2.innerHTML = listHtml;

	const btnRevert = document.getElementById('btn-revert-opt');
	if (btnRevert) btnRevert.classList.remove('hidden');

	const modal = document.getElementById('modal-wfm-optimize');
	if (modal) modal.classList.remove('hidden');
}

function applyCurrentOptimization() {
	if (!window.PrinceDemoEngine) return;
	const week = document.getElementById('wfm-filter-week')?.value || '2026-W47';
	window.PrinceDemoEngine.applyOptimization(week);
	closeWfmModal('modal-wfm-optimize');
	refreshWfmSchedule();
	alert('✓ Optimización inteligente aplicada exitosamente al borrador de la ' + (week === '2026-W47' ? 'Semana 47' : week) + '.');
}

function revertCurrentOptimization() {
	if (!window.PrinceDemoEngine) return;
	const week = document.getElementById('wfm-filter-week')?.value || '2026-W47';
	window.PrinceDemoEngine.revertOptimization(week);
	closeWfmModal('modal-wfm-optimize');
	refreshWfmSchedule();
	alert('✓ Optimización revertida al estado anterior.');
}

// E. MODAL DE NOTIFICACIONES DEMO
function openDemoNotificationsModal() {
	if (!window.PrinceDemoEngine) return;
	const notifs = window.PrinceDemoEngine.getNotifications();
	const list1 = document.getElementById('wfm-notifications-container');
	const list2 = document.getElementById('wfm-notifications-list');

	const html = (notifs.length === 0)
		? `<div class="p-6 text-center text-slate-400 text-xs">No hay notificaciones registradas.</div>`
		: notifs.map(n => {
			const badgeCol = n.severity === 'urgent' ? 'bg-rose-100 text-rose-700' : (n.severity === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700');
			return `
				<div class="p-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-2xs flex items-start justify-between gap-3">
					<div class="text-xs">
						<div class="flex items-center gap-2">
							<span class="font-bold text-slate-800 dark:text-slate-100">${n.title}</span>
							<span class="px-1.5 py-0.2 rounded text-[10px] font-bold ${badgeCol} uppercase">${n.severity || 'info'}</span>
						</div>
						<p class="text-slate-600 dark:text-slate-400 mt-0.5">${n.text || n.message}</p>
						<span class="text-[10px] text-slate-400 block mt-1">${n.time || 'Hace un momento'} • Módulo WFM</span>
					</div>
				</div>
			`;
		}).join('');

	if (list1) list1.innerHTML = html;
	if (list2) list2.innerHTML = html;

	window.PrinceDemoEngine.markAllNotificationsRead();
	updateDemoUnreadBadge();

	const modal = document.getElementById('modal-wfm-notifications');
	if (modal) modal.classList.remove('hidden');
}

function markAllNotificationsRead() {
	if (window.PrinceDemoEngine) {
		window.PrinceDemoEngine.markAllNotificationsRead();
		updateDemoUnreadBadge();
		openDemoNotificationsModal();
	}
}

// F. MODAL DE AJUSTES Y PERSISTENCIA DEMO
function openDemoSettingsModal() {
	const modal = document.getElementById('modal-wfm-demo-settings');
	if (modal) modal.classList.remove('hidden');
}

function exportDemoDatasetJson() {
	if (window.PrinceDemoEngine) {
		window.PrinceDemoEngine.exportDatasetJson();
	}
}

function confirmResetDemoData() {
	if (confirm('¿Confirma que desea restablecer todos los turnos, colaboradores y KPIs a sus valores originales de fábrica?')) {
		if (window.PrinceDemoEngine) {
			window.PrinceDemoEngine.resetToDefaults();
			closeWfmModal('modal-wfm-demo-settings');
			refreshWfmSchedule();
			alert('✓ Dataset restablecido exitosamente a sus valores originales.');
		}
	}
}

// G. CONTROL DE TAREAS & OPERACIONES EN TIENDA (SHARED WEB & MOBILE)
function renderWfmTasksAndInventory() {
	renderWfmTasksTable();
	renderWfmInventoryTable();
}

function renderWfmTasksTable() {
	if (!window.PrinceDemoEngine) return;
	const tbody = document.getElementById('wfm-tasks-tbody');
	if (!tbody) return;

	const statusFilter = document.getElementById('wfm-tasks-filter-status')?.value || 'all';
	const tasks = window.PrinceDemoEngine.getTasks({ status: statusFilter });

	if (tasks.length === 0) {
		tbody.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-slate-400">No se encontraron tareas registradas</td></tr>';
		return;
	}

	tbody.innerHTML = tasks.map(t => {
		const prioColors = {
			urgent: 'bg-red-100 text-red-700 dark:bg-red-950/80 dark:text-red-400',
			high: 'bg-amber-100 text-amber-700 dark:bg-amber-950/80 dark:text-amber-400',
			normal: 'bg-blue-100 text-blue-700 dark:bg-blue-950/80 dark:text-blue-400',
			low: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
		};
		const statusColors = {
			completed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300',
			in_progress: 'bg-sky-100 text-sky-700 dark:bg-sky-950/80 dark:text-sky-300',
			pending: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
		};
		const statusLabels = {
			completed: '✓ Completada',
			in_progress: '⏳ En curso',
			pending: '⚪ Pendiente'
		};

		return `
			<tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
				<td class="p-3">
					<span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ${prioColors[t.priority] || prioColors.normal}">${t.priority}</span>
				</td>
				<td class="p-3">
					<div class="font-bold text-slate-900 dark:text-white">${t.title}</div>
					<div class="text-[11px] text-slate-500 mt-0.5">${t.description}</div>
					${t.notes ? `<div class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5 font-medium">Nota: ${t.notes}</div>` : ''}
					${t.evidence_url ? `<a href="${t.evidence_url}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-emerald-600 font-semibold underline mt-1">📷 Ver Evidencia</a>` : ''}
				</td>
				<td class="p-3">
					<div class="font-medium text-slate-800 dark:text-slate-200">${t.employee_name}</div>
					<div class="text-[10px] text-slate-400">Supervisor: ${t.supervisor_name}</div>
				</td>
				<td class="p-3 text-slate-600 dark:text-slate-300">
					${t.establishment_name}
				</td>
				<td class="p-3 text-center">
					<span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${statusColors[t.status] || statusColors.pending}">
						${statusLabels[t.status] || t.status}
					</span>
				</td>
				<td class="p-3 text-right whitespace-nowrap">
					${t.status !== 'completed' ? `
						<button onclick="markTaskDone(${t.id})" class="px-2.5 py-1 text-[11px] font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-md transition-colors mr-1">
							Completar
						</button>
					` : `
						<span class="text-emerald-600 text-[11px] font-bold mr-1">Verificado</span>
					`}
					<button onclick="deleteTask(${t.id})" class="p-1 text-slate-400 hover:text-red-600 transition-colors" title="Eliminar tarea">
						✕
					</button>
				</td>
			</tr>
		`;
	}).join('');
	if (typeof lucide !== 'undefined') lucide.createIcons();
}

function renderWfmInventoryTable() {
	if (!window.PrinceDemoEngine) return;
	const tbody = document.getElementById('wfm-inventory-tbody');
	if (!tbody) return;

	const items = window.PrinceDemoEngine.getInventory();
	tbody.innerHTML = items.map(p => {
		const isLowShelf = p.shelf_stock < 15;
		const shelfPct = Math.min(100, Math.round((p.shelf_stock / p.shelf_capacity) * 100));

		return `
			<tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
				<td class="p-3 font-mono font-bold text-brand-700 dark:text-brand-400 text-xs">
					${p.upc}
				</td>
				<td class="p-3">
					<div class="font-bold text-slate-900 dark:text-white">${p.name}</div>
					<div class="text-[10px] text-slate-400 font-mono">SKU: ${p.sku} • Cat: ${p.category}</div>
				</td>
				<td class="p-3 font-mono text-xs">
					<span class="font-semibold text-slate-700 dark:text-slate-300">${p.lot}</span>
					<div class="text-[10px] text-slate-400">Exp: ${p.expiration_date}</div>
				</td>
				<td class="p-3 text-center">
					<span class="font-bold text-slate-800 dark:text-slate-200 text-sm">${p.warehouse_stock}</span>
					<span class="text-[10px] text-slate-400 block">en bodega</span>
				</td>
				<td class="p-3 text-center">
					<div class="flex items-center justify-center gap-1.5">
						<span class="font-bold ${isLowShelf ? 'text-amber-600' : 'text-emerald-600'} text-sm">${p.shelf_stock}</span>
						<span class="text-xs text-slate-400">/ ${p.shelf_capacity}</span>
					</div>
					<div class="w-16 bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full mx-auto mt-1 overflow-hidden">
						<div class="h-full ${isLowShelf ? 'bg-amber-500' : 'bg-emerald-500'}" style="width: ${shelfPct}%"></div>
					</div>
				</td>
				<td class="p-3 text-center text-slate-500">
					${shelfPct}%
				</td>
				<td class="p-3 text-right">
					<button onclick="quickRestockShelf(${p.id})" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900 text-emerald-700 dark:text-emerald-300 transition-colors" title="Trasladar 10 unidades de bodega a góndola">
						+10 a góndola
					</button>
				</td>
			</tr>
		`;
	}).join('');
	if (typeof lucide !== 'undefined') lucide.createIcons();
}

function markTaskDone(taskId) {
	if (window.PrinceDemoEngine) {
		window.PrinceDemoEngine.updateTaskStatus(taskId, 'completed', 'Completada desde la consola central');
		renderWfmTasksTable();
	}
}

function deleteTask(taskId) {
	if (confirm('¿Eliminar esta tarea operativa?')) {
		if (window.PrinceDemoEngine) {
			window.PrinceDemoEngine.deleteTask(taskId);
			renderWfmTasksTable();
		}
	}
}

function quickRestockShelf(productId) {
	if (window.PrinceDemoEngine) {
		const res = window.PrinceDemoEngine.restockShelf(productId, 10);
		if (res.success) {
			renderWfmInventoryTable();
		} else {
			alert(res.error || 'Error al reponer');
		}
	}
}

function openCreateTaskModal() {
	const title = prompt('Título de la tarea:');
	if (!title) return;
	const desc = prompt('Descripción operativa:');
	const empName = prompt('Nombre o Rol del colaborador asignado:', 'Rosa María Huamán');

	if (window.PrinceDemoEngine) {
		window.PrinceDemoEngine.createTask({
			title,
			description: desc || '',
			employee_id: 2,
			employee_name: empName,
			priority: 'high',
			establishment_id: 1,
			establishment_name: 'Prince Distributors — Hallandale HQ'
		});
		renderWfmTasksTable();
	}
}
</script>
