{{-- SECCIÓN: GESTIÓN DEL CICLO DE VIDA DEL PRODUCTO E INTELIGENCIA COMERCIAL --}}
<div id="section-product-lifecycle" class="hidden space-y-6">

	<!-- Header Principal con Título, Subtítulo y Acciones Globales -->
	<div class="pb-4 border-b border-slate-200/80 dark:border-slate-800 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
		<div>
			<div class="flex items-center gap-3">
				<div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60 flex items-center justify-center shadow-xs">
					<i data-lucide="refresh-cw" class="w-5 h-5"></i>
				</div>
				<div>
					<div class="flex items-center gap-2">
						<h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
							Ciclo de Vida del Producto
						</h1>
						<span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-indigo-100 dark:bg-indigo-900/60 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
							Inteligencia Comercial
						</span>
					</div>
					<p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
						Muestra en qué etapa está cada producto (crecimiento, estabilidad o declive), cuánto dinero inmoviliza en inventario y cuáles pueden agotarse. Sirve para decidir qué comprar, qué promocionar y qué retirar.
					</p>
				</div>
			</div>
		</div>

		<!-- Botones de Acción Global -->
		<div class="flex flex-wrap items-center gap-2">
			<button onclick="openMarkdownSimulatorModal()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/80 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 transition-colors shadow-xs" title="Simulador interactivo de descuentos por elasticidad de demanda y liquidación">
				<i data-lucide="percent" class="w-3.5 h-3.5"></i>
				<span>Simular Descuentos</span>
			</button>
			<button onclick="openDataImportModal()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 transition-colors shadow-xs" title="Importar datos desde planillas Excel o archivos CSV">
				<i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
				<span>Importar Datos (Excel / CSV)</span>
			</button>
			<button onclick="refreshLifecycleData()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-brand-700 hover:bg-brand-800 text-white shadow-xs transition-colors" title="Actualizar todos los indicadores con las últimas ventas y conteos">
				<i data-lucide="rotate-cw" id="btn-refresh-icon" class="w-3.5 h-3.5"></i>
				<span>Actualizar Indicadores</span>
			</button>
			<button onclick="startDashboardTour()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/60 dark:hover:bg-amber-900/80 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 transition-colors shadow-xs" title="Guía interactiva explicativa paso a paso">
				<i data-lucide="help-circle" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400"></i>
				<span>Guía del Módulo</span>
			</button>
		</div>
	</div>

	<div class="bg-gradient-to-r from-indigo-50 via-slate-50 to-blue-50 dark:from-indigo-950/40 dark:via-slate-900 dark:to-blue-950/40 border border-indigo-200/80 dark:border-indigo-800/80 rounded-xl p-3.5 shadow-xs flex flex-col md:flex-row md:items-center md:justify-end gap-3">
		<div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
			<span>Última sincronización:</span>
			<span id="plc-sync-timestamp" class="font-mono font-medium text-slate-700 dark:text-slate-300">Hoy, 13:30</span>
		</div>
	</div>

	<!-- BARRA DE FILTROS GLOBALES DEL DASHBOARD -->
	<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-xs">
		<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
			
			<!-- Presets de período -->
			<div class="flex items-center gap-2">
				<span class="text-xs font-medium text-slate-500 dark:text-slate-400 shrink-0">Período:</span>
				<div class="inline-flex p-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs">
					<button onclick="setLifecyclePeriod('7D')" id="plc-period-7D" class="px-3 py-1 font-medium rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all">7 Días</button>
					<button onclick="setLifecyclePeriod('MTD')" id="plc-period-MTD" title="Mes actual hasta hoy" class="px-3 py-1 font-semibold rounded-md bg-white dark:bg-slate-700 text-indigo-700 dark:text-white shadow-xs transition-all">Mes actual</button>
					<button onclick="setLifecyclePeriod('QTD')" id="plc-period-QTD" title="Trimestre actual hasta hoy" class="px-3 py-1 font-medium rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all">Trimestre actual</button>
					<button onclick="setLifecyclePeriod('YTD')" id="plc-period-YTD" title="Año actual hasta hoy" class="px-3 py-1 font-medium rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all">Año actual</button>
				</div>
			</div>

			<!-- Selectores de Sucursal, Categoría y Etapa -->
			<div class="flex flex-wrap items-center gap-3">
				<!-- Sucursal -->
				<div class="flex items-center gap-1.5 text-xs">
					<span class="text-slate-500 dark:text-slate-400">Sucursal:</span>
					<select id="plc-filter-branch" onchange="applyLifecycleFilters()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
						<option value="ALL">Todas las sucursales</option>
						<option value="1">Hallandale HQ (Almacén Central)</option>
						<option value="2">Broward South Hub</option>
						<option value="3">Miami North Dispatch</option>
					</select>
				</div>

				<!-- Categoría -->
				<div class="flex items-center gap-1.5 text-xs">
					<span class="text-slate-500 dark:text-slate-400">Categoría:</span>
					<select id="plc-filter-category" onchange="applyLifecycleFilters()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
						<option value="ALL">Todas las categorías</option>
						<option value="Lácteos">Lácteos</option>
						<option value="Bebidas">Bebidas</option>
						<option value="Abarrotes">Abarrotes</option>
						<option value="Golosinas">Golosinas</option>
					</select>
				</div>

				<!-- Etapa del Ciclo de Vida -->
				<div class="flex items-center gap-1.5 text-xs">
					<span class="text-slate-500 dark:text-slate-400">Etapa:</span>
					<select id="plc-filter-stage" onchange="applyLifecycleFilters()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
						<option value="ALL">Todas las etapas</option>
						<option value="introduction">Introducción</option>
						<option value="growth">Crecimiento</option>
						<option value="maturity">Madurez</option>
						<option value="decline">Declive</option>
						<option value="exit_renewal">Salida / Renovación</option>
						<option value="insufficient_data">Datos Insuficientes</option>
					</select>
				</div>
			</div>

		</div>
	</div>

	<!-- ========================================================================= -->
	<!-- GRID DE LOS 12 KPIS PRINCIPALES DE CICLO DE VIDA E INTELIGENCIA COMERCIAL -->
	<!-- ========================================================================= -->
	<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="plc-kpis-grid">
		<!-- KPI 1: Sell-through -->
		<div onclick="openKpiDrilldownModal('sell_through')" class="group cursor-pointer bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-indigo-600 rounded-xl p-4 shadow-xs transition-all hover:shadow-md">
			<div class="flex items-center justify-between">
				<span class="text-xs font-semibold text-slate-700 dark:text-slate-300" data-kpi="sell_through">Porcentaje vendido del inventario</span>
			</div>
			<div class="mt-2 flex items-baseline justify-between">
				<div class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white" id="val-kpi-sell-through">58.4%</div>
				<span id="badge-kpi-sell-through" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300">
					+4.2% vs ant.
				</span>
			</div>
			<div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-2">
				<span>Meta: ≥ 50.0%</span>
				<span class="text-indigo-600 dark:text-indigo-400 group-hover:underline">Diagnóstico →</span>
			</div>
		</div>

		<!-- KPI 2: Rotación de Inventario -->
		<div onclick="openKpiDrilldownModal('turnover')" class="group cursor-pointer bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-indigo-600 rounded-xl p-4 shadow-xs transition-all hover:shadow-md">
			<div class="flex items-center justify-between">
				<span class="text-xs font-semibold text-slate-700 dark:text-slate-300" data-kpi="turnover">Rotación de inventario</span>
			</div>
			<div class="mt-2 flex items-baseline justify-between">
				<div class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white" id="val-kpi-turnover">1.38x</div>
				<span id="badge-kpi-turnover" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300">
					+0.13x
				</span>
			</div>
			<div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-2">
				<span>Referencia: 1.25x</span>
				<span class="text-indigo-600 dark:text-indigo-400 group-hover:underline">Diagnóstico →</span>
			</div>
		</div>

		<!-- KPI 3: Cobertura en Días (DIO) -->
		<div onclick="openKpiDrilldownModal('coverage_dio')" class="group cursor-pointer bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-indigo-600 rounded-xl p-4 shadow-xs transition-all hover:shadow-md">
			<div class="flex items-center justify-between">
				<span class="text-xs font-semibold text-slate-700 dark:text-slate-300" data-kpi="coverage_dio">Días de inventario</span>
			</div>
			<div class="mt-2 flex items-baseline justify-between">
				<div class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white" id="val-kpi-coverage">24.2 d</div>
				<span id="badge-kpi-coverage" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300">
					-4.3 d (Óptimo)
				</span>
			</div>
			<div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-2">
				<span>Rango ideal: 20 - 30 d</span>
				<span class="text-indigo-600 dark:text-indigo-400 group-hover:underline">Diagnóstico →</span>
			</div>
		</div>

		<!-- KPI 4: GMROI -->
		<div onclick="openKpiDrilldownModal('gmroi')" class="group cursor-pointer bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-indigo-600 rounded-xl p-4 shadow-xs transition-all hover:shadow-md">
			<div class="flex items-center justify-between">
				<span class="text-xs font-semibold text-slate-700 dark:text-slate-300" data-kpi="gmroi">Ganancia por dólar en inventario</span>
			</div>
			<div class="mt-2 flex items-baseline justify-between">
				<div class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white" id="val-kpi-gmroi">$3.15</div>
				<span id="badge-kpi-gmroi" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300">
					+$0.30 vs ant.
				</span>
			</div>
			<div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-2">
				<span>Meta: ≥ $2.50</span>
				<span class="text-indigo-600 dark:text-indigo-400 group-hover:underline">Diagnóstico →</span>
			</div>
		</div>

		<!-- KPI 5: GMROS -->
		<div onclick="openKpiDrilldownModal('gmros')" class="group cursor-pointer bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-indigo-600 rounded-xl p-4 shadow-xs transition-all hover:shadow-md">
			<div class="flex items-center justify-between">
				<span class="text-xs font-semibold text-slate-700 dark:text-slate-300" data-kpi="gmros">Ganancia por metro cuadrado</span>
			</div>
			<div class="mt-2 flex items-baseline justify-between">
				<div class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white" id="val-kpi-gmros">$415/m²</div>
				<span id="badge-kpi-gmros" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300">
					+$35 vs ant.
				</span>
			</div>
			<div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-2">
				<span>Capacidad: 85 m²</span>
				<span class="text-indigo-600 dark:text-indigo-400 group-hover:underline">Diagnóstico →</span>
			</div>
		</div>

		<!-- KPI 6: Productos en Crecimiento -->
		<div onclick="openKpiDrilldownModal('growth_skus')" class="group cursor-pointer bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-indigo-600 rounded-xl p-4 shadow-xs transition-all hover:shadow-md">
			<div class="flex items-center justify-between">
				<span class="text-xs font-semibold text-slate-500 dark:text-slate-400" data-kpi="growth_skus">Productos en crecimiento</span>
			</div>
			<div class="mt-2 flex items-baseline justify-between">
				<div class="text-2xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400" id="val-kpi-growth">21.4%</div>
				<span id="badge-kpi-growth" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300">
					3 productos clave
				</span>
			</div>
			<div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-2">
				<span>Impulsores de volumen</span>
				<span class="text-indigo-600 dark:text-indigo-400 group-hover:underline">Ver productos →</span>
			</div>
		</div>

		<!-- KPI 7: Productos en Madurez -->
		<div onclick="openKpiDrilldownModal('maturity_skus')" class="group cursor-pointer bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-indigo-600 rounded-xl p-4 shadow-xs transition-all hover:shadow-md">
			<div class="flex items-center justify-between">
				<span class="text-xs font-semibold text-slate-500 dark:text-slate-400" data-kpi="maturity_skus">Productos estables</span>
			</div>
			<div class="mt-2 flex items-baseline justify-between">
				<div class="text-2xl font-bold tracking-tight text-indigo-600 dark:text-indigo-400" id="val-kpi-maturity">28.6%</div>
				<span id="badge-kpi-maturity" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-100 dark:bg-indigo-950/80 text-indigo-800 dark:text-indigo-300">
					4 Cash-Cows
				</span>
			</div>
			<div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-2">
				<span>Estabilidad 98%</span>
				<span class="text-indigo-600 dark:text-indigo-400 group-hover:underline">Ver productos →</span>
			</div>
		</div>

		<!-- KPI 8: Productos en Declive -->
		<div onclick="openKpiDrilldownModal('decline_skus')" class="group cursor-pointer bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 hover:border-amber-400 dark:hover:border-amber-600 rounded-xl p-4 shadow-xs transition-all hover:shadow-md">
			<div class="flex items-center justify-between">
				<span class="text-xs font-semibold text-slate-500 dark:text-slate-400" data-kpi="decline_skus">Productos en declive</span>
			</div>
			<div class="mt-2 flex items-baseline justify-between">
				<div class="text-2xl font-bold tracking-tight text-amber-600 dark:text-amber-400" id="val-kpi-decline">14.3%</div>
				<span id="badge-kpi-decline" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300">
					2 productos en riesgo
				</span>
			</div>
			<div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-2">
				<span>Acción preventiva</span>
				<span class="text-amber-600 dark:text-amber-400 group-hover:underline">Planificar →</span>
			</div>
		</div>

		<!-- KPI 9: Capital Inmovilizado ($) -->
		<div onclick="openKpiDrilldownModal('immobilized_capital')" class="group cursor-pointer bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 hover:border-red-400 dark:hover:border-red-600 rounded-xl p-4 shadow-xs transition-all hover:shadow-md">
			<div class="flex items-center justify-between">
				<span class="text-xs font-semibold text-slate-700 dark:text-slate-300" data-kpi="immobilized_capital">Dinero detenido en inventario</span>
			</div>
			<div class="mt-2 flex items-baseline justify-between">
				<div class="text-2xl font-bold tracking-tight text-red-600 dark:text-red-400" id="val-kpi-immobilized">$17,215</div>
				<span id="badge-kpi-immobilized" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300">
					-$2,260 vs mes ant.
				</span>
			</div>
			<div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-2">
				<span>$9,375 liquidable</span>
				<span class="text-red-600 dark:text-red-400 group-hover:underline">Recuperar →</span>
			</div>
		</div>

		<!-- KPI 10: Riesgo Quiebre de Stock -->
		<div onclick="openKpiDrilldownModal('stockout_risk')" class="group cursor-pointer bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 hover:border-red-400 dark:hover:border-red-600 rounded-xl p-4 shadow-xs transition-all hover:shadow-md">
			<div class="flex items-center justify-between">
				<span class="text-xs font-semibold text-slate-700 dark:text-slate-300" data-kpi="stockout_risk">Riesgo de quedarse sin inventario</span>
			</div>
			<div class="mt-2 flex items-baseline justify-between">
				<div class="text-2xl font-bold tracking-tight text-amber-600 dark:text-amber-400" id="val-kpi-stockout">14.3%</div>
				<span id="badge-kpi-stockout" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300">
					-3.7% vs ant.
				</span>
			</div>
			<div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-2">
				<span>2 productos en punto de reposición</span>
				<span class="text-amber-600 dark:text-amber-400 group-hover:underline">Reponer →</span>
			</div>
		</div>

		<!-- KPI 11: Margen Bruto Post-Descuentos -->
		<div onclick="openKpiDrilldownModal('net_margin_post_discounts')" class="group cursor-pointer bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-indigo-600 rounded-xl p-4 shadow-xs transition-all hover:shadow-md">
			<div class="flex items-center justify-between">
				<span class="text-xs font-semibold text-slate-700 dark:text-slate-300" data-kpi="net_margin_post_discounts">Margen después de descuentos</span>
			</div>
			<div class="mt-2 flex items-baseline justify-between">
				<div class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white" id="val-kpi-margin">24.2%</div>
				<span id="badge-kpi-margin" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300">
					+1.4% vs ant.
				</span>
			</div>
			<div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-2">
				<span>Descuentos: 3.8%</span>
				<span class="text-indigo-600 dark:text-indigo-400 group-hover:underline">Diagnóstico →</span>
			</div>
		</div>

		<!-- KPI 12: Precisión del Pronóstico -->
		<div onclick="openKpiDrilldownModal('forecast_accuracy')" class="group cursor-pointer bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-indigo-600 rounded-xl p-4 shadow-xs transition-all hover:shadow-md">
			<div class="flex items-center justify-between">
				<span class="text-xs font-semibold text-slate-700 dark:text-slate-300" data-kpi="forecast_accuracy">Precisión de la proyección</span>
			</div>
			<div class="mt-2 flex items-baseline justify-between">
				<div class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white" id="val-kpi-forecast">92.4%</div>
				<span id="badge-kpi-forecast" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300">
					+3.3% vs ant.
				</span>
			</div>
			<div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-2">
				<span>Modelo: Holt-Winters</span>
				<span class="text-indigo-600 dark:text-indigo-400 group-hover:underline">Detalles →</span>
			</div>
		</div>
	</div>

	<!-- ========================================================================= -->
	<!-- BLOQUE DE VISUALIZACIONES PRINCIPALES (6 GRÁFICOS Y PANELES)             -->
	<!-- ========================================================================= -->

	<!-- Fila 1: Distribución del Portafolio + Curva del Ciclo de Vida -->
	<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
		
		<!-- A. Distribución del Portafolio -->
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
			<div>
				<div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
					<div>
						<h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
							<i data-lucide="pie-chart" class="w-4 h-4 text-indigo-600"></i>
							<span>A. Distribución del Portafolio por Etapa</span>
						</h2>
						<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
							Porcentaje y cantidad de productos en cada etapa: crecimiento, estabilidad, declive o sin movimiento.
						</p>
					</div>
					<span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300">14 productos en total</span>
				</div>

				<!-- Barra visual apilada -->
				<div class="mt-4">
					<div class="h-4 w-full rounded-full overflow-hidden flex bg-slate-100 dark:bg-slate-800 shadow-inner" id="plc-stacked-bar">
						<div style="width: 14.3%; background-color: #3B82F6;" title="Introducción: 14.3%"></div>
						<div style="width: 21.4%; background-color: #10B981;" title="Crecimiento: 21.4%"></div>
						<div style="width: 28.6%; background-color: #6366F1;" title="Madurez: 28.6%"></div>
						<div style="width: 14.3%; background-color: #F59E0B;" title="Declive: 14.3%"></div>
						<div style="width: 14.3%; background-color: #EF4444;" title="Salida: 14.3%"></div>
						<div style="width: 7.1%; background-color: #94A3B8;" title="Insuficiente: 7.1%"></div>
					</div>
				</div>

				<!-- Leyenda interactiva -->
				<div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 mt-4 text-xs" id="plc-distribution-legend">
					<div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
						<div class="flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200">
							<span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
							<span>Introducción</span>
						</div>
						<div class="text-[11px] text-slate-500 mt-1">2 productos (14.3%) • $2.3k</div>
					</div>
					<div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
						<div class="flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200">
							<span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
							<span>Crecimiento</span>
						</div>
						<div class="text-[11px] text-slate-500 mt-1">3 productos (21.4%) • $27.2k</div>
					</div>
					<div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
						<div class="flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200">
							<span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
							<span>Madurez</span>
						</div>
						<div class="text-[11px] text-slate-500 mt-1">4 productos (28.6%) • $88.7k</div>
					</div>
					<div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
						<div class="flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200">
							<span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
							<span>Declive</span>
						</div>
						<div class="text-[11px] text-slate-500 mt-1">2 productos (14.3%) • $1.7k</div>
					</div>
					<div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
						<div class="flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200">
							<span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
							<span>Salida/Renov.</span>
						</div>
						<div class="text-[11px] text-slate-500 mt-1">2 productos (14.3%) • $0.4k</div>
					</div>
					<div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
						<div class="flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200">
							<span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
							<span>Datos Insuf.</span>
						</div>
						<div class="text-[11px] text-slate-500 mt-1">1 producto (7.1%) • $0.0k</div>
					</div>
				</div>
			</div>

			<div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
				<span>Portafolio saludable: 64.3% en etapas activas</span>
				<button onclick="filterByStageQuick('growth')" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">
					Filtrar Crecimiento →
				</button>
			</div>
		</div>

		<!-- B. Curva del Ciclo de Vida -->
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
			<div>
				<div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
					<div>
						<h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
							<i data-lucide="trending-up" class="w-4 h-4 text-indigo-600"></i>
							<span>B. Curva Canónica del Ciclo de Vida</span>
						</h2>
						<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
							Evolución de ventas, margen realizable y nivel de inventario promedio.
						</p>
					</div>
					<div class="flex items-center gap-1.5 text-[11px] text-slate-500">
						<span class="w-2 h-2 rounded-full bg-blue-500"></span> Ventas
						<span class="w-2 h-2 rounded-full bg-emerald-500 ml-1"></span> Margen %
					</div>
				</div>

				<div class="mt-4 relative h-56">
					<canvas id="chart-plc-lifecycle-curve"></canvas>
				</div>
			</div>

			<div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
				<div class="flex items-center gap-3 text-[11px]">
					<span class="px-1.5 py-0.5 bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 rounded font-semibold">Introducción</span>
					<span>→</span>
					<span class="px-1.5 py-0.5 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 rounded font-semibold">Crecimiento</span>
					<span>→</span>
					<span class="px-1.5 py-0.5 bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 rounded font-semibold">Madurez</span>
					<span>→</span>
					<span class="px-1.5 py-0.5 bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 rounded font-semibold">Declive</span>
				</div>
			</div>
		</div>

	</div>

	<!-- Fila 2: Matriz de Rentabilidad y Rotación (Scatter) + Capital Inmovilizado -->
	<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

		<!-- C. Matriz de Rentabilidad y Rotación (2 columnas) -->
		<div class="lg:col-span-2 bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
			<div>
				<div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
					<div>
						<h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
							<i data-lucide="layout-grid" class="w-4 h-4 text-indigo-600"></i>
							<span>C. Matriz de Rentabilidad y Rotación (Portafolio BCG Retail)</span>
						</h2>
						<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
							Eje X: Rotación de Inventario • Eje Y: Margen Bruto (%) • Tamaño: Valor Inventario ($)
						</p>
					</div>
					<div class="hidden sm:flex items-center gap-2 text-xs">
						<span class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 text-[10px] font-semibold">★ Estrellas</span>
						<span class="px-2 py-0.5 rounded-md bg-red-50 dark:bg-red-950/60 text-red-700 text-[10px] font-semibold">▲ Riesgo</span>
					</div>
				</div>

				<div class="mt-4 relative h-64">
					<canvas id="chart-plc-matrix-scatter"></canvas>
				</div>
			</div>

			<div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
				<span>Haga clic en cualquier burbuja para ver el diagnóstico detallado del producto.</span>
				<span class="text-indigo-600 font-semibold font-mono text-[11px]">Límite de margen: 25% | Rotación mínima: 1.2 veces</span>
			</div>
		</div>

		<!-- D. Capital Inmovilizado y Obsolescencia (1 columna) -->
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
			<div>
				<div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
					<div>
						<h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
							<i data-lucide="lock" class="w-4 h-4 text-red-600"></i>
							<span>D. Capital Inmovilizado</span>
						</h2>
						<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
							Inventario sin rotación segmentado por días en almacén.
						</p>
					</div>
					<span class="text-xs font-mono font-bold text-red-600 dark:text-red-400">$17,215</span>
				</div>

				<!-- Franjas de antigüedad de inventario -->
				<div class="mt-4 space-y-3" id="plc-aging-tiers-container">
					<!-- Tier 1 -->
					<div class="p-3 rounded-lg bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-800/60">
						<div class="flex items-center justify-between text-xs font-semibold">
							<span class="text-amber-900 dark:text-amber-200">60 - 90 Días (Alerta Media)</span>
							<span class="font-mono text-amber-700 dark:text-amber-300">$1,920</span>
						</div>
						<div class="w-full bg-amber-200 dark:bg-amber-900 h-1.5 rounded-full mt-2 overflow-hidden">
							<div class="bg-amber-500 h-full" style="width: 11.2%"></div>
						</div>
						<div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">1 producto afectado (Bebida Chocolatada)</div>
					</div>

					<!-- Tier 2 -->
					<div class="p-3 rounded-lg bg-orange-50/70 dark:bg-orange-950/40 border border-orange-200/60 dark:border-orange-800/60">
						<div class="flex items-center justify-between text-xs font-semibold">
							<span class="text-orange-900 dark:text-orange-200">91 - 180 Días (Riesgo Alto)</span>
							<span class="font-mono text-orange-700 dark:text-orange-300">$5,920</span>
						</div>
						<div class="w-full bg-orange-200 dark:bg-orange-900 h-1.5 rounded-full mt-2 overflow-hidden">
							<div class="bg-orange-500 h-full" style="width: 34.4%"></div>
						</div>
						<div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">1 producto afectado (Margarina Repostera)</div>
					</div>

					<!-- Tier 3 -->
					<div class="p-3 rounded-lg bg-red-50/70 dark:bg-red-950/40 border border-red-200/60 dark:border-red-800/60">
						<div class="flex items-center justify-between text-xs font-semibold">
							<span class="text-red-900 dark:text-red-200">> 180 Días (Obsolescencia Crítica)</span>
							<span class="font-mono text-red-700 dark:text-red-300">$9,375</span>
						</div>
						<div class="w-full bg-red-200 dark:bg-red-900 h-1.5 rounded-full mt-2 overflow-hidden">
							<div class="bg-red-600 h-full" style="width: 54.4%"></div>
						</div>
						<div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">2 productos descontinuados (Galleta, Conserva)</div>
					</div>
				</div>
			</div>

			<div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
				<span class="text-slate-500">Costo almacén: ~$450/mes</span>
				<button onclick="openMarkdownSimulatorModal(12)" class="text-red-600 dark:text-red-400 font-semibold hover:underline">
					Liquidar con rebaja de precio →
				</button>
			</div>
		</div>

	</div>

	<!-- Fila 3: Tendencia de Demanda y Pronósticos + Oportunidades Comerciales -->
	<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

		<!-- E. Tendencia de Demanda Real vs Pronosticada -->
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
			<div>
				<div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
					<div>
						<h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
							<i data-lucide="bar-chart-2" class="w-4 h-4 text-indigo-600"></i>
							<span>E. Demanda Real vs. Pronosticada (Holt-Winters)</span>
						</h2>
						<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
							La demanda estimada considera también los días en que el producto estuvo agotado.
						</p>
					</div>
					<div class="flex items-center gap-2 text-xs font-mono">
						<span class="text-indigo-600 font-semibold">WMAPE: 7.6%</span>
					</div>
				</div>

				<div class="mt-4 relative h-60">
					<canvas id="chart-plc-demand-forecast"></canvas>
				</div>
			</div>

			<div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
				<span>Línea roja punteada: punto de reposición (inventario mínimo + inventario de seguridad)</span>
				<span class="text-emerald-600 font-semibold">92.4% Confiabilidad</span>
			</div>
		</div>

		<!-- F. Oportunidades Comerciales y Recuperación de Capital -->
		<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
			<div>
				<div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
					<div>
						<h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
							<i data-lucide="zap" class="w-4 h-4 text-emerald-600"></i>
							<span>F. Oportunidades Comerciales Prioritarias</span>
						</h2>
						<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
							Impacto económico agregado y decisiones tácticas recomendadas.
						</p>
					</div>
					<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
						+$13,350 Potencial
					</span>
				</div>

				<div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
					<div class="p-3.5 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/60">
						<div class="text-[11px] font-semibold text-emerald-800 dark:text-emerald-300">Recuperación Capital</div>
						<div class="text-xl font-bold font-mono text-emerald-900 dark:text-emerald-100 mt-1">$9,375</div>
						<div class="text-[10px] text-slate-500 mt-1">Con rebajas de precio de 25–30% en productos sin salida</div>
					</div>

					<div class="p-3.5 rounded-xl bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-800/60">
						<div class="text-[11px] font-semibold text-amber-800 dark:text-amber-300">Prevención Quiebre</div>
						<div class="text-xl font-bold font-mono text-amber-900 dark:text-amber-100 mt-1">$7,700</div>
						<div class="text-[10px] text-slate-500 mt-1">Margen salvado en Avena y Mozzarella</div>
					</div>

					<div class="p-3.5 rounded-xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200/60 dark:border-blue-800/60">
						<div class="text-[11px] font-semibold text-blue-800 dark:text-blue-300">Optimización de días de inventario</div>
						<div class="text-xl font-bold font-mono text-blue-900 dark:text-blue-100 mt-1">-5.2 días</div>
						<div class="text-[10px] text-slate-500 mt-1">Reducción de costos financieros</div>
					</div>
				</div>

				<!-- Síntesis de las 5 preguntas estratégicas -->
				<div class="mt-4 p-3 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800 text-xs space-y-1.5">
					<div class="font-semibold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
						<i data-lucide="compass" class="w-3.5 h-3.5 text-indigo-500"></i>
						<span>Respuestas Estratégicas para Gerencia:</span>
					</div>
					<div class="text-[11px] text-slate-600 dark:text-slate-400 pl-5 space-y-1">
						<div>• <strong>Demanda:</strong> Avena Orgánica (+28%) y Queso Barra (+25%) lideran crecimiento; Chocolatada cae (-26%).</div>
						<div>• <strong>Reposición:</strong> Emitir orden de compra de 1,800 unidades de avena y 600 de queso para cubrir 10 días de inventario.</div>
						<div>• <strong>Capital atrapado:</strong> $9,375 en Galletas y Conservas con >180 días sin movimiento.</div>
						<div>• <strong>Liquidación:</strong> Aprobar markdown escalonado del 25% para recuperar $4,200 en 45 días.</div>
					</div>
				</div>
			</div>

			<div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
				<span>Evaluación algorítmica explicable</span>
				<span class="text-indigo-600 font-semibold">Decisiones Auditadas</span>
			</div>
		</div>

	</div>

	<!-- ========================================================================= -->
	<!-- MOTOR DE RECOMENDACIONES COMERCIALES EXPLICABLES (REGLAS DE NEGOCIO)     -->
	<!-- ========================================================================= -->
	<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs space-y-4">
		<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
			<div>
				<h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
					<i data-lucide="shield-alert" class="w-4 h-4 text-indigo-600"></i>
					<span>Motor de Recomendaciones Comerciales y Reposición</span>
				</h2>
				<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
					Sugerencias algorítmicas con evidencia demostrable, cálculo de impacto económico y flujo de autorización directiva.
				</p>
			</div>

			<!-- Filtro de estado de recomendaciones -->
			<div class="flex items-center gap-2">
				<button onclick="filterRecommendations('ALL')" id="plc-rec-btn-ALL" class="px-2.5 py-1 text-xs font-semibold rounded-md bg-indigo-100 dark:bg-indigo-900/60 text-indigo-800 dark:text-indigo-300">Todas</button>
				<button onclick="filterRecommendations('pending')" id="plc-rec-btn-pending" class="px-2.5 py-1 text-xs font-medium rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Pendientes</button>
				<button onclick="filterRecommendations('approved')" id="plc-rec-btn-approved" class="px-2.5 py-1 text-xs font-medium rounded-md text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Aprobadas</button>
			</div>
		</div>

		<!-- Lista interactiva de tarjetas de recomendación explicable -->
		<div class="space-y-3" id="plc-recommendations-list">
			<!-- Renderizado dinámico vía JavaScript -->
			<div class="text-center py-6 text-xs text-slate-400">Cargando recomendaciones del motor de inteligencia...</div>
		</div>
	</div>

