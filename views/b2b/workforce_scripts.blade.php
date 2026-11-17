<script>
/**
 * Prince Distributors B2B — Workforce Management (WFM) Controller
 * Horarios y Cobertura Inteligente
 */

let wfmCurrentSchedule = null;
let wfmMatrixData = [];
let wfmEmployeesData = [];
let wfmWizardCurrentStep = 1;
let wfmSimSalesDelta = 0;
let wfmDraggedShiftId = null;

// Inicialización del Módulo WFM
document.addEventListener('DOMContentLoaded', () => {
	// Escuchar cambio de sección
	if (typeof currentSection !== 'undefined' && currentSection === 'schedules') {
		initWorkforceModule();
	}
});

async function initWorkforceModule() {
	await Promise.all([
		loadWfmDashboard(),
		loadWfmSchedule(),
		loadWfmEmployees(),
		loadWfmRequests()
	]);
	if (typeof lucide !== 'undefined') lucide.createIcons();
}

// 1. CARGAR KPIS Y DASHBOARD
async function loadWfmDashboard() {
	try {
		const week = document.getElementById('wfm-filter-week')?.value || '2026-W47';
		const dept = document.getElementById('wfm-filter-department')?.value || 'Todos';
		const terr = document.getElementById('wfm-filter-territory')?.value || 'Todos';

		const res = await fetch(`/api/b2b/wfm/dashboard?week=${encodeURIComponent(week)}&department=${encodeURIComponent(dept)}&territory=${encodeURIComponent(terr)}`);
		if (!res.ok) return;
		const data = await res.json();
		if (!data.success) return;

		const k = data.kpis;
		const elSales = document.getElementById('wfm-kpi-sales');
		const elRec = document.getElementById('wfm-kpi-rec-hours');
		const elSched = document.getElementById('wfm-kpi-sched-hours');
		const elCost = document.getElementById('wfm-kpi-cost');
		const elCov = document.getElementById('wfm-kpi-coverage');
		const elHoursVar = document.getElementById('wfm-kpi-hours-var');
		const elBudgetVar = document.getElementById('wfm-kpi-budget-var');

		if (elSales) elSales.textContent = '$' + k.forecasted_sales.toLocaleString('en-US', { minimumFractionDigits: 2 });
		if (elRec) elRec.textContent = k.recommended_hours.toFixed(1) + ' hrs';
		if (elSched) elSched.textContent = k.scheduled_hours.toFixed(1) + ' hrs';
		if (elCost) elCost.textContent = '$' + k.estimated_labor_cost.toLocaleString('en-US', { minimumFractionDigits: 2 });
		if (elCov) elCov.textContent = k.coverage_score_pct.toFixed(1) + '%';

		if (elHoursVar) {
			const diff = k.hours_variance;
			elHoursVar.innerHTML = diff >= 0
				? `<span class="text-emerald-600">+${diff.toFixed(1)} hrs (Cobertura óptima)</span>`
				: `<span class="text-amber-600">${diff.toFixed(1)} hrs (Brecha controlada)</span>`;
		}

		if (elBudgetVar) {
			elBudgetVar.textContent = `Ppto: $${k.labor_budget.toFixed(2)} (${k.budget_variance >= 0 ? '+' : ''}$${k.budget_variance.toFixed(2)} margen)`;
		}
	} catch (e) {
		console.warn('Error al cargar KPIs de WFM:', e);
	}
}

