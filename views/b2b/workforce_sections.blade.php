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
						Planificación de fuerza laboral B2B optimizada según ventas proyectadas, productividad por rol y presupuesto.
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

	<!-- BANNER DE CONTROL DEMOSTRACIÓN EMPRESARIAL -->
	<div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-slate-50 dark:from-emerald-950/40 dark:via-teal-950/30 dark:to-slate-900 border border-emerald-200/80 dark:border-emerald-800/80 rounded-xl p-3.5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
		<div class="flex items-center gap-3">
			<div class="relative flex h-3 w-3 items-center justify-center shrink-0">
				<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
				<span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-600"></span>
			</div>
			<div>
				<div class="flex items-center gap-2">
					<span class="text-xs font-bold text-emerald-900 dark:text-emerald-200 tracking-wide uppercase">Modo Demostración Activo</span>
					<span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300">Motor Central Sincronizado</span>
				</div>
				<p class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5">
					20 colaboradores, 8 establecimientos y 4 semanas con persistencia local y recálculo matemático reactivo.
				</p>
			</div>
		</div>

		<!-- Controles de Demo: Selector de Rol, Notificaciones, Optimización y Configuración -->
		<div class="flex items-center flex-wrap gap-2">
			<!-- Selector de Rol Activo -->
			<div class="flex items-center gap-1.5 bg-white dark:bg-slate-800 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-2xs">
				<label for="demo-active-role-select" class="text-[11px] font-bold text-slate-500 dark:text-slate-400 flex items-center gap-1">
					<i data-lucide="user-check" class="w-3.5 h-3.5 text-emerald-600"></i>
					<span class="hidden sm:inline">Rol:</span>
				</label>
				<select id="demo-active-role-select" onchange="handleDemoRoleSwitch(this.value)" class="text-xs font-semibold bg-transparent border-none text-slate-800 dark:text-slate-200 focus:ring-0 p-0 cursor-pointer">
					<option value="admin">Administrador (Gonzalo Núñez)</option>
					<option value="supervisor">Supervisor (Carlos Mendoza)</option>
					<option value="employee">Vendedor (Jorge Quispe)</option>
					<option value="reponedor">Reponedora (Rosa Huamán)</option>
				</select>
			</div>

			<!-- Botón Optimizar Semana -->
			<button onclick="openOptimizationModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white shadow-2xs transition-colors" title="Optimizar dotación y cobertura de la semana de forma determinista">
				<i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
				<span>Optimizar semana</span>
			</button>

			<!-- Botón Centro de Notificaciones con Badge -->
			<button onclick="openDemoNotificationsModal()" class="relative inline-flex items-center justify-center p-2 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 transition-colors" title="Centro de Notificaciones Internas">
				<i data-lucide="bell" class="w-4 h-4"></i>
				<span id="demo-unread-badge" class="absolute -top-1 -right-1 px-1.5 py-0.2 min-w-4 text-[10px] font-bold rounded-full bg-red-600 text-white flex items-center justify-center">3</span>
			</button>

			<!-- Botón Ajustes de Demostración (Persistencia / Exportar / Resetear) -->
			<button onclick="openDemoSettingsModal()" class="inline-flex items-center justify-center p-2 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 transition-colors" title="Ajustes de persistencia y exportación de datos demo">
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
	</div>

	<!-- ========================================================
	     PESTAÑA 1: RESUMEN (KPIS, GRÁFICO PRINCIPAL Y RECOMENDACIONES)
	     ======================================================== -->
	<div id="wfm-tab-content-summary" class="space-y-6">
		<!-- 5 INDICADORES PRINCIPALES (CON PERÍODO Y EXPLICACIÓN DE CÁLCULO) -->
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
			<!-- KPI 1: Ventas Previstas -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs flex flex-col justify-between">
				<div>
					<div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs">
						<span class="font-medium text-slate-700 dark:text-slate-300">1. Ventas Previstas</span>
						<i data-lucide="trending-up" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
					</div>
					<div class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white" id="wfm-kpi-sales">
						$48,500.00
					</div>
					<div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1">
						<span class="px-1.5 py-0.2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-semibold rounded text-[10px]">Semana 47</span>
						<span>+8.4% vs anterior</span>
					</div>
				</div>
				<div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 leading-tight">
					<strong class="text-slate-600 dark:text-slate-300">Cálculo:</strong> Pedidos B2B confirmados en ERP ($38.5k) más proyección de reposición mayorista por tendencia de consumo.
				</div>
			</div>

			<!-- KPI 2: Horas Recomendadas -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs flex flex-col justify-between">
				<div>
					<div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs">
						<span class="font-medium text-slate-700 dark:text-slate-300">2. Horas Recomendadas</span>
						<i data-lucide="calculator" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
					</div>
					<div class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white" id="wfm-kpi-rec-hours">
						340.0 hrs
					</div>
					<div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1">
						<span class="px-1.5 py-0.2 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-semibold rounded text-[10px]">Semana 47</span>
						<span>Meta: $150/h laboral</span>
					</div>
				</div>
				<div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 leading-tight">
					<strong class="text-slate-600 dark:text-slate-300">Cálculo:</strong> Ventas previstas / meta de facturación por hora, sujeto a cobertura mínima de almacén (2 colaboradores por franja).
				</div>
			</div>

			<!-- KPI 3: Horas Programadas -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs flex flex-col justify-between">
				<div>
					<div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs">
						<span class="font-medium text-slate-700 dark:text-slate-300">3. Horas Programadas</span>
						<i data-lucide="clock" class="w-4 h-4 text-brand-700 dark:text-brand-400"></i>
					</div>
					<div class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white" id="wfm-kpi-sched-hours">
						236.0 hrs
					</div>
					<div class="mt-1 text-[11px] font-semibold text-amber-600 dark:text-amber-400 flex items-center gap-1" id="wfm-kpi-hours-var">
						<span class="px-1.5 py-0.2 bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-semibold rounded text-[10px]">Semana 47</span>
						<span>-104.0 hrs brecha</span>
					</div>
				</div>
				<div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 leading-tight">
					<strong class="text-slate-600 dark:text-slate-300">Cálculo:</strong> Suma de horas netas de turnos asignados en la semana, descontando descansos reglamentarios (45 min/turno).
				</div>
			</div>

			<!-- KPI 4: Costo Laboral Estimado -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs flex flex-col justify-between">
				<div>
					<div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs">
						<span class="font-medium text-slate-700 dark:text-slate-300">4. Costo Laboral Est.</span>
						<i data-lucide="dollar-sign" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
					</div>
					<div class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white" id="wfm-kpi-cost">
						$1,884.00
					</div>
					<div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400" id="wfm-kpi-budget-var">
						<span class="px-1.5 py-0.2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold rounded text-[10px]">Semana 47</span>
						<span>Ppto: $2,800 (+916 margen)</span>
					</div>
				</div>
				<div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 leading-tight">
					<strong class="text-slate-600 dark:text-slate-300">Cálculo:</strong> Horas programadas de cada colaborador multiplicadas por su tarifa contractual horaria ($7.50 a $12.00/h).
				</div>
			</div>

			<!-- KPI 5: Cobertura de Personal -->
			<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs flex flex-col justify-between col-span-1 sm:col-span-2 lg:col-span-1">
				<div>
					<div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs">
						<span class="font-medium text-slate-700 dark:text-slate-300">5. Cobertura de Personal</span>
						<i data-lucide="shield-check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
					</div>
					<div class="mt-2 text-2xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400" id="wfm-kpi-coverage">
						69.4%
					</div>
					<div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
						<span class="px-1.5 py-0.2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-semibold rounded text-[10px]">Semana 47</span>
						<span>Mínimo operacional alcanzado</span>
					</div>
				</div>
				<div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 leading-tight">
					<strong class="text-slate-600 dark:text-slate-300">Cálculo:</strong> (Horas Programadas 236.0h / Horas Recomendadas 340.0h) * 100 con factor de cumplimiento del 69.4%.
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
								La demanda de preventa y despacho del viernes culmina a las 17:30. Se puede adelantar el turno de cierre en 1 hora, reduciendo horas extras sin afectar el despacho OTIF a clientes corporativos.
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
	     PESTAÑA 2: PROGRAMACIÓN SEMANAL (CALENDARIO INTERACTIVO)
	     ======================================================== -->
	<div id="wfm-tab-content-schedule" class="hidden space-y-6">
		<!-- Barra de Filtros Globales -->
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs">
			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
				<!-- Filtro Semana -->
				<div>
					<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Semana</label>
					<select id="wfm-filter-week" onchange="refreshWfmSchedule()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 p-2 font-medium">
						<option value="2026-W46">Semana 46: 09 Nov – 15 Nov (Anterior)</option>
						<option value="2026-W47" selected>Semana 47: 16 Nov – 22 Nov (Actual)</option>
						<option value="2026-W48">Semana 48: 23 Nov – 29 Nov (Próxima)</option>
						<option value="2026-W49">Semana 49: 30 Nov – 06 Dic (Siguiente)</option>
					</select>
				</div>

				<!-- Filtro Tienda / Establecimiento -->
				<div>
					<label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Establecimiento</label>
					<select id="wfm-filter-establishment" onchange="refreshWfmSchedule()" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 p-2 font-medium">
						<option value="Todos">Todos los Establecimientos (8)</option>
						<option value="Almacén Central Norte" selected>Almacén Central Norte</option>
						<option value="Supermercados Los Andes">Supermercados Los Andes</option>
						<option value="Distribuidora San Juan">Distribuidora San Juan</option>
						<option value="Mega Market Colonial">Mega Market Colonial</option>
						<option value="Hipermercado Metro Plaza">Hipermercado Metro Plaza</option>
						<option value="Cash & Carry Fiori">Cash & Carry Fiori</option>
						<option value="Tiendas Mass Express">Tiendas Mass Express</option>
						<option value="Centro Logístico Huachipa">Centro Logístico Huachipa</option>
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
						<option value="Lima Este">Lima Este</option>
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
						<option value="María Torres">María Torres (Supervisora Operaciones)</option>
						<option value="Gonzalo Núñez">Gonzalo Núñez (Gerente General)</option>
					</select>
				</div>
			</div>
		</div>

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
							Haga clic en un turno para editarlo/eliminarlo o en una celda vacía para agregar turno. Arrastre (drag & drop) para mover de día.
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
							<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" id="wfm-emp-detail">Vendedor Comercial B2B • Lima Norte • Almacén Central Norte</p>
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
						<span class="text-slate-600 dark:text-slate-300">Ticket Promedio por Pedido</span>
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
</div>