</div>

<!-- ========================================================================= -->
<!-- MODALES INTERACTIVOS: DIAGNÓSTICO SKU, SIMULADOR MARKDOWN, IMPORTADOR     -->
<!-- ========================================================================= -->

<!-- 1. MODAL DE DIAGNÓSTICO DETALLADO DE SKU -->
<div id="plc-modal-sku-drilldown" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
	<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl max-w-3xl w-full p-6 shadow-2xl relative">
		<button onclick="closeSkuDrilldownModal()" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
			<i data-lucide="x" class="w-5 h-5"></i>
		</button>
		
		<div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
			<div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 flex items-center justify-center font-bold">
				<i data-lucide="package-search" class="w-5 h-5"></i>
			</div>
			<div>
				<div class="flex items-center gap-2">
					<h3 class="text-lg font-bold text-slate-900 dark:text-white" id="modal-sku-title">Leche Entera UHT 1 L</h3>
					<span id="modal-sku-stage-badge" class="px-2 py-0.5 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">Madurez</span>
				</div>
				<p class="text-xs text-slate-500 font-mono" id="modal-sku-code">SKU-LAC-001 • Gloria • Lácteos</p>
			</div>
		</div>

		<div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs" id="modal-sku-metrics-grid">
			<div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/50">
				<span class="text-slate-400 block">Ventas del mes actual</span>
				<span class="text-base font-bold text-slate-900 dark:text-white mt-1 block" id="modal-sku-sales">$21,250</span>
				<span class="text-[10px] text-slate-500" id="modal-sku-units">8,500 unidades</span>
			</div>
			<div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/50">
				<span class="text-slate-400 block">Margen Bruto</span>
				<span class="text-base font-bold text-emerald-600 mt-1 block" id="modal-sku-margin">26.0%</span>
				<span class="text-[10px] text-slate-500">Costo de los productos: $15,725</span>
			</div>
			<div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/50">
				<span class="text-slate-400 block">Inventario Actual</span>
				<span class="text-base font-bold text-slate-900 dark:text-white mt-1 block" id="modal-sku-stock">5,200 uds</span>
				<span class="text-[10px] text-slate-500" id="modal-sku-inv-value">$9,620 a costo</span>
			</div>
			<div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/50">
				<span class="text-slate-400 block">Días de inventario</span>
				<span class="text-base font-bold text-indigo-600 mt-1 block" id="modal-sku-dio">22.0 días</span>
				<span class="text-[10px] text-slate-500">Rotación: 1.63x</span>
			</div>
		</div>

		<!-- Justificación explicable de la clasificación -->
		<div class="mt-4 p-3.5 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-200/60 dark:border-indigo-800/60 text-xs">
			<div class="font-semibold text-indigo-900 dark:text-indigo-200 flex items-center gap-1.5">
				<i data-lucide="check-circle-2" class="w-4 h-4 text-indigo-600"></i>
				<span>Diagnóstico Algorítmico Explicable:</span>
			</div>
			<p class="text-slate-600 dark:text-slate-300 mt-1 leading-relaxed" id="modal-sku-reason">
				Ventas consolidadas de alta estabilidad (+1.8% intermensual). Días de inventario alineados al ciclo de compra semanal. Confiabilidad estadística: 98%.
			</p>
		</div>

		<div class="mt-5 flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
			<button onclick="closeSkuDrilldownModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
				Cerrar
			</button>
			<button onclick="openMarkdownSimulatorFromCurrentSku()" id="modal-sku-btn-markdown" class="px-4 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white">
				Simular Descuento
			</button>
		</div>
	</div>
