{{-- SECCIÓN 9: HORARIOS Y COBERTURA (WFM) INTEGRAL --}}
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
						Muestra los turnos de la semana frente a las horas que exige la demanda prevista, el costo laboral y el presupuesto. Sirve para detectar turnos sin cubrir o exceso de horas antes de publicar la planificación.
					</p>
				</div>
			</div>
		</div>

		<!-- Botones de Acción Globales -->
		<div class="flex flex-wrap items-center gap-2">
			<button onclick="openCopyWeekModal()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors" title="Copiar turnos de la semana anterior">
				<i data-lucide="copy" class="w-3.5 h-3.5"></i>
				<span>Copiar semana anterior</span>
			</button>
			<button onclick="saveScheduleDraft()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors" title="Guardar borrador de la programación">
				<i data-lucide="save" class="w-3.5 h-3.5"></i>
				<span>Guardar borrador</span>
			</button>
			<button onclick="publishCurrentSchedule()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 dark:hover:bg-slate-600 text-white transition-colors" title="Validar reglas laborales y publicar">
				<i data-lucide="send" class="w-3.5 h-3.5"></i>
				<span>Publicar programación</span>
			</button>
			<button onclick="openWizardStep(1)" class="inline-flex items-center gap-2 px-4 py-2 text-xs sm:text-sm font-semibold rounded-lg bg-brand-700 hover:bg-brand-800 text-white shadow-xs transition-colors" title="Asistente de planificación en 4 pasos">
				<i data-lucide="calendar-plus" class="w-4 h-4"></i>
				<span>Crear programación</span>
			</button>
		</div>
	</div>

	<div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-slate-50 dark:from-emerald-950/40 dark:via-teal-950/30 dark:to-slate-900 border border-emerald-200/80 dark:border-emerald-800/80 rounded-xl p-3.5 shadow-xs flex flex-col md:flex-row md:items-center md:justify-end gap-3">
		<div class="flex items-center flex-wrap gap-2">
			<!-- Selector de Rol Activo -->
			<div class="flex items-center gap-1.5 bg-white dark:bg-slate-800 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-2xs">
				<label for="demo-active-role-select" class="text-[11px] font-bold text-slate-500 dark:text-slate-400 flex items-center gap-1">
					<i data-lucide="user-check" class="w-3.5 h-3.5 text-emerald-600"></i>
					<span class="hidden sm:inline">Rol:</span>
				</label>
				<select id="demo-active-role-select" onchange="handleDemoRoleSwitch(this.value)" class="text-xs font-semibold bg-transparent border-none text-slate-800 dark:text-slate-200 focus:ring-0 p-0 cursor-pointer">
					<option value="admin">Administrador Global (Gonzalo Núñez)</option>
					<option value="supervisor">Supervisor de Ventas (Carlos Mendoza)</option>
					<option value="sales_rep">Vendedor Comercial (Jorge Quispe)</option>
					<option value="merchandiser">Reponedor / Merchandiser (Rosa Huamán)</option>
				</select>
			</div>

			<!-- Botón Optimizar Semana -->
			<button onclick="openOptimizationModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white shadow-2xs transition-colors" title="Ajustar automáticamente turnos y cobertura de la semana según la demanda">
				<i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
				<span>Optimizar semana</span>
			</button>

			<!-- Botón Centro de Notificaciones con Badge -->
			<button onclick="openDemoNotificationsModal()" class="relative inline-flex items-center justify-center p-2 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 transition-colors" title="Centro de Notificaciones Internas">
				<i data-lucide="bell" class="w-4 h-4"></i>
				<span id="demo-unread-badge" class="absolute -top-1 -right-1 px-1.5 py-0.2 min-w-4 text-[10px] font-bold rounded-full bg-red-600 text-white flex items-center justify-center">3</span>
			</button>

			<!-- Botón Ajustes de Demostración (Persistencia / Exportar / Resetear) -->
			<button onclick="openDemoSettingsModal()" class="inline-flex items-center justify-center p-2 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 transition-colors" title="Ajustes de persistencia y exportación de datos">
				<i data-lucide="settings-2" class="w-4 h-4"></i>
			</button>
		</div>
	</div>

	<!-- NAVEGACIÓN INTERNA EN 6 PESTAÑAS (DISEÑO NATIVO PRINCE DISTRIBUTORS) -->
	<div class="border-b border-slate-200 dark:border-slate-800 flex items-center gap-1 sm:gap-2 overflow-x-auto text-xs font-semibold">
		<button id="wfm-navtab-summary" onclick="switchWfmTab('summary')" class="wfm-tab-btn px-3 py-2.5 border-b-2 border-brand-700 text-brand-700 dark:text-brand-400 flex items-center gap-2 whitespace-nowrap">
			<i data-lucide="layout-dashboard" class="w-4 h-4"></i>
			<span>1. Resumen</span>
		</button>
		<button id="wfm-navtab-schedule" onclick="switchWfmTab('schedule')" class="wfm-tab-btn px-3 py-2.5 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white flex items-center gap-2 whitespace-nowrap">
			<i data-lucide="calendar" class="w-4 h-4"></i>
			<span>2. Programación semanal</span>
		</button>
		<button id="wfm-navtab-employees" onclick="switchWfmTab('employees')" class="wfm-tab-btn px-3 py-2.5 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white flex items-center gap-2 whitespace-nowrap">
			<i data-lucide="users" class="w-4 h-4"></i>
			<span>3. Empleados</span>
		</button>
		<button id="wfm-navtab-productivity" onclick="switchWfmTab('productivity')" class="wfm-tab-btn px-3 py-2.5 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white flex items-center gap-2 whitespace-nowrap">
			<i data-lucide="zap" class="w-4 h-4"></i>
			<span>4. Productividad</span>
		</button>
		<button id="wfm-navtab-simulator" onclick="switchWfmTab('simulator')" class="wfm-tab-btn px-3 py-2.5 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white flex items-center gap-2 whitespace-nowrap">
			<i data-lucide="sparkles" class="w-4 h-4"></i>
			<span>5. Simulador</span>
		</button>
		<button id="wfm-navtab-settings" onclick="switchWfmTab('settings')" class="wfm-tab-btn px-3 py-2.5 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white flex items-center gap-2 whitespace-nowrap">
			<i data-lucide="sliders" class="w-4 h-4"></i>
			<span>6. Configuración</span>
		</button>
		<button id="wfm-navtab-tasks" onclick="switchWfmTab('tasks')" class="wfm-tab-btn px-3 py-2.5 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white flex items-center gap-2 whitespace-nowrap">
			<i data-lucide="check-square" class="w-4 h-4"></i>
			<span>7. Tareas & Operaciones</span>
		</button>
	</div>

	<!-- ========================================================
	     PESTAÑA 1: RESUMEN (8 KPIS + 3 GRÁFICOS + RECOMENDACIONES)
	     ======================================================== -->
	<div id="wfm-tab-content-summary" class="space-y-6">
		<!-- 8 INDICADORES PRINCIPALES DE HORARIOS Y COBERTURA -->
		<div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-3 sm:gap-4">
			<!-- KPI 1: Horas Requeridas -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-3.5 shadow-xs flex flex-col justify-between">
				<div>
					<div class="flex items-center justify-between text-slate-500 text-xs">
						<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="required_hours">Horas requeridas</span>
						<i data-lucide="calculator" class="w-3.5 h-3.5 text-indigo-600"></i>
					</div>
					<div class="mt-2 text-xl font-bold font-mono tracking-tight text-slate-900 dark:text-white" id="wfm-kpi-rec-hours">
						340.0 hrs
					</div>
					<div class="mt-1 text-[10px] text-indigo-600 font-semibold">
						<span>Demanda óptima</span>
					</div>
				</div>
				<div class="mt-2 pt-1 border-t border-slate-100 dark:border-slate-800/80 text-[9px] text-slate-400 leading-tight">
					Cálculo por ventas
				</div>
			</div>

			<!-- KPI 2: Horas Programadas -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-3.5 shadow-xs flex flex-col justify-between">
				<div>
					<div class="flex items-center justify-between text-slate-500 text-xs">
						<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="scheduled_hours">Horas programadas</span>
						<i data-lucide="clock" class="w-3.5 h-3.5 text-brand-700"></i>
					</div>
					<div class="mt-2 text-xl font-bold font-mono tracking-tight text-slate-900 dark:text-white" id="wfm-kpi-sched-hours">
						236.0 hrs
					</div>
					<div class="mt-1 text-[10px] text-amber-600 font-semibold" id="wfm-kpi-hours-var">
						<span>Turnos asignados</span>
					</div>
				</div>
				<div class="mt-2 pt-1 border-t border-slate-100 dark:border-slate-800/80 text-[9px] text-slate-400 leading-tight">
					Matriz activa
				</div>
			</div>

			<!-- KPI 3: Cobertura de Personal % -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-3.5 shadow-xs flex flex-col justify-between">
				<div>
					<div class="flex items-center justify-between text-slate-500 text-xs">
						<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="staff_coverage">Cobertura de personal</span>
						<i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
					</div>
					<div class="mt-2 text-xl font-bold font-mono tracking-tight text-emerald-600 dark:text-emerald-400" id="wfm-kpi-coverage">
						69.4%
					</div>
					<div class="mt-1 text-[10px] text-emerald-600 font-semibold">
						<span>Nivel servicio</span>
					</div>
				</div>
				<div class="mt-2 pt-1 border-t border-slate-100 dark:border-slate-800/80 text-[9px] text-slate-400 leading-tight">
					Prog. vs demanda
				</div>
			</div>

			<!-- KPI 4: Costo Laboral Estimado $ -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-3.5 shadow-xs flex flex-col justify-between">
				<div>
					<div class="flex items-center justify-between text-slate-500 text-xs">
						<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="labor_cost">Costo laboral estimado</span>
						<i data-lucide="dollar-sign" class="w-3.5 h-3.5 text-amber-600"></i>
					</div>
					<div class="mt-2 text-xl font-bold font-mono tracking-tight text-slate-900 dark:text-white" id="wfm-kpi-cost">
						$1,884.00
					</div>
					<div class="mt-1 text-[10px] text-slate-400">
						<span>Nómina semanal</span>
					</div>
				</div>
				<div class="mt-2 pt-1 border-t border-slate-100 dark:border-slate-800/80 text-[9px] text-slate-400 leading-tight">
					Tarifa contractual
				</div>
			</div>

			<!-- KPI 5: Productividad Laboral $/h -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-3.5 shadow-xs flex flex-col justify-between">
				<div>
					<div class="flex items-center justify-between text-slate-500 text-xs">
						<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="labor_productivity">Productividad laboral</span>
						<i data-lucide="zap" class="w-3.5 h-3.5 text-blue-600"></i>
					</div>
					<div class="mt-2 text-xl font-bold font-mono tracking-tight text-blue-600 dark:text-blue-400" id="wfm-kpi-productivity">
						$205.51/h
					</div>
					<div class="mt-1 text-[10px] text-blue-600 font-semibold">
						<span>Venta / hora</span>
					</div>
				</div>
				<div class="mt-2 pt-1 border-t border-slate-100 dark:border-slate-800/80 text-[9px] text-slate-400 leading-tight">
					Meta: $150/h
				</div>
			</div>

			<!-- KPI 6: Déficit de Horas -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-3.5 shadow-xs flex flex-col justify-between">
				<div>
					<div class="flex items-center justify-between text-slate-500 text-xs">
						<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="hours_deficit">Horas sin cubrir</span>
						<i data-lucide="alert-circle" class="w-3.5 h-3.5 text-rose-600"></i>
					</div>
					<div class="mt-2 text-xl font-bold font-mono tracking-tight text-amber-600 dark:text-amber-400" id="wfm-kpi-deficit">
						104.0 hrs
					</div>
					<div class="mt-1 text-[10px] text-amber-600 font-semibold">
						<span>Brecha a cubrir</span>
					</div>
				</div>
				<div class="mt-2 pt-1 border-t border-slate-100 dark:border-slate-800/80 text-[9px] text-slate-400 leading-tight">
					Turnos faltantes
				</div>
			</div>

			<!-- KPI 7: Horas Extras Proyectadas -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-3.5 shadow-xs flex flex-col justify-between">
				<div>
					<div class="flex items-center justify-between text-slate-500 text-xs">
						<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="overtime_hours">Horas extras</span>
						<i data-lucide="calendar-plus" class="w-3.5 h-3.5 text-purple-600"></i>
					</div>
					<div class="mt-2 text-xl font-bold font-mono tracking-tight text-purple-600 dark:text-purple-400" id="wfm-kpi-overtime">
						0.0 hrs
					</div>
					<div class="mt-1 text-[10px] text-emerald-600 font-semibold">
						<span>0 sobrecosto</span>
					</div>
				</div>
				<div class="mt-2 pt-1 border-t border-slate-100 dark:border-slate-800/80 text-[9px] text-slate-400 leading-tight">
					Límite legal OK
				</div>
			</div>

			<!-- KPI 8: Presupuesto Utilizado % -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-3.5 shadow-xs flex flex-col justify-between">
				<div>
					<div class="flex items-center justify-between text-slate-500 text-xs">
						<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="budget_used">Presupuesto utilizado</span>
						<i data-lucide="pie-chart" class="w-3.5 h-3.5 text-emerald-600"></i>
					</div>
					<div class="mt-2 text-xl font-bold font-mono tracking-tight text-emerald-600 dark:text-emerald-400" id="wfm-kpi-budget-pct">
						67.3%
					</div>
					<div class="mt-1 text-[10px] text-emerald-600 font-semibold">
						<span>+$916 margen</span>
					</div>
				</div>
				<div class="mt-2 pt-1 border-t border-slate-100 dark:border-slate-800/80 text-[9px] text-slate-400 leading-tight">
					Ppto: $2,800
				</div>
			</div>
		</div>

		<!-- GRÁFICO PRINCIPAL: VENTAS PREVISTAS VS HORAS LABORALES NECESARIAS -->
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-xs">
			<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
				<div>
					<h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
						<i data-lucide="bar-chart-2" class="w-4 h-4 text-brand-700"></i>
						<span>Ventas Previstas vs Horas Laborales Necesarias (Lunes a Domingo)</span>
					</h3>
					<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
						Correlación diaria entre la demanda de pedidos mayoristas ($ USD) y la dotación de horas recomendada vs programada.
					</p>
				</div>
				<div class="flex items-center gap-4 text-xs">
					<div class="flex items-center gap-1.5">
						<span class="w-3 h-3 rounded-xs bg-emerald-500"></span>
						<span class="text-slate-600 dark:text-slate-300 font-medium">Ventas ($ USD)</span>
					</div>
					<div class="flex items-center gap-1.5">
						<span class="w-3 h-1 rounded-full bg-indigo-600"></span>
						<span class="text-slate-600 dark:text-slate-300 font-medium">Horas Recomendadas</span>
					</div>
					<div class="flex items-center gap-1.5">
						<span class="w-3 h-1 rounded-full bg-brand-700"></span>
						<span class="text-slate-600 dark:text-slate-300 font-medium">Horas Programadas</span>
					</div>
				</div>
			</div>

			<div class="h-64 sm:h-72 w-full relative">
				<canvas id="wfmSalesVsHoursChart"></canvas>
			</div>
		</div>

		<!-- 2 GRÁFICOS COMPLEMENTARIOS: COBERTURA HORARIA Y COSTO POR FUNCIÓN -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
			<!-- Gráfico 2: Cobertura Horaria por Franja -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
				<div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
					<div>
						<h3 class="text-xs font-bold text-slate-800 dark:text-slate-200">Cobertura Horaria por Franja del Día</h3>
						<p class="text-[11px] text-slate-400 mt-0.5">Demanda recomendada vs dotación programada (07:00 a 20:00)</p>
					</div>
					<span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300">Franjas</span>
				</div>
				<div class="relative h-64 mt-3">
					<canvas id="chart-sch-hourly-coverage"></canvas>
				</div>
			</div>

			<!-- Gráfico 3: Costo y Productividad por Rol / Función -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
				<div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
					<div>
						<h3 class="text-xs font-bold text-slate-800 dark:text-slate-200">Costo y Eficiencia Laboral por Función</h3>
						<p class="text-[11px] text-slate-400 mt-0.5">Distribución presupuestaria por rol: Ventas, Reposición, Despacho, Admin</p>
					</div>
					<span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">Costos</span>
				</div>
				<div class="relative h-64 mt-3">
					<canvas id="chart-sch-cost-function"></canvas>
				</div>
			</div>
		</div>

		<!-- BLOQUE DE RECOMENDACIONES OPERATIVAS & ALERTAS DE COBERTURA -->
		<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
			<div class="lg:col-span-2 bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-xs space-y-4">
				<div class="flex items-center justify-between">
					<h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
						<i data-lucide="lightbulb" class="w-4 h-4 text-amber-500"></i>
						<span>Recomendaciones Inteligentes de Dotación</span>
					</h3>
					<span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200/60">3 Sugerencias Activas</span>
				</div>

				<div class="space-y-3">
					<div class="p-3.5 rounded-lg border border-amber-200/80 bg-amber-50/50 dark:bg-amber-950/20 flex items-start gap-3">
						<div class="w-7 h-7 rounded-md bg-amber-100 text-amber-800 flex items-center justify-center shrink-0 mt-0.5">
							<i data-lucide="alert-triangle" class="w-4 h-4"></i>
						</div>
						<div class="flex-1 text-xs">
							<div class="font-bold text-amber-900 dark:text-amber-200">Refuerzo en Pico Mayorista (Lunes 16 Nov)</div>
							<p class="text-amber-800/90 dark:text-amber-300/90 mt-0.5">
								Las ventas previstas del lunes alcanzan $9,200.00 debido a la consolidación de pedidos del fin de semana. Se recomienda agregar 1 turno intermedio de reposición (09:00 - 18:00) en Almacén Central Norte.
							</p>
							<div class="mt-2 flex items-center gap-2">
								<button onclick="switchWfmTab('schedule')" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded font-semibold text-[11px]">Asignar Turno</button>
								<span class="text-[11px] text-amber-700">Impacto estimado: +8.0 hrs | +$60.00 costo</span>
							</div>
						</div>
					</div>

					<div class="p-3.5 rounded-lg border border-indigo-200/80 bg-indigo-50/50 dark:bg-indigo-950/20 flex items-start gap-3">
						<div class="w-7 h-7 rounded-md bg-indigo-100 text-indigo-800 flex items-center justify-center shrink-0 mt-0.5">
							<i data-lucide="trending-up" class="w-4 h-4"></i>
						</div>
						<div class="flex-1 text-xs">
							<div class="font-bold text-indigo-900 dark:text-indigo-200">Oportunidad de Eficiencia en Cierre (Viernes 20 Nov)</div>
							<p class="text-indigo-800/90 dark:text-indigo-300/90 mt-0.5">
								La demanda de preventa y despacho del viernes culmina a las 17:30. Se puede adelantar el turno de cierre en 1 hora, reduciendo horas extras sin afectar las entregas completas y a tiempo a clientes corporativos.
							</p>
						</div>
					</div>

					<div class="p-3.5 rounded-lg border border-emerald-200/80 bg-emerald-50/50 dark:bg-emerald-950/20 flex items-start gap-3">
						<div class="w-7 h-7 rounded-md bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0 mt-0.5">
							<i data-lucide="check-circle-2" class="w-4 h-4"></i>
						</div>
						<div class="flex-1 text-xs">
							<div class="font-bold text-emerald-900 dark:text-emerald-200">Presupuesto Laboral con Margen Favorable</div>
							<p class="text-emerald-800/90 dark:text-emerald-300/90 mt-0.5">
								El costo laboral estimado de $1,884.00 representa un ahorro de $916.00 frente al presupuesto semanal asignado ($2,800.00), cumpliendo el 100% de los descansos legales obligatorios de 12 horas.
							</p>
						</div>
					</div>
				</div>
			</div>

			<!-- Resumen Rápido de Estado y Dotación -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-xs space-y-4">
				<h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
					<i data-lucide="shield" class="w-4 h-4 text-emerald-600"></i>
					<span>Reglas de Cumplimiento Laboral</span>
				</h3>
				<div class="space-y-3 text-xs">
					<div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50">
						<span class="text-slate-600 dark:text-slate-300">Descanso entre turnos (12h mín)</span>
						<span class="text-emerald-600 font-bold flex items-center gap-1"><i data-lucide="check" class="w-3.5 h-3.5"></i> 100% Cumplido</span>
					</div>
					<div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50">
						<span class="text-slate-600 dark:text-slate-300">Límite semanal (48h máx)</span>
						<span class="text-emerald-600 font-bold flex items-center gap-1"><i data-lucide="check" class="w-3.5 h-3.5"></i> 0 Infracciones</span>
					</div>
					<div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50">
						<span class="text-slate-600 dark:text-slate-300">Turnos superpuestos</span>
						<span class="text-emerald-600 font-bold flex items-center gap-1"><i data-lucide="check" class="w-3.5 h-3.5"></i> 0 Conflictos</span>
					</div>
					<div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50">
						<span class="text-slate-600 dark:text-slate-300">Cobertura mínima de apertura</span>
						<span class="text-emerald-600 font-bold flex items-center gap-1"><i data-lucide="check" class="w-3.5 h-3.5"></i> Garantizada</span>
					</div>
				</div>

				<div class="pt-3 border-t border-slate-100 dark:border-slate-800">
					<button onclick="switchWfmTab('simulator')" class="w-full py-2.5 bg-brand-50 hover:bg-brand-100 text-brand-800 font-semibold rounded-lg text-xs flex items-center justify-center gap-2 transition-colors">
						<i data-lucide="sparkles" class="w-4 h-4"></i>
						<span>Simular escenarios de demanda</span>
					</button>
				</div>
			</div>
		</div>
	</div>

	<!-- ========================================================
	     PESTAÑA 2: CALENDARIO PROFESIONAL DE HORARIOS (DÍA / SEMANA / MES)
	     ======================================================== -->
	<div id="wfm-tab-content-schedule" class="hidden space-y-6">

		<!-- BARRA DE CONTROL PRINCIPAL: SELECTOR DE VISTAS, NAVEGACIÓN Y ACCIONES -->
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
			<!-- Selector de 3 Vistas: [Día] [Semana] [Mes] (Semana por defecto) -->
			<div class="flex items-center gap-3">
				<div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80 shadow-2xs" data-tour="wfm-view-switcher">
					<button id="btn-view-daily" onclick="switchCalendarView('daily')" class="wfm-view-btn px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white flex items-center gap-1.5" title="Vista Diaria detallada">
						<i data-lucide="sun" class="w-3.5 h-3.5"></i>
						<span>Día</span>
					</button>
					<button id="btn-view-weekly" onclick="switchCalendarView('weekly')" class="wfm-view-btn px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all bg-white dark:bg-slate-700 text-brand-700 dark:text-brand-300 shadow-xs flex items-center gap-1.5" title="Vista Semanal (Matriz 7 días)">
						<i data-lucide="calendar" class="w-3.5 h-3.5"></i>
						<span>Semana</span>
					</button>
					<button id="btn-view-monthly" onclick="switchCalendarView('monthly')" class="wfm-view-btn px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white flex items-center gap-1.5" title="Vista Mensual completa">
						<i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
						<span>Mes</span>
					</button>
				</div>

				<!-- Navegación Temporal: Anterior, Hoy, Siguiente -->
				<div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-900/40 p-1 rounded-xl border border-slate-200/80 dark:border-slate-800">
					<button onclick="navigateCalendarDate(-1)" class="p-1.5 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition-colors" title="Periodo anterior">
						<i data-lucide="chevron-left" class="w-4 h-4"></i>
					</button>
					<button onclick="goToToday()" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 transition-colors shadow-2xs">
						Hoy
					</button>
					<button onclick="navigateCalendarDate(1)" class="p-1.5 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition-colors" title="Periodo siguiente">
						<i data-lucide="chevron-right" class="w-4 h-4"></i>
					</button>
				</div>

				<!-- Etiqueta Dinámica del Periodo Activo -->
				<div class="flex items-center gap-2">
					<span id="wfm-current-range-label" class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
						Semana 47: 16 Nov – 22 Nov 2026
					</span>
					<span id="wfm-calendar-status-badge" class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300">
						Publicado v1
					</span>
				</div>
			</div>

			<!-- Botones de Acción en Calendario -->
			<div class="flex items-center gap-2">
				<button onclick="openCopyWeekModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors" title="Copiar turnos de la semana anterior">
					<i data-lucide="copy" class="w-3.5 h-3.5"></i>
					<span class="hidden sm:inline">Copiar semana</span>
				</button>
				<button onclick="publishCurrentSchedule()" data-tour="wfm-btn-publish" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 dark:hover:bg-slate-600 text-white transition-colors" title="Publicar y notificar a los colaboradores">
					<i data-lucide="send" class="w-3.5 h-3.5"></i>
					<span>Publicar</span>
				</button>
				<button onclick="openShiftModal(null, 1, 1)" data-tour="wfm-btn-add-shift" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold rounded-lg bg-brand-700 hover:bg-brand-800 text-white transition-colors shadow-2xs">
					<i data-lucide="plus" class="w-3.5 h-3.5"></i>
					<span>Agregar turno</span>
				</button>
			</div>
		</div>

		<!-- BARRA DE FILTROS GLOBALES -->
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs">
			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
				<!-- Filtro Periodo Dinámico (Semana / Fecha) -->
				<div>
					<label id="wfm-filter-period-label" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Semana Activa</label>
					<select id="wfm-filter-week" onchange="handleWeekFilterChange(this.value)" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 p-2 font-medium">
						<option value="2026-W46">Semana 46: 09 Nov – 15 Nov (Anterior)</option>
						<option value="2026-W47" selected>Semana 47: 16 Nov – 22 Nov (Actual)</option>
						<option value="2026-W48">Semana 48: 23 Nov – 29 Nov (Próxima)</option>
						<option value="2026-W49">Semana 49: 30 Nov – 06 Dic (Siguiente)</option>
					</select>
					<input type="date" id="wfm-filter-day-input" value="2026-11-20" onchange="handleDayInputChange(this.value)" class="hidden w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 p-2 font-medium" />
				</div>

				<!-- Filtro Tienda / Establecimiento -->
				<div>
					<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Establecimiento</label>
					<select id="wfm-filter-establishment" onchange="refreshWfmSchedule()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 p-2 font-medium">
						<option value="Todos">Todos los Establecimientos (8)</option>
						<option value="Prince Distributors — Hallandale HQ" selected>Prince Distributors — Hallandale HQ</option>
						<option value="Aventura Mall Retail Hub">Aventura Mall Retail Hub</option>
						<option value="Hollywood Boulevard Supercenter">Hollywood Boulevard Supercenter</option>
						<option value="Fort Lauderdale Logistics Hub">Fort Lauderdale Logistics Hub</option>
						<option value="Miami Gardens Wholesale">Miami Gardens Wholesale</option>
						<option value="Sunny Isles Gourmet Mart">Sunny Isles Gourmet Mart</option>
						<option value="Pembroke Pines Distribution">Pembroke Pines Distribution</option>
						<option value="Pompano Beach Central Depot">Pompano Beach Central Depot</option>
					</select>
				</div>

				<!-- Filtro Territorio -->
				<div>
					<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Territorio</label>
					<select id="wfm-filter-territory" onchange="refreshWfmSchedule()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 p-2 font-medium">
						<option value="Todos" selected>Todos los Territorios</option>
						<option value="Broward South">Broward South</option>
						<option value="Miami-Dade North">Miami-Dade North</option>
						<option value="Broward Central">Broward Central</option>
					</select>
				</div>

				<!-- Filtro Departamento / Función -->
				<div>
					<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Departamento</label>
					<select id="wfm-filter-department" onchange="refreshWfmSchedule()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 p-2 font-medium">
						<option value="Todos" selected>Todos los Departamentos</option>
						<option value="Reposición & Merchandising">Reposición y exhibición</option>
						<option value="Fuerza de Ventas B2B">Fuerza de Ventas B2B</option>
						<option value="Operaciones & Logística">Operaciones & Logística</option>
					</select>
				</div>

				<!-- Filtro Supervisor -->
				<div>
					<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Supervisor</label>
					<select id="wfm-filter-supervisor" onchange="refreshWfmSchedule()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 p-2 font-medium">
						<option value="Todos">Todos los Supervisores</option>
						<option value="Carlos Mendoza" selected>Carlos Mendoza (Dir. Ventas / Ops)</option>
						<option value="Patricia Vega">Patricia Vega (Supervisora Miami-Dade)</option>
						<option value="Roberto Salcedo">Roberto Salcedo (Supervisor Broward)</option>
						<option value="Gonzalo Núñez">Gonzalo Núñez (Director WFM Global)</option>
					</select>
				</div>
			</div>
		</div>

		<!-- 8 KPIS SUPERIORES ESPECIFICADOS (REACTIVOS A VISTA Y FECHA) -->
		<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
			<!-- KPI 1: Personal programado -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-3 shadow-xs flex flex-col justify-between relative group hover:border-brand-500/50 transition-colors" data-tour="wfm-kpi-staff">
				<div class="flex items-center justify-between text-slate-500">
					<span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300" data-kpi="scheduled_staff">Personal programado</span>
				</div>
				<div class="my-1.5">
					<div class="text-xl font-bold font-mono tracking-tight text-slate-900 dark:text-white" id="wfm-top-kpi-staff">
						18
					</div>
					<div class="text-[10px] text-emerald-600 font-semibold" id="wfm-top-kpi-staff-sub">
						18 colaboradores
					</div>
				</div>
				<div class="text-[9px] text-slate-400 leading-tight border-t border-slate-100 dark:border-slate-800/80 pt-1">
					Plantilla activa
				</div>
			</div>

			<!-- KPI 2: Cobertura de turnos (%) -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-3 shadow-xs flex flex-col justify-between relative group hover:border-emerald-500/50 transition-colors" data-tour="wfm-kpi-coverage">
				<div class="flex items-center justify-between text-slate-500">
					<span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300" data-kpi="staff_coverage">Cobertura de personal</span>
				</div>
				<div class="my-1.5">
					<div class="text-xl font-bold font-mono tracking-tight text-emerald-600 dark:text-emerald-400" id="wfm-top-kpi-coverage">
						98.8%
					</div>
					<div class="text-[10px] text-emerald-600 font-semibold" id="wfm-top-kpi-coverage-sub">
						Nivel óptimo
					</div>
				</div>
				<div class="text-[9px] text-slate-400 leading-tight border-t border-slate-100 dark:border-slate-800/80 pt-1">
					vs demanda meta
				</div>
			</div>

			<!-- KPI 3: Turnos sin cubrir -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-3 shadow-xs flex flex-col justify-between relative group hover:border-amber-500/50 transition-colors" data-tour="wfm-kpi-uncovered">
				<div class="flex items-center justify-between text-slate-500">
					<span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300" data-kpi="uncovered_shifts">Turnos sin cubrir</span>
				</div>
				<div class="my-1.5">
					<div class="text-xl font-bold font-mono tracking-tight text-slate-900 dark:text-white" id="wfm-top-kpi-uncovered">
						0
					</div>
					<div class="text-[10px] text-emerald-600 font-semibold" id="wfm-top-kpi-uncovered-sub">
						0 vacíos
					</div>
				</div>
				<div class="text-[9px] text-slate-400 leading-tight border-t border-slate-100 dark:border-slate-800/80 pt-1">
					Déficit de franjas
				</div>
			</div>

			<!-- KPI 4: Horas programadas -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-3 shadow-xs flex flex-col justify-between relative group hover:border-brand-500/50 transition-colors">
				<div class="flex items-center justify-between text-slate-500">
					<span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300" data-kpi="scheduled_hours">Horas programadas</span>
				</div>
				<div class="my-1.5">
					<div class="text-xl font-bold font-mono tracking-tight text-slate-900 dark:text-white" id="wfm-top-kpi-hours">
						336.0h
					</div>
					<div class="text-[10px] text-brand-700 dark:text-brand-400 font-semibold" id="wfm-top-kpi-hours-sub">
						Horas netas
					</div>
				</div>
				<div class="text-[9px] text-slate-400 leading-tight border-t border-slate-100 dark:border-slate-800/80 pt-1">
					Labor efectiva
				</div>
			</div>

			<!-- KPI 5: Horas adicionales previstas -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-3 shadow-xs flex flex-col justify-between relative group hover:border-blue-500/50 transition-colors">
				<div class="flex items-center justify-between text-slate-500">
					<span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300" data-kpi="overtime_hours">Horas extras</span>
				</div>
				<div class="my-1.5">
					<div class="text-xl font-bold font-mono tracking-tight text-blue-600 dark:text-blue-400" id="wfm-top-kpi-overtime">
						0.0h
					</div>
					<div class="text-[10px] text-blue-600 font-semibold" id="wfm-top-kpi-overtime-sub">
						Dentro de límite
					</div>
				</div>
				<div class="text-[9px] text-slate-400 leading-tight border-t border-slate-100 dark:border-slate-800/80 pt-1">
					Sobre jornada 48h
				</div>
			</div>

			<!-- KPI 6: Ausencias registradas -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-3 shadow-xs flex flex-col justify-between relative group hover:border-rose-500/50 transition-colors">
				<div class="flex items-center justify-between text-slate-500">
					<span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300" data-kpi="absences">Ausencias</span>
				</div>
				<div class="my-1.5">
					<div class="text-xl font-bold font-mono tracking-tight text-amber-600 dark:text-amber-400" id="wfm-top-kpi-absences">
						2
					</div>
					<div class="text-[10px] text-amber-600 font-semibold" id="wfm-top-kpi-absences-sub">
						1 vac. • 1 perm.
					</div>
				</div>
				<div class="text-[9px] text-slate-400 leading-tight border-t border-slate-100 dark:border-slate-800/80 pt-1">
					Vacaciones/Bajas
				</div>
			</div>

			<!-- KPI 7: Solicitudes pendientes -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-3 shadow-xs flex flex-col justify-between relative group hover:border-purple-500/50 transition-colors">
				<div class="flex items-center justify-between text-slate-500">
					<span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300" data-kpi="pending_requests">Solicitudes pendientes</span>
				</div>
				<div class="my-1.5">
					<div class="text-xl font-bold font-mono tracking-tight text-purple-600 dark:text-purple-400" id="wfm-top-kpi-requests">
						3
					</div>
					<div class="text-[10px] text-purple-600 font-semibold" id="wfm-top-kpi-requests-sub">
						Por revisar
					</div>
				</div>
				<div class="text-[9px] text-slate-400 leading-tight border-t border-slate-100 dark:border-slate-800/80 pt-1">
					Cambios de turno
				</div>
			</div>

			<!-- KPI 8: Costo estimado de personal -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-3 shadow-xs flex flex-col justify-between relative group hover:border-emerald-500/50 transition-colors">
				<div class="flex items-center justify-between text-slate-500">
					<span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300" data-kpi="labor_cost">Costo laboral estimado</span>
				</div>
				<div class="my-1.5">
					<div class="text-xl font-bold font-mono tracking-tight text-slate-900 dark:text-white" id="wfm-top-kpi-cost">
						$2,520.00
					</div>
					<div class="text-[10px] text-emerald-600 font-semibold" id="wfm-top-kpi-cost-sub">
						Ppto: $2,800
					</div>
				</div>
				<div class="text-[9px] text-slate-400 leading-tight border-t border-slate-100 dark:border-slate-800/80 pt-1">
					Tarifas autorizadas
				</div>
			</div>
		</div>

		<!-- ========================================================
		     CONTENEDOR DE VISTAS DE CALENDARIO (DIARIA / SEMANAL / MENSUAL)
		     ======================================================== -->
		<div id="wfm-calendar-content-wrap" data-tour="wfm-calendar-container" class="space-y-6">

		<!-- ========================================================
		     VISTA 1: DIARIA (DETALLE POR FECHA, EMPLEADOS Y COBERTURA HORARIA)
		     ======================================================== -->
		<div id="wfm-view-daily" class="hidden space-y-6 animate-in fade-in duration-150">
			<!-- Banner de Fecha y Dotación Necesaria vs Programada -->
			<div class="bg-gradient-to-r from-brand-50 via-indigo-50/50 to-white dark:from-brand-950/40 dark:via-indigo-950/30 dark:to-surface-dark rounded-xl border border-brand-200/80 dark:border-brand-800/80 p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
				<div class="flex items-center gap-3">
					<div class="w-10 h-10 rounded-xl bg-brand-700 text-white flex items-center justify-center font-bold text-sm shadow-xs">
						<i data-lucide="sun" class="w-5 h-5"></i>
					</div>
					<div>
						<div class="flex items-center gap-2">
							<h3 class="text-base font-bold text-slate-900 dark:text-white" id="wfm-daily-date-title">
								Viernes, 20 de Noviembre de 2026
							</h3>
							<span id="wfm-daily-coverage-badge" class="px-2 py-0.5 text-xs font-bold rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300">
								100% Cobertura
							</span>
						</div>
						<p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
							Personal necesario: <strong id="wfm-daily-required-staff" class="text-slate-800 dark:text-white">14</strong> colaboradores • Programado: <strong id="wfm-daily-scheduled-staff" class="text-emerald-700 dark:text-emerald-400">14</strong> en piso
						</p>
					</div>
				</div>

				<div class="flex items-center gap-2">
					<button onclick="openShiftModal(null, 1, null, document.getElementById('wfm-filter-day-input')?.value || '2026-11-20')" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-lg bg-brand-700 hover:bg-brand-800 text-white transition-colors shadow-2xs">
						<i data-lucide="plus" class="w-3.5 h-3.5"></i>
						<span>Asignar turno hoy</span>
					</button>
				</div>
			</div>

			<!-- COBERTURA POR CADA HORA (06:00 A 22:00) -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs space-y-3">
				<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
					<div class="flex items-center gap-2">
						<i data-lucide="bar-chart-2" class="w-4 h-4 text-brand-700"></i>
						<h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
							Cobertura de Personal por Franja Horaria (06:00 – 22:00)
						</h4>
					</div>
					<div class="flex items-center gap-3 text-[11px]">
						<span class="flex items-center gap-1 text-slate-500">
							<span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span> Cubierto (≥ Requerido)
						</span>
						<span class="flex items-center gap-1 text-slate-500">
							<span class="w-2.5 h-2.5 rounded-sm bg-amber-500"></span> Justo (Falta 1)
						</span>
						<span class="flex items-center gap-1 text-slate-500">
							<span class="w-2.5 h-2.5 rounded-sm bg-rose-500"></span> Déficit (> 1 faltante)
						</span>
					</div>
				</div>

				<!-- Barras de Cobertura Horaria -->
				<div class="overflow-x-auto pb-2">
					<div id="wfm-daily-hourly-bars" class="min-w-[700px] grid grid-cols-17 gap-1.5 text-center text-[10px]">
						<!-- Inyectado dinámicamente con JavaScript -->
					</div>
				</div>
			</div>

			<!-- LISTA DETALLADA DE COLABORADORES PROGRAMADOS HOY -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
				<div class="p-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/30">
					<div>
						<h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-2">
							<i data-lucide="user-check" class="w-4 h-4 text-emerald-600"></i>
							<span>Colaboradores Programados para Esta Fecha</span>
						</h4>
						<p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
							Horarios de entrada y salida, área asignada, tipo de turno y descansos autorizados.
						</p>
					</div>
					<span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300" id="wfm-daily-scheduled-count-badge">
						14 turnos activos
					</span>
				</div>

				<div class="overflow-x-auto">
					<table class="w-full text-left border-collapse min-w-[750px]">
						<thead>
							<tr class="bg-slate-100/70 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700 text-[11px] font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
								<th class="p-3 w-56">Colaborador</th>
								<th class="p-3 w-44">Área / Sucursal</th>
								<th class="p-3 w-36 text-center">Horario</th>
								<th class="p-3 w-32 text-center">Tipo de Turno</th>
								<th class="p-3 w-28 text-center">Descanso</th>
								<th class="p-3 w-24 text-center">Horas</th>
								<th class="p-3 w-28 text-right">Acciones</th>
							</tr>
						</thead>
						<tbody id="wfm-daily-scheduled-tbody" class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
							<!-- Rellenado dinámicamente -->
						</tbody>
					</table>
				</div>
			</div>

			<!-- PERSONAL EN DESCANSO O AUSENCIA HOY -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs space-y-3">
				<div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
					<div class="flex items-center gap-2">
						<i data-lucide="coffee" class="w-4 h-4 text-amber-600"></i>
						<h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
							Descansos, Vacaciones y Ausencias Registradas
						</h4>
					</div>
					<span class="text-[11px] text-slate-400">Disponible para refuerzo</span>
				</div>
				<div id="wfm-daily-off-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 text-xs">
					<!-- Rellenado dinámicamente -->
				</div>
			</div>
		</div>

		<!-- ========================================================
		     VISTA 2: SEMANAL (MATRIZ INTERACTIVA 7 DÍAS CON VINCULACIÓN A VISTA DIARIA)
		     ======================================================== -->
		<div id="wfm-view-weekly" class="space-y-6">
			<!-- VISTA ADMINISTRADOR / SUPERVISOR: MATRIZ COMPLETA DE TURNOS -->
			<div id="wfm-admin-schedule-view" class="space-y-6">
				<!-- CALENDARIO SEMANAL INTERACTIVO -->
				<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
					<!-- Cabecera de Tabla con Leyenda y Botón Agregar Turno -->
					<div class="p-4 border-b border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-900/30">
						<div>
							<h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
								<i data-lucide="calendar" class="w-4 h-4 text-brand-700"></i>
								<span>Matriz de Turnos Semanal — Colaboradores vs Días</span>
							</h2>
							<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
								Haga clic en la cabecera de cualquier día para abrir su <strong class="text-brand-700 dark:text-brand-400">Vista Diaria</strong>. Arrastre (drag & drop) para mover de día.
							</p>
						</div>

						<div class="flex items-center gap-2">
							<button onclick="openShiftModal(null, 1, 1)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-brand-700 hover:bg-brand-800 text-white transition-colors">
								<i data-lucide="plus" class="w-3.5 h-3.5"></i>
								<span>Agregar turno</span>
							</button>
						</div>
					</div>

					<!-- Leyenda de colores de turnos -->
					<div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center gap-2 text-[11px] bg-white dark:bg-slate-900">
						<span class="text-slate-400 font-semibold mr-1">Turnos:</span>
						<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
							<span class="w-2 h-2 rounded-full bg-emerald-600"></span> Apertura (07:00 - 15:30)
						</span>
						<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
							<span class="w-2 h-2 rounded-full bg-blue-600"></span> Intermedio (09:00 - 18:00)
						</span>
						<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
							<span class="w-2 h-2 rounded-full bg-purple-600"></span> Cierre (13:30 - 22:00)
						</span>
						<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
							<span class="w-2 h-2 rounded-full bg-amber-600"></span> Vacaciones / Ausencia
						</span>
						<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
							<span class="w-2 h-2 rounded-full bg-slate-400"></span> Descanso
						</span>
					</div>

					<!-- Matriz Semanal (Table) -->
					<div class="overflow-x-auto">
						<table class="w-full text-left border-collapse min-w-[950px]" id="wfm-schedule-table">
							<thead>
								<tr class="bg-slate-100/70 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700 text-[11px] font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
									<th class="p-3 w-64 sticky left-0 bg-slate-100 dark:bg-slate-800 z-10">Colaborador / Rol</th>
									<th class="p-2.5 text-center w-36 group cursor-pointer hover:bg-brand-50/60 dark:hover:bg-brand-950/30 transition-colors" onclick="openDailyViewFromDow(1)" title="Clic para ver programación diaria del Lunes">
										<div class="flex items-center justify-center gap-1 text-slate-800 dark:text-white font-bold">
											<span>Lun 16 Nov</span>
											<i data-lucide="arrow-up-right" class="w-3 h-3 text-brand-600 opacity-0 group-hover:opacity-100 transition-opacity"></i>
										</div>
										<span class="text-[9px] text-brand-700 dark:text-brand-400 font-semibold block">Ver día</span>
									</th>
									<th class="p-2.5 text-center w-36 group cursor-pointer hover:bg-brand-50/60 dark:hover:bg-brand-950/30 transition-colors" onclick="openDailyViewFromDow(2)" title="Clic para ver programación diaria del Martes">
										<div class="flex items-center justify-center gap-1 text-slate-800 dark:text-white font-bold">
											<span>Mar 17 Nov</span>
											<i data-lucide="arrow-up-right" class="w-3 h-3 text-brand-600 opacity-0 group-hover:opacity-100 transition-opacity"></i>
										</div>
										<span class="text-[9px] text-brand-700 dark:text-brand-400 font-semibold block">Ver día</span>
									</th>
									<th class="p-2.5 text-center w-36 group cursor-pointer hover:bg-brand-50/60 dark:hover:bg-brand-950/30 transition-colors" onclick="openDailyViewFromDow(3)" title="Clic para ver programación diaria del Miércoles">
										<div class="flex items-center justify-center gap-1 text-slate-800 dark:text-white font-bold">
											<span>Mié 18 Nov</span>
											<i data-lucide="arrow-up-right" class="w-3 h-3 text-brand-600 opacity-0 group-hover:opacity-100 transition-opacity"></i>
										</div>
										<span class="text-[9px] text-brand-700 dark:text-brand-400 font-semibold block">Ver día</span>
									</th>
									<th class="p-2.5 text-center w-36 group cursor-pointer hover:bg-brand-50/60 dark:hover:bg-brand-950/30 transition-colors" onclick="openDailyViewFromDow(4)" title="Clic para ver programación diaria del Jueves">
										<div class="flex items-center justify-center gap-1 text-slate-800 dark:text-white font-bold">
											<span>Jue 19 Nov</span>
											<i data-lucide="arrow-up-right" class="w-3 h-3 text-brand-600 opacity-0 group-hover:opacity-100 transition-opacity"></i>
										</div>
										<span class="text-[9px] text-brand-700 dark:text-brand-400 font-semibold block">Ver día</span>
									</th>
									<th class="p-2.5 text-center w-36 group cursor-pointer hover:bg-brand-50/60 dark:hover:bg-brand-950/30 transition-colors" onclick="openDailyViewFromDow(5)" title="Clic para ver programación diaria del Viernes">
										<div class="flex items-center justify-center gap-1 text-slate-800 dark:text-white font-bold">
											<span>Vie 20 Nov</span>
											<i data-lucide="arrow-up-right" class="w-3 h-3 text-brand-600 opacity-0 group-hover:opacity-100 transition-opacity"></i>
										</div>
										<span class="text-[9px] text-brand-700 dark:text-brand-400 font-semibold block">Ver día</span>
									</th>
									<th class="p-2.5 text-center w-36 group cursor-pointer hover:bg-brand-50/60 dark:hover:bg-brand-950/30 transition-colors" onclick="openDailyViewFromDow(6)" title="Clic para ver programación diaria del Sábado">
										<div class="flex items-center justify-center gap-1 text-slate-800 dark:text-white font-bold">
											<span>Sáb 21 Nov</span>
											<i data-lucide="arrow-up-right" class="w-3 h-3 text-brand-600 opacity-0 group-hover:opacity-100 transition-opacity"></i>
										</div>
										<span class="text-[9px] text-brand-700 dark:text-brand-400 font-semibold block">Ver día</span>
									</th>
									<th class="p-2.5 text-center w-36 group cursor-pointer hover:bg-brand-50/60 dark:hover:bg-brand-950/30 transition-colors" onclick="openDailyViewFromDow(7)" title="Clic para ver programación diaria del Domingo">
										<div class="flex items-center justify-center gap-1 text-slate-800 dark:text-white font-bold">
											<span>Dom 22 Nov</span>
											<i data-lucide="arrow-up-right" class="w-3 h-3 text-brand-600 opacity-0 group-hover:opacity-100 transition-opacity"></i>
										</div>
										<span class="text-[9px] text-brand-700 dark:text-brand-400 font-semibold block">Ver día</span>
									</th>
									<th class="p-3 text-center w-28">Total Horas</th>
								</tr>
							</thead>
							<tbody id="wfm-schedule-tbody" class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
								<!-- Rellenado dinámicamente con JavaScript -->
							</tbody>
							<!-- Fila Resumen Semanal de Dotación -->
							<tfoot>
								<tr class="bg-slate-50 dark:bg-slate-900/60 border-t-2 border-slate-200 dark:border-slate-700 text-xs font-bold" id="wfm-schedule-tfoot">
									<!-- Rellenado dinámicamente con JavaScript -->
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
		</div>

		<!-- ========================================================
		     VISTA 3: MENSUAL (CALENDARIO COMPLETO CON COLORES DE COBERTURA Y RESUMEN)
		     ======================================================== -->
		<div id="wfm-view-monthly" class="hidden space-y-6 animate-in fade-in duration-150">
			<!-- Calendario Mensual Completo Grid 7 Columnas -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
				<!-- Cabecera de Mes con Leyenda de Estados de Cobertura -->
				<div class="p-4 border-b border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-900/30">
					<div>
						<h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
							<i data-lucide="calendar-days" class="w-4 h-4 text-brand-700"></i>
							<span id="wfm-monthly-grid-title">Calendario Mensual — Noviembre 2026</span>
						</h3>
						<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
							Haga clic en cualquier fecha para abrir su <strong class="text-brand-700 dark:text-brand-400">Programación Diaria</strong>.
						</p>
					</div>

					<!-- Leyenda de colores del mes -->
					<div class="flex items-center gap-2.5 text-[11px] font-semibold">
						<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
							<span class="w-2 h-2 rounded-full bg-emerald-600"></span> Cobertura suficiente (≥95%)
						</span>
						<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
							<span class="w-2 h-2 rounded-full bg-amber-600"></span> Requiere atención (80-94%)
						</span>
						<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-rose-50 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
							<span class="w-2 h-2 rounded-full bg-rose-600"></span> Falta personal (&lt;80%)
						</span>
					</div>
				</div>

				<!-- Grilla de Días de la Semana (Lun a Dom) -->
				<div class="grid grid-cols-7 border-b border-slate-200 dark:border-slate-800 bg-slate-100/60 dark:bg-slate-800/60 text-center text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider py-2">
					<div>Lun</div>
					<div>Mar</div>
					<div>Mié</div>
					<div>Jue</div>
					<div>Vie</div>
					<div>Sáb</div>
					<div>Dom</div>
				</div>

				<!-- Días del Mes (Inyectado con JavaScript) -->
				<div id="wfm-monthly-days-grid" class="grid grid-cols-7 divide-x divide-y divide-slate-100 dark:divide-slate-800 bg-slate-50/20 dark:bg-slate-900/10 min-h-[480px]">
					<!-- Celdas de días -->
				</div>
			</div>

			<!-- Resumen Mensual de Horas, Descansos y Vacaciones -->
			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
				<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs flex items-center gap-3">
					<div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 flex items-center justify-center font-bold">
						<i data-lucide="clock" class="w-5 h-5"></i>
					</div>
					<div>
						<div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Horas del Mes</div>
						<div class="text-lg font-bold font-mono text-slate-900 dark:text-white" id="wfm-month-total-hours">
							1,344.0 hrs
						</div>
					</div>
				</div>

				<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs flex items-center gap-3">
					<div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-bold">
						<i data-lucide="shield-check" class="w-5 h-5"></i>
					</div>
					<div>
						<div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Cobertura Promedio</div>
						<div class="text-lg font-bold font-mono text-emerald-600 dark:text-emerald-400" id="wfm-month-avg-coverage">
							97.2%
						</div>
					</div>
				</div>

				<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs flex items-center gap-3">
					<div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold">
						<i data-lucide="coffee" class="w-5 h-5"></i>
					</div>
					<div>
						<div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Días Libres</div>
						<div class="text-lg font-bold font-mono text-slate-900 dark:text-white" id="wfm-month-total-rests">
							128 descansos
						</div>
					</div>
				</div>

				<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs flex items-center gap-3">
					<div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 flex items-center justify-center font-bold">
						<i data-lucide="palmtree" class="w-5 h-5"></i>
					</div>
					<div>
						<div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Vacaciones / Bajas</div>
						<div class="text-lg font-bold font-mono text-amber-600 dark:text-amber-400" id="wfm-month-total-vacations">
							4 registradas
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- VISTA PERSONALIZADA DEL COLABORADOR (SOLO CUANDO EL ROL ES EMPLEADO / REPONEDOR) -->
		<div id="wfm-employee-personal-view" class="hidden space-y-6">
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-6 shadow-xs">
				<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
					<div class="flex items-center gap-3">
						<div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 flex items-center justify-center font-bold text-base" id="wfm-emp-avatar">
							JQ
						</div>
						<div>
							<div class="flex items-center gap-2">
								<h3 class="text-base font-bold text-slate-900 dark:text-white" id="wfm-emp-name">Jorge Quispe</h3>
								<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300">Mi Horario Activo</span>
							</div>
							<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" id="wfm-emp-detail">Vendedor Comercial B2B • Broward South • Prince Distributors — Hallandale HQ</p>
						</div>
					</div>
					<div class="flex items-center gap-2">
						<button onclick="requestPersonalShiftSwap()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-brand-700 hover:bg-brand-800 text-white shadow-2xs transition-colors">
							<i data-lucide="arrow-left-right" class="w-3.5 h-3.5"></i>
							<span>Solicitar cambio de turno</span>
						</button>
					</div>
				</div>

				<div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
					<div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
						<span class="text-slate-500 dark:text-slate-400 text-[11px] block">Horas contratadas</span>
						<span class="text-base font-bold text-slate-800 dark:text-slate-200" id="wfm-emp-contracted-hours">48.0 hrs / sem</span>
					</div>
					<div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
						<span class="text-slate-500 dark:text-slate-400 text-[11px] block">Horas asignadas</span>
						<span class="text-base font-bold text-emerald-600 dark:text-emerald-400" id="wfm-emp-scheduled-hours">48.0 hrs</span>
					</div>
					<div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
						<span class="text-slate-500 dark:text-slate-400 text-[11px] block">Sede asignada</span>
						<span class="text-xs font-bold text-slate-800 dark:text-slate-200" id="wfm-emp-establishment">Almacén Central Norte</span>
					</div>
					<div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
						<span class="text-slate-500 dark:text-slate-400 text-[11px] block">Supervisor a cargo</span>
						<span class="text-xs font-bold text-slate-800 dark:text-slate-200" id="wfm-emp-supervisor">Carlos Mendoza</span>
					</div>
				</div>

				<div class="mt-6">
					<div class="flex items-center justify-between mb-3">
						<h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Mis Turnos de la Semana</h4>
						<span class="text-xs text-slate-500" id="wfm-emp-week-label">Semana 47 (16 Nov – 22 Nov)</span>
					</div>
					<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3" id="wfm-emp-schedule-cards">
						<!-- Rellenado dinámicamente con las tarjetas de los 7 días -->
					</div>
				</div>
			</div>
		</div>
		</div> <!-- Fin #wfm-calendar-content-wrap -->
	</div>

	<!-- ========================================================
	     PESTAÑA 3: EMPLEADOS (DIRECTORIO Y DOTACIÓN)
	     ======================================================== -->
	<div id="wfm-tab-content-employees" class="hidden space-y-6">
		<div class="pb-4 border-b border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
			<div>
				<h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
					<i data-lucide="users" class="w-5 h-5 text-indigo-600"></i>
					<span>Directorio de Personal & Dotación Activa</span>
				</h2>
				<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
					Fuerza de trabajo asignada a almacenes, preventa y logística con control de horas contratadas.
				</p>
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

	<!-- ========================================================
	     PESTAÑA 4: PRODUCTIVIDAD (MODELOS B2B DIFERENCIADOS)
	     ======================================================== -->
	<div id="wfm-tab-content-productivity" class="hidden space-y-6">
		<div class="pb-4 border-b border-slate-200/80 dark:border-slate-800">
			<h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
				<i data-lucide="zap" class="w-5 h-5 text-amber-500"></i>
				<span>Modelos de Productividad Laboral B2B</span>
			</h2>
			<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
				Estándares de rendimiento por hora según función operativa: Reponedores en Almacén vs Preventistas en Ruta.
			</p>
		</div>

		<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
			<!-- Modelo 1: Reponedores / Almacén -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-xs space-y-4">
				<div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
					<div class="flex items-center gap-2">
						<span class="p-2 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
							<i data-lucide="package" class="w-5 h-5"></i>
						</span>
						<div>
							<h3 class="text-sm font-bold text-slate-900 dark:text-white">Reposición & Almacén Mayorista</h3>
							<p class="text-[11px] text-slate-400">Métricas físicas de movimiento de carga</p>
						</div>
					</div>
					<span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-100 text-emerald-800">Operaciones</span>
				</div>

				<div class="space-y-3 text-xs">
					<div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50">
						<span class="text-slate-600 dark:text-slate-300">Descarga y Paletizado</span>
						<span class="font-bold text-slate-900 dark:text-white">45 cajas / hora</span>
					</div>
					<div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50">
						<span class="text-slate-600 dark:text-slate-300">Reposición en Racks / Picking</span>
						<span class="font-bold text-slate-900 dark:text-white">35 cajas / hora</span>
					</div>
					<div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50">
						<span class="text-slate-600 dark:text-slate-300">Control de Lotes & SKUs</span>
						<span class="font-bold text-slate-900 dark:text-white">60 SKUs / hora</span>
					</div>
					<div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50">
						<span class="text-slate-600 dark:text-slate-300">Estándar de Conversión</span>
						<span class="font-bold text-emerald-600">1 pallet = 48 cajas master</span>
					</div>
				</div>

				<div class="p-3 rounded-lg bg-emerald-50/50 dark:bg-emerald-950/30 text-emerald-900 dark:text-emerald-200 text-xs">
					<strong>Regla de Dimensionamiento:</strong> 1 reponedor a tiempo completo cubre 1,200 cajas/semana de demanda de despacho mayorista.
				</div>
			</div>

			<!-- Modelo 2: Vendedores / Preventa en Ruta -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-xs space-y-4">
				<div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
					<div class="flex items-center gap-2">
						<span class="p-2 rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400">
							<i data-lucide="briefcase" class="w-5 h-5"></i>
						</span>
						<div>
							<h3 class="text-sm font-bold text-slate-900 dark:text-white">Fuerza de Ventas & Preventa</h3>
							<p class="text-[11px] text-slate-400">Métricas comerciales y rutas de visita</p>
						</div>
					</div>
					<span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-blue-100 text-blue-800">Comercial</span>
				</div>

				<div class="space-y-3 text-xs">
					<div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50">
						<span class="text-slate-600 dark:text-slate-300">Visitas Diarias Programadas</span>
						<span class="font-bold text-slate-900 dark:text-white">16 a 18 clientes / día</span>
					</div>
					<div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50">
						<span class="text-slate-600 dark:text-slate-300">Efectividad de Pedido (Strike Rate)</span>
						<span class="font-bold text-emerald-600">72.5% de conversión</span>
					</div>
					<div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50">
						<span class="text-slate-600 dark:text-slate-300">Valor promedio por pedido</span>
						<span class="font-bold text-slate-900 dark:text-white">$850.00 USD</span>
					</div>
					<div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50">
						<span class="text-slate-600 dark:text-slate-300">Ventas Objetivo por Hora de Ruta</span>
						<span class="font-bold text-indigo-600">$150.00 / hora</span>
					</div>
				</div>

				<div class="p-3 rounded-lg bg-blue-50/50 dark:bg-blue-950/30 text-blue-900 dark:text-blue-200 text-xs">
					<strong>Regla de Dimensionamiento:</strong> Cada 10 clientes mayoristas activos con frecuencia de 2 visitas/semana requieren 1 ejecutivo comercial dedicado.
				</div>
			</div>
		</div>
	</div>

	<!-- ========================================================
	     PESTAÑA 5: SIMULADOR DE ESCENARIOS WHAT-IF
	     ======================================================== -->
	<div id="wfm-tab-content-simulator" class="hidden space-y-6">
		<div class="pb-4 border-b border-slate-200/80 dark:border-slate-800">
			<h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
				<i data-lucide="sparkles" class="w-5 h-5 text-indigo-600"></i>
				<span>Simulador Interactivo de Demanda y Dotación (What-If)</span>
			</h2>
			<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
				Evalúe el impacto de variaciones en ventas, ausencias de personal y productividad sobre las horas y el presupuesto.
			</p>
		</div>

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
			<!-- Controles del Simulador -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-xs space-y-5">
				<div>
					<label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Variación de Ventas Previstas</label>
					<div class="grid grid-cols-4 gap-2">
						<button type="button" onclick="setInlineSimulationSales(-10)" class="sim-sales-btn py-2 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300" id="btn-sim-m10">-10%</button>
						<button type="button" onclick="setInlineSimulationSales(0)" class="sim-sales-btn py-2 text-xs font-bold rounded-lg bg-brand-700 text-white shadow-xs" id="btn-sim-0">Base</button>
						<button type="button" onclick="setInlineSimulationSales(10)" class="sim-sales-btn py-2 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300" id="btn-sim-p10">+10%</button>
						<button type="button" onclick="setInlineSimulationSales(20)" class="sim-sales-btn py-2 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300" id="btn-sim-p20">+20%</button>
					</div>
				</div>

				<div>
					<label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Ajuste de Productividad</label>
					<select id="sim-inline-prod-factor" onchange="runInlineSimulation()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 text-slate-800 dark:text-slate-200">
						<option value="1.0" selected>Productividad Estándar (100%)</option>
						<option value="1.15">Alta Eficiencia (+15%)</option>
						<option value="0.85">Complejidad Operativa (-15%)</option>
					</select>
				</div>

				<div>
					<label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Ausentismo Imprevisto (Colaboradores)</label>
					<input type="number" id="sim-inline-absences" min="0" max="5" value="0" onchange="runInlineSimulation()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 text-slate-800 dark:text-slate-200" />
				</div>

				<button onclick="runInlineSimulation()" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-xs flex items-center justify-center gap-2 transition-colors">
					<i data-lucide="play" class="w-4 h-4"></i>
					<span>Ejecutar Simulación</span>
				</button>
			</div>

			<!-- Resultados de Simulación -->
			<div class="lg:col-span-2 bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-xs space-y-4">
				<h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
					<i data-lucide="bar-chart" class="w-4 h-4 text-brand-700"></i>
					<span>Impacto Proyectado en la Dotación</span>
				</h3>

				<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
					<div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/40">
						<div class="text-[11px] text-slate-500 font-medium">Ventas Proyectadas</div>
						<div class="text-lg font-bold text-slate-900 dark:text-white mt-1" id="sim-inline-sales">$48,500.00</div>
					</div>
					<div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/40">
						<div class="text-[11px] text-slate-500 font-medium">Horas Requeridas</div>
						<div class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-1" id="sim-inline-hours">340.0 hrs</div>
					</div>
					<div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/40">
						<div class="text-[11px] text-slate-500 font-medium">Costo Estimado</div>
						<div class="text-lg font-bold text-slate-900 dark:text-white mt-1" id="sim-inline-cost">$2,550.00</div>
					</div>
					<div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/40">
						<div class="text-[11px] text-slate-500 font-medium">Cobertura Proyectada</div>
						<div class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-1" id="sim-inline-cov">85.0%</div>
					</div>
				</div>

				<div class="p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 text-xs">
					<div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 mb-1">
						<i data-lucide="info" class="w-4 h-4 text-brand-700"></i>
						<span>Diagnóstico Operativo de la Simulación</span>
					</div>
					<p class="text-slate-600 dark:text-slate-400" id="sim-inline-recommendation">
						Bajo el escenario base, la plantilla actual de 10 colaboradores activos cubre el 100% de la demanda planificada sin necesidad de horas extras ni recontratación de contingencia.
					</p>
				</div>
			</div>
		</div>
	</div>

	<!-- ========================================================
	     PESTAÑA 6: CONFIGURACIÓN Y PARÁMETROS LABORALES
	     ======================================================== -->
	<div id="wfm-tab-content-settings" class="hidden space-y-6">
		<div class="pb-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
			<div>
				<h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
					<i data-lucide="sliders" class="w-5 h-5 text-brand-700"></i>
					<span>Parámetros Operativos & Normativa Laboral</span>
				</h2>
				<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
					Configuración de topes de jornada, descansos obligatorios, costo base y ventas objetivo.
				</p>
			</div>
			<button onclick="saveWfmSettingsForm()" class="px-4 py-2 bg-brand-700 hover:bg-brand-800 text-white text-xs font-semibold rounded-lg flex items-center gap-2">
				<i data-lucide="save" class="w-3.5 h-3.5"></i>
				<span>Guardar Parámetros WFM</span>
			</button>
		</div>

		<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-xs space-y-4">
				<h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Reglas Laborales</h3>
				<div>
					<label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Jornada Semanal Máxima</label>
					<input type="text" value="48 horas / semana" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-2" readonly />
				</div>
				<div>
					<label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Descanso Mínimo entre Jornadas</label>
					<input type="text" value="12 horas obligatorias" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-2" readonly />
				</div>
				<div>
					<label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Colación / Refrigerio</label>
					<input type="text" value="45 minutos por turno" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-2" readonly />
				</div>
			</div>

			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-xs space-y-4">
				<h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Métricas Comerciales</h3>
				<div>
					<label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Ventas Objetivo por Hora Laboral</label>
					<input type="number" id="wfm-cfg-sales-per-hour" value="150" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2" />
				</div>
				<div>
					<label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Costo Base Promedio por Hora ($)</label>
					<input type="number" id="wfm-cfg-base-hourly-rate" value="7.50" step="0.5" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2" />
				</div>
				<div>
					<label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Presupuesto Laboral Semanal ($)</label>
					<input type="number" id="wfm-cfg-weekly-budget" value="2800" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2" />
				</div>
			</div>

			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-xs space-y-4">
				<h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Políticas de Publicación</h3>
				<div>
					<label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Anticipación de Publicación</label>
					<select class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2">
						<option>7 días antes (Recomendado)</option>
						<option>14 días antes</option>
					</select>
				</div>
				<div>
					<label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Bloqueo de Modificación tras Publicar</label>
					<select class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2">
						<option>Solo con Aprobación del Supervisor</option>
						<option>Libre</option>
					</select>
				</div>
				<div class="pt-2 text-[11px] text-slate-400">
					✓ Todas las publicaciones generan un registro inmutable en la bitácora de auditoría.
				</div>
			</div>
		</div>
	</div>

	<!-- ========================================================
	     PESTAÑA 7: TAREAS Y OPERACIONES EN TIENDA (SHARED WEB & MOBILE)
	     ======================================================== -->
	<div id="wfm-tab-content-tasks" class="hidden space-y-6">
		<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-200 dark:border-slate-800">
			<div>
				<h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
					<span>Operaciones en Tienda, Tareas & Reposición FEFO</span>
					<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">USA Sincronizado</span>
				</h3>
				<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
					Seguimiento en tiempo real de actividades en góndola, escaneo UPC, traslados de almacén y registro de mermas.
				</p>
			</div>
			<div class="flex items-center gap-2">
				<button onclick="openCreateTaskModal()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-brand-700 hover:bg-brand-800 text-white shadow-2xs transition-colors">
					<i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
					<span>Asignar Nueva Tarea</span>
				</button>
			</div>
		</div>

		<!-- Listado de Tareas Operativas -->
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
			<div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-900/30">
				<div class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
					<i data-lucide="list-todo" class="w-4 h-4 text-brand-600"></i>
					<span>Tareas Asignadas a la Fuerza de Campo</span>
				</div>
				<div class="flex items-center gap-2 text-xs">
					<select id="wfm-tasks-filter-status" onchange="renderWfmTasksTable()" class="text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-1.5">
						<option value="all">Todos los estados</option>
						<option value="pending">Pendientes</option>
						<option value="in_progress">En progreso</option>
						<option value="completed">Completadas</option>
					</select>
				</div>
			</div>
			<div class="overflow-x-auto">
				<table class="w-full text-left border-collapse text-xs">
					<thead>
						<tr class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 uppercase">
							<th class="p-3">Prioridad</th>
							<th class="p-3">Título de la Tarea & Detalles</th>
							<th class="p-3">Colaborador Asignado</th>
							<th class="p-3">Establecimiento</th>
							<th class="p-3 text-center">Estado</th>
							<th class="p-3 text-right">Acción</th>
						</tr>
					</thead>
					<tbody id="wfm-tasks-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
						<!-- Rellenado dinámicamente -->
					</tbody>
				</table>
			</div>
		</div>

		<!-- Inventario FEFO, Stock Góndola vs Bodega & UPC -->
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
			<div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/30">
				<div class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
					<i data-lucide="scan-barcode" class="w-4 h-4 text-emerald-600"></i>
					<span>Auditoría de Góndolas, Códigos UPC & Lotes FEFO (Bodega ➔ Exhibición)</span>
				</div>
				<span class="text-[11px] font-mono text-slate-500">Regla FEFO: First Expired, First Out</span>
			</div>
			<div class="overflow-x-auto">
				<table class="w-full text-left border-collapse text-xs">
					<thead>
						<tr class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 uppercase">
							<th class="p-3">Código UPC</th>
							<th class="p-3">Producto / Presentación</th>
							<th class="p-3">Lote & Vencimiento</th>
							<th class="p-3 text-center">Stock Bodega</th>
							<th class="p-3 text-center">Stock Góndola</th>
							<th class="p-3 text-center">Capacidad</th>
							<th class="p-3 text-right">Traslado Interno</th>
						</tr>
					</thead>
					<tbody id="wfm-inventory-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
						<!-- Rellenado dinámicamente -->
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

