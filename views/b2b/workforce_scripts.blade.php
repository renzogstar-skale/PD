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
let wfmActiveTab = 'summary';
let wfmActiveDemoRole = 'admin';

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
	await Promise.all([
		loadWfmDashboard(),
		loadWfmSchedule(),
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

// 1. CARGAR KPIS Y DASHBOARD (CONECTADO AL MOTOR CENTRAL)
async function loadWfmDashboard() {
	try {
		const week = document.getElementById('wfm-filter-week')?.value || '2026-W47';
		const dept = document.getElementById('wfm-filter-department')?.value || 'Todos';
		const terr = document.getElementById('wfm-filter-territory')?.value || 'Todos';
		const estab = document.getElementById('wfm-filter-establishment')?.value || 'Todos';
		const sup = document.getElementById('wfm-filter-supervisor')?.value || 'Todos';

		let k = null;
		if (window.PrinceDemoEngine) {
			k = window.PrinceDemoEngine.getKpis(week, estab, terr, dept, sup);
		} else {
			const res = await fetch(`/api/b2b/wfm/dashboard?week=${encodeURIComponent(week)}&department=${encodeURIComponent(dept)}&territory=${encodeURIComponent(terr)}`);
			if (res.ok) {
				const data = await res.json();
				if (data.success) k = data.kpis;
			}
		}

		if (!k) return;

		const elSales = document.getElementById('wfm-kpi-sales');
		const elRec = document.getElementById('wfm-kpi-rec-hours');
		const elSched = document.getElementById('wfm-kpi-sched-hours');
		const elCost = document.getElementById('wfm-kpi-cost');
		const elCov = document.getElementById('wfm-kpi-coverage');
		const elHoursVar = document.getElementById('wfm-kpi-hours-var');
		const elBudgetVar = document.getElementById('wfm-kpi-budget-var');
		const badge = document.getElementById('wfm-status-badge');

		if (elSales) elSales.textContent = '$' + k.forecasted_sales.toLocaleString('en-US', { minimumFractionDigits: 2 });
		if (elRec) elRec.textContent = k.recommended_hours.toFixed(1) + ' hrs';
		if (elSched) elSched.textContent = k.scheduled_hours.toFixed(1) + ' hrs';
		if (elCost) elCost.textContent = '$' + k.estimated_labor_cost.toLocaleString('en-US', { minimumFractionDigits: 2 });
		if (elCov) elCov.textContent = k.coverage_score_pct.toFixed(1) + '%';

		const weekNum = week.replace(/.*W/, '');

		if (elHoursVar) {
			const diff = k.hours_variance;
			elHoursVar.innerHTML = `<span class="px-1.5 py-0.2 ${diff >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'} font-semibold rounded text-[10px]">Semana ${weekNum}</span><span>${diff >= 0 ? '+' : ''}${diff.toFixed(1)} hrs brecha</span>`;
		}

		if (elBudgetVar) {
			elBudgetVar.innerHTML = `<span class="px-1.5 py-0.2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold rounded text-[10px]">Semana ${weekNum}</span><span>Ppto: $${k.labor_budget.toFixed(2)} (${k.budget_variance >= 0 ? '+' : ''}$${k.budget_variance.toFixed(2)} margen)</span>`;
		}

		if (badge && window.PrinceDemoEngine) {
			const sched = window.PrinceDemoEngine.getSchedule(week);
			const label = sched.status === 'published' ? `Publicado v${sched.version || 1}` : (sched.status === 'in_review' ? 'En Revisión' : 'Borrador');
			badge.textContent = label;
			badge.className = sched.status === 'published' 
				? 'px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300'
				: 'px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300';
		}

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

// 3. CARGAR MATRIZ SEMANAL DE TURNOS (CALENDARIO)
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

// RENDERIZAR TABLA DE CALENDARIO INTERACTIVO
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

	if (typeof lucide !== 'undefined') lucide.createIcons();
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
					<button onclick="event.stopPropagation(); deleteShift(${shift.id})" title="Quitar turno" class="p-0.5 hover:bg-rose-100 dark:hover:bg-rose-900/40 rounded text-rose-600">
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
function openShiftModal(shiftId, empId, dayOfWeek) {
	const emp = wfmEmployeesData.find(e => e.id === empId) || wfmEmployeesData[0] || { id: 101, name: 'Colaborador' };
	openShiftEditModal(emp.id, emp.name, dayOfWeek || 1, null);
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
	if (!confirm('¿Confirma que desea eliminar este turno?')) return;
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

function saveScheduleDraft() {
	if (window.PrinceDemoEngine) {
		const week = document.getElementById('wfm-filter-week')?.value || '2026-W47';
		const sched = window.PrinceDemoEngine.getSchedule(week);
		sched.status = 'draft';
		window.PrinceDemoEngine.saveState();
		const badge = document.getElementById('wfm-status-badge');
		if (badge) {
			badge.textContent = 'Borrador Guardado';
			badge.className = 'px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-700';
		}
	}
	alert('✓ Borrador de programación semanal guardado exitosamente. Los turnos permanecen en estado borrador y pueden continuar editándose.');
}

// 6. MODAL DE EDICIÓN / ASIGNACIÓN DE TURNO
function openShiftEditModal(empId, empName, dow, shift) {
	const dowNames = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
	
	document.getElementById('shift-edit-id').value = shift ? shift.id : '';
	document.getElementById('shift-edit-emp-id').value = empId;
	document.getElementById('shift-edit-dow').value = dow;
	document.getElementById('shift-modal-emp-name').textContent = empName;
	document.getElementById('shift-modal-dow-name').textContent = dowNames[dow] || `Día ${dow}`;

	if (shift) {
		document.getElementById('wfm-shift-modal-title').textContent = 'Editar Turno de Trabajo';
		document.getElementById('shift-edit-start').value = shift.start_time;
		document.getElementById('shift-edit-end').value = shift.end_time;
		document.getElementById('shift-edit-break').value = shift.break_minutes;
		document.getElementById('shift-edit-type').value = shift.shift_type;
		document.getElementById('shift-edit-notes').value = shift.task_notes || '';
	} else {
		document.getElementById('wfm-shift-modal-title').textContent = 'Asignar Turno de Trabajo';
		document.getElementById('shift-edit-start').value = '08:00';
		document.getElementById('shift-edit-end').value = '16:30';
		document.getElementById('shift-edit-break').value = 30;
		document.getElementById('shift-edit-type').value = 'mid';
		document.getElementById('shift-edit-notes').value = '';
	}

	const modal = document.getElementById('modal-wfm-shift-edit');
	if (modal) modal.classList.remove('hidden');
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
	} else if (val === 'REST') {
		document.getElementById('shift-edit-start').value = '00:00';
		document.getElementById('shift-edit-end').value = '00:00';
		document.getElementById('shift-edit-break').value = 0;
		document.getElementById('shift-edit-type').value = 'rest';
	}
}

async function handleSaveShift(event) {
	event.preventDefault();
	const shiftId = document.getElementById('shift-edit-id').value;
	const empId = Number(document.getElementById('shift-edit-emp-id').value);
	const dow = Number(document.getElementById('shift-edit-dow').value);
	const startTime = document.getElementById('shift-edit-start').value;
	const endTime = document.getElementById('shift-edit-end').value;
	const breakMin = Number(document.getElementById('shift-edit-break').value);
	const shiftType = document.getElementById('shift-edit-type').value;
	const notes = document.getElementById('shift-edit-notes').value;
	const week = document.getElementById('wfm-filter-week')?.value || '2026-W47';

	const labelMap = {
		opening: 'Apertura',
		mid: 'Intermedio',
		closing: 'Cierre',
		standard: 'Ruta Preventa',
		rest: 'Descanso',
		vacation: 'Vacaciones',
		absence: 'Ausencia'
	};

	try {
		if (window.PrinceDemoEngine) {
			if (shiftId) {
				window.PrinceDemoEngine.updateShift(Number(shiftId), {
					start_time: startTime,
					end_time: endTime,
					break_minutes: breakMin,
					shift_type: shiftType,
					shift_label: labelMap[shiftType] || 'Turno',
					task_notes: notes
				});
			} else {
				const sched = window.PrinceDemoEngine.getSchedule(week);
				window.PrinceDemoEngine.addShift({
					schedule_id: sched.id,
					employee_id: empId,
					day_of_week: dow,
					shift_date: '2026-11-16',
					start_time: startTime,
					end_time: endTime,
					break_minutes: breakMin,
					shift_type: shiftType,
					shift_label: labelMap[shiftType] || 'Turno',
					task_notes: notes
				});
			}
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
				shift_date: '2026-11-16',
				start_time: startTime,
				end_time: endTime,
				break_minutes: breakMin,
				shift_type: shiftType,
				shift_label: labelMap[shiftType] || 'Turno',
				task_notes: notes
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
	const shiftId = document.getElementById('shift-edit-id').value;
	if (shiftId) {
		await deleteShift(Number(shiftId));
	} else {
		document.getElementById('shift-edit-type').value = 'rest';
		document.getElementById('shift-edit-start').value = '00:00';
		document.getElementById('shift-edit-end').value = '00:00';
		document.getElementById('shift-edit-break').value = 0;
		await handleSaveShift(new Event('submit'));
	}
	closeWfmModal('modal-wfm-shift-edit');
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
	if (confirm('¿Confirma que desea restablecer todos los turnos, colaboradores y KPIs de la demo a sus valores originales de fábrica?')) {
		if (window.PrinceDemoEngine) {
			window.PrinceDemoEngine.resetToDefaults();
			closeWfmModal('modal-wfm-demo-settings');
			refreshWfmSchedule();
			alert('✓ Dataset de demostración restablecido exitosamente a sus valores originales.');
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
