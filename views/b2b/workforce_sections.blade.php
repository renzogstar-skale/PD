{{-- SECCIÓN 9: HORARIOS Y COBERTURA (WFM) --}}
<div id="section-schedules" class="hidden space-y-6">
	<!-- Header con Título y Acciones Principales -->
	<div class="pb-4 border-b border-slate-200/80 dark:border-slate-800 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
		<div>
			<div class="flex items-center gap-2.5">
				<div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60 flex items-center justify-center">
					<i data-lucide="calendar-clock" class="w-5 h-5"></i>
				</div>
				<div>
					<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
						<span>Horarios y Cobertura Inteligente</span>
						<span id="wfm-status-badge" class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300">Publicado v1</span>
					</h1>
					<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
						Planificación laboral optimizada según ventas, demanda B2B, productividad por rol y presupuesto.
					</p>
				</div>
			</div>
		</div>

		<!-- Botones de Acción -->
		<div class="flex flex-wrap items-center gap-2">
			<button onclick="openWhatIfModal()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 transition-colors">
				<i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
				<span>Simulador What-If</span>
			</button>
			<button onclick="openCopyWeekModal()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">
				<i data-lucide="copy" class="w-3.5 h-3.5"></i>
				<span>Copiar Semana</span>
			</button>
			<button onclick="publishCurrentSchedule()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 dark:hover:bg-slate-600 text-white transition-colors">
				<i data-lucide="send" class="w-3.5 h-3.5"></i>
				<span>Publicar Horario</span>
			</button>
			<button onclick="openWizardStep(1)" class="inline-flex items-center gap-2 px-4 py-2 text-xs sm:text-sm font-semibold rounded-lg bg-brand-700 hover:bg-brand-800 text-white shadow-xs transition-colors">
				<i data-lucide="calendar-plus" class="w-4 h-4"></i>
				<span>Crear programación</span>
			</button>
		</div>
	</div>

	<!-- Barra de Filtros Globales -->
	<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs">
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
			<!-- Filtro Semana -->
			<div>
				<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Semana</label>
				<select id="wfm-filter-week" onchange="refreshWfmSchedule()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 p-2 font-medium">
					<option value="2026-W47" selected>Semana 47: 16 Nov – 22 Nov (Actual)</option>
					<option value="2026-W48">Semana 48: 23 Nov – 29 Nov (Próxima)</option>
				</select>
			</div>

			<!-- Filtro Tienda / Establecimiento -->
			<div>
				<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Establecimiento</label>
				<select id="wfm-filter-establishment" onchange="refreshWfmSchedule()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 p-2 font-medium">
					<option value="Todos">Todos los Establecimientos</option>
					<option value="Almacén Central Norte" selected>Almacén Central Norte</option>
					<option value="Supermercados Los Andes">Supermercados Los Andes</option>
					<option value="Distribuidora San Juan">Distribuidora San Juan</option>
				</select>
			</div>

			<!-- Filtro Territorio -->
			<div>
				<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Territorio</label>
				<select id="wfm-filter-territory" onchange="refreshWfmSchedule()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 p-2 font-medium">
					<option value="Todos" selected>Todos los Territorios</option>
					<option value="Lima Norte">Lima Norte</option>
					<option value="Lima Centro">Lima Centro</option>
					<option value="Lima Sur">Lima Sur</option>
					<option value="Callao">Callao</option>
				</select>
			</div>

			<!-- Filtro Departamento / Función -->
			<div>
				<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Departamento / Función</label>
				<select id="wfm-filter-department" onchange="refreshWfmSchedule()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 p-2 font-medium">
					<option value="Todos" selected>Todos los Departamentos</option>
					<option value="Reposición & Merchandising">Reposición & Merchandising</option>
					<option value="Fuerza de Ventas B2B">Fuerza de Ventas B2B</option>
					<option value="Operaciones & Logística">Operaciones & Logística</option>
				</select>
			</div>

			<!-- Filtro Supervisor -->
			<div>
				<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Supervisor</label>
				<select id="wfm-filter-supervisor" onchange="refreshWfmSchedule()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 p-2 font-medium">
					<option value="Todos">Todos los Supervisores</option>
					<option value="Carlos Mendoza" selected>Carlos Mendoza (Dir. Ventas)</option>
				</select>
			</div>
		</div>
	</div>

	<!-- 5 INDICADORES PRINCIPALES (REQUERIMIENTO OBLIGATORIO) -->
	<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
		<!-- KPI 1: Ventas Previstas -->
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs">
			<div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs">
				<span class="font-medium">1. Ventas Previstas</span>
				<i data-lucide="trending-up" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
			</div>
			<div class="mt-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white" id="wfm-kpi-sales">
				$48,500.00
			</div>
			<div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1">
				<span class="text-emerald-600 font-semibold">+8.4%</span> vs semana anterior
			</div>
		</div>

		<!-- KPI 2: Horas Laborales Recomendadas -->
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs">
			<div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs">
				<span class="font-medium">2. Horas Recomendadas</span>
				<i data-lucide="calculator" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
			</div>
			<div class="mt-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white" id="wfm-kpi-rec-hours">
				340.0 hrs
			</div>
			<div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
				Meta: $150.00 / hora laboral
			</div>
		</div>

		<!-- KPI 3: Horas Programadas -->
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs">
			<div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs">
				<span class="font-medium">3. Horas Programadas</span>
				<i data-lucide="clock" class="w-4 h-4 text-brand-700 dark:text-brand-400"></i>
			</div>
			<div class="mt-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white" id="wfm-kpi-sched-hours">
				336.0 hrs
			</div>
			<div class="mt-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1" id="wfm-kpi-hours-var">
				<i data-lucide="check" class="w-3 h-3"></i> -4.0 hrs (Balance óptimo)
			</div>
		</div>

		<!-- KPI 4: Costo Laboral Estimado -->
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs">
			<div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs">
				<span class="font-medium">4. Costo Laboral Est.</span>
				<i data-lucide="dollar-sign" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
			</div>
			<div class="mt-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white" id="wfm-kpi-cost">
				$2,520.00
			</div>
			<div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400" id="wfm-kpi-budget-var">
				Ppto: $2,800.00 (+$280 margen)
			</div>
		</div>

		<!-- KPI 5: Cobertura de Personal -->
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs col-span-2 sm:col-span-1">
			<div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs">
				<span class="font-medium">5. Cobertura de Personal</span>
				<i data-lucide="shield-check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
			</div>
			<div class="mt-2 text-xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400" id="wfm-kpi-coverage">
				98.8%
			</div>
			<div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
				Cobertura mínima garantizada
			</div>
		</div>
	</div>

	<!-- CALENDARIO SEMANAL INTERACTIVO -->
	<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
		<!-- Cabecera de Tabla con Leyenda y Accesos de Plantillas -->
		<div class="p-4 border-b border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-900/30">
			<div>
				<h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
					<i data-lucide="calendar" class="w-4 h-4 text-brand-700"></i>
					<span>Programación de Turnos — Semana 47</span>
				</h2>
				<p class="text-xs text-slate-500 dark:text-slate-400">
					Arrastre un turno para moverlo de día/colaborador o haga clic para editarlo.
				</p>
			</div>

			<!-- Leyenda de colores de turnos -->
			<div class="flex flex-wrap items-center gap-2 text-[11px]">
				<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
					<span class="w-2 h-2 rounded-full bg-emerald-600"></span> Apertura (07:00)
				</span>
				<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
					<span class="w-2 h-2 rounded-full bg-blue-600"></span> Intermedio (09:00)
				</span>
				<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
					<span class="w-2 h-2 rounded-full bg-purple-600"></span> Cierre (13:30)
				</span>
				<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
					<span class="w-2 h-2 rounded-full bg-slate-400"></span> Descanso
				</span>
			</div>
		</div>

		<!-- Matriz Semanal (Table) -->
		<div class="overflow-x-auto">
			<table class="w-full text-left border-collapse min-w-[950px]" id="wfm-schedule-table">
				<thead>
					<tr class="bg-slate-100/70 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700 text-[11px] font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
						<th class="p-3 w-64 sticky left-0 bg-slate-100 dark:bg-slate-800 z-10">Colaborador / Rol</th>
						<th class="p-2.5 text-center w-36">Lun 16 Nov</th>
						<th class="p-2.5 text-center w-36">Mar 17 Nov</th>
						<th class="p-2.5 text-center w-36">Mié 18 Nov</th>
						<th class="p-2.5 text-center w-36">Jue 19 Nov</th>
						<th class="p-2.5 text-center w-36">Vie 20 Nov</th>
						<th class="p-2.5 text-center w-36">Sáb 21 Nov</th>
						<th class="p-2.5 text-center w-36">Dom 22 Nov</th>
						<th class="p-3 text-center w-28">Total Horas</th>
					</tr>
				</thead>
				<tbody id="wfm-schedule-tbody" class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
					<!-- Rellenado dinámicamente con JavaScript -->
				</tbody>
			</table>
		</div>
	</div>