{{-- SECCIÓN 9.1: OPERACIONES Y TAREAS (DASHBOARD EJECUTIVO + CONSOLA OPERATIVA) --}}
<div id="section-operations" class="hidden space-y-6">
	<!-- Header con Título y Filtros -->
	<div class="pb-3 border-b border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
		<div class="flex items-center gap-2.5">
			<div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800/60 flex items-center justify-center">
				<i data-lucide="check-square" class="w-5 h-5"></i>
			</div>
			<div>
				<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
					<span>Operaciones y Tareas en Tienda</span>
					<span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300" title="Primero en vencer, primero en salir">Rotación por vencimiento activa</span>
				</h1>
				<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
					Muestra las tareas de reposición pendientes, completadas y vencidas, el control de fechas de vencimiento en góndola y los problemas reportados. Sirve para evitar góndolas vacías y productos vencidos.
				</p>
			</div>
		</div>

		<div class="flex items-center gap-2">
			<button onclick="openCreateTaskModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-brand-800 hover:bg-brand-700 text-white shadow-xs transition-all">
				<i data-lucide="plus" class="w-3.5 h-3.5"></i>
				<span>Nueva Tarea</span>
			</button>
		</div>
	</div>

	<!-- Selector de Sub-Pestañas: Tablero Ejecutivo vs Consola Operativa -->
	<div class="border-b border-slate-200 dark:border-slate-800 flex items-center justify-between gap-4">
		<div class="flex space-x-6 text-xs sm:text-sm font-semibold">
			<button id="ops-tab-btn-exec" onclick="switchOperationsSubTab('exec')" class="pb-3 border-b-2 border-brand-800 text-brand-800 dark:text-white dark:border-white transition-all flex items-center gap-2">
				<i data-lucide="layout-dashboard" class="w-4 h-4"></i>
				<span>Tablero Ejecutivo</span>
			</button>
			<button id="ops-tab-btn-manage" onclick="switchOperationsSubTab('manage')" class="pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-all flex items-center gap-2">
				<i data-lucide="list-checks" class="w-4 h-4"></i>
				<span>Consola de Tareas &amp; Reposición</span>
			</button>
		</div>
		<button onclick="switchOperationsSubTab('manage')" class="mb-2 text-xs text-brand-700 dark:text-brand-400 hover:underline flex items-center gap-1 font-medium">
			<span>Gestionar tareas operativas</span>
			<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
		</button>
	</div>

	<!-- VISTA 1: TABLERO EJECUTIVO DE OPERACIONES (8 KPIS + 3 GRÁFICOS + INDICADORES) -->
	<div id="ops-view-exec" class="space-y-6">
		<!-- 8 Tarjetas KPI Principales -->
		<div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-3 sm:gap-4">
			<!-- KPI 1: Tareas Pendientes -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="pending_tasks">Tareas pendientes</span>
					<div class="w-6 h-6 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center shrink-0">
						<i data-lucide="clock" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-slate-900 dark:text-white" id="kpi-ops-pending">8</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-amber-600 font-semibold">
					<span>-27.3%</span>
					<span class="text-slate-400">vs. ant.</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">En ejecución</div>
			</div>

			<!-- KPI 2: Tareas Completadas -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="completed_tasks">Tareas completadas</span>
					<div class="w-6 h-6 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center shrink-0">
						<i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-slate-900 dark:text-white" id="kpi-ops-completed">42</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>+10.5%</span>
					<span class="text-slate-400">vs. ant.</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Verificadas</div>
			</div>

			<!-- KPI 3: Tareas Vencidas -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="overdue_tasks">Tareas vencidas</span>
					<div class="w-6 h-6 rounded-md bg-rose-50 dark:bg-rose-950/60 text-rose-600 flex items-center justify-center shrink-0">
						<i data-lucide="alert-octagon" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-rose-600 dark:text-rose-400" id="kpi-ops-overdue">2</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>-50.0%</span>
					<span class="text-slate-400">vs. ant.</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Fuera de plazo</div>
			</div>

			<!-- KPI 4: Cumplimiento Operativo -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="task_compliance">Cumplimiento de tareas</span>
					<div class="w-6 h-6 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 flex items-center justify-center shrink-0">
						<i data-lucide="percent" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-indigo-600 dark:text-indigo-400" id="kpi-ops-compliance">80.8%</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>+2.3 pp</span>
					<span class="text-slate-400">vs. ant.</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Eficacia campo</div>
			</div>

			<!-- KPI 5: Productividad Reposición -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="task_productivity">Productividad operativa</span>
					<div class="w-6 h-6 rounded-md bg-teal-50 dark:bg-teal-950/60 text-teal-600 flex items-center justify-center shrink-0">
						<i data-lucide="zap" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-slate-900 dark:text-white" id="kpi-ops-prod">48.5</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-teal-600 font-semibold">
					<span>+7.8%</span>
					<span class="text-slate-400">cjs/h</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Cajas repuestas/h</div>
			</div>

			<!-- KPI 6: Alertas de Reposición -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="shelf_alerts">Alertas de góndola</span>
					<div class="w-6 h-6 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center shrink-0">
						<i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-amber-600 dark:text-amber-400" id="kpi-ops-alerts">5</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>-37.5%</span>
					<span class="text-slate-400">vs. ant.</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Existencia mínima</div>
			</div>

			<!-- KPI 7: Incidencias Operativas -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="incidents">Incidencias</span>
					<div class="w-6 h-6 rounded-md bg-rose-50 dark:bg-rose-950/60 text-rose-600 flex items-center justify-center shrink-0">
						<i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-slate-900 dark:text-white" id="kpi-ops-incidents">3</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>-40.0%</span>
					<span class="text-slate-400">vs. ant.</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Mermas/roturas</div>
			</div>

			<!-- KPI 8: Cumplimiento FEFO -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="fefo_compliance">Rotación por vencimiento</span>
					<div class="w-6 h-6 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center shrink-0">
						<i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400" id="kpi-ops-fefo">97.4%</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>+1.9 pp</span>
					<span class="text-slate-400">vs. ant.</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Fecha vencimiento</div>
			</div>
		</div>

		<!-- 2 Gráficos Principales -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
			<!-- Gráfico 1: Cumplimiento Operativo por Tipo de Tarea -->
			<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
					<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
						<i data-lucide="bar-chart-2" class="w-4 h-4 text-brand-600"></i>
						<span>Cumplimiento Operativo por Función &amp; Actividad</span>
					</h3>
					<span class="text-[10px] font-mono text-slate-400">Tareas</span>
				</div>
				<div class="h-64 w-full">
					<canvas id="chart-ops-compliance-shift"></canvas>
				</div>
			</div>

			<!-- Gráfico 2: Distribución de Tareas por Estado -->
			<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
					<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
						<i data-lucide="pie-chart" class="w-4 h-4 text-indigo-600"></i>
						<span>Distribución de Tareas por Estado</span>
					</h3>
					<span class="text-[10px] font-mono text-slate-400">Proporción</span>
				</div>
				<div class="h-64 w-full">
					<canvas id="chart-ops-status-dist"></canvas>
				</div>
			</div>
		</div>

		<!-- Indicadores Secundarios de Desempeño (4 Cards) -->
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
				<div>
					<p class="text-[11px] text-slate-500 dark:text-slate-400">Adopción Lector Código Barras</p>
					<h4 class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">94.2%</h4>
					<p class="text-[10px] text-emerald-600 font-medium">Escaneos válidos</p>
				</div>
				<div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center">
					<i data-lucide="qr-code" class="w-4 h-4"></i>
				</div>
			</div>

			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
				<div>
					<p class="text-[11px] text-slate-500 dark:text-slate-400">Góndolas Auditadas Hoy</p>
					<h4 class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">100%</h4>
					<p class="text-[10px] text-blue-600 font-medium">8 sedes cubiertas</p>
				</div>
				<div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center">
					<i data-lucide="store" class="w-4 h-4"></i>
				</div>
			</div>

			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
				<div>
					<p class="text-[11px] text-slate-500 dark:text-slate-400">Tasa de Merma en Tienda</p>
					<h4 class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">0.12%</h4>
					<p class="text-[10px] text-slate-400">Bajo el límite (meta &lt; 0,25%)</p>
				</div>
				<div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center">
					<i data-lucide="shield-check" class="w-4 h-4"></i>
				</div>
			</div>

			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
				<div>
					<p class="text-[11px] text-slate-500 dark:text-slate-400">Disponibilidad en Anaquel</p>
					<h4 class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">99.1%</h4>
					<p class="text-[10px] text-indigo-600 font-medium">Cero quiebres críticos</p>
				</div>
				<div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 flex items-center justify-center">
					<i data-lucide="layers" class="w-4 h-4"></i>
				</div>
			</div>
		</div>

		<!-- Gráficos de Riesgo y FEFO -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
			<!-- Evolución de Incidencias Operativas -->
			<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
					<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
						<i data-lucide="trending-down" class="w-4 h-4 text-emerald-600"></i>
						<span>Evolución Semanal de Incidencias &amp; Discrepancias</span>
					</h3>
					<span class="text-[10px] font-mono text-slate-400">Casos</span>
				</div>
				<div class="h-64 w-full">
					<canvas id="chart-ops-incidents-trend"></canvas>
				</div>
			</div>

			<!-- Cumplimiento de Rotación FEFO en el Tiempo -->
			<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
					<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
						<i data-lucide="shield-check" class="w-4 h-4 text-teal-600"></i>
						<span>Cumplimiento de la rotación por vencimiento (%)</span>
					</h3>
					<span class="text-[10px] font-mono text-slate-400">%</span>
				</div>
				<div class="h-64 w-full">
					<canvas id="chart-ops-fefo-trend"></canvas>
				</div>
			</div>
		</div>
	</div>

	<!-- VISTA 2: GESTIÓN OPERATIVA DE TAREAS Y REPOSICIÓN -->
	<div id="ops-view-manage" class="hidden space-y-6">
		<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-wrap items-center justify-between gap-3">
			<div class="flex items-center gap-2">
				<span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Filtrar por Estado:</span>
				<select id="wfm-tasks-filter-status" onchange="renderWfmTasksTable()" class="text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-1.5 text-slate-800 dark:text-slate-200">
					<option value="all">Todas las tareas</option>
					<option value="pending">Solo Pendientes</option>
					<option value="completed">Solo Completadas</option>
				</select>
			</div>
			<button onclick="openCreateTaskModal()" class="px-3 py-1.5 bg-brand-800 hover:bg-brand-700 text-white rounded-lg text-xs font-semibold">
				+ Crear Tarea Operativa
			</button>
		</div>

		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
			<div class="overflow-x-auto">
				<table class="w-full text-left border-collapse text-xs">
					<thead>
						<tr class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 uppercase">
							<th class="p-3">Prioridad / Tarea</th>
							<th class="p-3">Asignado a</th>
							<th class="p-3">Sede</th>
							<th class="p-3">Fecha Límite</th>
							<th class="p-3 text-center">Estado</th>
							<th class="p-3 text-right">Acción</th>
						</tr>
					</thead>
					<tbody id="wfm-tasks-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
						<!-- Render dinámico -->
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