{{-- SECCIÓN 10: EMPLEADOS (MENU LATERAL) --}}
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
			<input type="text" id="wfm-emp-search-2" oninput="filterEmployeesTable(this.value)" placeholder="Buscar colaborador o código..." class="text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 text-slate-800 dark:text-slate-200 w-64" />
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
				<tbody id="wfm-employees-tbody-2" class="divide-y divide-slate-100 dark:divide-slate-800">
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
			<div class="text-xl font-bold text-slate-900 dark:text-white mt-1">236.0 hrs</div>
			<div class="text-[11px] text-slate-400 mt-0.5">Semana 47</div>
		</div>
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 p-4">
			<div class="text-xs text-slate-500">Horas Trabajadas</div>
			<div class="text-xl font-bold text-brand-700 dark:text-brand-400 mt-1">232.5 hrs</div>
			<div class="text-[11px] text-emerald-600 mt-0.5">98.5% cumplimiento</div>
		</div>
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 p-4">
			<div class="text-xs text-slate-500">Horas Productivas</div>
			<div class="text-xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">224.0 hrs</div>
			<div class="text-[11px] text-slate-400 mt-0.5">Visitas y reposición</div>
		</div>
		<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 p-4">
			<div class="text-xs text-slate-500">Horas Pagadas</div>
			<div class="text-xl font-bold text-slate-900 dark:text-white mt-1">236.0 hrs</div>
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