</div>

{{-- SECCIÓN 10: EMPLEADOS --}}
<div id="section-employees" class="hidden space-y-6">
	<div class="pb-4 border-b border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
		<div class="flex items-center gap-2.5">
			<div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60 flex items-center justify-center">
				<i data-lucide="user-check" class="w-5 h-5"></i>
			</div>
			<div>
				<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
					Directorio de Personal & Dotación
				</h1>
				<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
					Colaboradores operacionales, reponedores, fuerza de ventas y personal logístico.
				</p>
			</div>
		</div>

		<div class="flex items-center gap-2">
			<input type="text" id="wfm-emp-search" oninput="filterEmployeesTable()" placeholder="Buscar colaborador o código..." class="text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 text-slate-800 dark:text-slate-200 w-64" />
		</div>
	</div>

	<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
		<div class="overflow-x-auto">
			<table class="w-full text-left border-collapse text-xs">
				<thead>
					<tr class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
						<th class="p-3">Código / Nombre</th>
						<th class="p-3">Rol & Función</th>
						<th class="p-3">Departamento</th>
						<th class="p-3">Establecimiento & Zona</th>
						<th class="p-3 text-center">Horas Contratadas</th>
						<th class="p-3 text-right">Costo Hora ($)</th>
						<th class="p-3">Supervisor</th>
						<th class="p-3 text-center">Estado</th>
					</tr>
				</thead>
				<tbody id="wfm-employees-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
					<!-- Rellenado dinámicamente -->
				</tbody>
			</table>
		</div>
	</div>