{{-- SECCIÓN 10: PERSONAL Y DOTACIÓN (DASHBOARD EJECUTIVO + DIRECTORIO DE GESTIÓN) --}}
<div id="section-employees" class="hidden space-y-6">
	<div class="pb-3 border-b border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
		<div class="flex items-center gap-2.5">
			<div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60 flex items-center justify-center">
				<i data-lucide="user-check" class="w-5 h-5"></i>
			</div>
			<div>
				<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
					Personal y Dotación
				</h1>
				<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
					Muestra la dotación de personal, el ausentismo, la rotación, los puestos sin cubrir y el costo por hora. Sirve para planificar contrataciones y controlar el costo laboral.
				</p>
			</div>
		</div>

		<div class="flex items-center gap-2">
			<button onclick="switchEmployeesSubTab('manage')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 shadow-xs">
				<i data-lucide="users" class="w-3.5 h-3.5"></i>
				<span>Ver Directorio Completo</span>
			</button>
		</div>
	</div>

	<!-- Selector de Sub-Pestañas: Tablero Ejecutivo vs Directorio Operativo -->
	<div class="border-b border-slate-200 dark:border-slate-800 flex items-center justify-between gap-4">
		<div class="flex space-x-6 text-xs sm:text-sm font-semibold">
			<button id="emp-tab-btn-exec" onclick="switchEmployeesSubTab('exec')" class="pb-3 border-b-2 border-brand-800 text-brand-800 dark:text-white dark:border-white transition-all flex items-center gap-2">
				<i data-lucide="layout-dashboard" class="w-4 h-4"></i>
				<span>Tablero Ejecutivo</span>
			</button>
			<button id="emp-tab-btn-manage" onclick="switchEmployeesSubTab('manage')" class="pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-all flex items-center gap-2">
				<i data-lucide="users" class="w-4 h-4"></i>
				<span>Directorio de Personal &amp; Gestión</span>
			</button>
		</div>
		<button onclick="switchEmployeesSubTab('manage')" class="mb-2 text-xs text-brand-700 dark:text-brand-400 hover:underline flex items-center gap-1 font-medium">
			<span>Gestionar colaboradores</span>
			<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
		</button>
	</div>

	<!-- VISTA 1: TABLERO EJECUTIVO (8 KPIS + 3 GRÁFICOS + INDICADORES) — CERO NOMBRES INDIVIDUALES -->
	<div id="emp-view-exec" class="space-y-6">
		<!-- 8 Tarjetas KPI Principales -->
		<div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-3 sm:gap-4">
			<!-- KPI 1: Personal Activo -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="active_staff">Personal activo</span>
					<div class="w-6 h-6 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 flex items-center justify-center shrink-0">
						<i data-lucide="users" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-slate-900 dark:text-white" id="kpi-emp-active">20</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-slate-500 font-semibold">
					<span>100% en plantilla</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Nómina activa</div>
			</div>

			<!-- KPI 2: Ausentismo -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="absenteeism">Ausentismo</span>
					<div class="w-6 h-6 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center shrink-0">
						<i data-lucide="user-x" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400" id="kpi-emp-absenteeism">1.8%</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>-0.6 pp</span>
					<span class="text-slate-400">vs. ant.</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Meta: menos de 3%</div>
			</div>

			<!-- KPI 3: Rotación -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="staff_turnover">Rotación de personal</span>
					<div class="w-6 h-6 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center shrink-0">
						<i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-slate-900 dark:text-white" id="kpi-emp-turnover">4.2%</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>-0.8 pp</span>
					<span class="text-slate-400">vs. ant.</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Alta estabilidad</div>
			</div>

			<!-- KPI 4: Horas Contratadas -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="contracted_hours">Horas contratadas</span>
					<div class="w-6 h-6 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-600 flex items-center justify-center shrink-0">
						<i data-lucide="clock" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-slate-900 dark:text-white" id="kpi-emp-hours">960 hrs</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-slate-500 font-semibold">
					<span>Semanal base</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Capacidad teórica</div>
			</div>

			<!-- KPI 5: Costo por Hora -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="avg_hourly_cost">Costo promedio por hora</span>
					<div class="w-6 h-6 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center shrink-0">
						<i data-lucide="dollar-sign" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-slate-900 dark:text-white" id="kpi-emp-hourly-cost">$18.25/h</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-slate-500 font-semibold">
					<span>Tarifa media</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Nómina horaria</div>
			</div>

			<!-- KPI 6: Productividad Media -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="staff_productivity">Productividad del personal</span>
					<div class="w-6 h-6 rounded-md bg-teal-50 dark:bg-teal-950/60 text-teal-600 flex items-center justify-center shrink-0">
						<i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-teal-600 dark:text-teal-400" id="kpi-emp-prod">$2,620</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-teal-600 font-semibold">
					<span>+8.4%</span>
					<span class="text-slate-400">vs. ant.</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Venta / colaborador</div>
			</div>

			<!-- KPI 7: Cobertura Puestos Clave -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="key_positions">Puestos clave cubiertos</span>
					<div class="w-6 h-6 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center shrink-0">
						<i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400" id="kpi-emp-key-roles">96.0%</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>Cubierto</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Línea crítica</div>
			</div>

			<!-- KPI 8: Vacantes Abiertas -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="vacancies">Vacantes</span>
					<div class="w-6 h-6 rounded-md bg-sky-50 dark:bg-sky-950/60 text-sky-600 flex items-center justify-center shrink-0">
						<i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-slate-900 dark:text-white" id="kpi-emp-vacancies">1</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-slate-500 font-semibold">
					<span>En selección</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Repositor ruta</div>
			</div>
		</div>

		<!-- 2 Gráficos Principales -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
			<!-- Gráfico 1: Dotación por Función y Departamento -->
			<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
					<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
						<i data-lucide="users" class="w-4 h-4 text-brand-600"></i>
						<span>Dotación de Personal por Función &amp; Departamento</span>
					</h3>
					<span class="text-[10px] font-mono text-slate-400">Headcount</span>
				</div>
				<div class="h-64 w-full">
					<canvas id="chart-emp-role-dist"></canvas>
				</div>
			</div>

			<!-- Gráfico 2: Distribución de Nómina por Establecimiento -->
			<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
					<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
						<i data-lucide="pie-chart" class="w-4 h-4 text-indigo-600"></i>
						<span>Distribución de Nómina por Establecimiento Logístico</span>
					</h3>
					<span class="text-[10px] font-mono text-slate-400">Costo ($ USD)</span>
				</div>
				<div class="h-64 w-full">
					<canvas id="chart-emp-payroll-loc"></canvas>
				</div>
			</div>
		</div>

		<!-- Indicadores Secundarios (4 Cards) -->
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
				<div>
					<p class="text-[11px] text-slate-500 dark:text-slate-400">Capacitación en Protocolos</p>
					<h4 class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">98.0%</h4>
					<p class="text-[10px] text-slate-400">Certificación en rotación por vencimiento y caja</p>
				</div>
				<div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center">
					<i data-lucide="award" class="w-4 h-4"></i>
				</div>
			</div>

			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
				<div>
					<p class="text-[11px] text-slate-500 dark:text-slate-400">Antigüedad Media</p>
					<h4 class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">1.8 años</h4>
					<p class="text-[10px] text-blue-600 font-medium">Estabilidad alta</p>
				</div>
				<div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center">
					<i data-lucide="calendar" class="w-4 h-4"></i>
				</div>
			</div>

			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
				<div>
					<p class="text-[11px] text-slate-500 dark:text-slate-400">Dependencia Horas Extras</p>
					<h4 class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">3.6%</h4>
					<p class="text-[10px] text-emerald-600 font-medium">Bajo control (&lt;5%)</p>
				</div>
				<div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-600 flex items-center justify-center">
					<i data-lucide="clock" class="w-4 h-4"></i>
				</div>
			</div>

			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
				<div>
					<p class="text-[11px] text-slate-500 dark:text-slate-400">Ratio Nómina / Ingresos</p>
					<h4 class="text-lg font-bold text-teal-600 dark:text-teal-400 mt-0.5">14.8%</h4>
					<p class="text-[10px] text-slate-400">Referencia del sector mayorista</p>
				</div>
				<div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-950/60 text-teal-600 flex items-center justify-center">
					<i data-lucide="pie-chart" class="w-4 h-4"></i>
				</div>
			</div>
		</div>

		<!-- Gráficos de Contratos y Estabilidad -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
			<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
					<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
						<i data-lucide="file-text" class="w-4 h-4 text-brand-600"></i>
						<span>Modalidad de Contratación &amp; Jornada</span>
					</h3>
					<span class="text-[10px] font-mono text-slate-400">%</span>
				</div>
				<div class="h-64 w-full">
					<canvas id="chart-emp-contract-types"></canvas>
				</div>
			</div>

			<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
					<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
						<i data-lucide="activity" class="w-4 h-4 text-emerald-600"></i>
						<span>Curva de Disponibilidad Laboral Semanal</span>
					</h3>
					<span class="text-[10px] font-mono text-slate-400">Horas</span>
				</div>
				<div class="h-64 w-full">
					<canvas id="chart-emp-availability"></canvas>
				</div>
			</div>
		</div>
	</div>

	<!-- VISTA 2: DIRECTORIO OPERATIVO DE PERSONAL -->
	<div id="emp-view-manage" class="hidden space-y-6">
		<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-wrap items-center justify-between gap-3">
			<div class="relative w-full sm:w-80">
				<i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
				<input type="text" id="wfm-emp-search-2" oninput="filterEmployeesTable(this.value)" placeholder="Buscar colaborador, código o rol..." class="text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-2 pl-9 text-slate-800 dark:text-slate-200 w-full" />
			</div>
			<div class="text-xs text-slate-500">
				Total registros: <span class="font-bold text-slate-800 dark:text-white">20 colaboradores</span>
			</div>
		</div>

		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
			<div class="overflow-x-auto">
				<table class="w-full text-left border-collapse text-xs">
					<thead>
						<tr class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
							<th class="p-3">Código / Nombre</th>
							<th class="p-3">Rol &amp; Función</th>
							<th class="p-3">Departamento</th>
							<th class="p-3">Establecimiento &amp; Zona</th>
							<th class="p-3 text-center">Horas Contratadas</th>
							<th class="p-3 text-right">Costo Hora ($)</th>
							<th class="p-3">Supervisor</th>
							<th class="p-3 text-center">Estado</th>
						</tr>
					</thead>
					<tbody id="wfm-employees-tbody-2" class="divide-y divide-slate-100 dark:divide-slate-800">
						<!-- Rellenado dinámicamente -->
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