</div>

<!-- 2. MODAL SIMULADOR DE MARKDOWN / LIQUIDACIÓN -->
<div id="plc-modal-markdown" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
	<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl max-w-2xl w-full p-6 shadow-2xl relative">
		<button onclick="closeMarkdownModal()" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
			<i data-lucide="x" class="w-5 h-5"></i>
		</button>

		<div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
			<div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center font-bold">
				<i data-lucide="percent" class="w-5 h-5"></i>
			</div>
			<div>
				<h3 class="text-lg font-bold text-slate-900 dark:text-white">Simulador de rebajas de precio y liquidación</h3>
				<p class="text-xs text-slate-500">Evalúa el impacto en margen, unidades por elasticidad y capital recuperable.</p>
			</div>
		</div>

		<!-- Selector de producto y controles de descuento -->
		<div class="mt-4 space-y-4">
			<div>
				<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Seleccionar SKU a Liquidar / Promocionar:</label>
				<select id="sim-product-select" onchange="runMarkdownSimulation()" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-2 text-xs text-slate-900 dark:text-white">
					<option value="12">SKU-GAL-030 — Galleta Salada Tradicional Pack 6x (2,100 uds en salida)</option>
					<option value="10">SKU-BEB-019 — Bebida Láctea Chocolatada 200ml (3,200 uds en declive)</option>
					<option value="11">SKU-GRA-022 — Margarina Repostera 1kg (1,850 uds)</option>
					<option value="13">SKU-CON-042 — Conserva Filete Caballa 170g (1,650 uds)</option>
				</select>
			</div>

			<!-- Botones de Descuento Predefinido -->
			<div>
				<label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Porcentaje de Descuento:</label>
				<div class="flex flex-wrap gap-2">
					<button onclick="setSimDiscount(5)" id="sim-btn-5" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">5%</button>
					<button onclick="setSimDiscount(10)" id="sim-btn-10" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">10%</button>
					<button onclick="setSimDiscount(15)" id="sim-btn-15" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">15%</button>
					<button onclick="setSimDiscount(20)" id="sim-btn-20" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">20%</button>
					<button onclick="setSimDiscount(25)" id="sim-btn-25" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 text-white shadow-xs">25% (Recomendado)</button>
					<button onclick="setSimDiscount(30)" id="sim-btn-30" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">30%</button>
				</div>
			</div>

			<!-- Resultados de Simulación en Tiempo Real -->
			<div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-800 space-y-3">
				<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
					<div>
						<span class="text-slate-400 block">Precio Propuesto</span>
						<span class="text-base font-bold text-slate-900 dark:text-white mt-0.5 block" id="sim-res-price">$2.25</span>
						<span class="text-[10px] text-slate-500 line-through" id="sim-res-orig-price">$3.00 original</span>
					</div>
					<div>
						<span class="text-slate-400 block">Margen Unitario</span>
						<span class="text-base font-bold text-emerald-600 mt-0.5 block" id="sim-res-unit-margin">$0.25/ud</span>
						<span class="text-[10px] text-slate-500" id="sim-res-cost">Costo: $2.00</span>
					</div>
					<div>
						<span class="text-slate-400 block">Elasticidad Supuesta</span>
						<span class="text-base font-bold text-slate-900 dark:text-white mt-0.5 block">1.4x</span>
						<span class="text-[10px] text-slate-500">+35% volumen est.</span>
					</div>
					<div>
						<span class="text-slate-400 block">Capital a Recuperar</span>
						<span class="text-base font-bold font-mono text-indigo-600 dark:text-indigo-400 mt-0.5 block" id="sim-res-recov-capital">$4,200</span>
						<span class="text-[10px] text-slate-500">Liquidez inmediata</span>
					</div>
					<div>
						<span class="text-slate-400 block">Pérdida Margen Potencial</span>
						<span class="text-base font-bold font-mono text-red-600 mt-0.5 block" id="sim-res-margin-loss">-$1,400</span>
						<span class="text-[10px] text-slate-500">Sacrificio vs lista</span>
					</div>
					<div>
						<span class="text-slate-400 block">Punto de Equilibrio</span>
						<span class="text-base font-bold font-mono text-slate-900 dark:text-white mt-0.5 block" id="sim-res-breakeven">1,120 uds</span>
						<span class="text-[10px] text-slate-500">Para empatar $ margen</span>
					</div>
				</div>
			</div>
		</div>

		<div class="mt-5 flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
			<button onclick="closeMarkdownModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
				Cancelar
			</button>
			<button onclick="saveMarkdownScenarioSubmit()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-brand-700 hover:bg-brand-800 text-white shadow-xs">
				Guardar para Aprobación
			</button>
		</div>
	</div>