</div>

{{-- SECCIÓN 11: ASISTENCIA --}}
<div id="section-attendance" class="hidden space-y-6">
	<div class="pb-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
		<div class="flex items-center gap-2.5">
			<div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800/60 flex items-center justify-center">
				<i data-lucide="clock" class="w-5 h-5"></i>
			</div>
			<div>
				<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
					Asistencia & Puntualidad Real
				</h1>
				<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
					Comparativa de horas programadas vs trabajadas, horas productivas y control de tardanzas.
				</p>
			</div>
		</div>
	</div>

	<!-- Desglose de Horas -->
	<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 p-4">
			<div class="text-xs text-slate-500">Horas Programadas</div>
			<div class="text-xl font-bold text-slate-900 dark:text-white mt-1">336.0 hrs</div>
			<div class="text-[11px] text-slate-400 mt-0.5">Semana 47</div>
		</div>
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 p-4">
			<div class="text-xs text-slate-500">Horas Trabajadas</div>
			<div class="text-xl font-bold text-brand-700 dark:text-brand-400 mt-1">332.5 hrs</div>
			<div class="text-[11px] text-emerald-600 mt-0.5">99.0% cumplimiento</div>
		</div>
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 p-4">
			<div class="text-xs text-slate-500">Horas Productivas</div>
			<div class="text-xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">318.0 hrs</div>
			<div class="text-[11px] text-slate-400 mt-0.5">Visitas y reposición</div>
		</div>
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 p-4">
			<div class="text-xs text-slate-500">Horas Pagadas</div>
			<div class="text-xl font-bold text-slate-900 dark:text-white mt-1">336.0 hrs</div>
			<div class="text-[11px] text-slate-400 mt-0.5">Planilla semanal</div>
		</div>
	</div>

	<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-6 shadow-xs">
		<h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">Registro Diario de Marcaciones</h3>
		<p class="text-xs text-slate-500 mb-4">Registro en tiempo real de entradas, descansos y salidas de personal en puntos de venta y almacenes.</p>
		<div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-800/40 text-xs text-slate-600 dark:text-slate-300">
			✓ 100% de colaboradores marcando con geolocalización activa en establecimiento asignado. 0 incidencias críticas registradas en la jornada.
		</div>
	</div>
</div>

{{-- SECCIÓN 12: SOLICITUDES --}}
<div id="section-shift-requests" class="hidden space-y-6">
	<div class="pb-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
		<div class="flex items-center gap-2.5">
			<div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/60 flex items-center justify-center">
				<i data-lucide="clipboard-check" class="w-5 h-5"></i>
			</div>
			<div>
				<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
					Solicitudes de Personal & Permisos
				</h1>
				<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
					Aprobación de cambios de turno, solicitudes de vacaciones y permisos por horas.
				</p>
			</div>
		</div>
	</div>

	<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
		<div class="overflow-x-auto">
			<table class="w-full text-left border-collapse text-xs">
				<thead>
					<tr class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
						<th class="p-3">Colaborador Solicitante</th>
						<th class="p-3">Tipo de Solicitud</th>
						<th class="p-3">Fecha del Turno</th>
						<th class="p-3">Motivo / Reemplazo</th>
						<th class="p-3 text-center">Estado</th>
						<th class="p-3 text-right">Acción</th>
					</tr>
				</thead>
				<tbody id="wfm-requests-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
					<!-- Rellenado dinámicamente -->
				</tbody>
			</table>
		</div>
	</div>
</div>