{{-- SECCIÓN 11: ASISTENCIA Y PUNTUALIDAD (DASHBOARD EJECUTIVO + REGISTRO DE MARCACIONES) --}}
<div id="section-attendance" class="hidden space-y-6">
	<div class="pb-3 border-b border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
		<div class="flex items-center gap-2.5">
			<div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800/60 flex items-center justify-center">
				<i data-lucide="clock" class="w-5 h-5"></i>
			</div>
			<div>
				<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
					Asistencia &amp; Puntualidad
				</h1>
				<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
					Compara las horas programadas con las trabajadas e indica ausencias, llegadas tarde y horas extras. Sirve para corregir desvíos de la planificación y controlar el costo de horas extras.
				</p>
			</div>
		</div>

		<div class="flex items-center gap-2">
			<button onclick="switchAttendanceSubTab('manage')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 shadow-xs">
				<i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
				<span>Ver Marcaciones en Vivo</span>
			</button>
		</div>
	</div>

	<!-- Selector de Sub-Pestañas: Tablero Ejecutivo vs Marcaciones -->
	<div class="border-b border-slate-200 dark:border-slate-800 flex items-center justify-between gap-4">
		<div class="flex space-x-6 text-xs sm:text-sm font-semibold">
			<button id="att-tab-btn-exec" onclick="switchAttendanceSubTab('exec')" class="pb-3 border-b-2 border-brand-800 text-brand-800 dark:text-white dark:border-white transition-all flex items-center gap-2">
				<i data-lucide="layout-dashboard" class="w-4 h-4"></i>
				<span>Tablero Ejecutivo</span>
			</button>
			<button id="att-tab-btn-manage" onclick="switchAttendanceSubTab('manage')" class="pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-all flex items-center gap-2">
				<i data-lucide="fingerprint" class="w-4 h-4"></i>
				<span>Registro Diario de Marcaciones</span>
			</button>
		</div>
		<button onclick="switchAttendanceSubTab('manage')" class="mb-2 text-xs text-brand-700 dark:text-brand-400 hover:underline flex items-center gap-1 font-medium">
			<span>Auditar marcaciones</span>
			<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
		</button>
	</div>

	<!-- VISTA 1: TABLERO EJECUTIVO DE ASISTENCIA (8 KPIS + 3 GRÁFICOS + INDICADORES) -->
	<div id="att-view-exec" class="space-y-6">
		<!-- 8 Tarjetas KPI Principales -->
		<div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-3 sm:gap-4">
			<!-- KPI 1: Tasa de Asistencia -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="attendance_rate">Asistencia</span>
					<div class="w-6 h-6 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center shrink-0">
						<i data-lucide="user-check" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400" id="kpi-att-rate">98.5%</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>+1.3 pp</span>
					<span class="text-slate-400">vs. ant.</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Jornada cumplida</div>
			</div>

			<!-- KPI 2: Tasa de Puntualidad -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="punctuality">Puntualidad</span>
					<div class="w-6 h-6 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center shrink-0">
						<i data-lucide="clock" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-blue-600 dark:text-blue-400" id="kpi-att-punctuality">96.8%</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>+1.8 pp</span>
					<span class="text-slate-400">vs. ant.</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Entradas a tiempo</div>
			</div>

			<!-- KPI 3: Horas Trabajadas -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="worked_hours">Horas trabajadas</span>
					<div class="w-6 h-6 rounded-md bg-brand-50 dark:bg-brand-950/60 text-brand-700 flex items-center justify-center shrink-0">
						<i data-lucide="activity" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-slate-900 dark:text-white" id="kpi-att-hours-worked">232.5 hrs</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>98.5% meta</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Marcación real</div>
			</div>

			<!-- KPI 4: Horas Programadas -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="scheduled_hours">Horas programadas</span>
					<div class="w-6 h-6 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 flex items-center justify-center shrink-0">
						<i data-lucide="calendar" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-slate-900 dark:text-white" id="kpi-att-hours-target">236.0 hrs</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-slate-500 font-semibold">
					<span>Semana 47</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Planilla de turnos</div>
			</div>

			<!-- KPI 5: Horas Productivas -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="productive_hours">Horas productivas</span>
					<div class="w-6 h-6 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 flex items-center justify-center shrink-0">
						<i data-lucide="zap" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-indigo-600 dark:text-indigo-400" id="kpi-att-hours-prod">224.0 hrs</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-indigo-600 font-semibold">
					<span>96.3%</span>
					<span class="text-slate-400">efectivas</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">En reposición/ruta</div>
			</div>

			<!-- KPI 6: Ausencias Injustificadas -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="absences">Ausencias</span>
					<div class="w-6 h-6 rounded-md bg-rose-50 dark:bg-rose-950/60 text-rose-600 flex items-center justify-center shrink-0">
						<i data-lucide="user-x" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400" id="kpi-att-absences">0</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>0 faltas</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Injustificadas</div>
			</div>

			<!-- KPI 7: Tardanzas -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="late_arrivals">Llegadas tarde</span>
					<div class="w-6 h-6 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center shrink-0">
						<i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-amber-600 dark:text-amber-400" id="kpi-att-tardiness">2</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>-50.0%</span>
					<span class="text-slate-400">vs. ant.</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">&gt;5 min retraso</div>
			</div>

			<!-- KPI 8: Horas Extras -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="overtime_hours">Horas extras</span>
					<div class="w-6 h-6 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-600 flex items-center justify-center shrink-0">
						<i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-slate-900 dark:text-white" id="kpi-att-overtime">4.5 hrs</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-slate-500 font-semibold">
					<span>Autorizadas</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Sobretiempo real</div>
			</div>
		</div>

		<!-- 2 Gráficos Principales -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
			<!-- Evolución Diaria de Asistencia y Puntualidad -->
			<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
					<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
						<i data-lucide="trending-up" class="w-4 h-4 text-emerald-600"></i>
						<span>Evolución Diaria de Asistencia y Puntualidad (%)</span>
					</h3>
					<span class="text-[10px] font-mono text-slate-400">%</span>
				</div>
				<div class="h-64 w-full">
					<canvas id="chart-att-trend"></canvas>
				</div>
			</div>

			<!-- Comparativa de Horas: Programadas vs Trabajadas vs Productivas -->
			<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
					<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
						<i data-lucide="bar-chart-2" class="w-4 h-4 text-brand-600"></i>
						<span>Comparativa Horaria: Programadas vs. Reales</span>
					</h3>
					<span class="text-[10px] font-mono text-slate-400">Horas</span>
				</div>
				<div class="h-64 w-full">
					<canvas id="chart-att-hours-compare"></canvas>
				</div>
			</div>
		</div>

		<!-- Indicadores Secundarios (4 Cards) -->
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
				<div>
					<p class="text-[11px] text-slate-500 dark:text-slate-400">Geocerca Verificada</p>
					<h4 class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">100%</h4>
					<p class="text-[10px] text-slate-400">Marcaciones en punto asignado</p>
				</div>
				<div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center">
					<i data-lucide="map-pin" class="w-4 h-4"></i>
				</div>
			</div>

			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
				<div>
					<p class="text-[11px] text-slate-500 dark:text-slate-400">Demora Media en Tardanza</p>
					<h4 class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">8.5 min</h4>
					<p class="text-[10px] text-blue-600 font-medium">Bajo impacto operativo</p>
				</div>
				<div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center">
					<i data-lucide="clock" class="w-4 h-4"></i>
				</div>
			</div>

			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
				<div>
					<p class="text-[11px] text-slate-500 dark:text-slate-400">Adherencia a Refrigerios</p>
					<h4 class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">98.2%</h4>
					<p class="text-[10px] text-slate-400">Tiempos reglamentarios</p>
				</div>
				<div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 flex items-center justify-center">
					<i data-lucide="coffee" class="w-4 h-4"></i>
				</div>
			</div>

			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
				<div>
					<p class="text-[11px] text-slate-500 dark:text-slate-400">Asistencia Perfecta</p>
					<h4 class="text-lg font-bold text-teal-600 dark:text-teal-400 mt-0.5">18 / 20</h4>
					<p class="text-[10px] text-slate-400">90% de colaboradores</p>
				</div>
				<div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-950/60 text-teal-600 flex items-center justify-center">
					<i data-lucide="award" class="w-4 h-4"></i>
				</div>
			</div>
		</div>

		<!-- Gráficos de Incidencias de Asistencia -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
			<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
					<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
						<i data-lucide="pie-chart" class="w-4 h-4 text-amber-600"></i>
						<span>Distribución de Tardanzas por Establecimiento</span>
					</h3>
					<span class="text-[10px] font-mono text-slate-400">Casos</span>
				</div>
				<div class="h-64 w-full">
					<canvas id="chart-att-incidents-loc"></canvas>
				</div>
			</div>

			<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
					<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
						<i data-lucide="clock" class="w-4 h-4 text-purple-600"></i>
						<span>Cumplimiento Horario por Día de la Semana</span>
					</h3>
					<span class="text-[10px] font-mono text-slate-400">Horas</span>
				</div>
				<div class="h-64 w-full">
					<canvas id="chart-att-weekday-compliance"></canvas>
				</div>
			</div>
		</div>
	</div>

	<!-- VISTA 2: REGISTRO DIARIO DE MARCACIONES -->
	<div id="att-view-manage" class="hidden space-y-6">
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-6 shadow-xs">
			<div class="flex items-center justify-between mb-4">
				<div>
					<h3 class="text-sm font-bold text-slate-900 dark:text-white">Consola de Marcaciones en Tiempo Real</h3>
					<p class="text-xs text-slate-500 mt-0.5">Control biométrico y de geocerca para entradas, descansos y salidas de personal.</p>
				</div>
				<span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 flex items-center gap-1.5">
					<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
					<span>Geocercas sincronizadas</span>
				</span>
			</div>
			<div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-800/40 text-xs text-slate-600 dark:text-slate-300">
				✓ 100% de colaboradores marcando con geolocalización activa en establecimiento asignado. 0 incidencias críticas registradas en la jornada.
			</div>
		</div>
	</div>