</div>

<!-- 3. MODAL ASISTENTE DE IMPORTACIÓN CSV / EXCEL -->
<div id="plc-modal-import" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
	<div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-2xl max-w-2xl w-full p-6 shadow-2xl relative">
		<button onclick="closeDataImportModal()" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
			<i data-lucide="x" class="w-5 h-5"></i>
		</button>

		<div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
			<div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center font-bold">
				<i data-lucide="upload-cloud" class="w-5 h-5"></i>
			</div>
			<div>
				<h3 class="text-lg font-bold text-slate-900 dark:text-white">Asistente de Importación de Portafolio</h3>
				<p class="text-xs text-slate-500">Carga archivos Excel o CSV con validación de columnas e importación idempotente.</p>
			</div>
		</div>

		<div class="mt-4 space-y-4">
			<!-- Zona de arrastre de archivo -->
			<div class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-6 text-center hover:border-indigo-400 transition-colors">
				<i data-lucide="file-spreadsheet" class="w-10 h-10 text-slate-400 mx-auto mb-2"></i>
				<div class="text-xs font-semibold text-slate-800 dark:text-slate-200">Arrastra tu archivo CSV o Excel aquí</div>
				<p class="text-[11px] text-slate-500 mt-1">Formato admitido: SKU, Nombre, Categoría, Ventas, Costo, Stock</p>
				<button onclick="loadSampleCsvData()" class="mt-3 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 border border-indigo-200">
					Cargar archivo de ejemplo
				</button>
			</div>

			<!-- Vista previa de datos -->
			<div id="import-preview-box" class="hidden space-y-2">
				<div class="flex items-center justify-between text-xs font-semibold text-slate-700 dark:text-slate-300">
					<span>Vista Previa de Filas Detectadas (Idempotente):</span>
					<span id="import-preview-count" class="text-indigo-600 font-mono">3 filas válidas</span>
				</div>
				<div class="max-h-40 overflow-y-auto border border-slate-200 dark:border-slate-700 rounded-lg text-xs">
					<table class="w-full text-left">
						<thead class="bg-slate-50 dark:bg-slate-800 text-[11px] text-slate-500">
							<tr>
								<th class="p-2">SKU</th>
								<th class="p-2">Producto</th>
								<th class="p-2">Ventas ($)</th>
								<th class="p-2">Stock</th>
								<th class="p-2 text-center">Estado</th>
							</tr>
						</thead>
						<tbody id="import-preview-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
							<!-- Filas inyectadas -->
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div class="mt-5 flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
			<button onclick="closeDataImportModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">
				Cerrar
			</button>
			<button onclick="confirmExecuteImport()" id="btn-execute-import" disabled class="px-4 py-2 text-xs font-semibold rounded-lg bg-brand-700 text-white disabled:opacity-50 disabled:cursor-not-allowed">
				Confirmar e Importar
			</button>
		</div>
	</div>
</div>