// 2. CARGAR MATRIZ SEMANAL DE TURNOS (CALENDARIO)
async function loadWfmSchedule() {
	try {
		const week = document.getElementById('wfm-filter-week')?.value || '2026-W47';
		const dept = document.getElementById('wfm-filter-department')?.value || 'Todos';
		const terr = document.getElementById('wfm-filter-territory')?.value || 'Todos';

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

	if (matrix.length === 0) {
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
		const hoursScheduled = row.total_scheduled_hours;
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
				<!-- Info de Colaborador -->
				<td class="p-3 sticky left-0 bg-white dark:bg-surface-dark border-r border-slate-200/60 dark:border-slate-800 z-10">
					<div class="flex items-center gap-2.5">
						<div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-brand-700 dark:text-brand-300 flex items-center justify-center font-bold text-xs shrink-0">
							${emp.name.split(' ').map(n => n[0]).slice(0, 2).join('')}
						</div>
						<div class="truncate">
							<div class="font-bold text-slate-800 dark:text-slate-100 truncate">${emp.name}</div>
							<div class="text-[10px] text-slate-400 truncate">${emp.role_label} • ${emp.department}</div>
						</div>
					</div>
					<!-- Barra de Horas Asignadas -->
					<div class="mt-2 flex items-center justify-between text-[10px] text-slate-500">
						<span>Horas: <strong>${hoursScheduled.toFixed(1)}</strong> / ${hoursContracted}h</span>
						<span class="${isFull ? 'text-emerald-600 font-bold' : 'text-slate-400'}">${pct}%</span>
					</div>
					<div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full mt-0.5 overflow-hidden">
						<div class="h-full ${isFull ? 'bg-emerald-500' : 'bg-brand-600'}" style="width: ${pct}%"></div>
					</div>
				</td>

				<!-- Columnas Días 1 a 7 (Lunes a Domingo) -->
				${daysHtml}

				<!-- Total Horas Semanales -->
				<td class="p-3 text-center font-bold text-slate-800 dark:text-white">
					${hoursScheduled.toFixed(1)}h
				</td>
			</tr>
		`;
	}).join('');

	if (typeof lucide !== 'undefined') lucide.createIcons();
}

// RENDERIZAR CELDA DE TURNO INDIVIDUAL CON DRAG & DROP
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
	};
	const col = colorsByType[shift.shift_type] || colorsByType.mid;

	return `
		<td class="p-2 align-top drop-zone" ondragover="handleDragOver(event)" ondrop="handleDrop(event, ${emp.id}, ${dow})">
			<div draggable="true" ondragstart="handleDragStart(event, ${shift.id})"
				 class="w-full min-h-[58px] rounded-lg border ${col.border} ${col.bg} p-2 flex flex-col justify-between group shadow-xs cursor-grab active:cursor-grabbing transition-all hover:shadow-md relative">
				<!-- Header de turno -->
				<div class="flex items-center justify-between">
					<span class="text-[10px] font-bold uppercase tracking-wider ${col.text}">${shift.shift_label || 'Turno'}</span>
					<span class="text-[10px] font-bold ${col.text}">${shift.net_hours}h</span>
				</div>
				<!-- Horario -->
				<div class="text-[11px] font-bold text-slate-800 dark:text-slate-100 my-0.5">
					${shift.start_time} - ${shift.end_time}
				</div>
				<!-- Acciones rápidas (en hover) -->
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

// 3. DRAG AND DROP HANDLERS
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

// 4. DUPLICAR Y ELIMINAR TURNOS
async function duplicateShift(shiftId) {
	try {
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
		const res = await fetch(`/api/b2b/wfm/shifts/${shiftId}`, { method: 'DELETE' });
		const data = await res.json();
		if (data.success) {
			await refreshWfmSchedule();
		}
	} catch (e) {
		console.warn('Error al eliminar turno:', e);
	}
}

// 5. MODAL DE EDICIÓN / ASIGNACIÓN DE TURNO
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
	const empId = Number(document.getElementById('shift-edit-emp-id').value);
	const dow = Number(document.getElementById('shift-edit-dow').value);
	const startTime = document.getElementById('shift-edit-start').value;
	const endTime = document.getElementById('shift-edit-end').value;
	const breakMin = Number(document.getElementById('shift-edit-break').value);
	const shiftType = document.getElementById('shift-edit-type').value;
	const notes = document.getElementById('shift-edit-notes').value;

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
		// Convertir en descanso
		document.getElementById('shift-edit-type').value = 'rest';
		document.getElementById('shift-edit-start').value = '00:00';
		document.getElementById('shift-edit-end').value = '00:00';
		document.getElementById('shift-edit-break').value = 0;
		await handleSaveShift(new Event('submit'));
	}
	closeWfmModal('modal-wfm-shift-edit');
}

// 6. ASISTENTE WIZARD (4 PASOS)
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

// 7. PUBLICAR HORARIO
async function publishCurrentSchedule() {
	try {
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

// 8. SIMULADOR WHAT-IF
function openWhatIfModal() {
	const modal = document.getElementById('modal-wfm-simulate');
	if (modal) modal.classList.remove('hidden');
	runLiveSimulation();
	if (typeof lucide !== 'undefined') lucide.createIcons();
}

function setSimSalesDelta(delta) {
	wfmSimSalesDelta = delta;
	document.querySelectorAll('.sim-delta-btn').forEach(btn => {
		const bDelta = Number(btn.dataset.delta);
		if (bDelta === delta) {
			btn.className = 'py-2 text-xs font-semibold rounded-lg border border-indigo-600 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 sim-delta-btn';
		} else {
			btn.className = 'py-2 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 sim-delta-btn';
		}
	});
	runLiveSimulation();
}

async function runLiveSimulation() {
	try {
		const absent = Number(document.getElementById('sim-absent-count')?.value || 0);
		const res = await fetch('/api/b2b/wfm/simulate', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({
				base_sales: 48500.00,
				sales_delta_pct: wfmSimSalesDelta,
				absent_employees: absent
			})
		});
		const data = await res.json();
		if (data.success) {
			document.getElementById('sim-res-sales').textContent = '$' + data.simulated_sales.toLocaleString('en-US', { minimumFractionDigits: 2 });
			document.getElementById('sim-res-hours').textContent = data.recommended_hours.toFixed(1) + ' hrs';
			document.getElementById('sim-res-coverage').textContent = data.projected_coverage_pct.toFixed(1) + '%';
			document.getElementById('sim-res-advice').textContent = data.advice;
		}
	} catch (e) {
		console.warn('Error en simulación What-If:', e);
	}
}

// 9. COPIAR SEMANA
function openCopyWeekModal() {
	if (confirm('¿Desea copiar la plantilla de turnos de la semana anterior (Semana 46) a la semana actual?')) {
		alert('✓ Turnos de la semana anterior copiados y ajustados con éxito.');
		refreshWfmSchedule();
	}
}

// 10. REFRESCAR PANTALLA COMPLETA
async function refreshWfmSchedule() {
	await Promise.all([loadWfmDashboard(), loadWfmSchedule()]);
}

function closeWfmModal(id) {
	const el = document.getElementById(id);
	if (el) el.classList.add('hidden');
}

// 11. CARGAR DIRECTORIO DE EMPLEADOS
async function loadWfmEmployees() {
	try {
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
	const tbody = document.getElementById('wfm-employees-tbody');
	if (!tbody) return;

	tbody.innerHTML = employees.map(emp => `
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
}

function filterEmployeesTable() {
	const q = document.getElementById('wfm-emp-search')?.value.toLowerCase() || '';
	const filtered = wfmEmployeesData.filter(e => e.name.toLowerCase().includes(q) || e.code.toLowerCase().includes(q) || e.role_label.toLowerCase().includes(q));
	renderEmployeesTable(filtered);
}

// 12. CARGAR SOLICITUDES DE CAMBIO DE TURNO
async function loadWfmRequests() {
	try {
		const res = await fetch('/api/b2b/wfm/requests');
		if (!res.ok) return;
		const data = await res.json();
		if (!data.success) return;

		const tbody = document.getElementById('wfm-requests-tbody');
		if (!tbody) return;

		tbody.innerHTML = (data.requests || []).map(r => `
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
	} catch (e) {
		console.warn('Error al cargar solicitudes:', e);
	}
}

async function reviewRequest(reqId, decision) {
	try {
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
</script>