</div>

{{-- SECCIÓN 12: SOLICITUDES Y PERMISOS (DASHBOARD EJECUTIVO + GESTIÓN OPERATIVA) --}}
<div id="section-shift-requests" class="hidden space-y-6">
	<div class="pb-3 border-b border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
		<div class="flex items-center gap-2.5">
			<div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/60 flex items-center justify-center">
				<i data-lucide="clipboard-check" class="w-5 h-5"></i>
			</div>
			<div>
				<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
					Solicitudes y Permisos
				</h1>
				<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
					Reúne las solicitudes de vacaciones, permisos, licencias y cambios de turno, y el tiempo de respuesta. Sirve para responder a tiempo y mantener cubiertos los turnos.
				</p>
			</div>
		</div>

		<div class="flex items-center gap-2">
			<button onclick="switchRequestsSubTab('manage')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 shadow-xs">
				<i data-lucide="check-square" class="w-3.5 h-3.5"></i>
				<span>Bandeja de Aprobación</span>
			</button>
		</div>
	</div>

	<!-- Selector de Sub-Pestañas: Tablero Ejecutivo vs Gestión -->
	<div class="border-b border-slate-200 dark:border-slate-800 flex items-center justify-between gap-4">
		<div class="flex space-x-6 text-xs sm:text-sm font-semibold">
			<button id="req-tab-btn-exec" onclick="switchRequestsSubTab('exec')" class="pb-3 border-b-2 border-brand-800 text-brand-800 dark:text-white dark:border-white transition-all flex items-center gap-2">
				<i data-lucide="layout-dashboard" class="w-4 h-4"></i>
				<span>Tablero Ejecutivo</span>
			</button>
			<button id="req-tab-btn-manage" onclick="switchRequestsSubTab('manage')" class="pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-all flex items-center gap-2">
				<i data-lucide="inbox" class="w-4 h-4"></i>
				<span>Bandeja de Aprobación &amp; Permisos</span>
			</button>
		</div>
		<button onclick="switchRequestsSubTab('manage')" class="mb-2 text-xs text-brand-700 dark:text-brand-400 hover:underline flex items-center gap-1 font-medium">
			<span>Resolver solicitudes</span>
			<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
		</button>
	</div>

	<!-- VISTA 1: TABLERO EJECUTIVO DE SOLICITUDES (8 KPIS + 3 GRÁFICOS + INDICADORES) -->
	<div id="req-view-exec" class="space-y-6">
		<!-- 8 Tarjetas KPI Principales -->
		<div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-3 sm:gap-4">
			<!-- KPI 1: Solicitudes Recibidas -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="requests_received">Solicitudes recibidas</span>
					<div class="w-6 h-6 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center shrink-0">
						<i data-lucide="inbox" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-slate-900 dark:text-white" id="kpi-req-received">6</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-blue-600 font-semibold">
					<span>+20.0%</span>
					<span class="text-slate-400">vs. ant.</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Trámites totales</div>
			</div>

			<!-- KPI 2: Pendientes -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="pending_requests">Solicitudes pendientes</span>
					<div class="w-6 h-6 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center shrink-0">
						<i data-lucide="clock" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-amber-600 dark:text-amber-400" id="kpi-req-pending">3</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>-25.0%</span>
					<span class="text-slate-400">vs. ant.</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">En supervisión</div>
			</div>

			<!-- KPI 3: Aprobadas -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="requests_approved">Solicitudes aprobadas</span>
					<div class="w-6 h-6 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center shrink-0">
						<i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400" id="kpi-req-approved">2</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>66.7% tasa</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Autorizadas</div>
			</div>

			<!-- KPI 4: Rechazadas -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="requests_rejected">Solicitudes rechazadas</span>
					<div class="w-6 h-6 rounded-md bg-rose-50 dark:bg-rose-950/60 text-rose-600 flex items-center justify-center shrink-0">
						<i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-slate-900 dark:text-white" id="kpi-req-rejected">1</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-slate-400 font-semibold">
					<span>Por cobertura</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">No procedentes</div>
			</div>

			<!-- KPI 5: Tiempo de Resolución -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="resolution_time">Respuesta en 24 horas</span>
					<div class="w-6 h-6 rounded-md bg-teal-50 dark:bg-teal-950/60 text-teal-600 flex items-center justify-center shrink-0">
						<i data-lucide="zap" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-slate-900 dark:text-white" id="kpi-req-resolution">3.8 hrs</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>-26.9%</span>
					<span class="text-slate-400">vs. ant.</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Plazo de respuesta</div>
			</div>

			<!-- KPI 6: Permisos Activos -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="active_permissions">Permisos vigentes</span>
					<div class="w-6 h-6 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 flex items-center justify-center shrink-0">
						<i data-lucide="calendar" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-slate-900 dark:text-white" id="kpi-req-active-leaves">2</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-slate-500 font-semibold">
					<span>Esta semana</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Ausencias prog.</div>
			</div>

			<!-- KPI 7: Cambios de Turno -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="shift_swaps">Cambios de turno</span>
					<div class="w-6 h-6 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-600 flex items-center justify-center shrink-0">
						<i data-lucide="arrow-right-left" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-slate-900 dark:text-white" id="kpi-req-swaps">3</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-purple-600 font-semibold">
					<span>50% del total</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Swaps entre pares</div>
			</div>

			<!-- KPI 8: Solicitudes Vencidas -->
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-start justify-between text-slate-500 text-xs">
					<span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]" data-kpi="requests_overdue">Solicitudes vencidas</span>
					<div class="w-6 h-6 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center shrink-0">
						<i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
					</div>
				</div>
				<div class="mt-2 text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400" id="kpi-req-expired">0</div>
				<div class="mt-1 flex items-center justify-between text-[10px] text-emerald-600 font-semibold">
					<span>100% en plazo</span>
				</div>
				<div class="mt-2 text-[9px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-1 leading-tight">Cero desatendidas</div>
			</div>
		</div>

		<!-- 2 Gráficos Principales -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
			<!-- Gráfico 1: Distribución por Tipo de Solicitud -->
			<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
					<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
						<i data-lucide="pie-chart" class="w-4 h-4 text-brand-600"></i>
						<span>Distribución de Solicitudes por Tipo</span>
					</h3>
					<span class="text-[10px] font-mono text-slate-400">Proporción</span>
				</div>
				<div class="h-64 w-full">
					<canvas id="chart-req-type-dist"></canvas>
				</div>
			</div>

			<!-- Gráfico 2: Tasa de Resolución & Dictámenes -->
			<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
					<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
						<i data-lucide="bar-chart-2" class="w-4 h-4 text-emerald-600"></i>
						<span>Dictámenes: Aprobadas vs. En Revisión vs. Rechazadas</span>
					</h3>
					<span class="text-[10px] font-mono text-slate-400">Casos</span>
				</div>
				<div class="h-64 w-full">
					<canvas id="chart-req-resolution"></canvas>
				</div>
			</div>
		</div>

		<!-- Indicadores Secundarios (4 Cards) -->
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
				<div>
					<p class="text-[11px] text-slate-500 dark:text-slate-400">Tasa de Aprobación</p>
					<h4 class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">66.7%</h4>
					<p class="text-[10px] text-slate-400">2 de 3 resueltas</p>
				</div>
				<div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center">
					<i data-lucide="check-circle" class="w-4 h-4"></i>
				</div>
			</div>

			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
				<div>
					<p class="text-[11px] text-slate-500 dark:text-slate-400">Respondidas en menos de 24 horas</p>
					<h4 class="text-lg font-bold text-blue-600 dark:text-blue-400 mt-0.5">100%</h4>
					<p class="text-[10px] text-slate-400">Tiempo meta 24 horas</p>
				</div>
				<div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center">
					<i data-lucide="clock" class="w-4 h-4"></i>
				</div>
			</div>

			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
				<div>
					<p class="text-[11px] text-slate-500 dark:text-slate-400">Ingreso vía App Móvil</p>
					<h4 class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-0.5">100%</h4>
					<p class="text-[10px] text-slate-400">Cero papeleo físico</p>
				</div>
				<div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 flex items-center justify-center">
					<i data-lucide="smartphone" class="w-4 h-4"></i>
				</div>
			</div>

			<div class="bg-white dark:bg-surface-dark p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
				<div>
					<p class="text-[11px] text-slate-500 dark:text-slate-400">Índice Flexibilidad Laboral</p>
					<h4 class="text-lg font-bold text-teal-600 dark:text-teal-400 mt-0.5">9.4 / 10</h4>
					<p class="text-[10px] text-slate-400">Satisfacción de equipo</p>
				</div>
				<div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-950/60 text-teal-600 flex items-center justify-center">
					<i data-lucide="smile" class="w-4 h-4"></i>
				</div>
			</div>
		</div>

		<!-- Gráficos de Evolución de Solicitudes -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
			<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
					<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
						<i data-lucide="trending-up" class="w-4 h-4 text-brand-600"></i>
						<span>Evolución Semanal de Solicitudes Recibidas vs. Resueltas</span>
					</h3>
					<span class="text-[10px] font-mono text-slate-400">Trámites</span>
				</div>
				<div class="h-64 w-full">
					<canvas id="chart-req-evolution"></canvas>
				</div>
			</div>

			<div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
				<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
					<h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
						<i data-lucide="bar-chart-2" class="w-4 h-4 text-purple-600"></i>
						<span>Distribución por Motivo de Permiso</span>
					</h3>
					<span class="text-[10px] font-mono text-slate-400">Casos</span>
				</div>
				<div class="h-64 w-full">
					<canvas id="chart-req-reasons"></canvas>
				</div>
			</div>
		</div>
	</div>

	<!-- VISTA 2: BANDEJA DE APROBACIÓN DE SOLICITUDES -->
	<div id="req-view-manage" class="hidden space-y-6">
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
			<div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
				<h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Bandeja de Dictamen para Supervisores</h3>
				<span class="text-xs text-slate-500">Dictámenes con impacto en turnos en tiempo real</span>
			</div>
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
</div>
